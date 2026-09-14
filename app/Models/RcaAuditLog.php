<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent Model representing the audit trail for all RCA API requests
 * and responses.
 * It tracks user inputs, external provider payloads and system performance
 * metrics.
 */
class RcaAuditLog extends Model
{
    /**
     * Disable mass assignment protection by specifying an empty guarded array.
     * Allows logging dynamic or extensive payloads without listing
     * every database column individually.
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast to native types.
     * Automatically converts stored JSON text from database columns into
     * native PHP associative arrays, and vice-versa.
     */
    protected $casts = [
        'user_input' => 'array',
        'provider_request' => 'array',
        'provider_response' => 'array',
    ];
}
