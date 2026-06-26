<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Location;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class JoomlaImportSeeder extends Seeder
{
    private array $catMap = [
        42 => 'analisi-cliniche',   44 => 'ecografia',
        45 => 'radiologia',         46 => 'cardiologia',
        47 => 'visite-specialistiche', 48 => 'risonanza-magnetica',
        49 => 'neurologia',         50 => 'fisioterapia',
        51 => 'ortopedia',          52 => 'ginecologia',
        53 => 'oculistica',         54 => 'otorinolaringoiatria',
        55 => 'urologia',           56 => 'odontoiatria',
        57 => 'tac',                58 => 'psicologia',
        60 => 'endoscopia',         65 => 'pneumologia',
        67 => 'pediatria',          75 => 'logopedia',
        78 => 'neuropsichiatria-infantile', 83 => 'chirurgia',
        85 => 'erboristeria',       95 => 'terme-e-benessere',
        108 => 'assistenza-domiciliare',
    ];

    public function run(): void
    {
        // 1. Seed categorie
        foreach (Category::defaults() as $cat) {
            Category::updateOrCreate(
                ['slug' => Str::slug($cat['name'])],
                ['name' => $cat['name'], 'published' => true]
            );
        }
        $categoryBySlug = Category::pluck('id', 'slug');

        // 2. Leggi e parsa il file SQL
        $sqlFile = database_path('seeders/data/gmapfp.sql');

        if (!file_exists($sqlFile)) {
            $this->command->error('File non trovato: ' . $sqlFile);
            $this->command->info('Salva il dump SQL come: database/seeders/data/gmapfp.sql');
            return;
        }

        $rows = $this->parseSqlFile($sqlFile);
        $this->command->info('Righe trovate nel dump: ' . count($rows));

        $imported = 0;
        $skipped  = 0;

        foreach ($rows as $row) {
            $name = trim($row['nom'] ?? '');
            if (empty($name)) { $skipped++; continue; }

            $baseSlug = Str::slug($row['alias'] ?: $name);
            if (empty($baseSlug)) $baseSlug = 'struttura';
            $slug = $baseSlug . '-' . $row['id'];

            $intro = $row['introtext'] ?? '';
            if ($intro) {
                $intro = html_entity_decode($intro, ENT_QUOTES, 'UTF-8');
                $intro = strip_tags($intro);
                $intro = trim(preg_replace('/\s+/', ' ', $intro));
            }

            $isIndiretta = $intro && stripos($intro, 'indiretta') !== false;
            if (!$isIndiretta && stripos($name, 'indiretta') !== false) {
                $isIndiretta = true;
            }

            $lat = $row['glat'] ?? '';
            $lng = $row['glng'] ?? '';
            $lat = ($lat && $lat !== '0') ? (float) $lat : null;
            $lng = ($lng && $lng !== '0') ? (float) $lng : null;

            $zoom = (int) ($row['gzoom'] ?? 13);
            if ($zoom < 1 || $zoom > 21) $zoom = 13;

            $province = strtoupper(trim($row['departement'] ?? ''));
            if (strlen($province) > 5) $province = substr($province, 0, 5);

            try {
                $location = Location::updateOrCreate(
                    ['slug' => $slug],
                    [
                        'name'            => $name,
                        'address'         => trim($row['adresse'] ?? '') ?: null,
                        'address2'        => trim($row['adresse2'] ?? '') ?: null,
                        'city'            => trim($row['ville'] ?? '') ?: null,
                        'province'        => $province ?: null,
                        'postal_code'     => trim($row['codepostal'] ?? '') ?: null,
                        'country'         => trim($row['pay'] ?? '') ?: 'Italia',
                        'phone'           => $this->extractPhone($intro),
                        'email'           => trim($row['email'] ?? '') ?: null,
                        'website'         => trim($row['web'] ?? '') ?: null,
                        'lat'             => $lat,
                        'lng'             => $lng,
                        'zoom'            => $zoom,
                        'intro'           => $intro ?: null,
                        'convention_type' => $isIndiretta ? 'indiretta' : 'diretta',
                        'published'       => (bool) ($row['published'] ?? false),
                        'featured'        => (bool) ($row['featured'] ?? false),
                        'hits'            => (int) ($row['hits'] ?? 0),
                    ]
                );

                $catIds = [];
                $catidRaw = $row['catid'] ?? '';
                if ($catidRaw && $catidRaw !== '0') {
                    foreach (explode(',', $catidRaw) as $joomlaCatId) {
                        $id = (int) trim($joomlaCatId);
                        if (isset($this->catMap[$id], $categoryBySlug[$this->catMap[$id]])) {
                            $catIds[] = $categoryBySlug[$this->catMap[$id]];
                        }
                    }
                }
                if (!empty($catIds)) {
                    $location->categories()->sync(array_unique($catIds));
                }

                $imported++;
            } catch (\Exception $e) {
                $this->command->warn("Riga {$row['id']} saltata: " . $e->getMessage());
                $skipped++;
            }
        }

        $this->command->info("Import completato: {$imported} strutture importate, {$skipped} saltate.");
    }

    private function extractPhone(string $text): ?string
    {
        if (preg_match('/(?:Tel\.?|TEL:?|Cel\.?|Cell\.?)[\s]*([\d\/\s\.\-\+]{6,20})/i', $text, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    private function parseSqlFile(string $path): array
    {
        $content = file_get_contents($path);

        $columns = [
            'id','nom','alias','sef','adresse','adresse2','ville','departement',
            'codepostal','pay','email','web','img','album','introtext','fulltext',
            'horaires_prix','link','article_id','icon','icon_label','glng','glat',
            'gzoom','kml','catid','created_by','published','featured','access',
            'checked_out','checked_out_time','metadesc','metakey','metadata',
            'hits','version','modified','modified_by','created','publish_up',
            'publish_down','ordering','attribs','index_marqueur','language',
            'classements','labels','pictos',
        ];

        $rows = [];
        preg_match_all('/INSERT INTO `sp8r4_gmapfp`[^;]+;/s', $content, $matches);

        foreach ($matches[0] as $insertBlock) {
            if (!preg_match('/VALUES\s*(.+)$/si', $insertBlock, $vm)) continue;
            $valuesSection = rtrim(trim($vm[1]), ';');
            $tuples = $this->splitTuples($valuesSection);

            foreach ($tuples as $tuple) {
                $values = $this->parseValues($tuple);
                if (count($values) !== count($columns)) continue;
                $rows[] = array_combine($columns, $values);
            }
        }

        return $rows;
    }

    private function splitTuples(string $valuesSection): array
    {
        $tuples = [];
        $depth  = 0;
        $inStr  = false;
        $escape = false;
        $start  = null;
        $len    = strlen($valuesSection);

        for ($i = 0; $i < $len; $i++) {
            $c = $valuesSection[$i];
            if ($escape) { $escape = false; continue; }
            if ($c === '\\' && $inStr) { $escape = true; continue; }
            if ($c === "'" && !$escape) { $inStr = !$inStr; continue; }
            if ($inStr) continue;

            if ($c === '(' && $depth === 0) { $start = $i + 1; $depth++; }
            elseif ($c === '(' && $depth > 0) { $depth++; }
            elseif ($c === ')' && $depth > 1) { $depth--; }
            elseif ($c === ')' && $depth === 1) {
                $tuples[] = substr($valuesSection, $start, $i - $start);
                $depth = 0; $start = null;
            }
        }
        return $tuples;
    }

    private function parseValues(string $tuple): array
    {
        $values  = [];
        $current = '';
        $inStr   = false;
        $escape  = false;
        $len     = strlen($tuple);

        for ($i = 0; $i < $len; $i++) {
            $c = $tuple[$i];
            if ($escape) {
                $current .= match($c) {
                    "'" => "'", '\\' => '\\',
                    'r' => '', 'n' => ' ', default => $c,
                };
                $escape = false; continue;
            }
            if ($c === '\\' && $inStr) { $escape = true; continue; }
            if ($c === "'" && !$inStr) { $inStr = true; continue; }
            if ($c === "'" && $inStr)  { $inStr = false; continue; }
            if ($inStr) { $current .= $c; continue; }
            if ($c === ',') { $values[] = $this->castValue(trim($current)); $current = ''; }
            else { $current .= $c; }
        }
        if ($current !== '' || count($values) > 0) {
            $values[] = $this->castValue(trim($current));
        }
        return $values;
    }

    private function castValue(string $v): mixed
    {
        if (strtoupper($v) === 'NULL') return null;
        if (is_numeric($v)) return $v + 0;
        return $v;
    }
}
