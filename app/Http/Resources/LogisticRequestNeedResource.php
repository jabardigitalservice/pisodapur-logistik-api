<?php

namespace App\Http\Resources;

use App\Traits\SelectTrait;
use App\Traits\TransformTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class LogisticRequestNeedResource extends JsonResource
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

        $administration = $this->data
            ->joinUnit()
            ->select($this->selectNeed())
            ->paginate($this->limit);

        $recommendation = $this->data
            ->joinNeed('recommendation')
            ->select($this->selectRecommendation())
            ->paginate($this->limit);

        $realization = $this->data
            ->joinNeed('realization')
            ->select($this->selectRealization())
            ->paginate($this->limit);

        array_push($data, $this->getDataTransform($administration));
        array_push($data, $this->getDataTransform($recommendation, 'recommendation'));
        array_push($data, $this->getDataTransform($realization, 'realization'));

        return $data;
    }
}
