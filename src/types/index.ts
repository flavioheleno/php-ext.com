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

export interface Snapshot {
  timestamp: string;
  extensions: Extension[];
}

export interface SnapshotMetadata {
  id: string;
  url: string;
  timestamp: string;
}

export interface Manifest {
  latest: string;
  snapshots: SnapshotMetadata[];
}

export interface FilterState {
  channel: string;
  platform: string;
  platformVersion: string;
  arch: string;
  search: string;
}

export interface MatrixCell {
  phpVersion: string;
  available: boolean;
}

export interface ExtensionRow {
  name: string;
  cells: MatrixCell[];
}
