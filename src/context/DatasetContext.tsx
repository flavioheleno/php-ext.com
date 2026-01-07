import { createContext, useContext, ReactNode, useState, useEffect, useCallback } from 'react';
import { 
  Manifest, 
  Snapshot, 
  DatasetContextValue, 
  DatasetError, 
  SchemaStatus 
} from '../types';
import { 
  getCachedManifest, 
  setCachedManifest, 
  getCachedSnapshot, 
  setCachedSnapshot,
  clearDatasetCache 
} from '../utils/cache';
import { validateManifestSchema, validateSnapshotSchema } from '../utils/schema';

const DatasetContext = createContext<DatasetContextValue | null>(null);

interface DatasetProviderProps {
  children: ReactNode;
  baseUrl?: string;
}

function getDatasetBaseUrl(): string {
  if (import.meta.env.VITE_DATASET_URL) {
    const url = import.meta.env.VITE_DATASET_URL;
    return url.substring(0, url.lastIndexOf('/') + 1);
  }
  
  return import.meta.env.VITE_DATASET_BASE_URL || '/sample-data/';
}

function resolveUrl(baseUrl: string, path: string): string {
  if (path.startsWith('http://') || path.startsWith('https://')) {
    return path;
  }
  
  return baseUrl + path;
}

export function DatasetProvider({ children, baseUrl: providedBaseUrl }: DatasetProviderProps) {
  const [manifest, setManifest] = useState<Manifest | null>(null);
  const [snapshot, setSnapshot] = useState<Snapshot | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<DatasetError | null>(null);
  const [schemaStatus, setSchemaStatus] = useState<SchemaStatus>(SchemaStatus.VALID);

  const baseUrl = providedBaseUrl || getDatasetBaseUrl();

  const loadDataset = useCallback(async (skipCache = false) => {
    try {
      setLoading(true);
      setError(null);

      // Load manifest
      let manifestData: Manifest | null = null;
      
      if (!skipCache) {
        const cached = getCachedManifest(baseUrl);
        if (cached) {
          const validation = validateManifestSchema(cached);
          if (validation.manifest) {
            manifestData = validation.manifest;
            setSchemaStatus(validation.status);
          }
        }
      }

      if (!manifestData) {
        const manifestUrl = resolveUrl(baseUrl, 'manifest.json');
        const manifestRes = await fetch(manifestUrl);
        
        if (!manifestRes.ok) {
          throw {
            type: 'network',
            message: `Failed to load manifest: ${manifestRes.statusText}`,
            details: { status: manifestRes.status, url: manifestUrl }
          };
        }

        const manifestJson = await manifestRes.json();
        setCachedManifest(baseUrl, manifestJson);
        
        const validation = validateManifestSchema(manifestJson);
        
        if (!validation.manifest) {
          throw {
            type: 'schema',
            message: 'Invalid manifest schema',
            details: { validation }
          };
        }

        manifestData = validation.manifest;
        setSchemaStatus(validation.status);
      } else {
        const validation = validateManifestSchema(manifestData);
        setSchemaStatus(validation.status);
      }

      setManifest(manifestData);

      // Load snapshot
      const snapshotUrl = manifestData.latest.url;
      let snapshotData: Snapshot | null = null;

      if (!skipCache) {
        const cached = getCachedSnapshot(baseUrl, snapshotUrl);
        if (cached) {
          const validation = validateSnapshotSchema(cached);
          if (validation.snapshot) {
            snapshotData = validation.snapshot;
            if (validation.status === SchemaStatus.MISMATCH) {
              setSchemaStatus(SchemaStatus.MISMATCH);
            }
          }
        }
      }

      if (!snapshotData) {
        const fullSnapshotUrl = resolveUrl(baseUrl, snapshotUrl);
        const snapshotRes = await fetch(fullSnapshotUrl);
        
        if (!snapshotRes.ok) {
          throw {
            type: 'network',
            message: `Failed to load snapshot: ${snapshotRes.statusText}`,
            details: { status: snapshotRes.status, url: fullSnapshotUrl }
          };
        }

        const snapshotJson = await snapshotRes.json();
        setCachedSnapshot(baseUrl, snapshotUrl, snapshotJson);
        
        const validation = validateSnapshotSchema(snapshotJson);
        
        if (!validation.snapshot) {
          throw {
            type: 'schema',
            message: 'Invalid snapshot schema',
            details: { validation }
          };
        }

        snapshotData = validation.snapshot;
        
        if (validation.status === SchemaStatus.MISMATCH) {
          setSchemaStatus(SchemaStatus.MISMATCH);
        }
      } else {
        const validation = validateSnapshotSchema(snapshotData);
        if (validation.status === SchemaStatus.MISMATCH) {
          setSchemaStatus(SchemaStatus.MISMATCH);
        }
      }

      setSnapshot(snapshotData);
    } catch (err) {
      const datasetError = err as DatasetError;
      setError(datasetError);
      console.error('Failed to load dataset:', err);
    } finally {
      setLoading(false);
    }
  }, [baseUrl]);

  const reload = useCallback(async () => {
    clearDatasetCache();
    await loadDataset(true);
  }, [loadDataset]);

  useEffect(() => {
    loadDataset();
  }, [loadDataset]);

  const value: DatasetContextValue = {
    manifest,
    snapshot,
    loading,
    error,
    schemaStatus,
    reload,
  };

  return <DatasetContext.Provider value={value}>{children}</DatasetContext.Provider>;
}

export function useDatasetContext(): DatasetContextValue {
  const context = useContext(DatasetContext);
  if (!context) {
    throw new Error('useDatasetContext must be used within DatasetProvider');
  }
  return context;
}
