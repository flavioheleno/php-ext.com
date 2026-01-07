import { useState, useEffect } from 'react';
import { Manifest, Snapshot } from '../types';

const DEFAULT_DATASET_BASE_URL = '/sample-data/';

function getDatasetUrl(): string {
  if (import.meta.env.VITE_DATASET_URL) {
    return import.meta.env.VITE_DATASET_URL;
  }
  
  const baseUrl = import.meta.env.VITE_DATASET_BASE_URL || DEFAULT_DATASET_BASE_URL;
  return `${baseUrl}manifest.json`;
}

interface UseDatasetResult {
  snapshot: Snapshot | null;
  manifest: Manifest | null;
  loading: boolean;
  error: string | null;
}

export function useDataset(datasetUrl?: string): UseDatasetResult {
  const [manifest, setManifest] = useState<Manifest | null>(null);
  const [snapshot, setSnapshot] = useState<Snapshot | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const url = datasetUrl || getDatasetUrl();
    
    async function loadData() {
      try {
        setLoading(true);
        setError(null);

        const manifestRes = await fetch(url);
        if (!manifestRes.ok) {
          throw new Error(`Failed to load manifest: ${manifestRes.statusText}`);
        }
        const manifestData: Manifest = await manifestRes.json();
        setManifest(manifestData);

        const baseUrl = url.substring(0, url.lastIndexOf('/') + 1);
        const snapshotUrl = manifestData.latest.startsWith('http')
          ? manifestData.latest
          : baseUrl + manifestData.latest;

        const snapshotRes = await fetch(snapshotUrl);
        if (!snapshotRes.ok) {
          throw new Error(`Failed to load snapshot: ${snapshotRes.statusText}`);
        }
        const snapshotData: Snapshot = await snapshotRes.json();
        setSnapshot(snapshotData);
      } catch (err) {
        setError(err instanceof Error ? err.message : 'Unknown error');
        console.error('Failed to load dataset:', err);
      } finally {
        setLoading(false);
      }
    }

    loadData();
  }, [datasetUrl]);

  return { snapshot, manifest, loading, error };
}
