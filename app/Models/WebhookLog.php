<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    protected $fillable = [
        'source', 'event', 'payload', 'headers', 'ip_address',
        'response_code', 'response_body', 'is_valid',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'headers' => 'array',
            'is_valid' => 'boolean',
        ];
    }
}
