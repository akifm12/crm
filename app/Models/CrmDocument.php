<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CrmDocument extends Model {
    protected $fillable = ['crm_client_id','document_type','document_label','file_path','file_name','mime_type','file_size','expiry_date','notes','uploaded_by'];
    protected $casts = ['expiry_date'=>'date'];
    public function client()   { return $this->belongsTo(CrmClient::class, 'crm_client_id'); }
    public function uploader() { return $this->belongsTo(User::class, 'uploaded_by'); }
    public function isExpired(): bool { return $this->expiry_date && $this->expiry_date->isPast(); }
    public function isExpiringSoon(): bool { return $this->expiry_date && $this->expiry_date->isFuture() && $this->expiry_date->diffInDays(now()) <= 30; }
    public function fileSizeFormatted(): string {
        if (!$this->file_size) return '—';
        $kb = $this->file_size / 1024;
        return $kb > 1024 ? round($kb/1024,1).' MB' : round($kb,0).' KB';
    }
    public static function documentTypes(): array {
        return [
            ['type'=>'trade_license',      'label'=>'Trade licence',                   'has_expiry'=>true],
            ['type'=>'moa',                'label'=>'Memorandum of Association',        'has_expiry'=>false],
            ['type'=>'certificate_incorp', 'label'=>'Certificate of incorporation',     'has_expiry'=>false],
            ['type'=>'signatory_passport', 'label'=>'Authorised signatory passport',    'has_expiry'=>true],
            ['type'=>'signatory_eid',      'label'=>'Signatory Emirates ID',            'has_expiry'=>true],
            ['type'=>'shareholder_passport','label'=>'Shareholder passport',            'has_expiry'=>true],
            ['type'=>'vat_certificate',    'label'=>'VAT registration certificate',     'has_expiry'=>false],
            ['type'=>'other',              'label'=>'Other document',                   'has_expiry'=>false],
        ];
    }
}
