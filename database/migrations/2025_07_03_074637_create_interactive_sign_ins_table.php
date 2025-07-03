<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInteractiveSignInsTable extends Migration
{
    public function up()
    {
        Schema::create('interactive_sign_ins', function (Blueprint $table) {
            $table->id();
            $table->timestamp('date_utc')->nullable();
            $table->string('request_id')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('correlation_id')->nullable();

            $table->string('user_id'); // Foreign key to users.id
            $table->string('user')->nullable();
            $table->string('username')->nullable();
            $table->string('user_type')->nullable();
            $table->string('cross_tenant_access_type')->nullable();
            $table->string('incoming_token_type')->nullable();
            $table->string('authentication_protocol')->nullable();
            $table->string('unique_token_identifier')->nullable();
            $table->string('original_transfer_method')->nullable();
            $table->string('client_credential_type')->nullable();
            $table->string('token_protection_sign_in_session')->nullable();

            $table->string('application')->nullable();
            $table->string('application_id')->nullable();
            $table->string('resource')->nullable();
            $table->string('resource_id')->nullable();
            $table->string('resource_tenant_id')->nullable();
            $table->string('resource_owner_tenant_id')->nullable();

            $table->string('home_tenant_id')->nullable();
            $table->string('home_tenant_name')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('location')->nullable();
            $table->string('status')->nullable();
            $table->string('sign_in_error_code')->nullable();
            $table->string('failure_reason')->nullable();

            $table->string('client_app')->nullable();
            $table->string('device_id')->nullable();
            $table->string('browser')->nullable();
            $table->string('operating_system')->nullable();
            $table->string('compliant')->nullable();
            $table->string('managed')->nullable();
            $table->string('join_type')->nullable();

            $table->string('mfa_result')->nullable();
            $table->string('mfa_auth_method')->nullable();
            $table->string('mfa_auth_detail')->nullable();
            $table->string('authentication_requirement')->nullable();
            $table->string('sign_in_identifier')->nullable();
            $table->string('session_id')->nullable();
            $table->string('ip_address_seen_by_resource')->nullable();

            $table->string('through_global_secure_access')->nullable();
            $table->string('global_secure_access_ip_address')->nullable();
            $table->string('autonomous_system_number')->nullable();
            $table->boolean('flagged_for_review')->nullable();

            $table->string('token_issuer_type')->nullable();
            $table->string('incoming_token_type_1')->nullable();
            $table->string('token_issuer_name')->nullable();
            $table->string('latency')->nullable();
            $table->string('conditional_access')->nullable();
            $table->string('managed_identity_type')->nullable();
            $table->string('associated_resource_id')->nullable();
            $table->string('federated_token_id')->nullable();
            $table->string('federated_token_issuer')->nullable();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('interactive_sign_ins');
    }
}
