<?php

namespace App\Domain\Services;

use App\Domain\Models\Document;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentService extends BaseService
{
    /**
     * @var Document
     */
    public $model = Document::class;

    public $searchColumns = [];

    public $filterColumns = [];

    public $with = [];

    protected $primaryKey = 'id';

    protected $guidKey = 'guid';

    public function create(array $input)
    {
        $file = $input['file'];
        $disk = $input['disk'];
        $path = $input['path'];
        $newname = time() . str_replace('-', '', guid()) . '.' . $file->getClientOriginalExtension();
        $this->makeDir($disk, $path);
        $path = Storage::disk($disk)->putFileAs($path, $file, $newname);

        $upload = self::store([
            'creator_id' => $input['creator_id'],
            'object_type' => $input['object_type'],
            'object_id' => (isset($input['object_id']) ? $input['object_id'] : null),
            'disk' => $disk,
            'path' => $path,
            'newname' => $newname,
            'filename' => $file->getClientOriginalName(),
            'mimetype' => $file->getMimeType(),
            'size' => $file->getClientSize(),
            'header' => $file->getClientMimeType(),
            'dummy_name' => str_replace('-','', guid()).'.'.$file->getClientOriginalExtension(),
            'extension' => $file->getClientOriginalExtension(),
        ]);

        return $upload;
    }

    public static function store(array $rInputs)
    {
        $upload = new Document();
        $upload->creator_id = $rInputs['creator_id'];
        $upload->object_type = $rInputs['object_type'];
        $upload->object_id = $rInputs['object_id'];
        $upload->name = $rInputs['newname'];
        $upload->disk = $rInputs['disk'];
        $upload->path = $rInputs['path'];
        $upload->filename = $rInputs['filename'];
        $upload->mimetype = $rInputs['mimetype'];
        $upload->size = $rInputs['size'];
        $upload->header = $rInputs['header'];
        $upload->dummy_name = $rInputs['dummy_name'];
        $upload->extension = $rInputs['extension'];
        $upload->save();

        return $upload;
    }

    public function makeDir($disk, $path)
    {
        $fPath = Storage::disk($disk)->path($path);

        if (!File::exists($fPath)) {
            File::makeDirectory($fPath, 0775, true);
        }

        return true;
    }

    public function deleteByGuid($id, array $input = [])
    {
        $entity = $this->model::query()->where('id', $id)->first();

        if ($entity)
            return $entity->delete();

        return false;
    }
}
