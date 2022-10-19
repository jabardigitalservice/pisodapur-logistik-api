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
        $this->limit = $request->input('limit', 10);
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

        $realization = $this->data
            ->joinNeed('realization')
            ->select($this->selectRealization())
            ->paginate($this->limit);

        $recommendation = $this->data
            ->joinNeed('recommendation')
            ->select($this->selectRecommendation())
            ->whereNotNull('logistic_realization_items.product_id')
            ->paginate($this->limit);


        array_push($data, $this->getDataTransform($recommendation, 'recommendation', true));
        array_push($data, $this->getDataTransform($realization, 'realiazation', true));

        return $data;
    }
}
