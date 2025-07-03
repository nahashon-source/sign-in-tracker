<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InteractiveSignIn extends Model
{
    use HasFactory;

    protected $table = 'interactive_sign_ins';

    public $timestamps = false; // Your table does NOT have created_at or updated_at

    protected $fillable = [
        'Date(UTC)',
        'Request ID',
        'User agent',
        'Correlation ID',
        'User ID',
        'User',
        'Username',
        'User type',
        'Cross tenant access type',
        'Incoming token type',
        'Authentication Protocol',
        'Unique token identifier',
        'Original transfer method',
        'Client credential type',
        'Token Protection - Sign In Session',
        'Application',
        'Application ID',
        'Resource',
        'Resource ID',
        'Resource tenant ID',
        'Resource owner tenant ID',
        'Home tenant ID',
        'Home tenant name',
        'IP address',
        'Location',
        'Status',
        'Sign-in error code',
        'Failure reason',
        'Client app',
        'Device ID',
        'Browser',
        'Operating System',
        'Compliant',
        'Managed',
        'Join Type',
        'Multifactor authentication result',
        'Multifactor authentication auth method',
        'Multifactor authentication auth detail',
        'Authentication requirement',
        'Sign-in identifier',
        'Session ID',
        'IP address(seen by resource)',
        'Through Global Secure Access',
        'Global Secure Access IP address',
        'Autonomous system number',
        'Flagged for review',
        'Token issuer type',
        'Incoming token type(1)',
        'Token issuer name',
        'Latency',
        'Conditional Access',
        'Managed identity type',
        'Associated Resource Id',
        'Federated Token Id',
        'Federated Token Issuer',
    ];

    // 👇 Optional relationship to the User model
    public function user()
    {
        return $this->belongsTo(User::class, 'User ID', 'id');
    }
}
