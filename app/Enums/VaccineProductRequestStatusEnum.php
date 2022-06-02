<?php

namespace App\Enums;

use Spatie\Enum\Enum;

/**
 * @method static self approved()
 * @method static self not_available()
 * @method static self replaced()
 * @method static self not_yet_fulfilled()
 * @method static self urgent()
 * @method static self other()
 */

class VaccineProductRequestStatusEnum extends Enum
{
    public static function getStatusValue($status)
    {
        $result = '';
        switch ($status) {

            case VaccineProductRequestStatusEnum::approved():
                $result = 'Barang Disetujui';
                break;

            case VaccineProductRequestStatusEnum::not_available():
                $result = 'Barang Tidak Tersedia';
                break;

            case VaccineProductRequestStatusEnum::replaced():
                $result = 'Barang Diganti';
                break;

            case VaccineProductRequestStatusEnum::not_yet_fulfilled():
                $result = 'Barang Belum Bisa Dipenuhi';
                break;

            case VaccineProductRequestStatusEnum::urgent():
                $result = 'Barang Penting';
                break;

            case VaccineProductRequestStatusEnum::other():
                $result = 'Barang Lainnya';
                break;

            default:
                # code...
                break;
        }
        return $result;
    }
}
