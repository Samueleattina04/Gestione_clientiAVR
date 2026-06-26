<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class LicenseType extends Model {
    protected $fillable = ['name','category','description','price_monthly','price_yearly','is_active'];
    protected $casts = ['is_active'=>'boolean','price_monthly'=>'float','price_yearly'=>'float'];
    public function subscriptions(): HasMany { return $this->hasMany(Subscription::class); }
}
