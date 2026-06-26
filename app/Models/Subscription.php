<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
class Subscription extends Model {
    protected $fillable = ['customer_id','license_type_id','microsoft_tenant_id','microsoft_subscription_id','quantity','start_date','end_date','billing_cycle','custom_price','status','auto_renew','reminder_6m_sent','reminder_1m_sent','reminder_1w_sent','notes'];
    protected $casts = ['start_date'=>'date','end_date'=>'date','auto_renew'=>'boolean','reminder_6m_sent'=>'boolean','reminder_1m_sent'=>'boolean','reminder_1w_sent'=>'boolean','custom_price'=>'float'];

    public function customer(): BelongsTo  { return $this->belongsTo(Customer::class); }
    public function licenseType(): BelongsTo { return $this->belongsTo(LicenseType::class); }
    public function emailLogs(): HasMany   { return $this->hasMany(EmailLog::class); }

    public function scopeActive(Builder $q): Builder     { return $q->where('status','active'); }
    public function scopeExpiringIn(Builder $q, int $days): Builder {
        return $q->whereDate('end_date','>=',now())
                 ->whereDate('end_date','<=',now()->addDays($days));
    }

    public function getEffectivePriceAttribute(): float {
        if ($this->custom_price !== null) return $this->custom_price;
        $lt = $this->relationLoaded('licenseType') ? $this->licenseType : $this->licenseType()->first();
        return $this->billing_cycle === 'monthly'
            ? (($lt->price_monthly ?? 0) * $this->quantity)
            : (($lt->price_yearly  ?? 0) * $this->quantity);
    }
    public function getDaysToExpiryAttribute(): int {
        return (int) now()->startOfDay()->diffInDays($this->end_date->copy()->startOfDay(), false);
    }
    public function getExpiryClassAttribute(): string {
        $d = $this->days_to_expiry;
        if ($d < 0)    return 'expired';
        if ($d <= 30)  return 'critical';
        if ($d <= 90)  return 'warning';
        if ($d <= 180) return 'soon';
        return 'ok';
    }
}
