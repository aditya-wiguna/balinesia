<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class JobPosting extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_id',
        'company_name',
        'job_title',
        'slug',
        'description',
        'requirements',
        'location',
        'employment_type',
        'category',
        'salary_range',
        'source_url',
        'source_name',
        'is_remote',
        'posted_date',
        'expires_date',
        'is_active',
        'is_approved',
    ];

    protected function casts(): array
    {
        return [
            'is_remote' => 'boolean',
            'is_active' => 'boolean',
            'is_approved' => 'boolean',
            'posted_date' => 'datetime',
            'expires_date' => 'datetime',
        ];
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByLocation($query, string $location)
    {
        return $query->where('location', 'like', "%{$location}%");
    }

    public function scopeByEmploymentType($query, string $type)
    {
        return $query->where('employment_type', $type);
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_date')
                ->orWhere('expires_date', '>', now());
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function booted(): void
    {
        static::creating(function (JobPosting $job) {
            if (empty($job->slug)) {
                $job->slug = static::generateUniqueSlug($job->job_title);
            }
        });
    }

    public static function generateUniqueSlug(string $title): string
    {
        $base = Str::slug($title, '-');
        $slug = $base;
        $counter = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
