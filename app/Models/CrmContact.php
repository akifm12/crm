<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CrmContact extends Model {
    protected $fillable = ['crm_client_id','name','position','email','phone','is_primary'];
    protected $casts = ['is_primary'=>'boolean'];
    public function client() { return $this->belongsTo(CrmClient::class, 'crm_client_id'); }
}
