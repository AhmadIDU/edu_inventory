<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FailedJob extends Model
{
    protected $table = 'failed_jobs';
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'payload'   => 'array',
            'failed_at' => 'datetime',
        ];
    }

    public function getDisplayNameAttribute(): string
    {
        return class_basename($this->payload['displayName'] ?? $this->payload['job'] ?? 'Unknown');
    }
}