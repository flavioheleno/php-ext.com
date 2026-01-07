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

export interface BuildResult {
  extension: string;
  extension_version: string;
  channel: string;
  php_version: string;
  platform: string;
  platform_version: string;
  arch: string;
  status: 'success' | 'failure' | 'skipped' | 'unknown';
  category?: string;
  finished_at?: string;
  run?: {
    url: string;
  };
  artifact?: {
    url: string;
  };
}

export interface DatasetMetadata {
  id: string;
  url: string;
  timestamp: string;
}

export interface ManifestAxes {
  php_versions: string[];
  channels?: string[];
  platforms?: string[];
  arches?: string[];
}

export interface Manifest {
  schema_version: string;
  latest: DatasetMetadata;
  datasets: DatasetMetadata[];
  axes?: ManifestAxes;
}

export interface Snapshot {
  schema_version: string;
  timestamp: string;
  extensions?: Extension[];
  results?: BuildResult[];
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
  status: 'success' | 'failure' | 'skipped' | 'unknown';
  category?: string;
  extensionVersion?: string;
  runUrl?: string;
  artifactUrl?: string;
  available: boolean;
}

export interface ExtensionMetrics {
  failures: number;
  successes: number;
  skipped: number;
  total: number;
  readiness: number;
}

export interface ExtensionRow {
  extension: string;
  cells: MatrixCell[];
  metrics: ExtensionMetrics;
}

export interface MatrixModel {
  columns: string[];
  rows: ExtensionRow[];
  totals: {
    totalExtensions: number;
    totalCells: number;
    totalSuccesses: number;
    totalFailures: number;
    totalSkipped: number;
    averageReadiness: number;
  };
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
