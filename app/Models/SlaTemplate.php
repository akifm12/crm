<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class SlaTemplate extends Model {
    protected $fillable = ['name','service_type','description','scope_of_work','client_obligations','deliverables','duration','default_fee','fee_frequency','payment_terms','termination_clause','governing_law','is_active','created_by'];
    protected $casts = ['is_active'=>'boolean','default_fee'=>'decimal:2'];
    public function slas() { return $this->hasMany(CrmSla::class, 'sla_template_id'); }
}
