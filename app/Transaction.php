<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/** Struktur tabel
 *
 *  id
 *  id_product                integer
 *  id_user                   integer
 *  id_recipient              integer
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
  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'id_product',
    //'id_user',
    'id_recipient',
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
  	'id_product' => 1, //saat ini baru untuk tipe item RDT
    'location_province_code' => '32', // default provinsi adalah jawa barat
  ];

  // ======================= RELATIONSHIPS ============================
  /**
   * Get the recipient object of this transaction
   */
  public function recipient()
  {
      return $this->belongsTo('App\Recipient', 'id_recipient');
  } 

  /**
   * Update recipient stock after this transaction
   */
  public function updateRecipient()
  {
      // times -1 because outgoing transaction has negative quantity
      $recipient = $this->recipient;
      //error_log('recipient:', $recipient);
      $recipient->total_stock += ($this->quantity * -1); 
      return $recipient->save();
  } 

  /**
   * Get the the user creating this transaction
   */
  public function user()
  {
      return $this->belongsTo('App\User', 'id_user');
  } 

}
