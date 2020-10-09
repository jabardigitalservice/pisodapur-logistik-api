<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SyncApiSchedules extends Model
{
    protected $fillable = [
        'api', 'schedule'
    ];

    static function getIntervalTimeByAPI($baseApi, $updateTime)
    {
        $result = $updateTime;
        try{
            $model = SyncApiSchedules::where('api', '=', $baseApi)->firstOrFail();
            $updateTime = static::addTime($updateTime, $model);
        } catch (\Exception $exception) {
            $result = $updateTime;
        }
        return $result;
    }

    static function addTime($updateTime, $model)
    {
        if ($updateTime) {
            if ($model->hour > 0) {
                $updateTime->modify('+' . $model->hour . ' hours');
            }
    
            if ($model->minute > 0) {
                $updateTime->modify('+' . $model->minute . ' minutes');
            }
    
            if ($model->second > 0) {
                $updateTime->modify('+' . $model->second . ' seconds');
            }
        }
        return $updateTime;
    }
}
