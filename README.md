# ☀️ Solar & EV Charging Payback Calculator (pSEO)

A production-ready, zero-dependency programmatic SEO (pSEO) web application designed for Apache shared hosting (such as Hostinger) and standard PHP environments.

Calculates real-time financial payback periods, annual fuel savings vs. petrol/gasoline, and 5-year cumulative net returns when pairing residential rooftop solar PV with popular electric vehicles across global cities.

---

## ⚡ Key Features

- **Zero External Dependencies**: Pure vanilla PHP 8+, semantic HTML5, scoped modern CSS, and vanilla ES6 JavaScript. No Composer, Node.js, or heavyweight frameworks required.
- **Programmatic SEO (pSEO) Routing**:
  - Automatically scales across all permutations of `City × EV` (`/{city-slug}/{ev-slug}`).
  - Emits localized, indexation-ready `<title>`, `<meta name="description">`, `<link rel="canonical">`, Open Graph, and Twitter Cards.
  - Generates rich JSON-LD structured data (`SoftwareApplication`, `FAQPage`, and `BreadcrumbList`).
  - Cross-comparison linking matrix ("Compare other EVs in {City}" and "Compare {EV} in other cities") to eliminate orphan pages.
- **Real-Time Client-Side Calculation Engine (`calculator.js`)**:
  - Live recalculation on slider and number spinbutton changes without page reloads.
  - Solar net investment after national & local subsidies (e.g. US Federal ITC, India PM Surya Ghar, Australia STC).
  - Annual solar generation (kWh) using localized peak sun hours and solar performance derate factors.
  - EV annual home charging cost (solar vs. grid hybrid) vs. ICE petrol baseline.
  - 5-year cumulative net savings and environmental carbon offset (metric tons of CO₂ avoided & tree equivalents).
  - One-click **Export Summary (CSV)** for lead capture or personal analysis.
- **Hostinger & Apache Optimized (`.htaccess`)**:
  - Forced HTTPS redirection.
  - Seamless route rewrites for clean URLs.
  - Passthrough for static CSS/JS/SVG assets.
  - Dynamic `/sitemap.xml` rewrite to `sitemap.php`.
  - Built-in Gzip compression, browser caching headers (`mod_expires`), and security headers (`X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`).
- **Dynamic XML Sitemap (`sitemap.php`)**:
  - Serves valid `application/xml` sitemap encompassing the homepage and every City × EV permutation with W3C ISO 8601 `<lastmod>` timestamps.
- **Monetization Architecture**:
  - Primary lead-gen installer quote card (`#lead-gen-solar-quotes`).
  - Secondary hardware affiliate card (`#affiliate-hardware-box`).

---

## 📁 Repository Structure

```
.
├── .htaccess             # Apache rewrite rules, HTTPS enforcement, sitemap routing & asset caching
├── .gitignore            # Git exclusion rules
├── index.php             # Front controller, dynamic route parser, SEO meta & HTML5 layout
├── sitemap.php           # Dynamic XML sitemap generator (application/xml)
├── calculator.js         # Reactive ES6 math engine, dual input binding, CSV exporter
├── style.css             # Scoped mobile-first CSS, WCAG AA contrast colors, comparison bars
├── favicon.svg           # Vector energy favicon
├── data.json             # Normalized dataset: 10+ cities, 8 EV models & monetization slots
└── README.md             # Project documentation & deployment guide
```

---

## 🚀 Local Development

To run the application locally using PHP's built-in web server:

```bash
# Clone the repository
git clone https://github.com/roshanchoudhury/solar-ev-payback-calculator.git
cd solar-ev-payback-calculator

# Start PHP built-in web server
php -S localhost:8000 index.php
```

Visit the routes in your browser:
- Homepage: `http://localhost:8000/`
- Austin, TX & Tesla Model Y: `http://localhost:8000/austin-tx/tesla-model-y`
- Bhubaneswar, OD & Tata Nexon.ev: `http://localhost:8000/bhubaneswar-od/tata-nexon-ev`
- Sydney, NSW & BYD Atto 3: `http://localhost:8000/sydney-nsw/byd-atto-3`
- XML Sitemap: `http://localhost:8000/sitemap.xml`

---

## 🌐 Deployment to Hostinger (Apache Shared Hosting)

1. Log into your **Hostinger hPanel**.
2. Navigate to **File Manager** (or connect via SFTP / Git deployment).
3. Upload all files directly into your domain's `public_html/` root directory.
4. Ensure `.htaccess` is uploaded and visible (enable "Show hidden files" in File Manager if needed).
5. Verify SSL is active under **Hostinger SSL Management**.
6. Submit `https://yourdomain.com/sitemap.xml` to Google Search Console and Bing Webmaster Tools.

---

## ⚙️ Customizing Cities & EVs

Edit `data.json` to add new cities, update local tariffs, or configure additional EV models:

```json
{
  "cities": {
    "your-city-slug": {
      "name": "City, State",
      "slug": "your-city-slug",
      "region": "Region, Country",
      "currency_symbol": "$",
      "currency_code": "USD",
      "currency_pos": "prefix",
      "distance_unit": "miles",
      "fuel_unit": "gallon",
      "peak_sun_hours": 5.2,
      "electricity_rate": 0.14,
      "solar_cost_per_kw": 2700,
      "rebate_pct": 30,
      "rebate_name": "Federal Clean Energy Rebate",
      "petrol_price": 3.45,
      "ice_efficiency_val": 28.0,
      "ice_efficiency_label": "28 MPG",
      "default_system_kw": 8.0,
      "default_daily_dist": 30
    }
  }
}
```

New permutations will automatically appear in `sitemap.xml`, internal linking matrices, and the calculator dropdown without requiring code changes.

---

## 📄 License

MIT License. Free for commercial and non-commercial use.
