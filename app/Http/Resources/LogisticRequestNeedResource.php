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

        $payload = $this->data
            ->joinUnit()
            ->select($this->selectRequestNeed());

        $administration = $this->getDataTransform($payload->paginate($this->limit));
        $recommendation = $this->getDataTransform($payload->paginate($this->limit), 'recommendation');
        $realization = $this->getDataTransform($payload->paginate($this->limit), 'realization');

        array_push($data, $administration);
        array_push($data, $recommendation);
        array_push($data, $realization);

        return $data;
    }
}
