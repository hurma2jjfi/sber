<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('products', function (Blueprint $table) {
        $table->text('name')->change(); // Изменяем на TEXT
        // ИЛИ для увеличения длины VARCHAR:
        // $table->string('name', 500)->change();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down()
{
    Schema::table('products', function (Blueprint $table) {
        $table->string('name', 255)->change(); // Возвращаем исходное значение
    });
}
};
