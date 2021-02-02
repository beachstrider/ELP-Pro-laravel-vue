<?php

namespace App\Domain\Services;

use App\Domain\Models\Client;
use App\Domain\Models\ClientBrand;
use App\Domain\Models\ClientContact;
use App\Domain\Models\ClientLocation;
use App\Domain\Models\Document;
use App\Domain\Models\ClientDocument;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ClientService extends BaseService
{
    /**
     * @var Client
     */
    public $model = Client::class;

    /**
     * @var ClientContact
     */
    public $modelClientContact = ClientContact::class;

    /**
     * @var ClientBrand
     */
    public $modelClientBrand = ClientBrand::class;

    /**
     * @var ClientLocation
     */
    public $modelClientLocation = ClientLocation::class;

    /**
     * @var ClientDocument
     */
    public $modelClientDocument = ClientDocument::class;

    public $searchColumns = ['company_name', 'identification_number', 'phone', 'email', 'fax'];

    public $filterColumns = ['start_date', 'end_date'];

    public $with = [];

    public $detailWith = ['dealers', 'mainLocation', 'clientContacts', 'clientContacts.contact',
        'clientContacts.locations', 'clientLocations', 'clientLocations.location', 'clientLocations.locationType',
        'clientBrands', 'clientBrands.brand', 'clientBrands.models',  'clientDocuments', 'clientDocuments.document'
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
        $entity = $this->model::create(Arr::except($input, ['client_dealers', 'client_locations', 'client_contacts', 'client_brands', 'client_documents']));
        $entity->dealers()->attach($input['client_dealers']);
        $this->addUpdateClientContact($input, $entity);
        $this->addUpdateClientLocation($input, $entity);
        $this->addUpdateBrandContact($input, $entity);
        $this->addUpdateClientDocument($input, $entity);
        DB::commit();

        return $entity;
    }

    public function updateByGuid($id, array $input)
    {
        DB::beginTransaction();
        $entity = $this->model::query()->findOrFailByGuid($id);
        $entity->update(Arr::except($input, ['client_dealers', 'client_locations', 'client_contacts', 'client_brands', 'client_documents']));
        $entity->dealers()->sync($input['client_dealers']);
        $this->addUpdateClientContact($input, $entity);
        $this->addUpdateClientLocation($input, $entity);
        $this->addUpdateBrandContact($input, $entity);
        $this->addUpdateClientDocument($input, $entity);
        DB::commit();
        return $this->getDetailByGuid($id, []);
    }

    private function addUpdateClientLocation($input, $client)
    {
        $this->modelClientLocation::query()->where('client_id', $client->id)->delete();

        foreach ($input['client_locations'] as $key => $value) {
            $clientLocation = null;
            if (isset($value['id']) && !blank($value['id'])) {
                $clientLocation = $this->modelClientLocation::query()->withTrashed()->where('id', $value['id'])->first();
                $clientLocation->deleted_at = null;
            }

            if (empty($clientLocation) || is_null($clientLocation)) {
                $clientLocation = new $this->modelClientLocation();
            }

            $clientLocation->client_id = $client->id;
            $clientLocation->location_id = $value['location_id'];
            $clientLocation->location_type_id = $value['location_type_id'];
            $clientLocation->save();
        }
    }

    private function addUpdateClientContact($input, $client)
    {
        $this->modelClientContact::query()->where('client_id', $client->id)->delete();

        foreach ($input['client_contacts'] as $key => $value) {
            $clientContact = null;
            if (isset($value['id']) && !blank($value['id'])) {
                $clientContact = $this->modelClientContact::query()->withTrashed()->where('id', $value['id'])->first();
                $clientContact->deleted_at = null;
            }

            if (empty($clientContact) || is_null($clientContact)) {
                $clientContact = new $this->modelClientContact();
            }

            $clientContact->client_id = $client->id;
            $clientContact->contact_id = $value['contact_id'];
            $clientContact->save();

            $clientContact->locations()->sync($value['locations']);
            DB::table('client_contact_locations')->where('client_contact_id', $clientContact->id)->update(['client_id' => $client->id]);
        }
    }

    private function addUpdateBrandContact($input, $client)
    {
        $this->modelClientBrand::query()->where('client_id', $client->id)->delete();

        foreach ($input['client_brands'] as $key => $value) {
            $clientBrand = null;
            if (isset($value['id']) && !blank($value['id'])) {
                $clientBrand = $this->modelClientBrand::query()->withTrashed()->where('id', $value['id'])->first();
                $clientBrand->deleted_at = null;
            }

            if (empty($clientBrand) || is_null($clientBrand)) {
                $clientBrand = new $this->modelClientBrand();
            }

            $clientBrand->client_id = $client->id;
            $clientBrand->brand_id = $value['brand_id'];
            $clientBrand->save();

            $clientBrand->models()->sync($value['models']);
            DB::table('client_brand_models')->where('client_brand_id', $clientBrand->id)->update(['client_id' => $client->id]);
        }
    }

    private function addUpdateClientDocument($input, $client)
    {
        $this->modelClientDocument::query()->where('client_id', $client->id)->delete();
        $clientDocumentIds = [];
        $documentIds = [];
        foreach ($input['client_documents'] as $key => $value) {
            $clientDocument = null;
            if (isset($value['id']) && !blank($value['id'])) {
                $clientDocument = $this->modelClientDocument::query()->withTrashed()->where('id', $value['id'])->first();
                $clientDocument->deleted_at = null;
            }

            if (empty($clientDocument) || is_null($clientDocument)) {
                $clientDocument = new $this->modelClientDocument();
            }

            $clientDocument->title = $value['title'];
            $clientDocument->client_id = $client->id;
            $clientDocument->document_id = $value['document_id'];
            $clientDocument->save();

            Document::whereObjectId($client->id)
                ->whereObjectType('client_document')
                ->delete();

            if (isset($value['document']) && !empty($value['document'])) {
                Document::withTrashed()
                    ->whereGuid($value['document']['id'])
                    ->update([
                        'object_type' => 'client_document',
                        'object_id' => $client->id,
                        'deleted_at' => null
                    ]);

                $documentIds[] = $value['document']['id'];
            }

            $clientDocumentIds[] = $clientDocument->id;
        }

        Document::whereNotIn('object_id', $clientDocumentIds)
            ->whereNotIn('guid', $documentIds)
            ->whereObjectType('client_document')
            ->delete();
    }

}
