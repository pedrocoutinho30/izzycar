<?php 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->decimal('warranty_price', 10, 2)->nullable(); 
            $table->date('warranty_start_date')->nullable()->after('warranty_price');
            $table->date('warranty_end_date')->nullable()->after('warranty_start_date');
            $table->string('warranty_type', 255)->nullable()->after('warranty_end_date');
            $table->string('payment_type', 255)->nullable()->after('warranty_type');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'warranty_price',
                'warranty_start_date',
                'warranty_end_date',
                'warranty_type',
                'payment_type',
            ]);
        });
    }
};
