<?php
declare(strict_types=1);

/**
 * Dynamic XML Sitemap Generator
 * Programmatically maps homepage + all City x EV permutations
 * Zero external dependencies, valid sitemap.org 0.9 schema
 */

header('Content-Type: application/xml; charset=utf-8');

$dataFile = __DIR__ . '/data.json';
if (!file_exists($dataFile)) {
    http_response_code(500);
    echo '<?xml version="1.0" encoding="UTF-8"?><error>Missing dataset</error>';
    exit;
}

$data = json_decode(file_get_contents($dataFile), true);
$cities = $data['cities'] ?? [];
$evs = $data['evs'] ?? [];

// Resolve base URL
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
$baseUrl = "{$scheme}://{$host}";

// Format last modified date (W3C ISO 8601 format)
$lastMod = date('Y-m-d');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <!-- Homepage -->
    <url>
        <loc><?= htmlspecialchars($baseUrl . '/') ?></loc>
        <lastmod><?= $lastMod ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>

    <!-- Programmatic SEO Routes: City x EV Permutations -->
    <?php foreach ($cities as $citySlug => $city): ?>
        <?php foreach ($evs as $evSlug => $ev): ?>
            <url>
                <loc><?= htmlspecialchars("{$baseUrl}/{$citySlug}/{$evSlug}") ?></loc>
                <lastmod><?= $lastMod ?></lastmod>
                <changefreq>weekly</changefreq>
                <priority>0.8</priority>
            </url>
        <?php endforeach; ?>
    <?php endforeach; ?>
</urlset>
