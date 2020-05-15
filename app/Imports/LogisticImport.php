<?php

namespace App\Imports;

use Illuminate\Database\Eloquent\Model;

class LogisticImport extends Model
{
    public static function import($data)
    {
        $application = $data->sheetData[0]->toArray();

        //Todo : post data to logistic request table
        foreach ($application as $item) {
            self::findProduct($data, $item['id_permohonan']);
        }
    }

    public static function findProduct($data, $idPermohonan)
    {
        $logisticItem = $data->sheetData[1]->toArray();

        $result = [];

        //Todo : post data to logistic request table
        foreach ($logisticItem as $item) {
            if ($item['id_permohonan'] === $idPermohonan) {
                $result[] = $item;
            }
        }
    }
}
