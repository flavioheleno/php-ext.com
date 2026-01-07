import { Extension, ExtensionAvailability, FilterState, ExtensionRow, MatrixCell, BuildResult, Snapshot } from '../types';

export function getUniqueValues(
  extensions: Extension[],
  field: keyof ExtensionAvailability
): string[] {
  const values = new Set<string>();
  extensions.forEach(ext => {
    ext.availability.forEach(avail => {
      const value = avail[field];
      if (typeof value === 'string') {
        values.add(value);
      }
    });
  });
  return Array.from(values).sort();
}

export function getUniqueValuesFromResults(
  results: BuildResult[],
  field: keyof BuildResult
): string[] {
  const values = new Set<string>();
  results.forEach(result => {
    const value = result[field];
    if (typeof value === 'string') {
      values.add(value);
    }
  });
  return Array.from(values).sort();
}

export function getUniquePHPVersions(extensions: Extension[]): string[] {
  const versions = new Set<string>();
  extensions.forEach(ext => {
    ext.availability.forEach(avail => {
      avail.php_versions.forEach(v => versions.add(v));
    });
  });
  return Array.from(versions).sort();
}

export function getUniquePHPVersionsFromResults(results: BuildResult[]): string[] {
  const versions = new Set<string>();
  results.forEach(result => {
    versions.add(result.php_version);
  });
  return Array.from(versions).sort();
}

export function filterExtensions(
  extensions: Extension[],
  filters: FilterState
): Extension[] {
  return extensions.filter(ext => {
    if (filters.search && !ext.name.toLowerCase().includes(filters.search.toLowerCase())) {
      return false;
    }
    
    if (!filters.channel && !filters.platform && !filters.platformVersion && !filters.arch) {
      return true;
    }

    return ext.availability.some(avail => {
      if (filters.channel && avail.channel !== filters.channel) return false;
      if (filters.platform && avail.platform !== filters.platform) return false;
      if (filters.platformVersion && avail.platform_version !== filters.platformVersion) return false;
      if (filters.arch && avail.arch !== filters.arch) return false;
      return true;
    });
  });
}

export function buildMatrixData(
  extensions: Extension[],
  phpVersions: string[],
  filters: FilterState
): ExtensionRow[] {
  return extensions.map(ext => {
    const cells: MatrixCell[] = phpVersions.map(phpVersion => {
      const hasVersion = ext.availability.some(avail => {
        const matchesFilters = 
          (!filters.channel || avail.channel === filters.channel) &&
          (!filters.platform || avail.platform === filters.platform) &&
          (!filters.platformVersion || avail.platform_version === filters.platformVersion) &&
          (!filters.arch || avail.arch === filters.arch);
        
        return matchesFilters && avail.available && avail.php_versions.includes(phpVersion);
      });

      return {
        phpVersion,
        status: hasVersion ? 'success' : 'unknown',
        available: hasVersion,
      } as MatrixCell;
    });

    return {
      extension: ext.name,
      cells,
      metrics: {
        failures: 0,
        successes: cells.filter(c => c.status === 'success').length,
        skipped: 0,
        total: cells.length,
        readiness: 0,
      }
    };
  });
}

export function getExtensionDetails(
  extensions: Extension[],
  name: string
): Extension | undefined {
  return extensions.find(ext => ext.name === name);
}

export function getExtensionFromResults(
  snapshot: Snapshot | null,
  name: string
): BuildResult[] {
  if (!snapshot?.results) return [];
  return snapshot.results.filter(r => r.extension === name);
}
