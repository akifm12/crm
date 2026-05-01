<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CrmSla extends Model {
    protected $fillable = ['crm_client_id','sla_template_id','sla_reference','name','scope_of_work','client_obligations','deliverables','start_date','end_date','fee','fee_frequency','payment_terms','status','signed_copy_path','signed_date','created_by'];
    protected $casts = ['start_date'=>'date','end_date'=>'date','signed_date'=>'date','fee'=>'decimal:2'];
    public function client()   { return $this->belongsTo(CrmClient::class, 'crm_client_id'); }
    public function template() { return $this->belongsTo(SlaTemplate::class, 'sla_template_id'); }
    public function creator()  { return $this->belongsTo(User::class, 'created_by'); }
    public function statusBadge(): string {
        return match($this->status) {
            'draft'      => 'bg-gray-100 text-gray-600',
            'sent'       => 'bg-blue-100 text-blue-700',
            'signed'     => 'bg-purple-100 text-purple-700',
            'active'     => 'bg-green-100 text-green-700',
            'expired'    => 'bg-red-100 text-red-700',
            'terminated' => 'bg-red-100 text-red-700',
            default      => 'bg-gray-100 text-gray-500',
        };
    }
    public static function generateReference(): string {
        $year = now()->year;
        $count = self::whereYear('created_at', $year)->count() + 1;
        return 'BA-SLA-' . $year . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }
}
