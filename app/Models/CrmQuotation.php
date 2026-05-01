<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CrmQuotation extends Model {
    protected $fillable = ['crm_client_id','quotation_template_id','quotation_reference','subject','line_items','subtotal','vat_amount','total_amount','terms','issued_date','valid_until','status','created_by'];
    protected $casts = ['line_items'=>'array','subtotal'=>'decimal:2','vat_amount'=>'decimal:2','total_amount'=>'decimal:2','issued_date'=>'date','valid_until'=>'date'];
    public function client()   { return $this->belongsTo(CrmClient::class, 'crm_client_id'); }
    public function creator()  { return $this->belongsTo(User::class, 'created_by'); }
    public function statusBadge(): string {
        return match($this->status) {
            'draft'    => 'bg-gray-100 text-gray-600',
            'sent'     => 'bg-blue-100 text-blue-700',
            'accepted' => 'bg-green-100 text-green-700',
            'rejected' => 'bg-red-100 text-red-700',
            'expired'  => 'bg-amber-100 text-amber-700',
            default    => 'bg-gray-100 text-gray-500',
        };
    }
    public static function generateReference(): string {
        $year = now()->year;
        $count = self::whereYear('created_at', $year)->count() + 1;
        return 'BA-QT-' . $year . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }
}
