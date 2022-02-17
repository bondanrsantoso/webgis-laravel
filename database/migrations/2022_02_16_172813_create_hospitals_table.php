<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hospitals', function (Blueprint $table) {
            $table->id();
            $table->string('provinsi');
            $table->string("kotakab");
            $table->string("tipefaskes");
            $table->string("kodefaskes");
            $table->string("namafaskes");
            $table->decimal("lat", 8, 5);
            $table->decimal("lng", 8, 5);
            $table->text("alamatfaskes")->nullable();
            $table->string("telpfaskes")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hospitals');
    }
};
