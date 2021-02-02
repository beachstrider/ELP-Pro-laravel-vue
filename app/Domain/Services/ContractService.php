<?php

namespace App\Domain\Services;

use App\Domain\Models\Contract;
use App\Domain\Models\Document;
use App\Domain\Models\ContractDocument;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ContractService extends BaseService
{
    /**
     * @var Contract
     */
    public $model = Contract::class;

    /**
     * @var ContractDocument
     */
    public $modelContractDocument = ContractDocument::class;

    public $searchColumns = ['title', 'duration', ['relationship' => 'supplier', 'column' => 'name']];

    public $filterColumns = ['start_date', 'end_date', 'suppliers'];

    public $with = ['supplier'];

    public $detailWith = ['supplier', 'contractDocuments', 'contractDocuments.document'];

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
                } else if ($column == 'suppliers') {
                    $query->orWhereHas('supplier', function ($qry) use ($value, $column) {
                        $qry->whereIn('suppliers.guid', $value);
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
        $entity = $this->model::create(Arr::except($input, ['contract_documents']));
        $this->addUpdateContractDocument($input, $entity);
        DB::commit();

        return $entity;
    }

    public function updateByGuid($id, array $input)
    {
        DB::beginTransaction();
        $entity = $this->model::query()->findOrFailByGuid($id);

        $entity->update(Arr::except($input, ['contract_documents']));

        $this->addUpdateContractDocument($input, $entity);
        DB::commit();
        return $this->getDetailByGuid($id, []);
    }

    private function addUpdateContractDocument($input, $contract)
    {
        $this->modelContractDocument::query()->where('contract_id', $contract->id)->delete();
        $contractDocumentIds = [];
        $documentIds = [];
        foreach ($input['contract_documents'] as $key => $value) {
            $contractDocument = null;
            if (isset($value['id']) && !blank($value['id'])) {
                $contractDocument = $this->modelContractDocument::query()->withTrashed()->where('id', $value['id'])->first();
                $contractDocument->deleted_at = null;
            }

            if (empty($contractDocument) || is_null($contractDocument)) {
                $contractDocument = new $this->modelContractDocument();
            }

            $contractDocument->title = $value['title'];
            $contractDocument->contract_id = $contract->id;
            $contractDocument->document_id = $value['document_id'];
            $contractDocument->save();

            Document::whereObjectId($contract->id)
                ->whereObjectType('contract_document')
                ->delete();

            if (isset($value['document']) && !empty($value['document'])) {
                Document::withTrashed()
                    ->whereGuid($value['document']['id'])
                    ->update([
                        'object_type' => 'contract_document',
                        'object_id' => $contract->id,
                        'deleted_at' => null
                    ]);

                $documentIds[] = $value['document']['id'];
            }

            $contractDocumentIds[] = $contract->id;
        }

        Document::whereNotIn('object_id', $contractDocumentIds)
            ->whereNotIn('guid', $documentIds)
            ->whereObjectType('contract_document')
            ->delete();
    }

    public function deleteByGuid($id, array $input = [])
    {
        $entity = $this->model::query()->where('guid', $id)->first();

        if ($entity)
            return $entity->delete();

        return false;
    }
}
