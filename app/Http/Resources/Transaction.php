<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Transaction extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'                        => $this->id,
            'id_product'                => $this->id_product,
            'id_recipient'              => $this->id_recipient,
            'name'                      => $this->name,
            'contact_person'            => $this->contact_person,
            'phone_number'              => $this->phone_number,
            'location_address'          => $this->location_address,
            'location_subdistrict_code' => $this->location_subdistrict_code,
            'location_subdistrict_name' => (!empty($this->subdistrict)) ? 
                                            $this->subdistrict->kemendagri_kecamatan_nama : '',
            'location_district_code'    => $this->location_district_code,
            'location_district_name'    => (!empty($this->city)) ? 
                                            $this->city->kemendagri_kabupaten_nama : '',
            'location_province_code'    => $this->location_province_code,
            'quantity'                  => $this->quantity,
            'time'                      => $this->time,
            'note'                      => $this->note,
        ];
    }
}
