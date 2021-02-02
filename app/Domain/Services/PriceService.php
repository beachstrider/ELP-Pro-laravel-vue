<?php

namespace App\Domain\Services;

use App\Domain\Models\Price;
use App\Domain\Models\Document;
use App\Domain\Models\PriceDocument;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class PriceService extends BaseService
{
    /**
     * @var Price
     */
    public $model = Price::class;

    /**
     * @var PriceDocument
     */
    public $modelPriceDocument = PriceDocument::class;

    public $searchColumns = [
        'leading_factors',
        'lead_time_pickup',
        'lead_time_transport',
        'full_loaded_price',
        'single_loaded_price',
        ['relationship' => 'supplier', 'column' => 'name'],
        ['relationship' => 'route', 'column' => 'name'],
        ['relationship' => 'brand', 'column' => 'title'],
        ['relationship' => 'model', 'column' => 'title'],
        ['relationship' => 'logisticType', 'column' => 'title'],
    ];

    public $filterColumns = ['from_date', 'to_date', 'suppliers', 'routes', 'logistic_types'];

    public $with = ['supplier', 'route', 'brand', 'model', 'logisticType'];

    public $detailWith = ['supplier', 'route', 'brand', 'model', 'logisticType', 'priceDocuments', 'priceDocuments.document'];

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
                } else if ($column == 'suppliers') {
                    $query->orWhereHas('supplier', function ($qry) use ($value, $column) {
                        $qry->whereIn('suppliers.guid', $value);
                    });
                } else if ($column == 'routes') {
                    $query->orWhereHas('route', function ($qry) use ($value, $column) {
                        $qry->whereIn('routes.guid', $value);
                    });
                } else if ($column == 'logistic_types') {
                    $query->orWhereHas('logisticType', function ($qry) use ($value, $column) {
                        $qry->whereIn('logistic_types.guid', $value);
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
        $entity = $this->model::create(Arr::except($input, ['price_documents']));
        $this->addUpdatePriceDocument($input, $entity);
        DB::commit();

        return $entity;
    }

    public function updateByGuid($id, array $input)
    {
        DB::beginTransaction();
        $entity = $this->model::query()->findOrFailByGuid($id);

        $entity->update(Arr::except($input, ['price_documents']));

        $this->addUpdatePriceDocument($input, $entity);
        DB::commit();
        return $this->getDetailByGuid($id, []);
    }

    private function addUpdatePriceDocument($input, $price)
    {
        $this->modelPriceDocument::query()->where('price_id', $price->id)->delete();
        $priceDocumentIds = [];
        $documentIds = [];
        foreach ($input['price_documents'] as $key => $value) {
            $priceDocument = null;
            if (isset($value['id']) && !blank($value['id'])) {
                $priceDocument = $this->modelPriceDocument::query()->withTrashed()->where('id', $value['id'])->first();
                $priceDocument->deleted_at = null;
            }

            if (empty($priceDocument) || is_null($priceDocument)) {
                $priceDocument = new $this->modelPriceDocument();
            }

            $priceDocument->title = $value['title'];
            $priceDocument->price_id = $price->id;
            $priceDocument->document_id = $value['document_id'];
            $priceDocument->save();

            Document::whereObjectId($price->id)
                ->whereObjectType('price_document')
                ->delete();

            if (isset($value['document']) && !empty($value['document'])) {
                Document::withTrashed()
                    ->whereGuid($value['document']['id'])
                    ->update([
                        'object_type' => 'price_document',
                        'object_id' => $price->id,
                        'deleted_at' => null
                    ]);

                $documentIds[] = $value['document']['id'];
            }

            $priceDocumentIds[] = $price->id;
        }

        Document::whereNotIn('object_id', $priceDocumentIds)
            ->whereNotIn('guid', $documentIds)
            ->whereObjectType('price_document')
            ->delete();
    }
}
