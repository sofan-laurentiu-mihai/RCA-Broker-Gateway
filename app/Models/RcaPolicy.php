<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RcaPolicy extends Model
{
    protected $guarded = [];

    // The policy is issued based on a specific offer
    public function offer()
    {
        return $this->belongsTo(RcaOffer::class, 'offer_id');
    }

    // Connection to the immovable audit log in compliance to the ASF
    public function auditLog()
    {
        return $this->belongsTo(RcaAuditLog::class, 'audit_log_id');
    }

    // We show that the policy belongs to the users who ordered it
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
