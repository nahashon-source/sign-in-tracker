<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\InteractiveSignIn;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public $timestamps = false; // ✅ Prevent Laravel from expecting created_at/updated_at columns

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'date_utc',
        'request_id',
        'user_agent',
        'correlation_id',
        'user_id',
        'user',
        'username',
        'user_type',
        'cross_tenant_access_type',
        'incoming_token_type',
        'authentication_protocol',
        'unique_token_identifier',
        'original_transfer_method',
        'client_credential_type',
        'token_protection_sign_in_session',
        'application',
        'application_id',
        'resource',
        'resource_id',
        'resource_tenant_id',
        'resource_owner_tenant_id',
        'home_tenant_id',
        'home_tenant_name',
        'ip_address',
        'location',
        'status',
        'sign_in_error_code',
        'failure_reason',
        'client_app',
        'device_id',
        'browser',
        'operating_system',
        'compliant',
        'managed',
        'join_type',
        'mfa_result',
        'mfa_auth_method',
        'mfa_auth_detail',
        'authentication_requirement',
        'sign_in_identifier',
        'session_id',
        'ip_address_seen_by_resource',
        'through_global_secure_access',
        'global_secure_access_ip_address',
        'autonomous_system_number',
        'flagged_for_review',
        'token_issuer_type',
        'incoming_token_type_1',
        'token_issuer_name',
        'latency',
        'conditional_access',
        'managed_identity_type',
        'associated_resource_id',
        'federated_token_id',
        'federated_token_issuer',
    ];
    

    // Relationships
    public function signIns()
    {
        return $this->hasMany(InteractiveSignIn::class, 'user_id', 'id');
        
    }
}
