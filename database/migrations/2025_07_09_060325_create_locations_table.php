<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campus_id')->constrained()->onDelete('cascade'); // 綁定校區
            $table->string('name');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('locations'); }
};