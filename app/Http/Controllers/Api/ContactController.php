<?php

namespace App\Http\Controllers\Api;

use App\Domain\Services\ContactService;
use App\Http\Controllers\CrudController;
use App\Http\Resources\ContactResource;
use App\Rules\ValidateTag;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ContactController extends CrudController
{
    /**
     * @var ContactService
     */
    public $service;

    public $resource = ContactResource::class;

    public function __construct(ContactService $service)
    {
        $this->service = $service;
    }

    /**
     * @param Request $request
     * @param null $id
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateInput(Request $request, $id = null)
    : array
    {
        $rules = [
            'email' => ['required', 'email', 'max:191', new ValidateTag()],
            'first_name' => ['required', 'max:191', new ValidateTag()],
            'last_name' => ['max:191', new ValidateTag()],
            'phone' => ['required', 'max:191', new ValidateTag()],
            'mobile' => ['required', 'max:191', new ValidateTag()],
            'functions' => [new ValidateTag(), 'max:191'],
        ];

        if (!$id) { // create
            $validated['author_id'] = auth()->id();

            if (!auth()->user()->hasRole('superadmin')) {
                $validated['user_id'] = auth()->id();
            }
        }

        $validated = $this->validate($request, $rules);
        $validated['name'] = $validated['first_name'] .' '. $validated['last_name'];

        if($this->service->hasContact($validated, $id)) {
            throw ValidationException::withMessages(['first_name' => __('validation.duplicate')]);
        }

        return $validated;
    }
}
