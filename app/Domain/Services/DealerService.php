<?php

namespace App\Domain\Services;

use App\Domain\Models\Dealer;
use App\Domain\Models\Document;
use App\Domain\Models\DealerBrand;
use App\Domain\Models\DealerContact;
use App\Domain\Models\DealerDocument;
use App\Domain\Models\DealerAdditionalLocation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class DealerService extends BaseService
{
    /**
     * @var Dealer
     */
    public $model = Dealer::class;

    /**
     * @var DealerContact
     */
    public $modelDealerContact = DealerContact::class;

    /**
     * @var DealerAdditionalLocation
     */
    public $modelDealerAdditionalLocation = DealerAdditionalLocation::class;

    /**
     * @var DealerDocument
     */
    public $modelDealerDocument = DealerDocument::class;

    /**
     * @var DealerBrand
     */
    public $modelDealerBrand = DealerBrand::class;

    public $searchColumns = [];

    public $filterColumns = ['from_date', 'to_date'];

    public $with = [];

    public $detailWith = ['mainLocation', 'dealerContacts', 'dealerContacts.contact', 'dealerContacts.locationType',
        'dealerContacts.locations', 'dealerBrands', 'dealerBrands.brand', 'dealerBrands.models',
        'dealerAdditionalLocations', 'dealerAdditionalLocations.location', 'dealerAdditionalLocations.locationType',
        'dealerDocuments', 'dealerDocuments.document'];

    protected $primaryKey = 'id';

    protected $guidKey = 'guid';

    protected function filterQuery(Builder $query, $input = [])
    {
        foreach (Arr::get($input, 'filters', []) as $column => $value) {
            if (($value && in_array($column, $this->filterColumns))) {
                if ((in_array($column, ['from_date', 'to_date']))) {
                    $query->where(function ($q) use ($column, $value) {
                        if ($column === 'from_date' && !empty($value)) {
                            $q->whereDate('created_at', '>=', $value);
                        }

                        if ($column === 'to_date' && !empty($value)) {
                            $q->whereDate('created_at', '<=', $value);
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

        $entity = $this->model::query()->create(Arr::except($input, ['dealer_additional_locations', 'dealer_contacts', 'dealer_brands', 'dealer_documents']));

        $this->addUpdateDealerContact($input, $entity);
        $this->addUpdateDealerBrand($input, $entity);
        $this->addUpdateDealerAdditionalLocation($input, $entity);
        $this->addUpdateDealerDocuments($input, $entity);

        DB::commit();
        return $entity;
    }

    public function updateByGuid($id, array $input)
    {
        DB::beginTransaction();
        $entity = $this->model::query()->findOrFailByGuid($id);

        $entity->update(Arr::except($input, ['dealer_additional_locations', 'dealer_contacts', 'dealer_brands', 'dealer_documents']));
        $this->addUpdateDealerContact($input, $entity);
        $this->addUpdateDealerBrand($input, $entity);
        $this->addUpdateDealerAdditionalLocation($input, $entity);
        $this->addUpdateDealerDocuments($input, $entity);

        DB::commit();
        return $this->getDetailByGuid($id, []);
    }

    private function addUpdateDealerContact($input, $dealer)
    {
        $this->modelDealerContact::query()->where('dealer_id', $dealer->id)->delete();

        foreach ($input['dealer_contacts'] as $key => $value) {
            $dealerContact = null;
            if (isset($value['id']) && !blank($value['id'])) {
                $dealerContact = $this->modelDealerContact::query()->withTrashed()->where('id', $value['id'])->first();
                $dealerContact->deleted_at = null;
            }

            if (empty($dealerContact) || is_null($dealerContact)) {
                $dealerContact = new $this->modelDealerContact();
            }

            $dealerContact->dealer_id = $dealer->id;
            $dealerContact->contact_id = $value['contact_id'];
            $dealerContact->save();

            $dealerContact->locations()->sync($value['locations']);
            DB::table('dealer_contact_locations')->where('dealer_contact_id', $dealerContact->id)->update(['dealer_id' => $dealer->id]);
        }
    }

    private function addUpdateDealerBrand($input, $dealer)
    {
        $this->modelDealerBrand::query()->where('dealer_id', $dealer->id)->delete();

        foreach ($input['dealer_brands'] as $key => $value) {
            $dealerBrand = null;
            if (isset($value['id']) && !blank($value['id'])) {
                $dealerBrand = $this->modelDealerBrand::query()->withTrashed()->where('id', $value['id'])->first();
                $dealerBrand->deleted_at = null;
            }

            if (empty($dealerBrand) || is_null($dealerBrand)) {
                $dealerBrand = new $this->modelDealerBrand();
            }

            $dealerBrand->dealer_id = $dealer->id;
            $dealerBrand->brand_id = $value['brand_id'];
            $dealerBrand->save();

            $dealerBrand->models()->sync($value['models']);
            DB::table('dealer_brand_models')->where('dealer_brand_id', $dealerBrand->id)->update(['dealer_id' => $dealer->id]);
        }
    }

    private function addUpdateDealerAdditionalLocation($input, $dealer)
    {
        $this->modelDealerAdditionalLocation::query()->where('dealer_id', $dealer->id)->delete();

        foreach ($input['dealer_additional_locations'] as $key => $value) {
            $dealerAdditionalLocation = null;
            if (isset($value['id']) && !blank($value['id'])) {
                $dealerAdditionalLocation = $this->modelDealerAdditionalLocation::query()->withTrashed()->where('id', $value['id'])->first();
                $dealerAdditionalLocation->deleted_at = null;
            }

            if (empty($dealerAdditionalLocation) || is_null($dealerAdditionalLocation)) {
                $dealerAdditionalLocation = new $this->modelDealerAdditionalLocation();
            }

            $dealerAdditionalLocation->dealer_id = $dealer->id;
            $dealerAdditionalLocation->location_type_id = $value['location_type_id'];
            $dealerAdditionalLocation->location_id = $value['location_id'];
            $dealerAdditionalLocation->save();
        }
    }

    private function addUpdateDealerDocuments($input, $dealer)
    {
        $this->modelDealerDocument::query()->where('dealer_id', $dealer->id)->delete();
        $dealerDocumentIds = [];
        $documentIds = [];
        foreach ($input['dealer_documents'] as $key => $value) {
            $dealerDocument = null;
            if (isset($value['id']) && !blank($value['id'])) {
                $dealerDocument = $this->modelDealerDocument::query()->withTrashed()->where('id', $value['id'])->first();
                $dealerDocument->deleted_at = null;
            }

            if (empty($dealerDocument) || is_null($dealerDocument)) {
                $dealerDocument = new $this->modelDealerDocument();
            }

            $dealerDocument->title = $value['title'];
            $dealerDocument->dealer_id = $dealer->id;
            $dealerDocument->document_id = $value['document_id'];
            $dealerDocument->save();

            Document::whereObjectId($dealer->id)
                ->whereObjectType('dealer_document')
                ->delete();

            if (isset($value['document']) && !empty($value['document'])) {
                Document::withTrashed()
                    ->whereGuid($value['document']['id'])
                    ->update([
                        'object_type' => 'dealer_document',
                        'object_id' => $dealer->id,
                        'deleted_at' => null
                    ]);

                $documentIds[] = $value['document']['id'];
            }

            $dealerDocumentIds[] = $dealerDocument->id;
        }

        Document::whereNotIn('object_id', $dealerDocumentIds)
            ->whereNotIn('guid', $documentIds)
            ->whereObjectType('dealer_document')
            ->delete();
    }

}
