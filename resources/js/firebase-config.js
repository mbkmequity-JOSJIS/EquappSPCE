/**
 * Firebase Configuration for Real-time Listeners
 * Fetches public config from API endpoint
 */

import { initializeApp } from 'firebase/app';
import { getDatabase } from 'firebase/database';

let database = null;

/**
 * Initialize Firebase with config from API
 */
export async function initializeFirebase() {
  try {
    const response = await fetch('/api/indicators/firebase-config');
    const result = await response.json();

    if (!result.success || !result.data) {
      throw new Error('Failed to get Firebase config from API');
    }

    const firebaseConfig = {
      apiKey: result.data.apiKey,
      projectId: result.data.projectId,
      databaseURL: result.data.databaseUrl,
      appId: result.data.appId,
    };

    console.log('[Firebase] Initializing with config:', firebaseConfig.projectId);

    const app = initializeApp(firebaseConfig);
    database = getDatabase(app);

    console.log('[Firebase] Initialized successfully');
    return database;
  } catch (error) {
    console.error('[Firebase] Initialization failed:', error.message);
    return null;
  }
}

/**
 * Get database reference
 */
export function getDB() {
  if (!database) {
    console.warn('[Firebase] Database not initialized, must call initializeFirebase first');
  }
  return database;
}

export default database;

