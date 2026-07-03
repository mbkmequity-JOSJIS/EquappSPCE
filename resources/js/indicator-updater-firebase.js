/**
 * Real-time Indicator Update Module with Firebase Listeners
 * Updates indicators ONLY when data changes in Firebase
 * Falls back to API polling if Firebase listeners fail
 */

import { initializeFirebase, getDB } from './firebase-config.js';
import { ref, onValue, off } from 'firebase/database';

// Sensor configurations
const AQUAVISKA_SENSORS = ['temperature', 'ph', 'do', 'turbidity', 'tds'];
const IOT_CLIMATE_SENSORS = ['suhu_udara', 'kelembaban', 'co2', 'tvoc', 'uv_index', 'kecepatan_angin', 'curah_hujan'];

const STATUS_LABELS = {
  'normal': 'Normal',
  'waspada': 'Waspada',
  'bahaya': 'Bahaya'
};

const IndicatorUpdater = {
  apiEndpoint: '/api/indicators/aquaviska',
  iotApiEndpoint: '/api/indicators/iot-climate',
  area: 'Area-1',
  locationId: 1,
  lastAquaviskaData: null,
  lastIotData: null,
  pollInterval: null, // Fallback polling
  firebaseListeners: [], // Track Firebase listeners for cleanup
  isInitialized: false,
  useFirebaseListener: false, // Flag to track if Firebase listener is active
  
  /**
   * Wait for table element to appear with retries
   */
  waitForTable() {
    return new Promise((resolve) => {
      const checkTable = () => {
        const tbody = document.getElementById('aquaviska-indicators');
        if (tbody) {
          console.log('[IndicatorUpdater] Table found!');
          resolve(tbody);
          return;
        }

        setTimeout(checkTable, 100);
      };
      checkTable();
    });
  },

  /**
   * Initialize with Firebase listeners
   */
  async init() {
    console.log('[IndicatorUpdater] Initializing with Firebase Listeners...');
    
    const tbody = await this.waitForTable();
    if (!tbody) {
      console.error('[IndicatorUpdater] Table not found, falling back to API polling');
      this.initFallback();
      return;
    }

    console.log('[IndicatorUpdater] Setting up Firebase listeners...');

    try {
      // Initialize Firebase
      const db = await initializeFirebase();
      if (!db) {
        throw new Error('Firebase initialization failed');
      }

      // Listen to AQUAVISKA data - trigger on any change
      const aquaviskaRef = ref(db, `water_quality/${this.area}/latest`);
      const aquaviskaListener = onValue(aquaviskaRef, (snapshot) => {
        if (snapshot.exists()) {
          const rawData = snapshot.val();
          
          // Check if data actually changed (deep comparison)
          const dataChanged = JSON.stringify(rawData) !== JSON.stringify(this.lastAquaviskaData);
          
          if (dataChanged) {
            console.log('[IndicatorUpdater] AQUAVISKA data changed (Firebase listener), fetching from API...');
            this.lastAquaviskaData = rawData;
            this.fetchAndUpdateData();
          }
        }
      }, (error) => {
        console.error('[IndicatorUpdater] AQUAVISKA Firebase listener error:', error.message);
        this.initFallback();
      });

      // Listen to IoT CLIMATE data - trigger on any change
      const iotRef = ref(db, `weather_station/device_1/latest`);
      const iotListener = onValue(iotRef, (snapshot) => {
        if (snapshot.exists()) {
          const rawData = snapshot.val();
          
          // Check if data actually changed (deep comparison)
          const dataChanged = JSON.stringify(rawData) !== JSON.stringify(this.lastIotData);
          
          if (dataChanged) {
            console.log('[IndicatorUpdater] IoT CLIMATE data changed (Firebase listener), fetching from API...');
            this.lastIotData = rawData;
            this.fetchAndUpdateData();
          }
        }
      }, (error) => {
        console.error('[IndicatorUpdater] IoT CLIMATE Firebase listener error:', error.message);
        this.initFallback();
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
      console.log('[IndicatorUpdater] Firebase listeners initialized successfully');
    } catch (error) {
      console.error('[IndicatorUpdater] Failed to setup Firebase listeners:', error);
      this.initFallback();
    }
  },

  /**
   * Fallback to API polling if Firebase fails
   */
  initFallback() {
    console.log('[IndicatorUpdater] Using fallback API polling (every 5 seconds)');
    
    this.updateIndicatorsViaAPI();
    this.pollInterval = setInterval(() => {
      this.updateIndicatorsViaAPI();
    }, 5000); // 5 seconds fallback

    this.isInitialized = true;
  },

  /**
   * Fetch data from API and update table
   * This gets data with status calculations from backend
   */
  fetchAndUpdateData() {
    try {
      const timestamp = Date.now();
      const urlAqua = `${this.apiEndpoint}?t=${timestamp}`;
      const urlIot = `${this.iotApiEndpoint}?t=${timestamp}`;

      Promise.all([
        fetch(urlAqua, {
          headers: {
            'Cache-Control': 'no-cache, no-store, must-revalidate',
            'Pragma': 'no-cache',
            'Expires': '0'
          }
        }),
        fetch(urlIot, {
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
      .then(([aquaJson, iotJson]) => {
        let allData = {};
        
        if (aquaJson.success && aquaJson.data) {
          allData = { ...allData, ...aquaJson.data };
        }
        
        if (iotJson.success && iotJson.data) {
          allData = { ...allData, ...iotJson.data };
        }
        
        if (Object.keys(allData).length > 0) {
          this.updateTable(allData);
          console.log('[IndicatorUpdater] Data updated from API at', new Date().toLocaleTimeString());
        }
      })
      .catch(error => {
        console.error('[IndicatorUpdater] API Fetch error:', error.message);
      });
    } catch (error) {
      console.error('[IndicatorUpdater] Fetch error:', error);
    }
  },

  /**
   * Handle data update (can be from Firebase or API)
   */
  handleDataUpdate(data) {
    // Check if data has changed (compare timestamp)
    const currentTimestamp = data.timestamp;
    
    if (currentTimestamp && currentTimestamp === this.lastTimestamp) {
      console.log('[IndicatorUpdater] No new data (timestamp unchanged):', currentTimestamp);
      return;
    }

    // Data is new, update the table
    this.lastTimestamp = currentTimestamp;
    this.updateTable(data);
    this.lastUpdateTime = new Date().toLocaleTimeString();
    console.log('[IndicatorUpdater] Table updated at', this.lastUpdateTime, '| Timestamp:', currentTimestamp);
  },

  /**
   * Fetch indicators data from API (fallback method)
   */
  updateIndicatorsViaAPI() {
    try {
      const timestamp = Date.now();
      const urlAquaviska = `${this.apiEndpoint}/${this.area}?t=${timestamp}`;
      const urlIotClimate = `${this.iotApiEndpoint}/${this.locationId}?t=${timestamp}`;

      Promise.all([
        fetch(urlAquaviska, {
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
      .then(([aquaviskaJson, iotClimateJson]) => {
        let allData = {};
        
        if (aquaviskaJson.success && aquaviskaJson.data) {
          allData = { ...allData, ...aquaviskaJson.data };
        }
        
        if (iotClimateJson.success && iotClimateJson.data) {
          allData = { ...allData, ...iotClimateJson.data };
        }
        
        this.handleDataUpdate(allData);
      })
      .catch(error => {
        console.error('[IndicatorUpdater] API Fetch error:', error.message);
      });
    } catch (error) {
      console.error('[IndicatorUpdater] API Error:', error);
    }
  },

  /**
   * Update table rows with fetched data
   */
  updateTable(data) {
    const tbody = document.getElementById('aquaviska-indicators');
    if (!tbody) return;

    let updateCount = 0;

    // Update AQUAVISKA sensors
    AQUAVISKA_SENSORS.forEach(key => {
      const indicator = data[key];
      if (!indicator) return;

      const row = tbody.querySelector(`tr[data-indicator="${key}"]`);
      if (!row) return;

      const statusCell = row.querySelector('td:last-child');
      if (statusCell) {
        const status = indicator.status;
        let chipClass = 'green';
        let statusLabel = STATUS_LABELS[status] || 'Normal';
        
        if (status === 'waspada') chipClass = 'yellow';
        else if (status === 'bahaya') chipClass = 'red';

        // Only update the status chip, preserve threshold info
        const statusChip = statusCell.querySelector('.status-chip');
        if (statusChip) {
          const newChipHTML = `<span class="status-chip ${chipClass}">
          ${statusLabel}
        </span>`;
          
          if (statusChip.outerHTML !== newChipHTML) {
            statusChip.outerHTML = newChipHTML;
            updateCount++;
          }
        }
      }
    });

    // Update IoT CLIMATE sensors
    IOT_CLIMATE_SENSORS.forEach(key => {
      const row = tbody.querySelector(`tr[data-indicator="${key}"]`);
      if (!row) return;

      const statusCell = row.querySelector('td:last-child');
      if (statusCell) {
        const indicator = data[key];
        if (!indicator) return;

        const status = indicator.status || 'unknown';
        let chipClass = 'green';
        if (status === 'waspada') chipClass = 'yellow';
        else if (status === 'bahaya') chipClass = 'red';
        
        const statusLabel = STATUS_LABELS[status] || 'Unknown';
        
        // Only update the status chip, preserve threshold info
        const statusChip = statusCell.querySelector('.status-chip');
        if (statusChip) {
          const newChipHTML = `<span class="status-chip ${chipClass}">
          ${statusLabel}
        </span>`;
          
          if (statusChip.outerHTML !== newChipHTML) {
            statusChip.outerHTML = newChipHTML;
            updateCount++;
          }
        }
      }
    });

    if (updateCount > 0) {
      console.log(`[IndicatorUpdater] Updated ${updateCount} indicators`);
    }
  },

  /**
   * Cleanup
   */
  destroy() {
    // Remove Firebase listeners
    this.firebaseListeners.forEach(({ listener }) => {
      off(listener);
    });
    this.firebaseListeners = [];

    // Clear polling interval
    if (this.pollInterval) {
      clearInterval(this.pollInterval);
    }

    console.log('[IndicatorUpdater] Cleaned up');
  }
};

// Initialize when document is ready
const startInit = async () => {
  console.log('[IndicatorUpdater] Initializing...');
  await IndicatorUpdater.init();
};

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', startInit);
} else {
  startInit();
}

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
  IndicatorUpdater.destroy();
});

window.IndicatorUpdater = IndicatorUpdater;

export default IndicatorUpdater;
