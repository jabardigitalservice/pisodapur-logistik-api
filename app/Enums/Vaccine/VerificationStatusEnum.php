<?php

namespace App\Enums\Vaccine;

use Spatie\Enum\Enum;

/**
 * @method static self not_verified()
 * @method static self verified()
 * @method static self verified_with_note()
 */

class VerificationStatusEnum extends Enum
{
    public static function not_verified(): VerificationStatusEnum
    {
        return new class () extends VerificationStatusEnum {
            public function getValue(): string
            {
                return 0;
            }
        };
    }

    public static function verified(): VerificationStatusEnum
    {
        return new class () extends VerificationStatusEnum {
            public function getValue(): string
            {
                return 1;
            }
        };
    }

    public static function verified_with_note(): VerificationStatusEnum
    {
        return new class () extends VerificationStatusEnum {
            public function getValue(): string
            {
                return 2;
            }
        };
    }
}
