<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/** Struktur tabel
 *
 *  id
 *  id_tipe                   integer
 *  id_user                   integer
 *  id_category               integer
 *  name                      string
 *  contact_person            string
 *  phone_number              string
 *  location_address          string
 *  location_subdistrict_code string
 *  location_district_code    string
 *  location_province_code    string
 *  quantity                  integer
 *  time                      DateTime
 *  note                      string
 *  timestamps
 */
class Transaction extends Model
{
  /* tipe item logistik. sementara ini di hardcode. kedepannya bisa dibuat jadi 
   * model terpisah */
  const TIPE_LOGISTIK = [
    1 => 'RDT',
  ];

  /* kategori pihak pemberi/penerima logistik. sementara ini di hardcode. 
   * kedepannya bisa dibuat jadi model terpisah */
  const CATEGORY = [
      1  => "KAB. BOGOR",
      2  => "KAB. SUKABUMI",
      3  => "KAB. CIANJUR",
      4  => "KAB. BANDUNG",
      5  => "KAB. GARUT",
      6  => "KAB. TASIKMALAYA",
      7  => "KAB. CIAMIS",
      8  => "KAB. KUNINGAN",
      9  => "KAB. CIREBON",
      10  => "KAB. MAJALENGKA",
      11 => "KAB. SUMEDANG",
      12 => "KAB. INDRAMAYU",
      13 => "KAB. SUBANG",
      14 => "KAB. PURWAKARTA",
      15 => "KAB. KARAWANG",
      16 => "KAB. BEKASI",
      17 => "KAB. BANDUNG BARAT",
      18 => "KAB. PANGANDARAN",
      19 => "KOTA BOGOR",
      20 => "KOTA SUKABUMI",
      21 => "KOTA BANDUNG",
      22 => "KOTA BEKASI",
      23 => "KOTA DEPOK",
      24 => "KOTA CIMAHI",
      25 => "KOTA TASIKMALAYA",
      26 => "KOTA BANJAR",
      27 => "KOTA CIREBON",
      28 => "Lainnya",
  ];

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'id_tipe',
    'id_user',
    'id_category',
    'name',
    'contact_person',
    'phone_number',
    'location_address',
    'location_subdistrict_code',
    'location_district_code',
    'location_province_code',
    'quantity',
    'time',
    'note',
  ];

	/**
	* The model's default values for attributes.
	*
	* @var array
	*/
	protected $attributes = [
  	'id_tipe' => 1, //saat ini baru untuk tipe item RDT
    'location_province_code' => '32', // default provinsi adalah jawa barat
  ];
    
}
