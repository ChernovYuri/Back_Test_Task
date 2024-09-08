<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            if (! Schema::hasTable('users')) {
                $table->uuid('id')->primary();
                $table->string('last_name', 40);
                $table->string('name', 40);
                $table->string('middle_name', 40)->nullable();
                $table->string('email', 80)->unique();
                $table->string('phone', 20)->nullable();
                $table->timestamps();
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
}
