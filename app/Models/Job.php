<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    protected $table = 'jobs';
    public $timestamps = false;

    protected function casts(): array
    {
        return ['payload' => 'array'];
    }

    public function getDisplayNameAttribute(): string
    {
        return class_basename($this->payload['displayName'] ?? $this->payload['job'] ?? 'Unknown');
    }

    public function getStatusAttribute(): string
    {
        return $this->reserved_at ? 'Processing' : 'Pending';
    }
}