<?php
declare(strict_types=1);

/**
 * Clean XML Sitemap Handler
 * Serves static sitemap.xml with proper W3C XML headers and Content-Length
 */

$sitemapXml = __DIR__ . '/sitemap.xml';

// Ensure sitemap.xml exists
if (!file_exists($sitemapXml)) {
    require_once __DIR__ . '/generate_sitemap.php';
}

if (file_exists($sitemapXml)) {
    header('Content-Type: application/xml; charset=utf-8');
    header('Cache-Control: public, max-age=86400');
    header('X-Content-Type-Options: nosniff');
    header('Content-Length: ' . (string)filesize($sitemapXml));
    readfile($sitemapXml);
    exit;
}

http_response_code(500);
header('Content-Type: text/plain; charset=utf-8');
echo "Error: sitemap.xml could not be generated.";
exit;
