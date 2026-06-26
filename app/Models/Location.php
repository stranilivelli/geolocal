<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class Location extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'address', 'address2', 'city', 'province',
        'postal_code', 'country', 'phone', 'email', 'website',
        'lat', 'lng', 'zoom', 'intro', 'full_text', 'hours_prices',
        'image', 'convention_type', 'published', 'featured',
        'notes', 'hits', 'published_at',
    ];

    protected $casts = [
        'published'    => 'boolean',
        'featured'     => 'boolean',
        'lat'          => 'float',
        'lng'          => 'float',
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Location $loc) {
            if (empty($loc->slug)) {
                $loc->slug = Str::slug($loc->name);
            }
        });

        static::updating(function (Location $loc) {
            if ($loc->isDirty('published') && $loc->published && !$loc->published_at) {
                $loc->published_at = now();
            }
        });

        // Invalida la cache API quando una struttura viene modificata
        $flushCache = fn () => Cache::flush();
        static::saved($flushCache);
        static::deleted($flushCache);
        static::restored($flushCache);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    // Scope per il frontend pubblico
    public function scopePublished($query)
    {
        return $query->where('published', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    public function scopeInCity($query, string $city)
    {
        return $query->where('city', $city);
    }

    public function scopeInProvince($query, string $province)
    {
        return $query->where('province', strtoupper($province));
    }

    public function scopeWithCategory($query, int|array $categoryIds)
    {
        return $query->whereHas('categories', fn ($q) =>
            $q->whereIn('categories.id', (array) $categoryIds)
        );
    }

    // Calcola distanza in km da un punto (formula Haversine)
    public function scopeNearby($query, float $lat, float $lng, float $radiusKm = 30)
    {
        return $query->selectRaw("
            *,
            ROUND(
                6371 * ACOS(
                    COS(RADIANS(?)) * COS(RADIANS(lat))
                    * COS(RADIANS(lng) - RADIANS(?))
                    + SIN(RADIANS(?)) * SIN(RADIANS(lat))
                ), 1
            ) AS distance_km
        ", [$lat, $lng, $lat])
        ->having('distance_km', '<=', $radiusKm)
        ->orderBy('distance_km');
    }

    // Incrementa hit in modo sicuro (no N+1)
    public function incrementHit(): void
    {
        $this->timestamps = false;
        $this->increment('hits');
        $this->timestamps = true;
    }

    // Helper per il frontend: indirizzo completo
    public function getFullAddressAttribute(): string
    {
        return collect([$this->address, $this->address2, $this->city, $this->province])
            ->filter()
            ->implode(', ');
    }

    // Attributo virtuale per il campo mappa di Filament Google Maps
    public function getLocationAttribute(): array
    {
        return [
            'lat' => (float) ($this->attributes['lat'] ?? 0),
            'lng' => (float) ($this->attributes['lng'] ?? 0),
        ];
    }

    public function setLocationAttribute(array $location): void
    {
        $this->attributes['lat'] = round((float) ($location['lat'] ?? 0), 8);
        $this->attributes['lng'] = round((float) ($location['lng'] ?? 0), 8);
    }
}
