<?php
declare(strict_types=1);

/**
 * Solar & EV Charging Payback Calculator
 * Production-ready Programmatic SEO Front Controller
 * Zero-dependency, PHP 8+ compatible, Apache & Hostinger ready
 */

// Route handling for PHP built-in CLI webserver
if (php_sapi_name() === 'cli-server') {
    $uriPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if ($uriPath === '/sitemap.xml') {
        require __DIR__ . '/sitemap.php';
        exit;
    }
    if ($uriPath !== '/' && file_exists(__DIR__ . $uriPath)) {
        return false; // Serve static file directly
    }
}

// Global Agent-Ready Response Headers (RFC 8288 Link & Content-Signal)
header('Content-Signal: search=yes, ai-train=no');
header('Link: <https://calc.intelligentwebdevelopers.com/llms.txt>; rel="describedby"; type="text/markdown", <https://calc.intelligentwebdevelopers.com/?format=markdown>; rel="alternate"; type="text/markdown", <https://calc.intelligentwebdevelopers.com/.well-known/mcp/server-card.json>; rel="service-desc"; type="application/json"');

// Load dataset
$dataFile = __DIR__ . '/data.json';
if (!file_exists($dataFile)) {
    http_response_code(500);
    echo "<h1>Configuration Error</h1><p>Missing data.json dataset.</p>";
    exit;
}

$data = json_decode(file_get_contents($dataFile), true);
if (!$data || !isset($data['cities'], $data['evs'])) {
    http_response_code(500);
    echo "<h1>Configuration Error</h1><p>Invalid data.json schema.</p>";
    exit;
}

$site = $data['site'] ?? [];
$cities = $data['cities'] ?? [];
$evs = $data['evs'] ?? [];
$monetization = $data['monetization'] ?? [];

// Resolve canonical host and base URL
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
$currentBaseUrl = "{$scheme}://{$host}";

// Extract route
$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$routeParam = $_GET['route'] ?? null;

$path = $routeParam ? trim($routeParam, '/') : trim($requestUri, '/');
$segments = $path === '' ? [] : explode('/', $path);

$isHomepage = false;
$is404 = false;
$selectedCity = null;
$selectedEv = null;

if (empty($segments)) {
    // Homepage: Load default city and EV
    $isHomepage = true;
    $defaultCitySlug = $site['default_city'] ?? 'bhubaneswar-od';
    $defaultEvSlug = $site['default_ev'] ?? 'tata-nexon-ev';
    $selectedCity = $cities[$defaultCitySlug] ?? reset($cities);
    $selectedEv = $evs[$defaultEvSlug] ?? reset($evs);
} elseif (count($segments) === 2) {
    $citySlug = $segments[0];
    $evSlug = $segments[1];

    if (isset($cities[$citySlug]) && isset($evs[$evSlug])) {
        $selectedCity = $cities[$citySlug];
        $selectedEv = $evs[$evSlug];
    } else {
        $is404 = true;
    }
} else {
    $is404 = true;
}

// Render 404 if invalid
if ($is404) {
    http_response_code(404);
    $pageTitle = "Page Not Found (404) - Solar & EV Payback Calculator";
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= htmlspecialchars($pageTitle) ?></title>
        <link rel="stylesheet" href="/style.css">
    </head>
    <body>
        <header class="site-header">
            <div class="nav-container">
                <a href="/" class="brand-link">
                    <span class="brand-icon">⚡</span>
                    <span>SolarChargeCalc</span>
                </a>
            </div>
        </header>
        <main class="main-wrapper">
            <div class="not-found-card">
                <div class="not-found-code">404</div>
                <h1 class="not-found-title">Comparison Route Not Found</h1>
                <p class="not-found-desc">The requested city or electric vehicle combination does not exist in our index. Please select one of our verified locations and vehicles below:</p>
                <div style="margin-bottom: 24px;">
                    <a href="/" class="btn btn-primary">Return to Homepage</a>
                </div>
                <div class="links-grid-wrap" style="text-align: left;">
                    <div>
                        <h3 class="link-column-title">Available Cities</h3>
                        <div class="link-pills">
                            <?php foreach ($cities as $c): ?>
                                <a href="/<?= htmlspecialchars($c['slug']) ?>/<?= htmlspecialchars(array_key_first($evs)) ?>" class="pill-link">
                                    <?= htmlspecialchars($c['name']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div>
                        <h3 class="link-column-title">Available EVs</h3>
                        <div class="link-pills">
                            <?php foreach ($evs as $e): ?>
                                <a href="/<?= htmlspecialchars(array_key_first($cities)) ?>/<?= htmlspecialchars($e['slug']) ?>" class="pill-link">
                                    <?= htmlspecialchars($e['name']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </body>
    </html>
    <?php
    exit;
}

// Compute Programmatic SEO Variables
$cityName = $selectedCity['name'];
$cityRegion = $selectedCity['region'];
$citySlug = $selectedCity['slug'];
$currencySym = $selectedCity['currency_symbol'];
$currencyCode = $selectedCity['currency_code'];
$distUnit = $selectedCity['distance_unit'];
$fuelUnit = $selectedCity['fuel_unit'];
$sunHours = $selectedCity['peak_sun_hours'];
$elecRate = $selectedCity['electricity_rate'];
$solarCost = $selectedCity['solar_cost_per_kw'];
$rebatePct = $selectedCity['rebate_pct'];
$rebateName = $selectedCity['rebate_name'];
$petrolPrice = $selectedCity['petrol_price'];
$iceEfficiency = $selectedCity['ice_efficiency_label'];

$evName = $selectedEv['name'];
$evBrand = $selectedEv['brand'];
$evSlug = $selectedEv['slug'];
$evBattery = $selectedEv['battery_kwh'];
$evSegment = $selectedEv['segment'];
$evMaxAc = $selectedEv['max_ac_charge_kw'];
$evRange = ($distUnit === 'miles') ? $selectedEv['range_mi'] . ' mi' : $selectedEv['range_km'] . ' km';

$currentYear = date('Y');
$canonicalPath = $isHomepage ? '' : "{$citySlug}/{$evSlug}";
$canonicalUrl = "{$currentBaseUrl}/{$canonicalPath}";

// Quick initial server-side payback estimate for SEO meta
$defaultKw = $selectedCity['default_system_kw'];
$grossCostEst = $defaultKw * $solarCost;
$netCostEst = $grossCostEst * (1 - ($rebatePct / 100));
$annualGenEst = $defaultKw * $sunHours * 365 * 0.82;
$annualKmEst = ($selectedCity['default_daily_dist'] ?? 35) * 365;
$effWh = ($distUnit === 'miles') ? $selectedEv['efficiency_wh_mi'] : $selectedEv['efficiency_wh_km'];
$evKwhEst = ($annualKmEst * $effWh) / 1000;
$iceLitersEst = $annualKmEst / ($selectedCity['ice_efficiency_val'] ?? 15);
$iceCostEst = $iceLitersEst * $petrolPrice;
$evSolarCostEst = ($evKwhEst * 0.3) * $elecRate; // 70% solar (0 cost), 30% grid
$fuelSavingsEst = max(0, $iceCostEst - $evSolarCostEst);
$homeSolarSavingsEst = max(0, $annualGenEst - ($evKwhEst * 0.7)) * $elecRate;
$totalSavingsEst = $fuelSavingsEst + $homeSolarSavingsEst;
$paybackYearsEst = ($totalSavingsEst > 0) ? round($netCostEst / $totalSavingsEst, 1) : 4.5;
if ($paybackYearsEst <= 0) $paybackYearsEst = 3.5;

// Dynamic Meta Tags
if ($isHomepage) {
    $metaTitle = "Solar & EV Charging Payback Calculator ({$currentYear}) | Free ROI Tool";
    $metaDesc = "Free Solar & EV Payback Calculator. Calculate solar panel payback years, home charging costs, grid vs solar savings, and 5-year ROI for top electric vehicles.";
} else {
    $metaTitle = "{$cityName} Solar & {$evName} Charging Payback Calculator ({$currentYear})";
    $metaDesc = "Calculate solar payback, home charging costs vs petrol, and 5-year savings for {$evName} in {$cityName}. Approx. payback: {$paybackYearsEst} yrs ({$sunHours} sun hrs/day, {$rebatePct}% rebate).";
}

// Content Negotiation: Serve Markdown representation when requested (Goal 9)
$acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? '';
$wantsMarkdown = (isset($_GET['format']) && $_GET['format'] === 'markdown')
    || (stripos($acceptHeader, 'text/markdown') !== false)
    || (stripos($acceptHeader, 'text/x-markdown') !== false);

if ($wantsMarkdown) {
    header('Content-Type: text/markdown; charset=UTF-8');
    header('Vary: Accept');

    $co2Avoided = round(($annualGenEst * 0.65 + ($iceLitersEst * 2.31 * 0.5)) / 1000, 2);
    $treesEq = round(($annualGenEst * 0.65 + ($iceLitersEst * 2.31 * 0.5)) / 21);
    $fiveYearGain = round(($totalSavingsEst * 5) - $netCostEst);

    echo "# Solar & EV Charging Payback Calculator - {$cityName} ({$evName})\n\n";
    echo "> **Summary**: {$metaDesc}\n\n";
    echo "- **Canonical URL**: {$canonicalUrl}\n";
    echo "- **Model Year**: {$currentYear}\n";
    echo "- **Format**: Markdown Agent Representation (RFC 8288 Link alternate)\n\n";

    echo "## Location Baseline & Tariffs: {$cityName} ({$cityRegion})\n\n";
    echo "| Parameter | Value |\n";
    echo "| :--- | :--- |\n";
    echo "| Peak Sun Hours | {$sunHours} hrs/day |\n";
    echo "| Residential Grid Rate | {$currencySym}{$elecRate} / kWh |\n";
    echo "| Petrol / Gas Price | {$currencySym}{$petrolPrice} / {$fuelUnit} |\n";
    echo "| Solar Incentive / Rebate | {$rebateName} ({$rebatePct}% subsidy) |\n";
    echo "| Installed Solar Cost | {$currencySym}{$solarCost} / kW |\n";
    echo "| Baseline ICE Efficiency | {$selectedCity['ice_efficiency_val']} {$iceEfficiency} |\n\n";

    echo "## Vehicle Specifications: {$evName} ({$evBrand})\n\n";
    echo "| Specification | Value |\n";
    echo "| :--- | :--- |\n";
    echo "| Usable Battery Capacity | {$evBattery} kWh |\n";
    echo "| Energy Efficiency | {$effWh} Wh/{$distUnit} |\n";
    echo "| Real-World Range | {$evRange} |\n";
    echo "| Vehicle Segment | {$evSegment} |\n";
    echo "| AC Charging Max Rate | {$evMaxAc} kW |\n\n";

    echo "## Payback & ROI Analysis ({$defaultKw} kW Solar Array, {$selectedCity['default_daily_dist']} {$distUnit}/day, 70% Solar Charging)\n\n";
    echo "- **Estimated Payback Period**: **{$paybackYearsEst} Years**\n";
    echo "- **5-Year Cumulative Net Financial Gain**: **{$currencySym}" . number_format($fiveYearGain) . "**\n";
    echo "- **Gross Solar System Cost**: {$currencySym}" . number_format($grossCostEst) . "\n";
    echo "- **Government Rebate Amount**: -{$currencySym}" . number_format($grossCostEst - $netCostEst) . " ({$rebatePct}%)\n";
    echo "- **Net Solar System Investment**: {$currencySym}" . number_format($netCostEst) . "\n";
    echo "- **Annual Solar Generation**: " . number_format(round($annualGenEst)) . " kWh/year\n";
    echo "- **EV Annual Electricity Demand**: " . number_format(round($evKwhEst)) . " kWh/year\n";
    echo "- **Annual Fuel Savings (EV vs Petrol)**: {$currencySym}" . number_format(round($fuelSavingsEst)) . " / year\n";
    echo "- **Annual Home Power Solar Offset**: {$currencySym}" . number_format(round($homeSolarSavingsEst)) . " / year\n";
    echo "- **Total Annual Financial Savings**: {$currencySym}" . number_format(round($totalSavingsEst)) . " / year\n";
    echo "- **Annual Carbon Offset**: {$co2Avoided} Metric Tons CO2 (~{$treesEq} trees equivalent)\n\n";

    echo "## Annual Driving Energy Cost Comparison\n\n";
    echo "| Driving Mode | Annual Fuel / Energy Cost |\n";
    echo "| :--- | :--- |\n";
    echo "| Petrol / Gasoline ICE Vehicle | {$currencySym}" . number_format(round($iceCostEst)) . " / year |\n";
    echo "| {$evName} (100% Grid Power) | {$currencySym}" . number_format(round($evKwhEst * $elecRate)) . " / year |\n";
    echo "| {$evName} (70% Solar + 30% Grid) | {$currencySym}" . number_format(round($evSolarCostEst)) . " / year |\n\n";

    echo "## Machine-Readable Agent Resources\n\n";
    echo "- **Robots Policy**: https://calc.intelligentwebdevelopers.com/robots.txt\n";
    echo "- **Agent Overview (LLMS.txt)**: https://calc.intelligentwebdevelopers.com/llms.txt\n";
    echo "- **MCP Server Card (SEP-1649)**: https://calc.intelligentwebdevelopers.com/.well-known/mcp/server-card.json\n";
    echo "- **XML Sitemap**: https://calc.intelligentwebdevelopers.com/sitemap.xml\n";
    echo "- **Open Source Repository**: https://github.com/roshanchoudhury/solar-ev-payback-calculator\n\n";

    echo "## Business Entity & Legal Verification\n\n";
    echo "- **Publisher**: Intelligent Web Developers\n";
    echo "- **Website**: https://intelligentwebdevelopers.com\n";
    echo "- **Contact Email**: support@intelligentwebdevelopers.com\n";
    echo "- **Telephone**: +1-512-555-0199\n";
    echo "- **About Us**: https://intelligentwebdevelopers.com/about-us/\n";
    echo "- **Contact Page**: https://intelligentwebdevelopers.com/contact-us/\n";
    echo "- **Terms of Service**: https://intelligentwebdevelopers.com/about-us/terms-of-service/\n";
    echo "- **Privacy Policy**: https://intelligentwebdevelopers.com/privacy-policy/\n";
    exit;
}

// Monetization content resolution
$primaryCta = $monetization['primary_lead_gen'] ?? [];
$primaryCtaTitle = str_replace(['{city_name}', '{ev_name}'], [$cityName, $evName], $primaryCta['title'] ?? 'Get Local Solar & EV Charger Quotes');
$primaryCtaDesc = str_replace(['{city_name}', '{ev_name}'], [$cityName, $evName], $primaryCta['description'] ?? '');
$primaryCtaUrl = str_replace(['{city_slug}', '{ev_slug}'], [$citySlug, $evSlug], $primaryCta['cta_url'] ?? '#');

$affiliateBox = $monetization['hardware_affiliate'] ?? [];
$affiliateTitle = str_replace(['{city_name}', '{ev_name}'], [$cityName, $evName], $affiliateBox['title'] ?? '');
$affiliateProductName = str_replace(['{city_name}', '{ev_name}'], [$cityName, $evName], $affiliateBox['product_name'] ?? '');
$affiliateUrl = str_replace(['{city_slug}', '{ev_slug}'], [$citySlug, $evSlug], $affiliateBox['affiliate_url'] ?? '#');

// Structured Data (JSON-LD)
$schemaData = [
    "@context" => "https://schema.org",
    "@graph" => [
        [
            "@type" => "Organization",
            "@id" => "https://calc.intelligentwebdevelopers.com/#organization",
            "name" => "Intelligent Web Developers",
            "alternateName" => "SolarChargeCalc",
            "url" => "https://intelligentwebdevelopers.com",
            "logo" => [
                "@type" => "ImageObject",
                "url" => "https://calc.intelligentwebdevelopers.com/favicon.svg"
            ],
            "description" => "Provider of intelligent web engineering, clean energy modeling calculators, and programmatic web solutions.",
            "email" => "support@intelligentwebdevelopers.com",
            "telephone" => "+1-512-555-0199",
            "sameAs" => [
                "https://github.com/roshanchoudhury/solar-ev-payback-calculator",
                "https://intelligentwebdevelopers.com",
                "https://intelligentwebdevelopers.com/about-us/",
                "https://twitter.com/intelligentweb",
                "https://linkedin.com/company/intelligentwebdevelopers"
            ],
            "contactPoint" => [
                [
                    "@type" => "ContactPoint",
                    "telephone" => "+1-512-555-0199",
                    "contactType" => "customer support",
                    "email" => "support@intelligentwebdevelopers.com",
                    "url" => "https://intelligentwebdevelopers.com/contact-us/",
                    "availableLanguage" => ["English"]
                ]
            ]
        ],
        [
            "@type" => "SoftwareApplication",
            "name" => "Solar & EV Charging Payback Calculator - {$cityName} ({$evName})",
            "operatingSystem" => "All (Web Browser)",
            "applicationCategory" => "FinanceApplication",
            "url" => $canonicalUrl,
            "description" => $metaDesc,
            "publisher" => [
                "@id" => "https://calc.intelligentwebdevelopers.com/#organization"
            ],
            "author" => [
                "@id" => "https://calc.intelligentwebdevelopers.com/#organization"
            ],
            "offers" => [
                "@type" => "Offer",
                "price" => "0",
                "priceCurrency" => $currencyCode
            ],
            "featureList" => [
                "Real-time solar payback period calculation",
                "EV home charging cost vs petrol fuel comparison",
                "Rooftop solar subsidy and rebate calculation",
                "5-year cumulative net financial savings",
                "One-click CSV summary data export"
            ]
        ],
        [
            "@type" => "FAQPage",
            "mainEntity" => [
                [
                    "@type" => "Question",
                    "name" => "How long does solar take to pay for itself with a {$evName} in {$cityName}?",
                    "acceptedAnswer" => [
                        "@type" => "Answer",
                        "text" => "In {$cityName}, a standard {$defaultKw} kW rooftop solar system paired with a {$evName} typically pays for itself in approximately {$paybackYearsEst} years. This accounts for {$cityName}'s {$sunHours} average daily peak sun hours, the {$rebateName}, and displacing costly petrol with free rooftop solar power."
                    ]
                ],
                [
                    "@type" => "Question",
                    "name" => "How much does it cost to charge a {$evName} at home in {$cityName}?",
                    "acceptedAnswer" => [
                        "@type" => "Answer",
                        "text" => "Charging the {$evBattery} kWh battery of the {$evName} at {$cityName}'s residential electricity rate ({$currencySym}{$elecRate}/kWh) costs approximately " . $currencySym . round($evBattery * $elecRate, 2) . " for a full charge from the grid. When charging with your rooftop solar array during peak generation hours, the marginal electricity fuel cost is effectively {$currencySym}0."
                    ]
                ],
                [
                    "@type" => "Question",
                    "name" => "How many solar panels are needed to charge a {$evName} in {$cityName}?",
                    "acceptedAnswer" => [
                        "@type" => "Answer",
                        "text" => "For an average daily commute of 35-40 {$distUnit}, the {$evName} requires approximately 5 to 7 kWh of energy daily. In {$cityName} ({$sunHours} peak sun hours/day), this requires around 4 to 6 modern 450W solar panels (approx 2.0 to 2.5 kW) dedicated exclusively to the EV, in addition to regular household power needs."
                    ]
                ],
                [
                    "@type" => "Question",
                    "name" => "What solar subsidies and rebates apply in {$cityName} in {$currentYear}?",
                    "acceptedAnswer" => [
                        "@type" => "Answer",
                        "text" => "Homeowners in {$cityName} ({$cityRegion}) can benefit from the {$rebateName}, reducing the gross rooftop installation cost by approximately {$rebatePct}%."
                    ]
                ]
            ]
        ],
        [
            "@type" => "BreadcrumbList",
            "itemListElement" => [
                [
                    "@type" => "ListItem",
                    "position" => 1,
                    "name" => "Home",
                    "item" => $currentBaseUrl . "/"
                ],
                [
                    "@type" => "ListItem",
                    "position" => 2,
                    "name" => $cityName,
                    "item" => "{$currentBaseUrl}/{$citySlug}/{$evSlug}"
                ],
                [
                    "@type" => "ListItem",
                    "position" => 3,
                    "name" => $evName,
                    "item" => $canonicalUrl
                ]
            ]
        ]
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($metaTitle) ?></title>
    <meta name="description" content="<?= htmlspecialchars($metaDesc) ?>">
    <link rel="canonical" href="<?= htmlspecialchars($canonicalUrl) ?>">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= htmlspecialchars($canonicalUrl) ?>">
    <meta property="og:title" content="<?= htmlspecialchars($metaTitle) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($metaDesc) ?>">
    <meta property="og:site_name" content="SolarChargeCalc">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($metaTitle) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($metaDesc) ?>">

    <!-- Stylesheet & Favicon -->
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="stylesheet" href="/style.css">

    <!-- JSON-LD Structured Data -->
    <script type="application/ld+json">
        <?= json_encode($schemaData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?>
    </script>
</head>
<body>
    <!-- Site Header -->
    <header class="site-header">
        <div class="nav-container">
            <a href="/" class="brand-link" title="Solar & EV Charging Payback Calculator">
                <span class="brand-icon">⚡</span>
                <span>SolarChargeCalc</span>
            </a>

            <!-- Quick City & EV Switcher -->
            <div class="nav-quick-select">
                <label for="nav_city_select" class="visually-hidden" style="display:none;">Change City</label>
                <select id="nav_city_select" class="quick-select-dropdown" onchange="window.location.href='/' + this.value + '/<?= htmlspecialchars($evSlug) ?>'">
                    <?php foreach ($cities as $c): ?>
                        <option value="<?= htmlspecialchars($c['slug']) ?>" <?= $c['slug'] === $citySlug ? 'selected' : '' ?>>
                            📍 <?= htmlspecialchars($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="nav_ev_select" class="visually-hidden" style="display:none;">Change EV</label>
                <select id="nav_ev_select" class="quick-select-dropdown" onchange="window.location.href='/<?= htmlspecialchars($citySlug) ?>/' + this.value">
                    <?php foreach ($evs as $e): ?>
                        <option value="<?= htmlspecialchars($e['slug']) ?>" <?= $e['slug'] === $evSlug ? 'selected' : '' ?>>
                            🚗 <?= htmlspecialchars($e['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </header>

    <main class="main-wrapper">
        <!-- Breadcrumbs -->
        <nav class="breadcrumbs" aria-label="Breadcrumb">
            <a href="/">Home</a>
            <span class="breadcrumb-separator">/</span>
            <a href="/<?= htmlspecialchars($citySlug) ?>/<?= htmlspecialchars($evSlug) ?>"><?= htmlspecialchars($cityName) ?></a>
            <span class="breadcrumb-separator">/</span>
            <span aria-current="page"><?= htmlspecialchars($evName) ?></span>
        </nav>

        <!-- SEO Hero Banner -->
        <section class="hero-card">
            <div class="hero-badge">☀️ Clean Energy ROI Engine • <?= htmlspecialchars($currentYear) ?></div>
            <h1 class="hero-title"><?= htmlspecialchars($cityName) ?> Solar &amp; <?= htmlspecialchars($evName) ?> Payback Calculator</h1>
            <p class="hero-description">
                Real-time financial payback analysis for pairing rooftop solar PV with a <?= htmlspecialchars($evName) ?> in <?= htmlspecialchars($cityName) ?> (<?= htmlspecialchars($cityRegion) ?>).
                Calculate your solar net cost after government subsidies, annual generation, EV home charging savings vs petrol, and 5-year cumulative net profit.
            </p>

            <div class="hero-stats-bar">
                <div class="hero-stat-item">
                    <span class="stat-label">Peak Sun Hours</span>
                    <span class="stat-val">☀️ <?= htmlspecialchars((string)$sunHours) ?> hrs/day</span>
                </div>
                <div class="hero-stat-item">
                    <span class="stat-label">Grid Power Rate</span>
                    <span class="stat-val">⚡ <?= htmlspecialchars($currencySym . $elecRate) ?>/kWh</span>
                </div>
                <div class="hero-stat-item">
                    <span class="stat-label">Petrol / Gas Price</span>
                    <span class="stat-val">⛽ <?= htmlspecialchars($currencySym . $petrolPrice) ?>/<?= htmlspecialchars($fuelUnit) ?></span>
                </div>
                <div class="hero-stat-item">
                    <span class="stat-label">Solar Incentive</span>
                    <span class="stat-val">🏷️ <?= htmlspecialchars((string)$rebatePct) ?>% Subsidy</span>
                </div>
                <div class="hero-stat-item">
                    <span class="stat-label">EV Usable Battery</span>
                    <span class="stat-val">🔋 <?= htmlspecialchars((string)$evBattery) ?> kWh</span>
                </div>
            </div>
        </section>

        <!-- Interactive Calculator Section -->
        <section class="calc-grid">
            <!-- Left Column: Inputs & Sliders (WebMCP Annotated Tool Form) -->
            <form class="app-card" id="solar_calc_form" toolname="calculate_solar_ev_payback" tooldescription="Calculate solar and EV charging payback period, annual savings, and net ROI based on city tariffs and EV model specifications" onsubmit="return false;">
                <h2 class="card-heading">
                    <span><span class="card-heading-icon">⚙️</span>Customize Parameters</span>
                    <button type="button" id="btn_reset_defaults" class="btn btn-secondary" style="padding: 4px 10px; font-size: 0.78rem;" title="Reset all fields to localized defaults">Reset</button>
                </h2>

                <!-- Solar System Size -->
                <div class="form-group">
                    <div class="form-label-row">
                        <label for="system_kw_input" class="form-label">Rooftop Solar Size (kW)</label>
                        <input type="number" id="system_kw_input" name="system_kw" class="form-number-box" min="1" max="25" step="0.5" value="<?= htmlspecialchars((string)$defaultKw) ?>">
                    </div>
                    <input type="range" id="system_kw_slider" class="slider-control" min="1" max="25" step="0.5" value="<?= htmlspecialchars((string)$defaultKw) ?>">
                    <div class="slider-marks">
                        <span>1 kW</span>
                        <span>5 kW (Typical)</span>
                        <span>15 kW</span>
                        <span>25 kW</span>
                    </div>
                </div>

                <!-- Daily Commute / Driving Distance -->
                <div class="form-group">
                    <div class="form-label-row">
                        <label for="daily_dist_input" class="form-label">Daily Driving Distance (<?= htmlspecialchars($distUnit) ?>/day)</label>
                        <input type="number" id="daily_dist_input" name="daily_dist" class="form-number-box" min="5" max="250" step="5" value="<?= htmlspecialchars((string)($selectedCity['default_daily_dist'] ?? 35)) ?>">
                    </div>
                    <input type="range" id="daily_dist_slider" class="slider-control" min="5" max="250" step="5" value="<?= htmlspecialchars((string)($selectedCity['default_daily_dist'] ?? 35)) ?>">
                    <div class="slider-marks">
                        <span>5 <?= htmlspecialchars($distUnit) ?></span>
                        <span>40 <?= htmlspecialchars($distUnit) ?> (Average)</span>
                        <span>120 <?= htmlspecialchars($distUnit) ?></span>
                        <span>250 <?= htmlspecialchars($distUnit) ?></span>
                    </div>
                </div>

                <!-- Solar Charging Share -->
                <div class="form-group">
                    <div class="form-label-row">
                        <label for="solar_charge_pct_input" class="form-label">EV Solar Charging Share (%)</label>
                        <input type="number" id="solar_charge_pct_input" name="solar_charge_pct" class="form-number-box" min="0" max="100" step="5" value="70">
                    </div>
                    <input type="range" id="solar_charge_pct_slider" class="slider-control" min="0" max="100" step="5" value="70">
                    <div class="slider-marks">
                        <span>0% (Grid Only)</span>
                        <span>50%</span>
                        <span>70% (Day/Battery)</span>
                        <span>100% (Pure Solar)</span>
                    </div>
                </div>

                <!-- Financial & Energy Tariffs (Editable Advanced Inputs) -->
                <div class="input-grid-2" style="margin-top: 24px;">
                    <div class="input-field-wrap">
                        <label for="solar_cost_input">Solar Cost (<?= htmlspecialchars($currencySym) ?>/kW installed)</label>
                        <input type="number" id="solar_cost_input" name="solar_cost" class="input-text-box" value="<?= htmlspecialchars((string)$solarCost) ?>" step="10">
                    </div>
                    <div class="input-field-wrap">
                        <label for="rebate_pct_input">Government Rebate (%)</label>
                        <input type="number" id="rebate_pct_input" name="rebate_pct" class="input-text-box" value="<?= htmlspecialchars((string)$rebatePct) ?>" step="1" min="0" max="100">
                    </div>
                    <div class="input-field-wrap">
                        <label for="elec_rate_input">Grid Rate (<?= htmlspecialchars($currencySym) ?>/kWh)</label>
                        <input type="number" id="elec_rate_input" name="elec_rate" class="input-text-box" value="<?= htmlspecialchars((string)$elecRate) ?>" step="0.01">
                    </div>
                    <div class="input-field-wrap">
                        <label for="petrol_price_input">Petrol (<?= htmlspecialchars($currencySym) ?>/<?= htmlspecialchars($fuelUnit) ?>)</label>
                        <input type="number" id="petrol_price_input" name="petrol_price" class="input-text-box" value="<?= htmlspecialchars((string)$petrolPrice) ?>" step="0.05">
                    </div>
                </div>

                <div class="input-field-wrap" style="margin-top: 14px;">
                    <label for="ice_eff_input">ICE Car Efficiency Baseline (<?= htmlspecialchars($iceEfficiency) ?>)</label>
                    <input type="number" id="ice_eff_input" name="ice_eff" class="input-text-box" value="<?= htmlspecialchars((string)$selectedCity['ice_efficiency_val']) ?>" step="0.1">
                </div>

                <!-- Action Controls -->
                <div class="button-row" style="margin-top: 24px;">
                    <button type="button" id="btn_export_csv" class="btn btn-primary" style="flex: 1;">
                        📥 Export Summary (CSV)
                    </button>
                    <button type="button" onclick="window.print()" class="btn btn-secondary" style="flex: 1;">
                        🖨️ Print / Save PDF
                    </button>
                </div>
            </form>

            <!-- Right Column: Results & Interactive Visuals -->
            <div class="app-card">
                <h2 class="card-heading">
                    <span><span class="card-heading-icon">📊</span>Calculated Payback &amp; Savings</span>
                    <span id="res_payback_years_badge" class="metric-pill pill-green">Calculating...</span>
                </h2>

                <!-- Main Highlight Banner -->
                <div class="payback-banner">
                    <div class="payback-banner-left">
                        <span class="payback-banner-title">Solar &amp; EV Payback Period</span>
                        <div id="res_payback_years" class="payback-years-big">-- yrs</div>
                        <span style="font-size: 0.8rem; color: var(--text-muted); margin-top: 4px;">Time to recover 100% net solar investment from fuel &amp; power savings</span>
                    </div>
                    <div class="payback-banner-right">
                        <span class="stat-label">5-Year Net Financial Gain</span>
                        <div id="res_5yr_savings" class="metric-value text-positive" style="font-size: 1.5rem; margin-top: 4px;">--</div>
                        <span style="font-size: 0.75rem; color: var(--text-dim);">Net cash in pocket after 5 yrs</span>
                    </div>
                </div>

                <!-- 4-Card Results Grid -->
                <div class="results-metric-grid">
                    <div class="metric-card">
                        <div class="metric-title">Net Solar Investment</div>
                        <div id="res_net_solar_cost" class="metric-value">--</div>
                        <div class="metric-sub">
                            Gross: <span id="res_gross_solar_cost">--</span> | Subsidy: <span id="res_rebate_amount" style="color: #34d399;">--</span>
                        </div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-title">Annual Solar Generation</div>
                        <div id="res_annual_generation" class="metric-value">--</div>
                        <div class="metric-sub">
                            <?= htmlspecialchars($evName) ?> demand: <span id="res_ev_annual_kwh">--</span>
                        </div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-title">Annual Fuel Savings</div>
                        <div id="res_annual_fuel_savings" class="metric-value text-positive">--</div>
                        <div class="metric-sub">
                            Petrol cost vs EV home charging
                        </div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-title">Total Annual Savings</div>
                        <div id="res_total_annual_savings" class="metric-value text-positive">--</div>
                        <div class="metric-sub">
                            EV savings + Home solar offset (<span id="res_home_solar_savings">--</span>)
                        </div>
                    </div>
                </div>

                <!-- Annual Cost Comparison Bars -->
                <div class="comparison-section">
                    <div class="comparison-title">Annual Driving Energy Cost Comparison (<?= htmlspecialchars($cityName) ?>)</div>
                    <div class="bar-row">
                        <div class="bar-label-wrap">
                            <span>Standard Petrol/Gasoline Car</span>
                            <span id="bar_petrol_val" style="font-weight: 700; color: #f87171;">--</span>
                        </div>
                        <div class="bar-track">
                            <div id="bar_petrol" class="bar-fill fill-petrol" style="width: 100%;"></div>
                        </div>
                    </div>
                    <div class="bar-row">
                        <div class="bar-label-wrap">
                            <span><?= htmlspecialchars($evName) ?> (100% Grid Charged)</span>
                            <span id="bar_grid_ev_val" style="font-weight: 700; color: #fbbf24;">--</span>
                        </div>
                        <div class="bar-track">
                            <div id="bar_grid_ev" class="bar-fill fill-grid-ev" style="width: 45%;"></div>
                        </div>
                    </div>
                    <div class="bar-row">
                        <div class="bar-label-wrap">
                            <span><?= htmlspecialchars($evName) ?> (Solar + Grid Hybrid)</span>
                            <span id="bar_solar_ev_val" style="font-weight: 700; color: #34d399;">--</span>
                        </div>
                        <div class="bar-track">
                            <div id="bar_solar_ev" class="bar-fill fill-solar-ev" style="width: 15%;"></div>
                        </div>
                    </div>
                </div>

                <!-- Environmental Impact -->
                <div style="background-color: var(--accent-green-bg); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: var(--radius-md); padding: 14px 16px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                    <div>
                        <div style="font-size: 0.8rem; color: #6ee7b7; font-weight: 600;">🌱 Environmental Carbon Offset</div>
                        <div style="font-size: 0.95rem; color: #ffffff; margin-top: 2px;">
                            Avoids <strong id="res_co2_offset">--</strong> CO₂ emissions annually.
                        </div>
                    </div>
                    <div style="font-size: 0.85rem; color: #a7f3d0; background-color: rgba(16, 185, 129, 0.2); padding: 4px 10px; border-radius: 999px;">
                        Equivalent to planting <strong id="res_trees_equivalent">--</strong> trees
                    </div>
                </div>
            </div>
        </section>

        <!-- Configurable Monetization Slots -->
        <section class="monetization-grid">
            <!-- Slot 1: Primary Lead-Gen CTA Card -->
            <div class="cta-box" id="<?= htmlspecialchars($primaryCta['slot_id'] ?? 'lead-gen') ?>">
                <div>
                    <span class="cta-badge"><?= htmlspecialchars($primaryCta['badge'] ?? '⚡ Verified Installers') ?></span>
                    <h3 class="cta-title"><?= htmlspecialchars($primaryCtaTitle) ?></h3>
                    <p class="cta-desc"><?= htmlspecialchars($primaryCtaDesc) ?></p>
                    <ul class="cta-trust-list">
                        <?php foreach (($primaryCta['trust_points'] ?? []) as $point): ?>
                            <li><span>✓</span> <?= htmlspecialchars($point) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <a href="<?= htmlspecialchars($primaryCtaUrl) ?>" target="_blank" rel="noopener noreferrer nofollow" class="btn btn-cta-main">
                    <?= htmlspecialchars($primaryCta['cta_text'] ?? 'Get Free Quotes') ?>
                </a>
            </div>

            <!-- Slot 2: Secondary Hardware Affiliate Slot -->
            <div class="affiliate-box" id="<?= htmlspecialchars($affiliateBox['slot_id'] ?? 'affiliate') ?>">
                <div>
                    <span class="affiliate-badge"><?= htmlspecialchars($affiliateBox['badge'] ?? '⭐ Hardware') ?></span>
                    <h3 class="affiliate-product-name"><?= htmlspecialchars($affiliateProductName) ?></h3>
                    <div class="affiliate-product-sub"><?= htmlspecialchars($affiliateBox['product_subtitle'] ?? '') ?></div>
                    <div class="affiliate-rating"><?= htmlspecialchars($affiliateBox['rating'] ?? '') ?></div>
                    <ul class="affiliate-features">
                        <?php foreach (($affiliateBox['features'] ?? []) as $feature): ?>
                            <li>
                                <span>⚡</span>
                                <?= htmlspecialchars(str_replace('{ev_name}', $evName, $feature)) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div style="font-size: 0.78rem; color: #38bdf8; margin-bottom: 14px;">
                        <?= htmlspecialchars($affiliateBox['discount_note'] ?? '') ?>
                    </div>
                </div>
                <a href="<?= htmlspecialchars($affiliateUrl) ?>" target="_blank" rel="noopener noreferrer nofollow" class="btn btn-affiliate">
                    <?= htmlspecialchars($affiliateBox['cta_text'] ?? 'View Deal') ?>
                </a>
            </div>
        </section>

        <!-- Programmatic SEO FAQ Section -->
        <section class="faq-section">
            <h2 class="section-heading">Frequently Asked Questions: Solar &amp; <?= htmlspecialchars($evName) ?> in <?= htmlspecialchars($cityName) ?></h2>

            <div class="faq-item">
                <h3 class="faq-question"><span>❓</span> How long does solar take to pay for itself with a <?= htmlspecialchars($evName) ?> in <?= htmlspecialchars($cityName) ?>?</h3>
                <div class="faq-answer">
                    In <?= htmlspecialchars($cityName) ?>, a standard <?= htmlspecialchars((string)$defaultKw) ?> kW rooftop solar system paired with a <?= htmlspecialchars($evName) ?> typically achieves a full financial payback in approximately <strong><?= htmlspecialchars((string)$paybackYearsEst) ?> years</strong>.
                    This accounts for <?= htmlspecialchars($cityName) ?>'s solar irradiance (<?= htmlspecialchars((string)$sunHours) ?> peak sun hours/day), the <?= htmlspecialchars($rebateName) ?>, and substituting volatile petrol with self-generated rooftop electricity.
                </div>
            </div>

            <div class="faq-item">
                <h3 class="faq-question"><span>❓</span> What is the home charging cost of <?= htmlspecialchars($evName) ?> in <?= htmlspecialchars($cityName) ?>?</h3>
                <div class="faq-answer">
                    The usable battery capacity of the <?= htmlspecialchars($evName) ?> is <?= htmlspecialchars((string)$evBattery) ?> kWh. At the local utility rate of <?= htmlspecialchars($currencySym . $elecRate) ?>/kWh, a 0-100% full home charge on grid power costs roughly <strong><?= htmlspecialchars($currencySym . round($evBattery * $elecRate, 2)) ?></strong>.
                    When using rooftop solar generation, your vehicle charges on surplus solar energy, reducing your marginal driving fuel cost to virtually <?= htmlspecialchars($currencySym) ?>0.
                </div>
            </div>

            <div class="faq-item">
                <h3 class="faq-question"><span>❓</span> How many solar panels are needed to charge a <?= htmlspecialchars($evName) ?> in <?= htmlspecialchars($cityName) ?>?</h3>
                <div class="faq-answer">
                    Driving <?= htmlspecialchars((string)($selectedCity['default_daily_dist'] ?? 35)) ?> <?= htmlspecialchars($distUnit) ?> daily requires roughly 5 to 7 kWh of energy. In <?= htmlspecialchars($cityName) ?> with <?= htmlspecialchars((string)$sunHours) ?> peak sun hours daily, approximately <strong>4 to 6 solar panels (1.8 to 2.5 kW)</strong> provide enough electricity to cover your annual EV driving needs, with the remaining system capacity offsetting household air conditioning and appliances.
                </div>
            </div>

            <div class="faq-item">
                <h3 class="faq-question"><span>❓</span> What government rebates or subsidies exist in <?= htmlspecialchars($cityName) ?>?</h3>
                <div class="faq-answer">
                    Eligible residential installations in <?= htmlspecialchars($cityName) ?> (<?= htmlspecialchars($cityRegion) ?>) can claim the <strong><?= htmlspecialchars($rebateName) ?></strong>, reducing upfront installation expenses by roughly <?= htmlspecialchars((string)$rebatePct) ?>%. In addition, many local distribution companies offer accelerated net metering or time-of-day EV charging discounts.
                </div>
            </div>
        </section>

        <!-- Internal Linking Matrix to Prevent Orphan Pages -->
        <section class="internal-links-section">
            <h2 class="section-heading">Explore Other Solar &amp; EV Comparisons</h2>
            <div class="links-grid-wrap">
                <!-- Column 1: Compare other EVs in this city -->
                <div>
                    <h3 class="link-column-title">Compare Other EVs in <?= htmlspecialchars($cityName) ?></h3>
                    <div class="link-pills">
                        <?php foreach ($evs as $e): ?>
                            <?php if ($e['slug'] !== $evSlug): ?>
                                <a href="/<?= htmlspecialchars($citySlug) ?>/<?= htmlspecialchars($e['slug']) ?>" class="pill-link" title="Solar payback for <?= htmlspecialchars($e['name']) ?> in <?= htmlspecialchars($cityName) ?>">
                                    🚗 <?= htmlspecialchars($e['name']) ?>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Column 2: Compare this EV in other cities -->
                <div>
                    <h3 class="link-column-title">Compare <?= htmlspecialchars($evName) ?> in Other Cities</h3>
                    <div class="link-pills">
                        <?php foreach ($cities as $c): ?>
                            <?php if ($c['slug'] !== $citySlug): ?>
                                <a href="/<?= htmlspecialchars($c['slug']) ?>/<?= htmlspecialchars($evSlug) ?>" class="pill-link" title="Solar payback for <?= htmlspecialchars($evName) ?> in <?= htmlspecialchars($c['name']) ?>">
                                    📍 <?= htmlspecialchars($c['name']) ?>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Top Permutations Hub -->
            <div style="margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--border-color);">
                <div style="font-size: 0.9rem; font-weight: 700; color: var(--text-muted); margin-bottom: 10px;">Featured Clean Energy Calculator Hubs:</div>
                <div class="link-pills">
                    <a href="/austin-tx/tesla-model-y" class="pill-link">Austin • Tesla Model Y</a>
                    <a href="/phoenix-az/rivian-r1s" class="pill-link">Phoenix • Rivian R1S</a>
                    <a href="/bhubaneswar-od/tata-nexon-ev" class="pill-link">Bhubaneswar • Tata Nexon.ev</a>
                    <a href="/bengaluru-ka/mg-zs-ev" class="pill-link">Bengaluru • MG ZS EV</a>
                    <a href="/sydney-nsw/byd-atto-3" class="pill-link">Sydney • BYD Atto 3</a>
                    <a href="/london-uk/hyundai-ioniq-5" class="pill-link">London • Hyundai Ioniq 5</a>
                    <a href="/toronto-on/kia-ev6" class="pill-link">Toronto • Kia EV6</a>
                    <a href="/los-angeles-ca/tesla-model-y" class="pill-link">Los Angeles • Tesla Model Y</a>
                    <a href="/sitemap.xml" class="pill-link" style="color: #38bdf8;">🗺️ Full XML Sitemap</a>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="footer-links">
            <a href="/">Home</a>
            <a href="https://intelligentwebdevelopers.com/about-us/" target="_blank" rel="noopener">About Us</a>
            <a href="https://intelligentwebdevelopers.com/contact-us/" target="_blank" rel="noopener">Contact Us</a>
            <a href="https://intelligentwebdevelopers.com/privacy-policy/" target="_blank" rel="noopener">Privacy Policy</a>
            <a href="https://intelligentwebdevelopers.com/about-us/terms-of-service/" target="_blank" rel="noopener">Terms of Service</a>
            <a href="/sitemap.xml">XML Sitemap</a>
            <a href="/llms.txt">llms.txt</a>
            <a href="/.well-known/mcp/server-card.json">MCP Card</a>
        </div>
        <div class="footer-contact-info" style="margin: 14px 0 10px 0; font-size: 0.85rem; color: var(--text-muted); display: flex; justify-content: center; align-items: center; gap: 20px; flex-wrap: wrap;">
            <span>🏢 <strong>Intelligent Web Developers</strong></span>
            <span>✉️ Email: <a href="mailto:support@intelligentwebdevelopers.com" style="color: #38bdf8;">support@intelligentwebdevelopers.com</a></span>
            <span>📞 Tel: <a href="tel:+15125550199" style="color: #38bdf8;">+1 (512) 555-0199</a></span>
        </div>
        <p>
            © <?= htmlspecialchars((string)$currentYear) ?> SolarChargeCalc • Intelligent Web Developers. Zero-dependency programmatic SEO architecture designed for Apache shared hosting.
        </p>
        <p style="margin-top: 4px; font-size: 0.75rem; color: var(--text-dim);">
            Disclaimer: Payback estimates are modeled for informative and comparison purposes. Actual solar system yields and savings vary based on roof pitch, shading, inverter efficiency, utility tariff structures, and driving behavior.
        </p>
    </footer>

    <!-- Pass Configuration to Reactive JavaScript -->
    <script>
        window.APP_DATA = {
            site: <?= json_encode($site, JSON_UNESCAPED_SLASHES) ?>,
            city: <?= json_encode($selectedCity, JSON_UNESCAPED_SLASHES) ?>,
            ev: <?= json_encode($selectedEv, JSON_UNESCAPED_SLASHES) ?>
        };
    </script>
    <script src="/calculator.js" defer></script>
</body>
</html>
