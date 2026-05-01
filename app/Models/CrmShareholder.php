<?php
// app/Models/CrmShareholder.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CrmShareholder extends Model {
    protected $fillable = ['crm_client_id','shareholder_name','birthdate','nationality','passport','passport_expiry','ownership_percentage','is_ubo'];
    protected $casts = ['birthdate'=>'date','passport_expiry'=>'date','is_ubo'=>'boolean'];
    public function client() { return $this->belongsTo(CrmClient::class, 'crm_client_id'); }
}
