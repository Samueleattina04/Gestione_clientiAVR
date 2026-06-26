<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('license_type_id')->constrained()->cascadeOnDelete();
            $table->string('microsoft_tenant_id')->nullable();
            $table->string('microsoft_subscription_id')->nullable();
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('billing_cycle', ['monthly', 'yearly'])->default('yearly');
            $table->decimal('custom_price', 10, 2)->nullable();
            $table->enum('status', ['active', 'expired', 'cancelled', 'suspended'])->default('active');
            $table->boolean('auto_renew')->default(true);
            $table->boolean('reminder_6m_sent')->default(false);
            $table->boolean('reminder_1m_sent')->default(false);
            $table->boolean('reminder_1w_sent')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('subscriptions'); }
};
