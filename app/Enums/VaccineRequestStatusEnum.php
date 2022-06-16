<?php

namespace App\Enums;

use Spatie\Enum\Enum;

/**
 * @method static self rejected()
 * @method static self not_verified()
 * @method static self verified()
 * @method static self verified_with_note()
 * @method static self approved()
 * @method static self finalized()
 * @method static self integrated()
 * @method static self booked()
 * @method static self do()
 * @method static self intransit()
 * @method static self delivered()
 */

class VaccineRequestStatusEnum extends Enum
{

}
