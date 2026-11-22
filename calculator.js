/**
 * Solar & EV Charging Payback Calculator Engine
 * Vanilla ES6 JavaScript - Zero Dependencies
 * Real-time reactive updates & CSV Export
 */

(function () {
  'use strict';

  // Config injected from server
  const config = window.APP_DATA || {};
  const city = config.city || {};
  const ev = config.ev || {};

  // DOM Elements cache
  const el = {
    // Sliders & Inputs
    systemKwSlider: document.getElementById('system_kw_slider'),
    systemKwInput: document.getElementById('system_kw_input'),
    dailyDistSlider: document.getElementById('daily_dist_slider'),
    dailyDistInput: document.getElementById('daily_dist_input'),
    solarChargePctSlider: document.getElementById('solar_charge_pct_slider'),
    solarChargePctInput: document.getElementById('solar_charge_pct_input'),
    solarCostInput: document.getElementById('solar_cost_input'),
    rebatePctInput: document.getElementById('rebate_pct_input'),
    elecRateInput: document.getElementById('elec_rate_input'),
    petrolPriceInput: document.getElementById('petrol_price_input'),
    iceEffInput: document.getElementById('ice_eff_input'),

    // Result displays
    paybackYearsBadge: document.getElementById('res_payback_years_badge'),
    paybackYearsText: document.getElementById('res_payback_years'),
    netCostText: document.getElementById('res_net_solar_cost'),
    grossCostText: document.getElementById('res_gross_solar_cost'),
    rebateAmountText: document.getElementById('res_rebate_amount'),
    annualGenText: document.getElementById('res_annual_generation'),
    evAnnualKwhText: document.getElementById('res_ev_annual_kwh'),
    petrolCostText: document.getElementById('res_petrol_cost'),
    evGridCostText: document.getElementById('res_ev_grid_cost'),
    evSolarCostText: document.getElementById('res_ev_solar_cost'),
    evTotalCostText: document.getElementById('res_ev_total_cost'),
    annualFuelSavingsText: document.getElementById('res_annual_fuel_savings'),
    homeSolarSavingsText: document.getElementById('res_home_solar_savings'),
    totalAnnualSavingsText: document.getElementById('res_total_annual_savings'),
    fiveYearSavingsText: document.getElementById('res_5yr_savings'),
    co2OffsetYearText: document.getElementById('res_co2_offset'),
    treesEquivalentText: document.getElementById('res_trees_equivalent'),

    // Visual comparison bars
    barPetrol: document.getElementById('bar_petrol'),
    barGridEv: document.getElementById('bar_grid_ev'),
    barSolarEv: document.getElementById('bar_solar_ev'),
    barPetrolVal: document.getElementById('bar_petrol_val'),
    barGridEvVal: document.getElementById('bar_grid_ev_val'),
    barSolarEvVal: document.getElementById('bar_solar_ev_val'),

    // Actions
    btnExportCsv: document.getElementById('btn_export_csv'),
    btnResetDefaults: document.getElementById('btn_reset_defaults')
  };

  // Format currency according to locale rules
  function formatMoney(amount, decimals = 0) {
    if (isNaN(amount)) amount = 0;
    const rounded = amount.toLocaleString(undefined, {
      minimumFractionDigits: decimals,
      maximumFractionDigits: decimals
    });
    if (city.currency_pos === 'suffix') {
      return `${rounded} ${city.currency_symbol || '$'}`;
    }
    return `${city.currency_symbol || '$'}${rounded}`;
  }

  function formatNumber(num, decimals = 0) {
    if (isNaN(num)) num = 0;
    return num.toLocaleString(undefined, {
      minimumFractionDigits: decimals,
      maximumFractionDigits: decimals
    });
  }

  // Sync dual slider + number inputs
  function bindDualInput(slider, numberInput, callback) {
    if (!slider || !numberInput) return;
    slider.addEventListener('input', () => {
      numberInput.value = slider.value;
      callback();
    });
    numberInput.addEventListener('input', () => {
      slider.value = numberInput.value;
      callback();
    });
  }

  // Core Calculation Engine
  function calculate() {
    // Read raw inputs
    const systemKw = parseFloat(el.systemKwInput.value) || 0;
    const dailyDist = parseFloat(el.dailyDistInput.value) || 0;
    const solarChargePct = Math.min(100, Math.max(0, parseFloat(el.solarChargePctInput.value) || 0));
    const solarCostPerKw = parseFloat(el.solarCostInput.value) || 0;
    const rebatePct = Math.min(100, Math.max(0, parseFloat(el.rebatePctInput.value) || 0));
    const elecRate = parseFloat(el.elecRateInput.value) || 0;
    const petrolPrice = parseFloat(el.petrolPriceInput.value) || 0;
    const iceEfficiency = parseFloat(el.iceEffInput.value) || 1;

    const sunHours = city.peak_sun_hours || 4.5;
    const distUnit = city.distance_unit || 'km';

    // 1. Solar Financials & Generation
    const grossSolarCost = systemKw * solarCostPerKw;
    const rebateAmount = grossSolarCost * (rebatePct / 100);
    const netSolarCost = Math.max(0, grossSolarCost - rebateAmount);

    // Standard 82% performance ratio (inverter losses, temperature coefficient, dust)
    const annualGenerationKwh = systemKw * sunHours * 365 * 0.82;

    // 2. EV Energy Usage
    const annualDistance = dailyDist * 365;
    const effWh = (distUnit === 'miles')
      ? (ev.efficiency_wh_mi || 250)
      : (ev.efficiency_wh_km || 155);

    const evAnnualKwh = (annualDistance * effWh) / 1000;
    const evSolarKwh = evAnnualKwh * (solarChargePct / 100);
    const evGridKwh = evAnnualKwh - evSolarKwh;

    // 3. Operating Costs
    // ICE petrol cost
    let annualPetrolCost = 0;
    if (distUnit === 'miles') {
      // MPG: gallons = miles / MPG
      const annualGallons = iceEfficiency > 0 ? (annualDistance / iceEfficiency) : 0;
      annualPetrolCost = annualGallons * petrolPrice;
    } else {
      // km/L: liters = km / (km/L)
      const annualLiters = iceEfficiency > 0 ? (annualDistance / iceEfficiency) : 0;
      annualPetrolCost = annualLiters * petrolPrice;
    }

    // EV 100% Grid baseline cost
    const ev100GridCost = evAnnualKwh * elecRate;

    // Actual EV Charging Cost (Grid portion + Solar variable cost [0])
    const evActualChargingCost = evGridKwh * elecRate;

    // 4. Savings & Value Realization
    // Fuel savings compared to ICE petrol
    const annualFuelSavings = Math.max(0, annualPetrolCost - evActualChargingCost);

    // Solar electricity remaining for home offset
    const remainingSolarKwh = Math.max(0, annualGenerationKwh - evSolarKwh);
    const homeSolarSavings = remainingSolarKwh * elecRate;

    // Total annual utility and fuel savings
    const totalAnnualSavings = annualFuelSavings + homeSolarSavings;

    // Payback period
    let paybackYears = 0;
    if (netSolarCost > 0 && totalAnnualSavings > 0) {
      paybackYears = netSolarCost / totalAnnualSavings;
    }

    // 5-Year Cumulative Net Savings (Total Savings * 5 minus initial net investment)
    const fiveYearNetSavings = (totalAnnualSavings * 5) - netSolarCost;

    // CO2 offset calculations
    // Approx 2.31 kg CO2 per liter petrol or 8.89 kg per gallon
    const co2PerFuelUnit = (distUnit === 'miles') ? 8.89 : 2.31;
    const fuelUnitsConsumed = (distUnit === 'miles')
      ? (annualDistance / Math.max(1, iceEfficiency))
      : (annualDistance / Math.max(1, iceEfficiency));
    const co2PetrolAvoidedKg = fuelUnitsConsumed * co2PerFuelUnit;
    const co2SolarGeneratedAvoidedKg = annualGenerationKwh * 0.65; // ~0.65 kg CO2/kWh displaced from grid
    const totalCo2AvoidedKg = co2PetrolAvoidedKg + (co2SolarGeneratedAvoidedKg * 0.5);
    const totalCo2AvoidedTons = totalCo2AvoidedKg / 1000;
    const treesEquivalent = Math.round(totalCo2AvoidedKg / 21); // 1 mature tree absorbs ~21kg CO2/year

    // Update DOM Displays
    if (el.paybackYearsBadge) {
      if (paybackYears > 0 && paybackYears <= 25) {
        el.paybackYearsBadge.textContent = `${paybackYears.toFixed(1)} Years`;
        el.paybackYearsBadge.className = 'metric-pill ' + (paybackYears <= 5 ? 'pill-green' : (paybackYears <= 8 ? 'pill-amber' : 'pill-blue'));
      } else if (netSolarCost === 0) {
        el.paybackYearsBadge.textContent = 'Instant (100% Subsidized)';
        el.paybackYearsBadge.className = 'metric-pill pill-green';
      } else {
        el.paybackYearsBadge.textContent = '> 25 Years';
        el.paybackYearsBadge.className = 'metric-pill pill-amber';
      }
    }

    if (el.paybackYearsText) {
      el.paybackYearsText.textContent = paybackYears > 0 ? `${paybackYears.toFixed(1)} yrs` : 'Immediate';
    }

    if (el.netCostText) el.netCostText.textContent = formatMoney(netSolarCost);
    if (el.grossCostText) el.grossCostText.textContent = formatMoney(grossSolarCost);
    if (el.rebateAmountText) el.rebateAmountText.textContent = `- ${formatMoney(rebateAmount)}`;
    if (el.annualGenText) el.annualGenText.textContent = `${formatNumber(Math.round(annualGenerationKwh))} kWh`;
    if (el.evAnnualKwhText) el.evAnnualKwhText.textContent = `${formatNumber(Math.round(evAnnualKwh))} kWh`;

    if (el.petrolCostText) el.petrolCostText.textContent = formatMoney(annualPetrolCost);
    if (el.evGridCostText) el.evGridCostText.textContent = formatMoney(ev100GridCost);
    if (el.evSolarCostText) el.evSolarCostText.textContent = `${formatMoney(0)} (Solar)`;
    if (el.evTotalCostText) el.evTotalCostText.textContent = formatMoney(evActualChargingCost);

    if (el.annualFuelSavingsText) el.annualFuelSavingsText.textContent = formatMoney(annualFuelSavings);
    if (el.homeSolarSavingsText) el.homeSolarSavingsText.textContent = formatMoney(homeSolarSavings);
    if (el.totalAnnualSavingsText) el.totalAnnualSavingsText.textContent = `${formatMoney(totalAnnualSavings)} / yr`;

    if (el.fiveYearSavingsText) {
      if (fiveYearNetSavings >= 0) {
        el.fiveYearSavingsText.textContent = `+ ${formatMoney(fiveYearNetSavings)}`;
        el.fiveYearSavingsText.classList.remove('text-negative');
        el.fiveYearSavingsText.classList.add('text-positive');
      } else {
        el.fiveYearSavingsText.textContent = `- ${formatMoney(Math.abs(fiveYearNetSavings))}`;
        el.fiveYearSavingsText.classList.remove('text-positive');
        el.fiveYearSavingsText.classList.add('text-negative');
      }
    }

    if (el.co2OffsetYearText) {
      el.co2OffsetYearText.textContent = `${totalCo2AvoidedTons.toFixed(1)} Metric Tons`;
    }
    if (el.treesEquivalentText) {
      el.treesEquivalentText.textContent = `${formatNumber(treesEquivalent)} Trees`;
    }

    // Comparison Chart Bars
    const maxBarVal = Math.max(annualPetrolCost, ev100GridCost, evActualChargingCost, 1);
    if (el.barPetrol) {
      const petrolPct = Math.min(100, Math.round((annualPetrolCost / maxBarVal) * 100));
      el.barPetrol.style.width = `${Math.max(12, petrolPct)}%`;
      if (el.barPetrolVal) el.barPetrolVal.textContent = formatMoney(annualPetrolCost);
    }
    if (el.barGridEv) {
      const gridPct = Math.min(100, Math.round((ev100GridCost / maxBarVal) * 100));
      el.barGridEv.style.width = `${Math.max(12, gridPct)}%`;
      if (el.barGridEvVal) el.barGridEvVal.textContent = formatMoney(ev100GridCost);
    }
    if (el.barSolarEv) {
      const solarPct = Math.min(100, Math.round((evActualChargingCost / maxBarVal) * 100));
      el.barSolarEv.style.width = `${Math.max(8, solarPct)}%`;
      if (el.barSolarEvVal) el.barSolarEvVal.textContent = formatMoney(evActualChargingCost);
    }

    // Return calculated snapshot for export
    return {
      cityName: city.name,
      evName: ev.name,
      currency: city.currency_code,
      systemKw: systemKw,
      dailyDist: dailyDist,
      distUnit: distUnit,
      solarChargePct: solarChargePct,
      grossSolarCost: Math.round(grossSolarCost),
      rebateAmount: Math.round(rebateAmount),
      netSolarCost: Math.round(netSolarCost),
      annualGenerationKwh: Math.round(annualGenerationKwh),
      evAnnualKwh: Math.round(evAnnualKwh),
      annualPetrolCost: Math.round(annualPetrolCost),
      evActualChargingCost: Math.round(evActualChargingCost),
      annualFuelSavings: Math.round(annualFuelSavings),
      homeSolarSavings: Math.round(homeSolarSavings),
      totalAnnualSavings: Math.round(totalAnnualSavings),
      paybackYears: paybackYears > 0 ? paybackYears.toFixed(2) : '0',
      fiveYearNetSavings: Math.round(fiveYearNetSavings),
      totalCo2AvoidedTons: totalCo2AvoidedTons.toFixed(2)
    };
  }

  // Export Summary to CSV
  function exportCsv() {
    const data = calculate();
    const rows = [
      ['Metric', 'Value', 'Unit / Currency'],
      ['Location', data.cityName, ''],
      ['Electric Vehicle', data.evName, ''],
      ['Solar System Size', data.systemKw, 'kW'],
      ['Daily Driving Distance', data.dailyDist, data.distUnit + '/day'],
      ['Solar Home Charging Share', data.solarChargePct, '%'],
      ['Solar System Gross Cost', data.grossSolarCost, data.currency],
      ['Government Rebates & Subsidies', data.rebateAmount, data.currency],
      ['Solar System Net Investment', data.netSolarCost, data.currency],
      ['Annual Solar Generation', data.annualGenerationKwh, 'kWh/year'],
      ['EV Annual Energy Demand', data.evAnnualKwh, 'kWh/year'],
      ['Annual Equivalent Petrol Cost', data.annualPetrolCost, data.currency + '/year'],
      ['Annual EV Charging Cost', data.evActualChargingCost, data.currency + '/year'],
      ['Annual EV Fuel Savings', data.annualFuelSavings, data.currency + '/year'],
      ['Annual Home Solar Offset Savings', data.homeSolarSavings, data.currency + '/year'],
      ['Total Annual Savings', data.totalAnnualSavings, data.currency + '/year'],
      ['Solar & EV Payback Period', data.paybackYears, 'Years'],
      ['5-Year Cumulative Net Financial Gain', data.fiveYearNetSavings, data.currency],
      ['Annual CO2 Emissions Prevented', data.totalCo2AvoidedTons, 'Metric Tons/year'],
      ['Generated At', new Date().toISOString(), 'UTC']
    ];

    const csvContent = 'data:text/csv;charset=utf-8,' +
      rows.map(e => e.map(item => `"${String(item).replace(/"/g, '""')}"`).join(',')).join('\n');

    const encodedUri = encodeURI(csvContent);
    const link = document.createElement('a');
    link.setAttribute('href', encodedUri);
    const filename = `solar-ev-payback-${city.slug || 'city'}-${ev.slug || 'ev'}.csv`;
    link.setAttribute('download', filename);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }

  // Reset to server defaults
  function resetDefaults() {
    if (el.systemKwSlider) el.systemKwSlider.value = city.default_system_kw || 5;
    if (el.systemKwInput) el.systemKwInput.value = city.default_system_kw || 5;

    if (el.dailyDistSlider) el.dailyDistSlider.value = city.default_daily_dist || 35;
    if (el.dailyDistInput) el.dailyDistInput.value = city.default_daily_dist || 35;

    if (el.solarChargePctSlider) el.solarChargePctSlider.value = 70;
    if (el.solarChargePctInput) el.solarChargePctInput.value = 70;

    if (el.solarCostInput) el.solarCostInput.value = city.solar_cost_per_kw || 50000;
    if (el.rebatePctInput) el.rebatePctInput.value = city.rebate_pct || 30;
    if (el.elecRateInput) el.elecRateInput.value = city.electricity_rate || 6.5;
    if (el.petrolPriceInput) el.petrolPriceInput.value = city.petrol_price || 100;
    if (el.iceEffInput) el.iceEffInput.value = city.ice_efficiency_val || 15;

    calculate();
  }

  // Initialize
  function init() {
    // Bind interactive sliders and inputs
    bindDualInput(el.systemKwSlider, el.systemKwInput, calculate);
    bindDualInput(el.dailyDistSlider, el.dailyDistInput, calculate);
    bindDualInput(el.solarChargePctSlider, el.solarChargePctInput, calculate);

    // Bind other numeric parameters
    const directInputs = [
      el.solarCostInput,
      el.rebatePctInput,
      el.elecRateInput,
      el.petrolPriceInput,
      el.iceEffInput
    ];
    directInputs.forEach(input => {
      if (input) input.addEventListener('input', calculate);
    });

    if (el.btnExportCsv) {
      el.btnExportCsv.addEventListener('click', exportCsv);
    }

    if (el.btnResetDefaults) {
      el.btnResetDefaults.addEventListener('click', resetDefaults);
    }

    // Initial calculation run
    calculate();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
