<?php

namespace App\Http\Requests\MasterFaskes;

use Illuminate\Foundation\Http\FormRequest;
use Spatie\Enum\Laravel\Rules\EnumRule;
use App\Enums\MasterFaskesVerificationStatusEnum;

class StoreMasterFaskesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'nama_faskes' => 'required',
            'id_tipe_faskes' => 'required|numeric|exists:master_faskes_types,id',
            'nomor_telepon' => 'numeric',
            'kode_kab_kemendagri' => 'exists:districtcities,kemendagri_kabupaten_kode',
            'kode_kec_kemendagri' => 'exists:subdistricts,kemendagri_kecamatan_kode',
            'kode_kel_kemendagri' => 'exists:villages,kemendagri_desa_kode',
            'alamat' => 'nullable',
            'permit_file' => 'mimes:jpeg,jpg,png|max:10240',
            'verification_status' => [new EnumRule(MasterFaskesVerificationStatusEnum::class)],
        ];
    }
}
