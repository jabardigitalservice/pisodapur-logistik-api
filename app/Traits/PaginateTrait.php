<?php

namespace App\Traits;

trait PaginateTrait
{
    public function getValidOrderDirection($orderDirection, $orderDirectionDefault = 'asc')
    {
        if (!in_array($orderDirection, ['asc', 'desc'])) {
            $orderDirection = $orderDirectionDefault;
        }
        return $orderDirection;
    }
}
