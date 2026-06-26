<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Customer extends Model {
    protected $fillable = ['first_name','last_name','email','phone','company','fiscal_code','vat_number','address','city','notes','is_active'];
    protected $casts = ['is_active'=>'boolean'];
    public function subscriptions(): HasMany { return $this->hasMany(Subscription::class); }
    public function getFullNameAttribute(): string { return "{$this->first_name} {$this->last_name}"; }
    public function getActiveSubscriptionsCountAttribute(): int {
        return $this->subscriptions()->where('status','active')->count();
    }
}
