import { Manifest, Snapshot, SchemaStatus } from '../types';

const EXPECTED_SCHEMA_VERSION = '1.0';

export function validateManifestSchema(manifest: unknown): {
  status: SchemaStatus;
  manifest: Manifest | null;
} {
  if (!manifest || typeof manifest !== 'object') {
    return { status: SchemaStatus.MISSING, manifest: null };
  }

  const data = manifest as Record<string, unknown>;

  if (!data.schema_version) {
    return { status: SchemaStatus.MISSING, manifest: null };
  }

  if (data.schema_version !== EXPECTED_SCHEMA_VERSION) {
    console.warn(
      `Manifest schema version mismatch: expected ${EXPECTED_SCHEMA_VERSION}, got ${data.schema_version}`
    );
    return { 
      status: SchemaStatus.MISMATCH, 
      manifest: data as unknown as Manifest 
    };
  }

  return { status: SchemaStatus.VALID, manifest: data as unknown as Manifest };
}

export function validateSnapshotSchema(snapshot: unknown): {
  status: SchemaStatus;
  snapshot: Snapshot | null;
} {
  if (!snapshot || typeof snapshot !== 'object') {
    return { status: SchemaStatus.MISSING, snapshot: null };
  }

  const data = snapshot as Record<string, unknown>;

  if (!data.schema_version) {
    return { status: SchemaStatus.MISSING, snapshot: null };
  }

  if (data.schema_version !== EXPECTED_SCHEMA_VERSION) {
    console.warn(
      `Snapshot schema version mismatch: expected ${EXPECTED_SCHEMA_VERSION}, got ${data.schema_version}`
    );
    return { 
      status: SchemaStatus.MISMATCH, 
      snapshot: data as unknown as Snapshot 
    };
  }

  return { status: SchemaStatus.VALID, snapshot: data as unknown as Snapshot };
}
