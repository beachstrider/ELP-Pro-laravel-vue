<?php

namespace App\Http\Controllers\Api;

use App\Domain\Services\DropdownService;
use App\Http\Controllers\CrudController;

class DropdownController extends CrudController
{
    /**
     * @var DropdownService
     */
    public $service;

    public function __construct(DropdownService $dropdownService)
    {
        $this->service = $dropdownService;
    }

    public function postPermissions()
    {
        return response()->json([
            "data" => $this->service->permissions()
        ]);
    }

    public function postRoles()
    {
        return response()->json([
            "data" => $this->service->roles()
        ]);
    }

    public function postBrands()
    {
        return response()->json([
            "data" => $this->service->brands()
        ]);
    }

    public function postLocationTypes()
    {
        return response()->json([
            "data" => $this->service->locationTypes()
        ]);
    }

    public function postCountries()
    {
        return response()->json([
            "data" => $this->service->countries()
        ]);
    }

    public function postSuppliers()
    {
        return response()->json([
            "data" => $this->service->suppliers()
        ]);
    }

    public function postSuppliersByType($slug)
    {
        return response()->json([
            "data" => $this->service->suppliersByType($slug)
        ]);
    }

    public function postTransportVehiclesTypes()
    {
        return response()->json([
            "data" => $this->service->transportVehiclesTypes()
        ]);
    }

    public function postDealers()
    {
        return response()->json([
            "data" => $this->service->dealers()
        ]);
    }

    public function postLocations()
    {
        return response()->json([
            "data" => $this->service->locations()
        ]);
    }

    public function postContacts()
    {
        return response()->json([
            "data" => $this->service->contacts()
        ]);
    }

    public function postModels()
    {
        return response()->json([
            "data" => $this->service->models()
        ]);
    }

    public function postLogisticTypes()
    {
        return response()->json([
            "data" => $this->service->logisticTypes()
        ]);
    }

    public function postRoutes()
    {
        return response()->json([
            "data" => $this->service->routes()
        ]);
    }

    public function postContracts()
    {
        return response()->json([
            "data" => $this->service->contracts()
        ]);
    }

    public function postSupplierTypes()
    {
        return response()->json([
            "data" => $this->service->supplierTypes()
        ]);
    }
}
