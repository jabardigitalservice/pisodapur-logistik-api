<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LogisticRealizationItems extends Model
{
    use SoftDeletes;

    const STATUS = [
        'delivered',
        'not_delivered',
        'approved',
        'not_approved',
        'not_available',
        'replaced',
        'not_yet_fulfilled'
    ];

    const STATUS_DELIVERED = 'delivered';
    const STATUS_NOT_DELIVERED = 'not_delivered';
    const STATUS_APPROVED = 'approved';
    const STATUS_NOT_APPROVED = 'not_approved';
    const STATUS_NOT_AVAILABLE = 'not_available';
    const STATUS_REPLACED = 'replaced';
    const STATUS_NOT_YET_FULFILLED = 'not_yet_fulfilled';

    protected $table = 'logistic_realization_items';

    protected $fillable = [
        'id',
        'agency_id',
        'applicant_id',
        'need_id',
        'product_id',
        'product_name',
        'realization_unit',
        'material_group',
        'realization_quantity',
        'unit_id',
        'status',
        'realization_date',
        'created_by',
        'updated_by',
        'recommendation_soh_location',
        'recommendation_soh_location_name',
        'recommendation_by',
        'recommendation_at',
        'final_product_id',
        'final_product_name',
        'final_soh_location',
        'final_soh_location_name',
        'final_quantity',
        'final_unit',
        'final_date',
        'final_status',
        'final_unit_id',
        'final_by',
        'final_at',
    ];

    public function agency()
    {
        return $this->belongsToMany('App\Agency', 'id', 'agency_id');
    }

    public function product()
    {
        return $this->hasOne('App\Product', 'id', 'product_id');
    }

    public function unit()
    {
        return $this->hasOne('App\MasterUnit', 'id', 'unit_id');
    }

    public function verifiedBy()
    {
        return $this->hasOne('App\User', 'id', 'created_by');
    }

    public function recommendBy()
    {
        return $this->hasOne('App\User', 'id', 'recommendation_by');
    }

    public function realizedBy()
    {
        return $this->hasOne('App\User', 'id', 'realization_by');
    }

    public function scopeJoinProduct($query)
    {
        return $query->leftjoin('products', 'products.id', 'logistic_realization_items.product_id');
    }

    public function scopeJoinNeed($query)
    {
        return $query->leftjoin('needs', 'needs.id', 'logistic_realization_items.need_id');
    }

    public function scopeJoinUnit($query)
    {
        return $query->leftjoin('master_unit', 'master_unit.id', 'needs.unit');
    }


    public function getFinalUnitAttribute($value)
    {
        return $value ? $value : 'PCS';
    }

    static function withPICData($data)
    {
        return $data->with([
            'recommendBy:id,name,agency_name,handphone',
            'verifiedBy:id,name,agency_name,handphone',
            'realizedBy:id,name,agency_name,handphone'
        ]);
    }

    static function setValue($request, $findOne)
    {
        if ($request->input('store_type') === 'recommendation') {
            $request['realization_quantity'] = $request->input('recommendation_quantity');
            $request['realization_date'] = $request->input('recommendation_date');
            $request['recommendation_by'] = auth()->user()->id;
            $request['recommendation_at'] = date('Y-m-d H:i:s');
        } else {
            $request['final_product_id'] = $request->input('product_id');
            $request['final_product_name'] = $request->input('product_name');
            $request['final_quantity'] = $request->input('realization_quantity');
            $request['final_unit'] = $request['realization_unit'];
            $request['final_date'] = $request->input('realization_date');
            $request['final_status'] = $request->input('status');
            $request['final_by'] = auth()->user()->id;
            $request['final_at'] = date('Y-m-d H:i:s');
            $request = self::setValueIfFindOneExists($request, $findOne);
        }
        return $request;
    }

    static function setValueIfFindOneExists($request, $findOne)
    {
        if ($findOne) {
            $request['product_id'] = $findOne->product_id;
            $request['product_name'] = $findOne->product_name;
            $request['realization_quantity'] = $findOne->realization_quantity;
            $request['realization_unit'] = $findOne->realization_unit;
            $request['realization_date'] = $findOne->realization_date;
            $request['material_group'] = $findOne->material_group;
            $request['quantity'] = $findOne->quantity;
            $request['date'] = $findOne->date;
            $request['status'] = $findOne->status;
            $request['recommendation_by'] = $findOne->recommendation_by;
            $request['recommendation_at'] = $findOne->recommendation_at;
        } else {
            unset($request['product_id']);
            unset($request['product_name']);
            unset($request['realization_unit']);
            unset($request['material_group']);
            unset($request['quantity']);
            unset($request['date']);
            unset($request['status']);
            unset($request['unit_id']);
            unset($request['recommendation_by']);
            unset($request['recommendation_at']);
        }
        return $request;
    }

    static function getList($request)
    {
        $limit = $request->input('limit', 3);
        $data = self::selectList();
        $data = self::withPICData($data);
        $data = $data->whereNotNull('created_by')
            ->orderBy('logistic_realization_items.id')
            ->where('logistic_realization_items.agency_id', $request->agency_id)
            ->paginate($limit);

        $logisticItemSummary = self::where('agency_id', $request->agency_id)->sum('realization_quantity');
        $data->getCollection()->transform(function ($item, $key) use ($logisticItemSummary) {
            $item->status = !$item->status ? 'not_approved' : $item->status;
            $item->logistic_item_summary = (int)$logisticItemSummary;
            return $item;
        });
        return $data;
    }

    static function selectList()
    {
        $fields = self::fieldNeeds([]);
        $fields = self::fieldRecommendations($fields);
        $fields = self::fieldRealizations($fields);
        return self::select($fields);
    }

    static function fieldRealizations($fields)
    {
        $fields[] = 'final_product_id';
        $fields[] = 'final_product_name';
        $fields[] = 'final_date';
        $fields[] = 'final_quantity';
        $fields[] = 'final_unit';
        $fields[] = 'final_status';

        return $fields;
    }

    static function fieldRecommendations($fields)
    {
        $fields[] = 'logistic_realization_items.product_id as recommendation_product_id';
        $fields[] = 'logistic_realization_items.product_name as recommendation_product_name';
        $fields[] = 'logistic_realization_items.realization_date as recommendation_date';
        $fields[] = 'logistic_realization_items.realization_quantity as recommendation_quantity';
        $fields[] = 'logistic_realization_items.realization_unit as recommendation_unit';

        return $fields;
    }

    static function fieldNeeds($fields)
    {
        $fields[] = 'logistic_realization_items.id';
        $fields[] = 'logistic_realization_items.status';
        $fields[] = 'needs.id as need_id';
        $fields[] = 'needs.product_id';
        $fields[] = 'needs.product_id as need_product_id';
        $fields[] = 'products.name as product_name';
        $fields[] = 'needs.quantity';
        $fields[] = 'needs.quantity as request_quantity';
        $fields[] = 'master_unit.unit';
        $fields[] = 'needs.brand';
        $fields[] = 'products.category';
        $fields[] = 'needs.created_at as date';
        return $fields;
    }

    public function scopeAcceptedStatusOnly($query, $field)
    {
        return $query->whereNotIn($field, [self::STATUS_NOT_AVAILABLE, self::STATUS_NOT_YET_FULFILLED]);
    }
}
