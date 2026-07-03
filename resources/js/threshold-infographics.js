/**
 * Threshold Infographics Renderer
 * Displays sensor thresholds as horizontal bar charts with color coding
 */

const ThresholdInfographics = {
  thresholdConfig: null,
  sensorIcons: {
    // AQUAVISKA
    temperature: { icon: '🌡️', color: '#ff6b6b' },
    ph: { icon: '🧪', color: '#4ecdc4' },
    do: { icon: '🫁', color: '#45b7d1' },
    turbidity: { icon: '💧', color: '#96ceb4' },
    tds: { icon: '🌊', color: '#6c5ce7' },
    // IoT CLIMATE
    suhu_udara: { icon: '🌡️', color: '#ff7675' },
    kelembaban: { icon: '💨', color: '#0984e3' },
    co2: { icon: '🌫️', color: '#2d3436' },
    tvoc: { icon: '💨', color: '#fd79a8' },
    uv_index: { icon: '☀️', color: '#fdcb6e' },
    kecepatan_angin: { icon: '💨', color: '#74b9ff' },
    curah_hujan: { icon: '🌧️', color: '#6c5ce7' },
  },

  async init() {
    console.log('[ThresholdInfographics] Initializing...');
    
    try {
      // Fetch threshold config
      const response = await fetch('/api/indicators/threshold-config');
      const result = await response.json();

      if (!result.success) {
        throw new Error('Failed to load threshold config');
      }

      this.thresholdConfig = result.data;
      console.log('[ThresholdInfographics] Threshold config loaded');

      // Render AQUAVISKA
      this.renderAquaviska();
      
      // Render IoT CLIMATE
      this.renderIotClimate();
    } catch (error) {
      console.error('[ThresholdInfographics] Error:', error);
    }
  },

  /**
   * Render AQUAVISKA sensors
   */
  renderAquaviska() {
    const container = document.getElementById('aquaviska-thresholds');
    if (!container) return;

    const aquaviskaConfig = this.thresholdConfig.aqua_viska || {};

    Object.entries(aquaviskaConfig).forEach(([key, sensor]) => {
      const card = this.createThresholdCard(key, sensor);
      container.appendChild(card);
    });

    console.log('[ThresholdInfographics] AQUAVISKA rendered');
  },

  /**
   * Render IoT CLIMATE sensors
   */
  renderIotClimate() {
    const container = document.getElementById('climate-thresholds');
    if (!container) return;

    const climateConfig = this.thresholdConfig.iot_climate || {};

    Object.entries(climateConfig).forEach(([key, sensor]) => {
      const card = this.createThresholdCard(key, sensor);
      container.appendChild(card);
    });

    console.log('[ThresholdInfographics] IoT CLIMATE rendered');
  },

  /**
   * Create threshold card element
   */
  createThresholdCard(key, sensor) {
    const { icon, color } = this.sensorIcons[key] || { icon: '📊', color: '#0f172a' };
    const thresholds = sensor.thresholds;

    const card = document.createElement('div');
    card.className = 'threshold-card';
    card.innerHTML = `
      <div class="threshold-header">
        <div class="threshold-icon" style="background: ${color}20; color: ${color}">
          ${icon}
        </div>
        <div class="threshold-title">
          <div class="threshold-label">${sensor.label}</div>
          <div class="threshold-unit">${sensor.unit}</div>
        </div>
      </div>

      <div class="threshold-bar-container">
        ${this.createThresholdBar(thresholds)}
      </div>

      <div class="threshold-range-list">
        ${this.createRangeList(thresholds)}
      </div>
    `;

    return card;
  },

  /**
   * Create horizontal bar chart
   */
  createThresholdBar(thresholds) {
    const amanRanges = this.normalizeRanges(thresholds.normal);
    const waspadaRanges = this.normalizeRanges(thresholds.waspada);
    const bahayaRanges = this.normalizeRanges(thresholds.bahaya);

    // Calculate total range to determine bar proportions
    const allValues = [
      ...amanRanges.flat(),
      ...waspadaRanges.flat(),
      ...bahayaRanges.flat(),
    ].filter(v => v !== null && v !== undefined && isFinite(v));

    const minVal = Math.min(...allValues);
    const maxVal = Math.max(...allValues);
    const range = maxVal - minVal || 1;

    let barHTML = '<div class="threshold-bar-row">';

    // Calculate flex basis for each section
    const amanWidth = this.calculateSectionWidth(amanRanges, minVal, range);
    const waspadaWidth = this.calculateSectionWidth(waspadaRanges, minVal, range);
    const bahayaWidth = this.calculateSectionWidth(bahayaRanges, minVal, range);

    if (amanWidth > 0) {
      barHTML += `<div class="threshold-bar-section aman" style="flex: ${amanWidth}">Aman</div>`;
    }
    if (waspadaWidth > 0) {
      barHTML += `<div class="threshold-bar-section waspada" style="flex: ${waspadaWidth}">Waspada</div>`;
    }
    if (bahayaWidth > 0) {
      barHTML += `<div class="threshold-bar-section bahaya" style="flex: ${bahayaWidth}">Bahaya</div>`;
    }

    barHTML += '</div>';
    return barHTML;
  },

  /**
   * Calculate width for bar section
   */
  calculateSectionWidth(ranges, minVal, totalRange) {
    let sectionRange = 0;

    ranges.forEach(range => {
      if (range.min !== null && range.max !== null) {
        sectionRange += (range.max - range.min);
      } else if (range.max !== null) {
        sectionRange += (range.max - minVal);
      } else if (range.min !== null) {
        sectionRange += (totalRange - (range.min - minVal));
      }
    });

    return sectionRange > 0 ? Math.max(1, sectionRange / totalRange * 100) : 0;
  },

  /**
   * Create range list
   */
  createRangeList(thresholds) {
    let html = '';

    // Aman ranges
    const amanRanges = this.normalizeRanges(thresholds.normal);
    amanRanges.forEach(range => {
      html += `<div class="threshold-range-item aman">
        <span><strong>Aman:</strong> ${this.formatRange(range)}</span>
      </div>`;
    });

    // Waspada ranges
    const waspadaRanges = this.normalizeRanges(thresholds.waspada);
    waspadaRanges.forEach(range => {
      html += `<div class="threshold-range-item waspada">
        <span><strong>Waspada:</strong> ${this.formatRange(range)}</span>
      </div>`;
    });

    // Bahaya ranges
    const bahayaRanges = this.normalizeRanges(thresholds.bahaya);
    bahayaRanges.forEach(range => {
      html += `<div class="threshold-range-item bahaya">
        <span><strong>Bahaya:</strong> ${this.formatRange(range)}</span>
      </div>`;
    });

    return html;
  },

  /**
   * Normalize ranges to array format
   */
  normalizeRanges(threshold) {
    if (Array.isArray(threshold)) {
      return threshold;
    }
    return [threshold];
  },

  /**
   * Format range display
   */
  formatRange(range) {
    if (!range) return '—';

    const { min, max } = range;

    if (min !== null && max !== null) {
      return `${min} – ${max}`;
    } else if (min !== null) {
      return `≥ ${min}`;
    } else if (max !== null) {
      return `≤ ${max}`;
    }
    return '—';
  },
};

// Initialize when DOM ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    console.log('[ThresholdInfographics] DOMContentLoaded, initializing...');
    ThresholdInfographics.init();
  });
} else {
  console.log('[ThresholdInfographics] DOM already ready, initializing...');
  ThresholdInfographics.init();
}

export default ThresholdInfographics;
