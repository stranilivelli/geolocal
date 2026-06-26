<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'published', 'ordering'];

    protected $casts = [
        'published' => 'boolean',
    ];

    // Auto-genera lo slug dal nome
    protected static function booted(): void
    {
        static::creating(function (Category $cat) {
            if (empty($cat->slug)) {
                $cat->slug = Str::slug($cat->name);
            }
        });
    }

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class);
    }

    // Le categorie dal dump Joomla, già normalizzate
    public static function defaults(): array
    {
        return [
            ['id' => 42,  'name' => 'Analisi cliniche'],
            ['id' => 44,  'name' => 'Ecografia'],
            ['id' => 45,  'name' => 'Radiologia'],
            ['id' => 46,  'name' => 'Cardiologia'],
            ['id' => 47,  'name' => 'Visite specialistiche'],
            ['id' => 48,  'name' => 'Risonanza magnetica'],
            ['id' => 49,  'name' => 'Neurologia'],
            ['id' => 50,  'name' => 'Fisioterapia'],
            ['id' => 51,  'name' => 'Ortopedia'],
            ['id' => 52,  'name' => 'Ginecologia'],
            ['id' => 53,  'name' => 'Oculistica'],
            ['id' => 54,  'name' => 'Otorinolaringoiatria'],
            ['id' => 55,  'name' => 'Urologia'],
            ['id' => 56,  'name' => 'Odontoiatria'],
            ['id' => 57,  'name' => 'TAC'],
            ['id' => 58,  'name' => 'Psicologia'],
            ['id' => 60,  'name' => 'Endoscopia'],
            ['id' => 65,  'name' => 'Pneumologia'],
            ['id' => 67,  'name' => 'Pediatria'],
            ['id' => 75,  'name' => 'Logopedia'],
            ['id' => 78,  'name' => 'Neuropsichiatria infantile'],
            ['id' => 83,  'name' => 'Chirurgia'],
            ['id' => 85,  'name' => 'Erboristeria'],
            ['id' => 95,  'name' => 'Terme e benessere'],
            ['id' => 108, 'name' => 'Assistenza domiciliare'],
        ];
    }
}
