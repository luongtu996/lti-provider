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
        Schema::create('lti_keys', function (Blueprint $table) {
            $table->id();
            $table->uuid('kid');
            $table->text('private_key');
            $table->text('public_key');
            $table->timestamps();
        });

        Schema::create('lti_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('issuer')->nullable();
            $table->string('client_id')->nullable();
            $table->string('platform_login_auth_endpoint')->nullable();
            $table->string('platform_auth_token_endpoint')->nullable();
            $table->string('platform_key_set_endpoint')->nullable();
            $table->unsignedBigInteger('lti_key_id');
            $table->foreign('lti_key_id')->references('id')->on('lti_keys')->onDelete('cascade');
            $table->timestamps();
            $table->unique(array('issuer', 'client_id'));
        });

        Schema::create('lti_deployments', function (Blueprint $table) {
            $table->id();
            $table->string('deployment_id');
            $table->unsignedBigInteger('lti_registration_id');
            $table->foreign('lti_registration_id')->references('id')->on('lti_registrations')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lti_keys');
        Schema::dropIfExists('lti_registrations');
        Schema::dropIfExists('lti_deployments');
    }
};
