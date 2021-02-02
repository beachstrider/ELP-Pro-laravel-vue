<?php

namespace App\Http\Controllers;

use App\Domain\Services\BaseService;
use App\Http\Resources\CrudResource;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CrudController extends Controller
{
    /**
     * @var BaseService
     */
    protected $service;

    public $resource = CrudResource::class;

    public $resourceCollection;

    public function getList(Request $request)
    {
        $input = $request->all();
        $input['author_id'] = auth('api')->id();
        $input['author_role'] = (auth()->check()) ? optional(auth('api')->user()->roles()->first())->name : null;
        $entities = $this->service->getList($input);

        return $this->getResourceCollection($entities);
    }

    protected function validateInput(Request $request, $id = null)
    : array
    {
        return [];
    }

    public function postCreate(Request $request)
    {
        $validatedData = $this->validateInput($request);
        $entity = $this->service->create($validatedData);
        return $this->getResource($entity);
    }

    public function postUpdate(Request $request)
    {
        $id = $request->input('id');
        $validatedData = $this->validateInput($request, $id);
        $entity = $this->service->update($id, $validatedData);
        return $this->getResource($entity);
    }

    public function getDetail(Request $request, $id)
    {
        $entity = $this->service->getDetail($id, $request->all());

        return $this->getResource($entity);
    }

    public function postDelete(Request $request)
    {
        return response()->json(['success' => $this->service->delete($request->input('id'), $request->all())]);
    }

    protected function getResourceCollection($entities)
    {
        if ($this->resourceCollection) {
            return new $this->resourceCollection($entities);
        }

        return $this->resource::collection($entities);
    }

    protected function getResource($entity)
    {
        return new $this->resource($entity);
    }

    public function postUpdateByGuid(Request $request)
    {
        $id = $request->input('id');
        $validatedData = $this->validateInput($request, $id);
        $entity = $this->service->updateByGuid($id, $validatedData);
        return $this->getResource($entity);
    }

    public function getDetailByGuid(Request $request, $id)
    {
        $entity = $this->service->getDetailByGuid($id, $request->all());

        return $this->getResource($entity);
    }

    public function postDeleteByGuid(Request $request)
    {
        try{
            $validated = $request->all();
            $validated['author_id'] = auth()->user()->id;
            $this->service->deleteByGuid($request->input('id'), $request->all());
        } catch(ValidationException $e) {
            return response()->json([
                'message' => __('validation.delete_dependency')
            ], 422);
        } catch(Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }

        return response()->json([
            'success' => true
        ]);
    }
}
