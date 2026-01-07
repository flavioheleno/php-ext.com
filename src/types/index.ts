export interface ExtensionAvailability {
  channel: string;
  platform: string;
  platform_version: string;
  arch: string;
  php_versions: string[];
  available: boolean;
}

export interface Extension {
  name: string;
  availability: ExtensionAvailability[];
}

export interface DatasetMetadata {
  id: string;
  url: string;
  timestamp: string;
}

export interface Manifest {
  schema_version: string;
  latest: DatasetMetadata;
  datasets: DatasetMetadata[];
}

export interface Snapshot {
  schema_version: string;
  timestamp: string;
  extensions: Extension[];
}

export interface FilterState {
  channel: string;
  platform: string;
  platformVersion: string;
  arch: string;
  search: string;
  show: 'all' | 'failing' | 'regressions';
  sort: 'name' | 'readiness' | 'failures';
}

export type ShowFilter = 'all' | 'failing' | 'regressions';
export type SortOption = 'name' | 'readiness' | 'failures';

export interface FilterOptions {
  channels: string[];
  platforms: string[];
  platformVersions: string[];
  arches: string[];
}

export interface MatrixCell {
  phpVersion: string;
  available: boolean;
}

export interface ExtensionRow {
  name: string;
  cells: MatrixCell[];
}

export enum LoadingStatus {
  IDLE = 'idle',
  LOADING = 'loading',
  SUCCESS = 'success',
  ERROR = 'error',
}

export enum SchemaStatus {
  VALID = 'valid',
  MISMATCH = 'mismatch',
  MISSING = 'missing',
}

export interface DatasetError {
  type: 'network' | 'parse' | 'schema' | 'unknown';
  message: string;
  details?: unknown;
}

export interface DatasetContextValue {
  manifest: Manifest | null;
  snapshot: Snapshot | null;
  loading: boolean;
  error: DatasetError | null;
  schemaStatus: SchemaStatus;
  reload: () => Promise<void>;
}
