/**
 * Location Sensor Real-time Update Module with Firebase Listeners
 * Updates sensor cards ONLY when data changes in Firebase
 * Falls back to API polling if Firebase listeners fail
 */

import { initializeFirebase, getDB } from './firebase-config.js';
import { ref, onValue, off } from 'firebase/database';

const LocationSensorUpdater = {
  locationId: null,
  apiEndpoint: '/api/indicators/location',
  iotApiEndpoint: '/api/indicators/iot-climate',
  lastAquaviskaData: null,
  lastIotData: null,
  lastPolledData: null,
  pollInterval: null,
  firebaseListeners: [],
  isInitialized: false,
  useFirebaseListener: false,

  /**
   * Initialize from page data
   */
  init() {
    console.log('[LocationSensorUpdater] Initializing with Firebase Listeners...');

    // Get location ID from URL or data attribute
    const locationIdFromUrl = window.location.pathname.match(/\/modul\/lokasi\/(\d+)/)?.[1];
    const locationIdFromData = document.body.getAttribute('data-location-id');
    
    this.locationId = locationIdFromData || locationIdFromUrl;
    
    if (!this.locationId) {
      console.error('[LocationSensorUpdater] Location ID not found!');
      return;
    }

    console.log('[LocationSensorUpdater] Location ID:', this.locationId);

    // Check if sensor cards exist
    const sensorCards = document.querySelectorAll('[data-sensor-label]');
    if (sensorCards.length === 0) {
      console.error('[LocationSensorUpdater] No sensor cards found!');
      return;
    }

    console.log('[LocationSensorUpdater] Found', sensorCards.length, 'sensor cards');

    // Fetch initial data first
    this.fetchAndUpdateSensorData();

    // Try Firebase listeners first
    this.initFirebaseListeners();
  },

  /**
   * Initialize Firebase listeners
   * Listener detects data changes, then fetches from API for status calculation
   */
  async initFirebaseListeners() {
    try {
      const db = await initializeFirebase();
      if (!db) {
        throw new Error('Firebase initialization failed');
      }

      console.log('[LocationSensorUpdater] Setting up Firebase listeners...');

      // Use arrow functions to preserve context
      const aquaviskaRef = ref(db, `water_quality/Area-1/latest`);
      const aquaviskaListener = onValue(aquaviskaRef, (snapshot) => {
        if (snapshot.exists()) {
          const rawData = snapshot.val();
          console.log('[LocationSensorUpdater] AQUAVISKA Firebase update received:', rawData);
          
          // Check if data actually changed (deep comparison)
          const dataChanged = JSON.stringify(rawData) !== JSON.stringify(this.lastAquaviskaData);
          
          if (dataChanged) {
            console.log('[LocationSensorUpdater] AQUAVISKA data changed (Firebase listener), fetching from API...');
            this.lastAquaviskaData = rawData;
            this.fetchAndUpdateSensorData();
          } else {
            console.log('[LocationSensorUpdater] AQUAVISKA data unchanged (Firebase listener), skipping update');
          }
        }
      }, (error) => {
        console.error('[LocationSensorUpdater] AQUAVISKA Firebase listener error:', error.message);
      });

      // Listen to IoT CLIMATE data - trigger on any change
      const iotRef = ref(db, `weather_station/device_1/latest`);
      const iotListener = onValue(iotRef, (snapshot) => {
        if (snapshot.exists()) {
          const rawData = snapshot.val();
          console.log('[LocationSensorUpdater] IoT CLIMATE Firebase update received:', rawData);
          
          // Check if data actually changed (deep comparison)
          const dataChanged = JSON.stringify(rawData) !== JSON.stringify(this.lastIotData);
          
          if (dataChanged) {
            console.log('[LocationSensorUpdater] IoT CLIMATE data changed (Firebase listener), fetching from API...');
            this.lastIotData = rawData;
            this.fetchAndUpdateSensorData();
          } else {
            console.log('[LocationSensorUpdater] IoT CLIMATE data unchanged (Firebase listener), skipping update');
          }
        }
      }, (error) => {
        console.error('[LocationSensorUpdater] IoT CLIMATE Firebase listener error:', error.message);
      });

      this.firebaseListeners.push({
        ref: aquaviskaRef,
        listener: aquaviskaListener
      });

      this.firebaseListeners.push({
        ref: iotRef,
        listener: iotListener
      });

      this.useFirebaseListener = true;
      this.isInitialized = true;
      console.log('[LocationSensorUpdater] Firebase listeners initialized successfully');
      
      // IMPORTANT: Also start polling in hybrid mode
      // This ensures updates are detected even if Firebase listener fails
      console.log('[LocationSensorUpdater] Starting hybrid polling (5 seconds interval)...');
      this.startPolling();
    } catch (error) {
      console.error('[LocationSensorUpdater] Firebase setup failed:', error);
      console.log('[LocationSensorUpdater] Falling back to polling only');
      this.initFallback();
    }
  },

  /**
   * Start polling in hybrid mode (alongside Firebase listeners)
   */
  startPolling() {
    if (this.pollInterval) {
      clearInterval(this.pollInterval);
    }
    
    this.pollInterval = setInterval(() => {
      console.log('[LocationSensorUpdater] Polling check...');
      this.updateSensorData();
    }, 5000);
  },

  /**
   * Fallback to API polling only (no Firebase)
   */
  initFallback() {
    console.log('[LocationSensorUpdater] Using fallback API polling (every 5 seconds)');
    
    this.updateSensorData();
    this.startPolling();
    this.isInitialized = true;
  },
  /**
   * Fetch sensor data from API (triggered by Firebase listener)
   * Gets data with status calculations from backend
   */
  fetchAndUpdateSensorData() {
    try {
      const timestamp = Date.now();
      const urlLocation = `${this.apiEndpoint}/${this.locationId}?t=${timestamp}`;
      const urlIotClimate = `${this.iotApiEndpoint}/${this.locationId}?t=${timestamp}`;

      Promise.all([
        fetch(urlLocation, {
          headers: {
            'Cache-Control': 'no-cache, no-store, must-revalidate',
            'Pragma': 'no-cache',
            'Expires': '0'
          }
        }),
        fetch(urlIotClimate, {
          headers: {
            'Cache-Control': 'no-cache, no-store, must-revalidate',
            'Pragma': 'no-cache',
            'Expires': '0'
          }
        })
      ])
      .then(responses => {
        return Promise.all(responses.map(response => {
          if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
          }
          return response.json();
        }));
      })
      .then(([locationJson, iotClimateJson]) => {
        let allData = {};
        
        if (locationJson && locationJson.success && locationJson.data) {
          allData = { ...allData, ...locationJson.data };
        }
        
        if (iotClimateJson && iotClimateJson.success && iotClimateJson.data) {
          allData = { ...allData, ...iotClimateJson.data };
        }
        
        console.log('[LocationSensorUpdater] Merged API data:', allData);
        
        if (Object.keys(allData).length > 0) {
          this.updateSensorCards(allData);
          console.log('[LocationSensorUpdater] Data updated from API at', new Date().toLocaleTimeString());
        }
      })
      .catch(error => {
        console.error('[LocationSensorUpdater] Fetch error:', error.message);
      });
    } catch (error) {
      console.error('[LocationSensorUpdater] Error in fetchAndUpdateSensorData:', error);
    }
  },
  /**
   * Handle data update
   */
  handleDataUpdate(data) {
    const currentTimestamp = data.timestamp;
    
    if (currentTimestamp && currentTimestamp === this.lastTimestamp) {
      console.log('[LocationSensorUpdater] No new data (timestamp unchanged):', currentTimestamp);
      return;
    }

    this.lastTimestamp = currentTimestamp;
    this.updateSensorCards(data);
    console.log('[LocationSensorUpdater] Data updated at', new Date().toLocaleTimeString(), '| Timestamp:', currentTimestamp);
  },

  /**
   * Fetch sensor data from API (fallback method)
   * Includes deep comparison to detect actual changes
   */
  updateSensorData() {
    try {
      const timestamp = Date.now();
      const urlLocation = `${this.apiEndpoint}/${this.locationId}?t=${timestamp}`;
      const urlIotClimate = `${this.iotApiEndpoint}/${this.locationId}?t=${timestamp}`;

      const headers = {
        'Cache-Control': 'no-cache, no-store, must-revalidate',
        'Pragma': 'no-cache',
        'Expires': '0'
      };

      Promise.all([
        fetch(urlLocation, { headers }),
        fetch(urlIotClimate, { headers })
      ])
      .then(responses => {
        return Promise.all(responses.map(response => {
          if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
          }
          return response.json();
        }));
      })
      .then(([locationJson, iotClimateJson]) => {
        let allData = {};
        
        if (locationJson && locationJson.success && locationJson.data) {
          allData = { ...allData, ...locationJson.data };
        }
        
        if (iotClimateJson && iotClimateJson.success && iotClimateJson.data) {
          allData = { ...allData, ...iotClimateJson.data };
        }

        // Check if data actually changed (deep comparison)
        const dataChanged = JSON.stringify(allData) !== JSON.stringify(this.lastPolledData);
        
        if (dataChanged) {
          console.log('[LocationSensorUpdater] Polling detected data change, updating UI...');
          this.lastPolledData = JSON.parse(JSON.stringify(allData)); // Deep copy
          this.updateSensorCards(allData);
        } else {
          console.log('[LocationSensorUpdater] Polling check: no changes detected');
        }
      })
      .catch(error => {
        console.error('[LocationSensorUpdater] Polling fetch error:', error.message);
      });
    } catch (error) {
      console.error('[LocationSensorUpdater] Polling error:', error);
    }
  },

  /**
   * Map API indicator keys to sensor labels
   */
  getKeyFromLabel(label) {
    const mapping = {
      // AQUAVISKA sensors
      'Suhu Air': 'temperature',
      'pH': 'ph',
      'Kekeruhan (Turbidity)': 'turbidity',
      'Kekeruhan': 'turbidity',
      'Dissolved Oxygen (DO)': 'do',
      'Total Dissolved Solids (TDS)': 'tds',
      // IoT CLIMATE sensors
      'Suhu Udara': 'suhu_udara',
      'Kelembapan': 'kelembaban',
      'TVOC': 'tvoc',
      'CO₂': 'co2',
      'UV Index': 'uv_index',
      'Kecepatan Angin': 'kecepatan_angin',
      'Curah Hujan': 'curah_hujan',
    };
    return mapping[label] || null;
  },

  flashUpdatedCard(card) {
    if (!card) return;

    if (card._updateHighlightTimer) {
      clearTimeout(card._updateHighlightTimer);
    }

    card.classList.add('is-updated');
    card._updateHighlightTimer = setTimeout(() => {
      card.classList.remove('is-updated');
      card._updateHighlightTimer = null;
    }, 1800);
  },

  /**
   * Update all sensor cards with new data
   */
  updateSensorCards(apiData) {
    const sensorCards = document.querySelectorAll('[data-sensor-label]');
    let updateCount = 0;

    sensorCards.forEach(card => {
      const label = card.getAttribute('data-sensor-label');
      const key = this.getKeyFromLabel(label);

      if (!key || typeof apiData[key] === 'undefined' || apiData[key] === null) {
        console.warn(`[LocationSensorUpdater] No data for sensor: ${label} (key: ${key})`);
        return;
      }

      const indicator = apiData[key];
      const value = indicator.value !== null && indicator.value !== undefined
        ? indicator.value
        : '—';
      const status = indicator.status || 'unknown';
      const sensorValue = card.querySelector('.sensor-value');
      const previousValue = sensorValue?.dataset.currentValue ?? sensorValue?.textContent?.trim() ?? '';
      const valueChanged = String(previousValue).trim() !== String(value).trim();

      console.log(`[LocationSensorUpdater] Updating ${label}: ${value} → ${status}`);

      // Update sensor value
      if (sensorValue) {
        const unitElement = sensorValue.querySelector('.sensor-unit');
        const unit = unitElement?.textContent || '';
        sensorValue.innerHTML = `${value}<span class="sensor-unit">${unit}</span>`;
        sensorValue.dataset.currentValue = String(value);
      }

      // Update sensor bar (status color and percentage)
      const sensorBar = card.querySelector('.sensor-bar');
      if (sensorBar) {
        sensorBar.classList.remove('good', 'medium', 'bad');
        let barClass = 'good';
        switch (status) {
          case 'waspada':
            barClass = 'medium';
            break;
          case 'bahaya':
            barClass = 'bad';
            break;
        }
        sensorBar.classList.add(barClass);
      }

      // Update sensor status chip
      const sensorStatus = card.querySelector('.sensor-status');
      if (sensorStatus) {
        sensorStatus.classList.remove('loading');
        
        sensorStatus.classList.remove('good', 'medium', 'bad');
        let statusClass = 'good';
        let statusText = 'Normal';
        switch (status) {
          case 'waspada':
            statusClass = 'medium';
            statusText = 'Waspada';
            break;
          case 'bahaya':
            statusClass = 'bad';
            statusText = 'Bahaya';
            break;
        }
        sensorStatus.classList.add(statusClass);
        sensorStatus.textContent = statusText;
      }

      if (valueChanged) {
        this.flashUpdatedCard(card);
      }

      updateCount++;
    });

    if (updateCount > 0) {
      console.log(`[LocationSensorUpdater] Successfully updated ${updateCount} sensors`);
    }
  },

  /**
   * Cleanup
   */
  destroy() {
    this.firebaseListeners.forEach(({ listener }) => {
      off(listener);
    });
    this.firebaseListeners = [];

    if (this.pollInterval) {
      clearInterval(this.pollInterval);
    }

    console.log('[LocationSensorUpdater] Cleaned up');
  }
};

// Initialize when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    console.log('[LocationSensorUpdater] DOMContentLoaded, initializing...');
    LocationSensorUpdater.init();
  });
} else {
  console.log('[LocationSensorUpdater] DOM already ready, initializing...');
  LocationSensorUpdater.init();
}

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
  console.log('[LocationSensorUpdater] Cleaning up...');
  LocationSensorUpdater.destroy();
});

window.LocationSensorUpdater = LocationSensorUpdater;

export default LocationSensorUpdater;
