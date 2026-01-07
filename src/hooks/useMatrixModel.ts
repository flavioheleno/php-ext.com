import { useMemo } from 'react';
import { 
  Snapshot, 
  Manifest, 
  FilterState, 
  MatrixModel, 
  MatrixCell, 
  ExtensionRow,
  ExtensionMetrics,
  BuildResult
} from '../types';
import { selectBestResult, matchesFilters } from '../utils/matrix';

function computeMetrics(cells: MatrixCell[]): ExtensionMetrics {
  const metrics = cells.reduce(
    (acc, cell) => {
      if (cell.status === 'success') acc.successes++;
      else if (cell.status === 'failure') acc.failures++;
      else if (cell.status === 'skipped') acc.skipped++;
      acc.total++;
      return acc;
    },
    { successes: 0, failures: 0, skipped: 0, total: 0, readiness: 0 }
  );

  const testable = metrics.successes + metrics.failures;
  metrics.readiness = testable > 0 ? (metrics.successes / testable) * 100 : 0;

  return metrics;
}

function getPhpVersionsFromManifest(manifest: Manifest | null): string[] {
  if (manifest?.axes?.php_versions) {
    return manifest.axes.php_versions.sort();
  }
  return [];
}

function getPhpVersionsFromSnapshot(snapshot: Snapshot | null): string[] {
  if (!snapshot?.results) return [];
  
  const versions = new Set<string>();
  snapshot.results.forEach(result => {
    versions.add(result.php_version);
  });
  
  return Array.from(versions).sort();
}

function getPhpVersions(manifest: Manifest | null, snapshot: Snapshot | null): string[] {
  const fromManifest = getPhpVersionsFromManifest(manifest);
  if (fromManifest.length > 0) return fromManifest;
  
  return getPhpVersionsFromSnapshot(snapshot);
}

function buildMatrixModel(
  snapshot: Snapshot | null,
  manifest: Manifest | null,
  filters: FilterState
): MatrixModel {
  if (!snapshot?.results) {
    return {
      columns: [],
      rows: [],
      totals: {
        totalExtensions: 0,
        totalCells: 0,
        totalSuccesses: 0,
        totalFailures: 0,
        totalSkipped: 0,
        averageReadiness: 0,
      },
    };
  }

  const phpVersions = getPhpVersions(manifest, snapshot);
  const results = snapshot.results;

  // Group results by extension
  const extensionMap = new Map<string, BuildResult[]>();
  results.forEach(result => {
    if (!extensionMap.has(result.extension)) {
      extensionMap.set(result.extension, []);
    }
    extensionMap.get(result.extension)!.push(result);
  });

  // Build rows
  const rows: ExtensionRow[] = [];

  extensionMap.forEach((extensionResults, extension) => {
    // Filter by search
    if (filters.search && !extension.toLowerCase().includes(filters.search.toLowerCase())) {
      return;
    }

    const cells: MatrixCell[] = phpVersions.map(phpVersion => {
      // Find matching results for this cell
      const matchingResults = extensionResults.filter(result => {
        return (
          result.php_version === phpVersion &&
          matchesFilters(
            result,
            filters.channel,
            filters.platform,
            filters.platformVersion,
            filters.arch
          )
        );
      });

      if (matchingResults.length === 0) {
        return {
          phpVersion,
          status: 'unknown' as const,
          available: false,
        };
      }

      // Select best result
      const bestResult = selectBestResult(matchingResults, filters.channel || 'release')!;

      return {
        phpVersion,
        status: bestResult.status,
        category: bestResult.category,
        extensionVersion: bestResult.extension_version,
        runUrl: bestResult.run?.url,
        artifactUrl: bestResult.artifact?.url,
        available: true,
      };
    });

    const metrics = computeMetrics(cells);
    
    rows.push({
      extension,
      cells,
      metrics,
    });
  });

  // Apply show filter
  let filteredRows = rows;
  if (filters.show === 'failing') {
    filteredRows = rows.filter(row => row.metrics.failures > 0);
  } else if (filters.show === 'regressions') {
    // For now, regressions filter is same as failing
    // TODO: implement regression detection with historical data
    filteredRows = rows.filter(row => row.metrics.failures > 0);
  }

  // Apply sort
  if (filters.sort === 'readiness') {
    filteredRows.sort((a, b) => a.metrics.readiness - b.metrics.readiness);
  } else if (filters.sort === 'failures') {
    filteredRows.sort((a, b) => b.metrics.failures - a.metrics.failures);
  } else {
    filteredRows.sort((a, b) => a.extension.localeCompare(b.extension));
  }

  // Compute totals
  const totals = filteredRows.reduce(
    (acc, row) => {
      acc.totalExtensions++;
      acc.totalCells += row.metrics.total;
      acc.totalSuccesses += row.metrics.successes;
      acc.totalFailures += row.metrics.failures;
      acc.totalSkipped += row.metrics.skipped;
      return acc;
    },
    {
      totalExtensions: 0,
      totalCells: 0,
      totalSuccesses: 0,
      totalFailures: 0,
      totalSkipped: 0,
      averageReadiness: 0,
    }
  );

  if (totals.totalExtensions > 0) {
    const totalReadiness = filteredRows.reduce((sum, row) => sum + row.metrics.readiness, 0);
    totals.averageReadiness = totalReadiness / totals.totalExtensions;
  }

  return {
    columns: phpVersions,
    rows: filteredRows,
    totals,
  };
}

export function useMatrixModel(
  snapshot: Snapshot | null,
  manifest: Manifest | null,
  filters: FilterState
): MatrixModel {
  return useMemo(
    () => buildMatrixModel(snapshot, manifest, filters),
    [snapshot, manifest, filters]
  );
}

// Validation/assertion helpers for development
export function validateMatrixModel(model: MatrixModel): boolean {
  if (import.meta.env.DEV) {
    console.group('Matrix Model Validation');
    
    // Check columns
    console.assert(Array.isArray(model.columns), 'Columns must be an array');
    console.assert(model.columns.length >= 0, 'Columns must not be negative');
    
    // Check rows
    console.assert(Array.isArray(model.rows), 'Rows must be an array');
    
    model.rows.forEach((row, idx) => {
      // Check row structure
      console.assert(typeof row.extension === 'string', `Row ${idx}: extension must be a string`);
      console.assert(Array.isArray(row.cells), `Row ${idx}: cells must be an array`);
      console.assert(row.cells.length === model.columns.length, 
        `Row ${idx}: cells length must match columns length`);
      
      // Check metrics
      const metrics = row.metrics;
      console.assert(metrics.total === row.cells.length, 
        `Row ${idx}: metrics.total must equal cells.length`);
      console.assert(
        metrics.successes + metrics.failures + metrics.skipped <= metrics.total,
        `Row ${idx}: sum of successes, failures, skipped must not exceed total`
      );
      console.assert(
        metrics.readiness >= 0 && metrics.readiness <= 100,
        `Row ${idx}: readiness must be between 0 and 100`
      );
      
      // Check cells
      row.cells.forEach((cell, cellIdx) => {
        console.assert(
          cell.phpVersion === model.columns[cellIdx],
          `Row ${idx}, Cell ${cellIdx}: phpVersion must match column`
        );
        console.assert(
          ['success', 'failure', 'skipped', 'unknown'].includes(cell.status),
          `Row ${idx}, Cell ${cellIdx}: invalid status ${cell.status}`
        );
      });
    });
    
    // Check totals
    const calculatedExtensions = model.rows.length;
    console.assert(
      model.totals.totalExtensions === calculatedExtensions,
      `Totals: totalExtensions mismatch (${model.totals.totalExtensions} vs ${calculatedExtensions})`
    );
    
    console.assert(
      model.totals.averageReadiness >= 0 && model.totals.averageReadiness <= 100,
      'Totals: averageReadiness must be between 0 and 100'
    );
    
    console.log('✓ Matrix model validation passed');
    console.groupEnd();
    
    return true;
  }
  
  return true;
}
