<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminAuditLog extends Model
{
    protected $fillable = ['admin_source', 'admin_identifier', 'product_key', 'action', 'target_type', 'target_id', 'payload', 'ip_address', 'user_agent'];

    protected $casts = ['payload' => 'array'];
}
