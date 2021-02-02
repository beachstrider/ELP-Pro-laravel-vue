<?php

namespace App\Domain\Services;

use App\Domain\Models\BrandModel;
use App\Domain\Models\Manufacturer;
use App\Domain\Models\Document;
use App\Domain\Models\ManufacturerBrand;
use App\Domain\Models\ManufacturerContact;
use App\Domain\Models\ManufacturerLocation;
use App\Domain\Models\ManufacturerDocument;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ManufacturerService extends BaseService
{
    /**
     * @var Manufacturer
     */
    public $model = Manufacturer::class;

    /**
     * @var ManufacturerContact
     */
    public $modelManufacturerContact = ManufacturerContact::class;

    /**
     * @var ManufacturerBrand
     */
    public $modelManufacturerBrand = ManufacturerBrand::class;

    /**
     * @var ManufacturerLocation
     */
    public $modelManufacturerLocation = ManufacturerLocation::class;

    /**
     * @var ManufacturerDocument
     */
    public $modelManufacturerDocument = ManufacturerDocument::class;

    public $searchColumns = ['name', 'first_name', 'last_name', 'phone', 'email', 'fax'];

    public $filterColumns = ['start_date', 'end_date'];

    public $with = ['mainLocation'];

    public $detailWith = [
        'mainLocation', 'manufacturerContacts', 'manufacturerContacts.contact', 'manufacturerContacts.locations',
        'manufacturerBrands', 'manufacturerBrands.brand', 'manufacturerBrands.models',
        'manufacturerLocations', 'manufacturerLocations.location', 'manufacturerLocations.supplierReleaseAgent', 'manufacturerLocations.supplierTransportations',
        'manufacturerLocations.brands', 'manufacturerLocations.brandModels', 'manufacturerLocations.locationType', 'manufacturerDocuments', 'manufacturerDocuments.document',
    ];

    protected $primaryKey = 'id';

    protected $guidKey = 'guid';

    protected function filterQuery(Builder $query, $input = [])
    {
        foreach (Arr::get($input, 'filters', []) as $column => $value) {
            if (($value && in_array($column, $this->filterColumns))) {
                if ((in_array($column, ['start_date', 'end_date']))) {
                    $query->where(function ($q) use ($column, $value) {
                        if ($column === 'start_date' && !empty($value)) {
                            $q->whereDate('start_date', '>=', $value);
                        }

                        if ($column === 'end_date' && !empty($value)) {
                            $q->whereDate('end_date', '<=', $value);
                        }
                    });
                } else {
                    $query->where($column, $value);
                }
            }
        }

        return $query;
    }

    public function create(array $input)
    {
        DB::beginTransaction();
        $entity = $this->model::create(Arr::except($input, ['manufacturer_contacts', 'manufacturer_brands', 'manufacturer_documents']));
        $this->addUpdateManufacturerContact($input, $entity);
        $this->addUpdateManufacturerBrand($input, $entity);
        $this->addUpdateManufacturerLocation($input, $entity);
        $this->addUpdateManufacturerDocument($input, $entity);
        DB::commit();

        return $entity;
    }

    public function updateByGuid($id, array $input)
    {
        DB::beginTransaction();
        $entity = $this->model::query()->findOrFailByGuid($id);
        $entity->update(Arr::except($input, ['manufacturer_contacts', 'manufacturer_brands']));
        $this->addUpdateManufacturerContact($input, $entity);
        $this->addUpdateManufacturerBrand($input, $entity);
        $this->addUpdateManufacturerLocation($input, $entity);
        $this->addUpdateManufacturerDocument($input, $entity);
        DB::commit();
        return $this->getDetailByGuid($id, []);
    }

    private function addUpdateManufacturerContact($input, $manufacturer)
    {
        $this->modelManufacturerContact::query()->where('manufacturer_id', $manufacturer->id)->delete();

        foreach ($input['manufacturer_contacts'] as $key => $value) {
            $manufacturerContact = null;
            if (isset($value['id']) && !blank($value['id'])) {
                $manufacturerContact = $this->modelManufacturerContact::query()->withTrashed()->where('id', $value['id'])->first();
                $manufacturerContact->deleted_at = null;
            }

            if (empty($manufacturerContact) || is_null($manufacturerContact)) {
                $manufacturerContact = new $this->modelManufacturerContact();
            }

            $manufacturerContact->manufacturer_id = $manufacturer->id;
            $manufacturerContact->contact_id = $value['contact_id'];
            $manufacturerContact->save();

            $manufacturerContact->locations()->sync($value['locations']);
            DB::table('manufacturer_contact_locations')->where('manufacturer_contact_id', $manufacturerContact->id)->update(['manufacturer_id' => $manufacturer->id]);
        }
    }

    private function addUpdateManufacturerBrand($input, $manufacturer)
    {
        $this->modelManufacturerBrand::query()->where('manufacturer_id', $manufacturer->id)->delete();

        foreach ($input['manufacturer_brands'] as $key => $value) {
            $manufacturerBrand = null;
            if (isset($value['id']) && !blank($value['id'])) {
                $manufacturerBrand = $this->modelManufacturerBrand::query()->withTrashed()->where('id', $value['id'])->first();
                $manufacturerBrand->deleted_at = null;
            }

            if (empty($manufacturerBrand) || is_null($manufacturerBrand)) {
                $manufacturerBrand = new $this->modelManufacturerBrand();
            }

            $manufacturerBrand->manufacturer_id = $manufacturer->id;
            $manufacturerBrand->brand_id = $value['brand_id'];
            $manufacturerBrand->save();

            $manufacturerBrand->models()->sync($value['models']);
            DB::table('manufacturer_brand_models')->where('manufacturer_brand_id', $manufacturerBrand->id)->update(['manufacturer_id' => $manufacturer->id]);
        }
    }

    private function addUpdateManufacturerLocation($input, $manufacturer)
    {
        $this->modelManufacturerLocation::query()->where('manufacturer_id', $manufacturer->id)->delete();

        foreach ($input['manufacturer_locations'] as $key => $value) {
            $manufacturerLocation = null;
            if (isset($value['id']) && !blank($value['id'])) {
                $manufacturerLocation = $this->modelManufacturerLocation::query()->withTrashed()->where('id', $value['id'])->first();
                $manufacturerLocation->deleted_at = null;
            }

            if (empty($manufacturerLocation) || is_null($manufacturerLocation)) {
                $manufacturerLocation = new $this->modelManufacturerLocation();
            }

            $manufacturerLocation->manufacturer_id = $manufacturer->id;
            $manufacturerLocation->location_type_id = $value['location_type_id'];
            $manufacturerLocation->location_id = $value['location_id'];
            $manufacturerLocation->supplier_id = $value['supplier_id'];
            $manufacturerLocation->save();

            $manufacturerLocation->supplierTransportations()->sync($value['suppliers']);
            $manufacturerLocation->brands()->sync($value['brands']);
            $manufacturerLocation->brandModels()->sync($value['models']);
            DB::table('manufacturer_location_brands')->where('manufacturer_location_id', $manufacturerLocation->id)->update(['manufacturer_id' => $manufacturer->id]);
            DB::table('manufacturer_location_brand_models')->where('manufacturer_location_id', $manufacturerLocation->id)->update(['manufacturer_id' => $manufacturer->id]);

            array_map(function ($item) use ($manufacturerLocation, $manufacturer) {
                $model = BrandModel::query()->where('id', $item)->first();
                DB::table('manufacturer_location_brand_models')
                    ->where('manufacturer_location_id', $manufacturerLocation->id)
                    ->where('model_id', $item)
                    ->update(['brand_id' => $model->brand_id]);
            }, $value['models']);
        }
    }

    private function addUpdateManufacturerDocument($input, $manufacturer)
    {
        $this->modelManufacturerDocument::query()->where('manufacturer_id', $manufacturer->id)->delete();
        $manufacturerDocumentIds = [];
        $documentIds = [];
        foreach ($input['manufacturer_documents'] as $key => $value) {
            $manufacturerDocument = null;
            if (isset($value['id']) && !blank($value['id'])) {
                $manufacturerDocument = $this->modelManufacturerDocument::query()->withTrashed()->where('id', $value['id'])->first();
                $manufacturerDocument->deleted_at = null;
            }

            if (empty($manufacturerDocument) || is_null($manufacturerDocument)) {
                $manufacturerDocument = new $this->modelManufacturerDocument();
            }

            $manufacturerDocument->title = $value['title'];
            $manufacturerDocument->manufacturer_id = $manufacturer->id;
            $manufacturerDocument->document_id = $value['document_id'];
            $manufacturerDocument->save();

            Document::whereObjectId($manufacturer->id)
                ->whereObjectType('manufacturer_document')
                ->delete();

            if (isset($value['document']) && !empty($value['document'])) {
                Document::withTrashed()
                    ->whereGuid($value['document']['id'])
                    ->update([
                        'object_type' => 'manufacturer_document',
                        'object_id' => $manufacturer->id,
                        'deleted_at' => null
                    ]);

                $documentIds[] = $value['document']['id'];
            }

            $manufacturerDocumentIds[] = $manufacturerDocument->id;
        }

        Document::whereNotIn('object_id', $manufacturerDocumentIds)
            ->whereNotIn('guid', $documentIds)
            ->whereObjectType('manufacturer_document')
            ->delete();
    }
}
