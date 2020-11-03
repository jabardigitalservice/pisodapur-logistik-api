<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;
use JWTAuth;

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
        'recommendation_by',
        'recommendation_at',
        'final_product_id',
        'final_product_name',
        'final_quantity',
        'final_unit',
        'final_date',
        'final_status',
        'final_unit_id',
        'final_by',
        'final_at',
    ];

    static function deleteData($id)
    {
        $result = [
            'code' => 422,
            'message' => 'Gagal Terhapus',
            'data' => $id
        ];
        DB::beginTransaction();
        try {   
            $deleteRealization = self::where('id', $id)->delete();
            DB::commit();
            $result = [
                'code' => 200,
                'message' => 'success',
                'data' => $id
            ];
        } catch (\Exception $exception) {
            DB::rollBack();
            $result['message'] = $exception->getMessage();
        }
        return $result;
    }

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

    public function getFinalUnitAttribute($value)
    {
        return $value ? $value : 'PCS';
    }

    public function getQtyAttribute($value)
    {
        return number_format($value, 0, ",", ".");
    }

    static function storeData($store_type)
    {
        DB::beginTransaction();
        try {
            $realization = self::create($store_type);
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            $realization = $exception->getMessage();
        }
        return $realization;
    }

    static function withPICData($data)
    {
        return $data->with([
            'recommendBy' => function ($query) {
                return $query->select(['id', 'name', 'agency_name', 'handphone']);
            },
            'verifiedBy' => function ($query) {
                return $query->select(['id', 'name', 'agency_name', 'handphone']);
            },
            'realizedBy' => function ($query) {
                return $query->select(['id', 'name', 'agency_name', 'handphone']);
            }
        ]);
    }

    static function setValue($request, $findOne)
    {
        if ($request->input('store_type') === 'recommendation') {
            $request['realization_quantity'] = $request->input('recommendation_quantity');
            $request['realization_date'] = $request->input('recommendation_date');
            $request['recommendation_by'] = JWTAuth::user()->id;
            $request['recommendation_at'] = date('Y-m-d H:i:s');
        } else {
            $request['final_product_id'] = $request->input('product_id');
            $request['final_product_name'] = $request->input('product_name');
            $request['final_quantity'] = $request->input('realization_quantity');
            $request['final_unit'] = $request['realization_unit'];
            $request['final_date'] = $request->input('realization_date');
            $request['final_status'] = $request->input('status');
            $request['final_by'] = JWTAuth::user()->id;
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
}
