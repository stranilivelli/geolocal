<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Location;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Importa i dati dal dump SQL di Joomla (sp8r4_gmapfp).
 *
 * Utilizzo:
 *   php artisan db:seed --class=JoomlaImportSeeder
 *
 * Prima di eseguire: impostare DB_JOOMLA_* in .env
 * oppure esportare sp8r4_gmapfp e sp8r4_gmapfp_classements in CSV
 * e modificare il metodo getData().
 */
class JoomlaImportSeeder extends Seeder
{
    // Mappa catid Joomla → id categoria nel nuovo schema
    private array $catMap = [
        42  => 'analisi-cliniche',
        44  => 'ecografia',
        45  => 'radiologia',
        46  => 'cardiologia',
        47  => 'visite-specialistiche',
        48  => 'risonanza-magnetica',
        49  => 'neurologia',
        50  => 'fisioterapia',
        51  => 'ortopedia',
        52  => 'ginecologia',
        53  => 'oculistica',
        54  => 'otorinolaringoiatria',
        55  => 'urologia',
        56  => 'odontoiatria',
        57  => 'tac',
        58  => 'psicologia',
        60  => 'endoscopia',
        65  => 'pneumologia',
        67  => 'pediatria',
        75  => 'logopedia',
        78  => 'neuropsichiatria-infantile',
        83  => 'chirurgia',
        85  => 'erboristeria',
        95  => 'terme-e-benessere',
        108 => 'assistenza-domiciliare',
    ];

    public function run(): void
    {
        // 1. Seed categorie
        foreach (Category::defaults() as $cat) {
            Category::updateOrCreate(
                ['slug' => Str::slug($cat['name'])],
                ['name' => $cat['name'], 'published' => true, 'ordering' => $cat['id']]
            );
        }

        $categoryBySlug = Category::pluck('id', 'slug');

        // 2. Leggi le righe Joomla (da connessione separata o array hardcoded)
        $rows = $this->getData();

        foreach ($rows as $row) {
            // Pulizia HTML dal campo intro
            $intro = $row['introtext']
                ? strip_tags(html_entity_decode($row['introtext'], ENT_QUOTES, 'UTF-8'))
                : null;

            // Rileva convenzione indiretta dal testo (pattern comune nel dump)
            $isIndiretta = $intro && str_contains(strtolower($intro), 'indiretta');

            $location = Location::updateOrCreate(
                ['slug' => Str::slug($row['nom']) . '-' . $row['id']],
                [
                    'name'             => trim($row['nom']),
                    'address'          => $row['adresse'] ?: null,
                    'address2'         => $row['adresse2'] ?: null,
                    'city'             => $row['ville'] ?: null,
                    'province'         => strtoupper($row['departement'] ?: ''),
                    'postal_code'      => $row['codepostal'] ?: null,
                    'country'          => $row['pay'] ?: 'Italia',
                    'phone'            => $row['phone'] ?? null,
                    'lat'              => $row['glat'] ?: null,
                    'lng'              => $row['glng'] ?: null,
                    'zoom'             => (int) ($row['gzoom'] ?: 13),
                    'intro'            => $intro,
                    'published'        => (bool) $row['published'],
                    'featured'         => (bool) $row['featured'],
                    'convention_type'  => $isIndiretta ? 'indiretta' : 'diretta',
                    'hits'             => (int) ($row['hits'] ?? 0),
                ]
            );

            // 3. Collega categorie
            $catIds = [];
            $catidList = explode(',', $row['catid'] ?? '');
            foreach ($catidList as $joomlaCatId) {
                $joomlaCatId = (int) trim($joomlaCatId);
                if (isset($this->catMap[$joomlaCatId])) {
                    $slug = $this->catMap[$joomlaCatId];
                    if (isset($categoryBySlug[$slug])) {
                        $catIds[] = $categoryBySlug[$slug];
                    }
                }
            }
            if (!empty($catIds)) {
                $location->categories()->syncWithoutDetaching($catIds);
            }
        }

        $this->command->info('Import completato: ' . count($rows) . ' strutture importate.');
    }

    /**
     * Sostituisci questo metodo con la lettura reale dal dump SQL Joomla.
     * Opzione A: connessione DB Joomla configurata in config/database.php
     * Opzione B: parse del file .sql con un parser dedicato
     * Opzione C: esporta sp8r4_gmapfp in CSV e leggilo qui
     */
    private function getData(): array
    {
        // Esempio con lettura da DB Joomla su connessione separata:
        // return DB::connection('joomla')->table('sp8r4_gmapfp')->get()->toArray();

        // Per ora restituisce array vuoto — da sostituire
        return [];
    }
}
