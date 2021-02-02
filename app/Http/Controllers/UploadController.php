<?php

namespace App\Http\Controllers;

use App\Domain\Models\Document;
use App\Domain\Models\User;
use App\Domain\Services\DocumentService;
use App\Http\Resources\DocumentResource;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Pion\Laravel\ChunkUpload\Exceptions\UploadMissingFileException;
use Pion\Laravel\ChunkUpload\Handler\AbstractHandler;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;
use Image;

class UploadController extends Controller
{
    public static $rules = [
        'image' => 'required|mimes:jpeg,jpg,png',
        'doc' => 'required|mimes:doc,docx,xls,xlsx,pdf,jpeg,jpg,png,rar,zip',
        'default' => 'required',
    ];

    public function upload(Request $request)
    {
        // create the file receiver
        $receiver = new FileReceiver("file", $request, HandlerFactory::classFromRequest($request));

        // check if the upload is success, throw exception or return response you need
        if ($receiver->isUploaded() === false) {
            throw new UploadMissingFileException();
        }

        // receive the file
        $save = $receiver->receive();

        // check if the upload has finished (in chunk mode it will send smaller files)
        if ($save->isFinished()) {
            // save the file and return any response you need, current example uses `move` function. If you are
            // not using move, you need to manually delete the file by unlink($save->getFile()->getPathname())
            $response = $this->saveFile($save->getFile());
            unlink($save->getFile()->getPathname());
            return $response;
        }

        // we are in chunk mode, lets send the current progress
        /** @var AbstractHandler $handler */
        $handler = $save->handler();

        return response()->json([
            "done" => $handler->getPercentageDone(),
            'status' => true
        ]);
    }

    /**
     * Saves the file to S3 server
     *
     * @param UploadedFile $file
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function saveFileToS3($file)
    {
        $fileName = $this->createFilename($file);

        $disk = Storage::disk('s3');
        // It's better to use streaming Streaming (laravel 5.4+)
        $disk->putFileAs('photos', $file, $fileName);

        // for older laravel
        // $disk->put($fileName, file_get_contents($file), 'public');
        $mime = str_replace('/', '-', $file->getMimeType());

        // We need to delete the file when uploaded to s3
        unlink($file->getPathname());

        return response()->json([
            'path' => $disk->url($fileName),
            'name' => $fileName,
            'mime_type' => $mime
        ]);
    }

    /**
     * Saves the file
     *
     * @param UploadedFile $file
     *
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    protected function saveFile(UploadedFile $file)
    {
        $user = User::findOrFailByGuid(request()->header('X-Content-Token'));
        $disk = 'public';
        $path = config('sattviki.file_structure.documents');
        $newname = $this->createFilename($file);

        $this->makeDir($disk, $path);
        $path = Storage::disk($disk)->putFileAs($path, $file, $newname);

        $entity = $this->createDocument($file, $newname, $path, $disk, $user);

        if (request()->has('thum')) {
            $this->postThumbnailImage($disk, $entity, $file);
        }

        return new DocumentResource($entity);
    }

    /**
     * Create unique filename for uploaded file
     * @param UploadedFile $file
     * @return string
     */
    protected function createFilename(UploadedFile $file)
    {
        $extension = $file->getClientOriginalExtension();

        $filename = time() . str_replace('-', '', guid()) . "." . $extension;

        return $filename;
    }

    private function createDocument(UploadedFile $file, $newname, $path, $disk, $user)
    {
        return DocumentService::store([
            'creator_id' => $user->id,
            'object_type' => 'miscellaneous',
            'object_id' => null,
            'newname' => $newname,
            'disk' => $disk,
            'path' => $path,
            'filename' => $file->getClientOriginalName(),
            'mimetype' => $file->getMimeType(),
            'size' => $file->getSize(),
            'header' => $file->getClientMimeType(),
            'dummy_name' => str_replace('-', '', guid()) . '.' . $file->getClientOriginalExtension(),
            'extension' => $file->getClientOriginalExtension(),
        ]);
    }

    private function makeDir($disk, $path)
    {
        $fPath = Storage::disk($disk)->path($path);

        if (!File::exists($fPath)) {
            File::makeDirectory($fPath, 0775, true);
        }

        return true;
    }

    public function postThumbnailImage($disk, Document $entity, $file)
    {
        if (!isset(config('sattviki.thumbnail')[request()->input('thum')])) {
            return;
        }

        $thumbnailPath = config('sattviki.file_structure.thumbnail');
        $thumbnailSizes = config('sattviki.thumbnail')[request()->input('thum')];
        if ($file) {
            $oImg = Image::make($file->getRealPath());
            foreach ($thumbnailSizes['sizes'] as $key => $value) {
                $img = clone $oImg;
                $width = explode('x', $value)[0];
                $height = explode('x', $value)[1];

                if ($thumbnailSizes['watermark']) {
                    $watermark = Image::make(public_path('water_mark_image/watermark.png'));
                    $watermark->resize($width, $height);
                }

                $img->resize($width, $height);

                if ($thumbnailSizes['watermark']) {
                    $img->insert($watermark, 'center');
                }

                $img->stream();
                $newPath = $thumbnailPath . "/" . $key;
                $this->makeDir($disk, $thumbnailPath . "/" . $key);
                $fileName = $entity->dummy_name;
                Storage::disk($disk)->put($newPath . '/' . $fileName, $img);

                $img = null;
                $watermark = null;
            }
        }
    }
}
