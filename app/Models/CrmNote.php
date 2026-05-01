<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CrmNote extends Model {
    protected $fillable = ['crm_client_id','type','subject','body','interaction_at','created_by'];
    protected $casts = ['interaction_at'=>'datetime'];
    public function client()  { return $this->belongsTo(CrmClient::class, 'crm_client_id'); }
    public function author()  { return $this->belongsTo(User::class, 'created_by'); }
    public function typeIcon(): string {
        return match($this->type) {
            'call'     => '📞',
            'email'    => '📧',
            'meeting'  => '🤝',
            'whatsapp' => '💬',
            default    => '📝',
        };
    }
    public function typeBadge(): string {
        return match($this->type) {
            'call'     => 'bg-green-100 text-green-700',
            'email'    => 'bg-blue-100 text-blue-700',
            'meeting'  => 'bg-purple-100 text-purple-700',
            'whatsapp' => 'bg-emerald-100 text-emerald-700',
            default    => 'bg-gray-100 text-gray-600',
        };
    }
}
