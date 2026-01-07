import { useParams } from 'react-router-dom';
import { useDatasetContext } from '../context/DatasetContext';
import { useFilters } from '../hooks/useFilters';
import { SchemaWarning } from '../components/SchemaWarning';
import { MatrixCell, ExtensionMetrics } from '../types';
import { useMemo, useState } from 'react';
import { matchesFilters, selectBestResult } from '../utils/matrix';
import { CellDrawer } from '../components/CellDrawer';
import { buildFilteredURL } from '../utils/url';

function computeExtensionMetrics(cells: MatrixCell[]): ExtensionMetrics {
  const failures = cells.filter(c => c.status === 'failure').length;
  const successes = cells.filter(c => c.status === 'success').length;
  const skipped = cells.filter(c => c.status === 'skipped').length;
  const total = cells.filter(c => c.status !== 'unknown').length;
  const readiness = total > 0 ? (successes / (successes + failures)) * 100 : 0;

  return { failures, successes, skipped, total, readiness };
}

interface FailingCombination {
  phpVersion: string;
  platform: string;
  platformVersion: string;
  arch: string;
  channel: string;
  category?: string;
  runUrl?: string;
  artifactUrl?: string;
}

export function ExtensionDetailPage() {
  const { name } = useParams<{ name: string }>();
  const { snapshot, manifest, loading, error } = useDatasetContext();
  const { filters } = useFilters();
  const [selectedCell, setSelectedCell] = useState<{ phpVersion: string; cell: MatrixCell | null } | null>(null);

  const extensionData = useMemo(() => {
    if (!name || !snapshot?.results) return null;

    const results = snapshot.results;
    const extensionResults = results.filter(r => r.extension === name);

    if (extensionResults.length === 0) return null;

    // Get PHP versions from manifest or results
    const phpVersions = manifest?.axes?.php_versions || 
      [...new Set(extensionResults.map(r => r.php_version))].sort();

    // Build cells for the mini-grid
    const cells: MatrixCell[] = phpVersions.map(phpVersion => {
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

      const bestResult = selectBestResult(matchingResults, filters.channel || 'stable')!;

      return {
        phpVersion,
        status: bestResult.status,
        category: bestResult.category,
        extensionVersion: bestResult.extension_version,
        runUrl: bestResult.run?.url,
        artifactUrl: bestResult.artifact?.url,
        startedAt: bestResult.started_at,
        finishedAt: bestResult.finished_at,
        available: true,
      };
    });

    const metrics = computeExtensionMetrics(cells);

    // Get failing combinations
    const failingCombos: FailingCombination[] = [];
    extensionResults.forEach(result => {
      if (result.status === 'failure' &&
          matchesFilters(
            result,
            filters.channel,
            filters.platform,
            filters.platformVersion,
            filters.arch
          )) {
        failingCombos.push({
          phpVersion: result.php_version,
          platform: result.platform,
          platformVersion: result.platform_version,
          arch: result.arch,
          channel: result.channel,
          category: result.category,
          runUrl: result.run?.url,
          artifactUrl: result.artifact?.url,
        });
      }
    });

    // Group failing combos by PHP version
    const groupedFailures: Record<string, FailingCombination[]> = {};
    failingCombos.forEach(combo => {
      if (!groupedFailures[combo.phpVersion]) {
        groupedFailures[combo.phpVersion] = [];
      }
      groupedFailures[combo.phpVersion].push(combo);
    });

    return {
      extensionName: name,
      phpVersions,
      cells,
      metrics,
      groupedFailures,
    };
  }, [name, snapshot, manifest, filters]);

  const handleCellClick = (phpVersion: string) => {
    if (!extensionData) return;
    const cell = extensionData.cells.find(c => c.phpVersion === phpVersion) || null;
    setSelectedCell({ phpVersion, cell });
  };

  const closeCellDrawer = () => {
    setSelectedCell(null);
  };

  if (loading) {
    return (
      <div className="page-container">
        <div className="loading">
          <div className="spinner"></div>
          <p>Loading extension data...</p>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="page-container">
        <div className="error">
          <h2>Error Loading Data</h2>
          <p>{error.message}</p>
        </div>
      </div>
    );
  }

  if (!extensionData) {
    const matrixUrl = buildFilteredURL('/matrix', filters);
    return (
      <div className="page-container">
        <div className="extension-not-found">
          <div className="not-found-icon">🔍</div>
          <h2>Extension Not Found</h2>
          <p>The extension <strong>"{name}"</strong> was not found in the dataset.</p>
          <p className="not-found-hint">
            It may not exist, or it might be filtered out by your current filter settings.
          </p>
          <a href={matrixUrl} className="cta-button">
            ← Back to Matrix
          </a>
        </div>
      </div>
    );
  }

  const { extensionName, phpVersions, cells, metrics, groupedFailures } = extensionData;
  const matrixUrl = buildFilteredURL('/matrix', filters);

  return (
    <div className="page-container">
      <SchemaWarning />
      
      {/* Header */}
      <div className="page-header">
        <a href={matrixUrl} className="back-link">
          ← Back to Matrix
        </a>
        <h1>{extensionName}</h1>
        <p className="page-subtitle">
          Extension build status across {phpVersions.length} PHP version(s)
        </p>
      </div>

      {/* Metrics Cards */}
      <div className="stats-grid">
        <div className="stat-card">
          <div className="stat-number">{metrics.readiness.toFixed(1)}%</div>
          <div className="stat-label">Readiness</div>
        </div>
        <div className="stat-card">
          <div className="stat-number" style={{ color: 'var(--success)' }}>{metrics.successes}</div>
          <div className="stat-label">Successes</div>
        </div>
        <div className="stat-card">
          <div className="stat-number" style={{ color: 'var(--danger)' }}>{metrics.failures}</div>
          <div className="stat-label">Failures</div>
        </div>
        <div className="stat-card">
          <div className="stat-number" style={{ color: 'var(--gray-500)' }}>{metrics.skipped}</div>
          <div className="stat-label">Skipped</div>
        </div>
      </div>

      {/* Mini Grid */}
      <div className="extension-mini-grid">
        <h2>Build Status by PHP Version</h2>
        <div className="mini-grid-container">
          <div className="mini-grid-row">
            {cells.map((cell) => (
              <div key={cell.phpVersion} className="mini-grid-cell-wrapper">
                <div className="mini-grid-php-label">PHP {cell.phpVersion}</div>
                <div
                  className={`cell ${getCellClassName(cell)}`}
                  onClick={() => handleCellClick(cell.phpVersion)}
                  role="button"
                  tabIndex={0}
                  onKeyDown={(e) => {
                    if (e.key === 'Enter' || e.key === ' ') {
                      e.preventDefault();
                      handleCellClick(cell.phpVersion);
                    }
                  }}
                  title={getCellTooltip(extensionName, cell)}
                  aria-label={getCellAriaLabel(extensionName, cell)}
                />
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* Failing Combinations */}
      {Object.keys(groupedFailures).length > 0 && (
        <div className="failing-combinations">
          <h2>Failing Builds</h2>
          <p className="section-subtitle">
            Builds that failed for this extension with current filters
          </p>
          {Object.entries(groupedFailures).sort(([a], [b]) => a.localeCompare(b)).map(([phpVersion, combos]) => (
            <div key={phpVersion} className="failure-group">
              <h3>PHP {phpVersion}</h3>
              <div className="failure-list">
                {combos.map((combo, idx) => (
                  <div key={idx} className="failure-card">
                    <div className="failure-header">
                      <span className="failure-platform">
                        {combo.platform} {combo.platformVersion} ({combo.arch})
                      </span>
                      <span className="failure-channel">{combo.channel}</span>
                    </div>
                    {combo.category && (
                      <div className="failure-category">
                        <strong>Category:</strong> {combo.category}
                      </div>
                    )}
                    {(combo.runUrl || combo.artifactUrl) && (
                      <div className="failure-links">
                        {combo.runUrl && (
                          <a 
                            href={combo.runUrl} 
                            target="_blank" 
                            rel="noopener noreferrer"
                            className="failure-link"
                          >
                            View Run →
                          </a>
                        )}
                        {combo.artifactUrl && (
                          <a 
                            href={combo.artifactUrl} 
                            target="_blank" 
                            rel="noopener noreferrer"
                            className="failure-link"
                          >
                            Download Artifact →
                          </a>
                        )}
                      </div>
                    )}
                  </div>
                ))}
              </div>
            </div>
          ))}
        </div>
      )}

      {/* No failures message */}
      {Object.keys(groupedFailures).length === 0 && metrics.total > 0 && (
        <div className="no-failures-message">
          <div className="success-icon">✓</div>
          <h3>No Failures!</h3>
          <p>All builds for this extension are passing with current filters.</p>
        </div>
      )}

      {/* Cell Drawer */}
      {selectedCell && (
        <CellDrawer
          extensionName={extensionName}
          phpVersion={selectedCell.phpVersion}
          cell={selectedCell.cell}
          onClose={closeCellDrawer}
        />
      )}
    </div>
  );
}

function getCellClassName(cell: MatrixCell): string {
  switch (cell.status) {
    case 'success':
      return 'cell-success';
    case 'failure':
      return 'cell-failure';
    case 'skipped':
    case 'unknown':
      return 'cell-skipped';
    default:
      return '';
  }
}

function getCellTooltip(extension: string, cell: MatrixCell): string {
  const parts = [
    `${extension} - PHP ${cell.phpVersion}`,
    `Status: ${cell.status}`,
  ];
  
  if (cell.category) {
    parts.push(`Category: ${cell.category}`);
  }
  
  if (cell.extensionVersion) {
    parts.push(`Version: ${cell.extensionVersion}`);
  }
  
  return parts.join('\n');
}

function getCellAriaLabel(extension: string, cell: MatrixCell): string {
  const parts = [extension, `PHP ${cell.phpVersion}`, cell.status];
  if (cell.extensionVersion) {
    parts.push(`version ${cell.extensionVersion}`);
  }
  return parts.join(', ');
}
