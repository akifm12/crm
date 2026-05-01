<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class QuotationTemplate extends Model {
    protected $fillable = ['name','service_type','description','line_items','terms','validity_days','is_active','created_by'];
    protected $casts = ['line_items'=>'array','is_active'=>'boolean'];
}