<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateVehicleImagesTable extends Migration
{
    public function up()
    {
        Schema::table('vehicle_images', function (Blueprint $table) {
            // Adicionar coluna order se não existir
            if (!Schema::hasColumn('vehicle_images', 'order')) {
                $table->integer('order')->default(0)->after('vehicle_id');
            }
            
            // Renomear image_path para path se necessário
            if (Schema::hasColumn('vehicle_images', 'image_path') && !Schema::hasColumn('vehicle_images', 'path')) {
                $table->renameColumn('image_path', 'path');
            }
        });
    }

    public function down()
    {
        Schema::table('vehicle_images', function (Blueprint $table) {
            if (Schema::hasColumn('vehicle_images', 'order')) {
                $table->dropColumn('order');
            }
            
            if (Schema::hasColumn('vehicle_images', 'path') && !Schema::hasColumn('vehicle_images', 'image_path')) {
                $table->renameColumn('path', 'image_path');
            }
        });
    }
}
