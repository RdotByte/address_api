<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('postcodes', function (Blueprint $table) {
            $table->id();
            $table->string('postcode', 8)->unique();
            $table->string('postcode_trimmed', 7)->index();
            $table->decimal('latitude', 11, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('country')->nullable();
            $table->string('nhs_ha')->nullable();
            $table->string('admin_county')->nullable();
            $table->string('admin_district')->nullable();
            $table->string('admin_ward')->nullable();
            $table->integer('quality')->default(0);
            $table->string('constituency')->nullable();
            $table->string('european_electoral_region')->nullable();
            $table->string('primary_care_trust')->nullable();
            $table->string('region')->nullable();
            $table->string('parish')->nullable();
            $table->string('lsoa')->nullable();
            $table->string('msoa')->nullable();
            $table->string('nuts')->nullable();
            $table->string('incode', 3)->index();
            $table->string('outcode', 4)->index();
            $table->geometry('location')->spatialIndex();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('postcodes');
    }
};
