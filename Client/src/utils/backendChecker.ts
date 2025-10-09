/**
 * Backend Availability Checker
 * Checks if the backend is running before making API calls
 */

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:3001';
const HEALTH_CHECK_ENDPOINT = `${API_BASE_URL}/api/health`;
const CACHE_DURATION = 30000; // 30 seconds

interface BackendStatus {
  isAvailable: boolean;
  lastChecked: number;
}

let cachedStatus: BackendStatus = {
  isAvailable: false,
  lastChecked: 0
};

/**
 * Check if the backend is currently available
 * Results are cached for performance
 */
export async function isBackendAvailable(): Promise<boolean> {
  const now = Date.now();

  // Return cached result if recent
  if (now - cachedStatus.lastChecked < CACHE_DURATION) {
    console.log('[Backend Checker] Using cached status:', cachedStatus.isAvailable);
    return cachedStatus.isAvailable;
  }

  try {
    console.log('[Backend Checker] Checking backend availability...');

    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 5000); // 5 second timeout

    const response = await fetch(HEALTH_CHECK_ENDPOINT, {
      method: 'GET',
      signal: controller.signal,
      headers: {
        'Accept': 'application/json',
      },
    });

    clearTimeout(timeoutId);

    const isAvailable = response.ok;

    cachedStatus = {
      isAvailable,
      lastChecked: now
    };

    console.log('[Backend Checker] Backend available:', isAvailable);
    return isAvailable;

  } catch (error) {
    console.log('[Backend Checker] Backend not available:', error);

    cachedStatus = {
      isAvailable: false,
      lastChecked: now
    };

    return false;
  }
}

/**
 * Force a fresh backend availability check
 */
export async function forceBackendCheck(): Promise<boolean> {
  cachedStatus.lastChecked = 0; // Invalidate cache
  return await isBackendAvailable();
}

/**
 * Reset the backend status cache
 */
export function resetBackendCache(): void {
  cachedStatus = {
    isAvailable: false,
    lastChecked: 0
  };
}
