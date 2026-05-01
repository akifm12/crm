<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CrmTask extends Model {
    protected $fillable = ['crm_client_id','task_description','due_date','priority','status','assigned_to','created_by','completed_at'];
    protected $casts = ['due_date'=>'date','completed_at'=>'datetime'];
    public function client()   { return $this->belongsTo(CrmClient::class, 'crm_client_id'); }
    public function assignee() { return $this->belongsTo(User::class, 'assigned_to'); }
    public function creator()  { return $this->belongsTo(User::class, 'created_by'); }
    public function isOverdue(): bool { return $this->due_date && $this->due_date->isPast() && $this->status !== 'completed'; }
    public function priorityBadge(): string {
        return match($this->priority) {
            'high'   => 'bg-red-100 text-red-700',
            'medium' => 'bg-amber-100 text-amber-700',
            default  => 'bg-gray-100 text-gray-500',
        };
    }
}
