<?php

namespace App;
use Validator;

class Validation
{
    static function validate($request, $param)
    {
        $validator = Validator::make(
            $request->all(),
            $param
        );
        if ($validator->fails()) {
            return response()->format(422, $validator->errors(), $param);
        }
        return true;
    }
}
