<?php
declare(strict_types=1);

/**
 * Static XML Sitemap Generator
 * Programmatically maps homepage + all 81 City x EV permutations
 * Generates W3C sitemap.org 0.9 compliant sitemap.xml
 */

$dataFile = __DIR__ . '/data.json';
if (!file_exists($dataFile)) {
    fwrite(STDERR, "Missing data.json dataset.\n");
    exit(1);
}

$data = json_decode(file_get_contents($dataFile), true);
$cities = $data['cities'] ?? [];
$evs = $data['evs'] ?? [];
$baseUrl = 'https://calc.intelligentwebdevelopers.com';
$lastMod = date('Y-m-d');

$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
$xml .= "  <!-- Homepage -->\n";
$xml .= "  <url>\n";
$xml .= "    <loc>" . htmlspecialchars($baseUrl . '/') . "</loc>\n";
$xml .= "    <lastmod>{$lastMod}</lastmod>\n";
$xml .= "    <changefreq>daily</changefreq>\n";
$xml .= "    <priority>1.0</priority>\n";
$xml .= "  </url>\n";

foreach ($cities as $citySlug => $city) {
    foreach ($evs as $evSlug => $ev) {
        $loc = htmlspecialchars("{$baseUrl}/{$citySlug}/{$evSlug}");
        $xml .= "  <url>\n";
        $xml .= "    <loc>{$loc}</loc>\n";
        $xml .= "    <lastmod>{$lastMod}</lastmod>\n";
        $xml .= "    <changefreq>weekly</changefreq>\n";
        $xml .= "    <priority>0.8</priority>\n";
        $xml .= "  </url>\n";
    }
}

$xml .= "</urlset>\n";

$targetFile = __DIR__ . '/sitemap.xml';
file_put_contents($targetFile, $xml);
echo "Sitemap generated successfully: " . strlen($xml) . " bytes written to " . $targetFile . "\n";
