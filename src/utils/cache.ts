const MANIFEST_TTL = 10 * 60 * 1000; // 10 minutes
const SNAPSHOT_TTL = 10 * 60 * 1000; // 10 minutes

interface CacheEntry<T> {
  data: T;
  timestamp: number;
}

function getCacheKey(baseUrl: string, path: string): string {
  return `dataset_cache:${baseUrl}:${path}`;
}

function getFromCache<T>(key: string, ttl: number): T | null {
  try {
    const item = localStorage.getItem(key);
    if (!item) return null;

    const entry: CacheEntry<T> = JSON.parse(item);
    const now = Date.now();

    if (now - entry.timestamp > ttl) {
      localStorage.removeItem(key);
      return null;
    }

    return entry.data;
  } catch {
    return null;
  }
}

function setToCache<T>(key: string, data: T): void {
  try {
    const entry: CacheEntry<T> = {
      data,
      timestamp: Date.now(),
    };
    localStorage.setItem(key, JSON.stringify(entry));
  } catch (error) {
    console.warn('Failed to cache data:', error);
  }
}

export function getCachedManifest(baseUrl: string): unknown {
  return getFromCache(getCacheKey(baseUrl, 'manifest.json'), MANIFEST_TTL);
}

export function setCachedManifest(baseUrl: string, data: unknown): void {
  setToCache(getCacheKey(baseUrl, 'manifest.json'), data);
}

export function getCachedSnapshot(baseUrl: string, snapshotUrl: string): unknown {
  return getFromCache(getCacheKey(baseUrl, snapshotUrl), SNAPSHOT_TTL);
}

export function setCachedSnapshot(baseUrl: string, snapshotUrl: string, data: unknown): void {
  setToCache(getCacheKey(baseUrl, snapshotUrl), data);
}

export function clearDatasetCache(): void {
  try {
    const keys = Object.keys(localStorage);
    keys.forEach(key => {
      if (key.startsWith('dataset_cache:')) {
        localStorage.removeItem(key);
      }
    });
  } catch (error) {
    console.warn('Failed to clear cache:', error);
  }
}
