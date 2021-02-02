<?php

namespace App\Domain\Services;

use App\Domain\Models\Brand;
use App\Domain\Models\BrandModel;
use App\Domain\Models\Contact;
use App\Domain\Models\Contract;
use App\Domain\Models\Dealer;
use App\Domain\Models\Location;
use App\Domain\Models\LocationType;
use App\Domain\Models\Supplier;
use App\Domain\Models\SupplierType;
use App\Domain\Models\LogisticType;
use App\Domain\Models\Route;
use Illuminate\Support\Facades\DB;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class DropdownService
{
    public function permissions()
    {
        return Permission::query()->select([
            'id',
            'name',
        ])
            ->get();
    }

    public function roles()
    {
        return Role::query()->select([
            'id',
            'name',
        ])
            ->get();
    }

    public function brands()
    {
        return Brand::query()->select([
            'guid',
            'title',
            'is_active',
        ])
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->guid,
                    'label' => $item->title,
                    'is_active' => $item->is_active,
                ];
            });
    }

    public function locationTypes()
    {
        return LocationType::query()->select([
            'guid',
            'title',
            'is_active',
        ])
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->guid,
                    'label' => $item->title,
                    'is_active' => $item->is_active,
                ];
            });
    }

    public function countries()
    {
        return DB::table('countries')->select([
            'name',
            'sort_name',
            'is_active',
        ])
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->name,
                    'label' => $item->name,
                    'is_active' => $item->is_active,
                    'sort_name' => $item->sort_name,
                ];
            });
    }

    public function suppliers()
    {
        return Supplier::select([
            'guid',
            'name',
        ])
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->guid,
                    'label' => $item->name,
                ];
            });
    }

    public function suppliersByType($slug = null)
    {
        $query = Supplier::select(['guid', 'name']);

        if($slug != 'all') {
            $query = $query->whereHas('supplierUserTypes', function ($query) use ($slug) {
                $query->whereIn('slug', [$slug]);
            });
        }

        return $query->get()
            ->map(function ($item) {
                return [
                    'id' => $item->guid,
                    'label' => $item->name,
                ];
            });
    }

    public function transportVehiclesTypes()
    {
        return [[
            'id' => 'air',
            'label' => 'Air',
        ], [
            'id' => 'land',
            'label' => 'Land',
        ], [
            'id' => 'rail',
            'label' => 'Rail',
        ], [
            'id' => 'road',
            'label' => 'Road',
        ], [
            'id' => 'water',
            'label' => 'Water',
        ], [
            'id' => 'other means',
            'label' => 'Other Means',
        ]];
    }

    public function dealers()
    {
        return Dealer::select([
            'guid',
            'name',
        ])
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->guid,
                    'label' => $item->name,
                    'is_active' => (!($item->email_verified_at)),
                ];
            });
    }

    public function locations()
    {
        return Location::select([
            'guid',
            'street', 'street_no', 'zip', 'city', 'country',
        ])
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->guid,
                    'label' => "{$item->street} {$item->street_no} {$item->city} {$item->zip} {$item->country}",
                    'street' => $item->street,
                    'street_no' => $item->street_no,
                    'zip' => $item->zip,
                    'city' => $item->city,
                    'country' => $item->country,
                ];
            });
    }

    public function contacts()
    {
        return Contact::select([
            'guid',
            'name', 'functions', 'phone', 'email'
        ])
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->guid,
                    'label' => $item->name,
                    'functions' => $item->functions,
                    'phone' => $item->phone,
                    'email' => $item->email,
                ];
            });
    }

    public function models()
    {
        return BrandModel::query()
            ->whereHas('brand')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->guid,
                    'label' => $item->title,
                    'is_active' => $item->is_active,
                    'brand_id' => $item->brand->guid,
                    'type' => $item->type,
                ];
            });
    }

    public function routes()
    {
        return Route::select([
            'guid',
            'name'
        ])
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->guid,
                    'label' => $item->name,
                ];
            });
    }

    public function logisticTypes()
    {
        return LogisticType::select([
            'guid',
            'title'
        ])
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->guid,
                    'label' => $item->title,
                ];
            });
    }

    public function contracts()
    {
        return Contract::select([
            'guid',
            'title'
        ])
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->guid,
                    'label' => $item->title,
                ];
            });
    }

    public function supplierTypes()
    {
        return SupplierType::select([
            'id',
            'title',
            'slug',
        ])
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'label' => $item->title,
                    'slug' => $item->slug,
                ];
            });
    }
}


