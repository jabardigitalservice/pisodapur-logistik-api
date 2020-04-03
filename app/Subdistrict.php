<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/*
 * id                          primary key
 * kemendagri_kecamatan_kode   string
 * kemendagri_kecamatan_nama   string
 * kemendagri_kabupaten_kode   string
 * kemendagri_kabupaten_nama   string
 * kemendagri_provinsi_kode    string
 * kemendagri_provinsi_nama    string
 * is_desa                     
 */
class Subdistrict extends Model {

	protected $table = 'subdistricts';


}
