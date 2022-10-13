<?php

namespace App\Http\Resources;

use App\Product;
use App\Traits\SelectTrait;
use App\Traits\TransformTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class LogisticAdminRealizationResource extends JsonResource
{
    use SelectTrait;
    use TransformTrait;

    public $data;
    public $limit;

    function __construct($data, $request)
    {
        $this->limit = $request->input('limit', 3);
        $this->data = $data;
    }
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->getData();
    }

    public function getData()
    {
        $data = array();

        $recommendation = $this->data
            ->select($this->selectRecommendation())
            ->paginate($this->limit);

        $realization = $this->data
            ->select($this->selectRealization())
            ->paginate($this->limit);

        array_push($data, $this->getDataTransform($recommendation, 'recommendation', true));
        array_push($data, $this->getDataTransform($realization, 'realiazation', true));

        return $data;
    }
}
