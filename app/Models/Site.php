<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_online' => 'boolean',
        'is_resolving' => 'boolean',
        'archived_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function checks()
    {
        return $this->hasMany(Check::class);
    }

    public function scopeOffline($query)
    {
        return $query->where('is_online', false);
    }

    public function scopeArchived($query)
    {
        return $query->whereNotNull('archived_at');
    }

    public function scopeActive($query)
    {
        return $query->whereNull('archived_at');
    }

    public function isCurrentlyResolving(): bool
    {
        $host = parse_url($this->url)['host'];
        return gethostbyname($host) !== $host;
    }

    public function archive(): void
    {
        $this->update(['archived_at' => now()]);
    }
}
