<?php

namespace App\Traits;

use App\LogisticRealizationItems;
use App\Needs;
use App\Product;

trait TransformTrait
{
    public function getDataTransform($data, $phase = 'administration', $isSalur = false)
    {
        $data->getCollection()->transform(function ($item) use ($phase, $isSalur) {
            $item->status = !$item->status ? 'not_approved' : $item->status;

            if ($item->status == 'not_approved' && $phase != 'administration') {
                $data = $this->getItems($item, $phase, $isSalur);
                $item->product_id = $phase == 'recommendation' ? ($data->product->id) ?? null : $data->product_id;
                $item->product_name = $phase == 'recommendation' ? ($data->product->name) ?? null : $data->product_name;
                $item->unit = $phase == 'recommendation' ? ($data->masterUnit->unit) ?? null : $data->realization_unit;
                $item->quantity = null;
            }

            if ($item->status == 'not_approved' && $isSalur) {
                $data = $this->getItems($item, $phase, $isSalur);
                $item->product_id = $data->product->id ?? $data->product_id;
                $item->product_name = $data->product->name ?? $data->product_name;
                $item->unit = $data->realization_unit;
                $item->quantity = null;
            }


            return $item;
        });

        return $data;
    }

    public function getItems($item, $phase, $isSalur)
    {
        $data = LogisticRealizationItems::find($item->id);

        if ($phase == 'recommendation' && !$isSalur) {
            $data = Needs::find($item->need_id);
        }

        return $data;
    }
}
