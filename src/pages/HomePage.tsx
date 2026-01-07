import { useDatasetContext } from '../context/DatasetContext';
import { useFilters } from '../hooks/useFilters';
import { useMatrixModel } from '../hooks/useMatrixModel';
import { buildFilteredURL } from '../utils/url';
import { SchemaWarning } from '../components/SchemaWarning';
import { useMemo } from 'react';

interface FailingExtension {
  name: string;
  failures: number;
  total: number;
  readiness: number;
}

export function HomePage() {
  const { snapshot, manifest, loading, error } = useDatasetContext();
  const { filters } = useFilters();

  // Use matrix model to compute overall stats
  const matrixModel = useMatrixModel(snapshot, manifest, filters);

  // Get top 10 failing extensions
  const topFailingExtensions = useMemo((): FailingExtension[] => {
    return matrixModel.rows
      .filter(row => row.metrics.failures > 0)
      .map(row => ({
        name: row.extension,
        failures: row.metrics.failures,
        total: row.metrics.total,
        readiness: row.metrics.readiness,
      }))
      .sort((a, b) => b.failures - a.failures)
      .slice(0, 10);
  }, [matrixModel]);

  if (loading) {
    return (
      <div className="page-container">
        <div className="loading">
          <div className="spinner"></div>
          <p>Loading data...</p>
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

  const matrixUrl = buildFilteredURL('/matrix', filters);
  const hasFilters = filters.channel || filters.platform || filters.platformVersion || 
                     filters.arch || filters.search || 
                     (filters.show && filters.show !== 'all') || 
                     (filters.sort && filters.sort !== 'name');

  // Dataset status info
  const generatedAt = snapshot?.generated_at || snapshot?.timestamp || manifest?.generated_at;
  const publisher = snapshot?.publisher || manifest?.publisher;

  return (
    <div className="page-container">
      <SchemaWarning />
      
      {/* Hero Section */}
      <div className="home-hero">
        <h1>PHP Extension Matrix</h1>
        <p className="hero-subtitle">
          Track PHP extension build status across platforms, architectures, and PHP versions
        </p>
        <a href={matrixUrl} className="cta-button">
          Open Matrix →
        </a>
      </div>

      {/* Dataset Status */}
      <div className="dataset-status">
        <h2>Dataset Status</h2>
        <div className="status-grid">
          {generatedAt && (
            <div className="status-item">
              <span className="status-label">Generated</span>
              <span className="status-value">
                {new Date(generatedAt).toLocaleString()}
              </span>
            </div>
          )}
          {publisher?.repo && (
            <div className="status-item">
              <span className="status-label">Publisher</span>
              <span className="status-value">
                <a 
                  href={`https://github.com/${publisher.repo}`}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="publisher-link"
                >
                  {publisher.repo}
                </a>
              </span>
            </div>
          )}
          {publisher?.commit && (
            <div className="status-item">
              <span className="status-label">Commit</span>
              <span className="status-value">
                {publisher.repo ? (
                  <a 
                    href={`https://github.com/${publisher.repo}/commit/${publisher.commit}`}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="commit-link"
                  >
                    {publisher.commit.substring(0, 7)}
                  </a>
                ) : (
                  <code>{publisher.commit.substring(0, 7)}</code>
                )}
              </span>
            </div>
          )}
          <div className="status-item">
            <span className="status-label">Schema Version</span>
            <span className="status-value">{snapshot?.schema_version || 'N/A'}</span>
          </div>
        </div>
      </div>

      {/* Overall Stats */}
      <div className="overall-stats">
        <h2>
          Overall Readiness
          {hasFilters && <span className="filter-indicator"> (filtered)</span>}
        </h2>
        <div className="stats-grid">
          <div className="stat-card stat-card-primary">
            <div className="stat-number">{matrixModel.totals.averageReadiness.toFixed(1)}%</div>
            <div className="stat-label">Average Readiness</div>
          </div>
          <div className="stat-card">
            <div className="stat-number">{matrixModel.totals.totalExtensions}</div>
            <div className="stat-label">Extensions</div>
          </div>
          <div className="stat-card">
            <div className="stat-number" style={{ color: 'var(--success)' }}>
              {matrixModel.totals.totalSuccesses}
            </div>
            <div className="stat-label">Successes</div>
          </div>
          <div className="stat-card">
            <div className="stat-number" style={{ color: 'var(--danger)' }}>
              {matrixModel.totals.totalFailures}
            </div>
            <div className="stat-label">Failures</div>
          </div>
          <div className="stat-card">
            <div className="stat-number" style={{ color: 'var(--gray-500)' }}>
              {matrixModel.totals.totalSkipped}
            </div>
            <div className="stat-label">Skipped</div>
          </div>
          <div className="stat-card">
            <div className="stat-number">{matrixModel.totals.totalCells}</div>
            <div className="stat-label">Total Builds</div>
          </div>
        </div>
      </div>

      {/* Top Failing Extensions */}
      {topFailingExtensions.length > 0 && (
        <div className="top-failing">
          <h2>
            Top Failing Extensions
            {hasFilters && <span className="filter-indicator"> (filtered)</span>}
          </h2>
          <p className="section-subtitle">
            Extensions with the most build failures
          </p>
          <div className="failing-extensions-list">
            {topFailingExtensions.map((ext, index) => {
              const extUrl = buildFilteredURL(`/ext/${ext.name}`, filters);
              return (
                <a key={ext.name} href={extUrl} className="failing-extension-card">
                  <div className="failing-rank">#{index + 1}</div>
                  <div className="failing-info">
                    <div className="failing-name">{ext.name}</div>
                    <div className="failing-stats">
                      <span className="failing-count">{ext.failures} failures</span>
                      <span className="failing-separator">•</span>
                      <span className="failing-readiness">{ext.readiness.toFixed(1)}% ready</span>
                    </div>
                  </div>
                  <div className="failing-progress">
                    <div 
                      className="failing-progress-bar"
                      style={{ width: `${ext.readiness}%` }}
                    />
                  </div>
                  <div className="failing-arrow">→</div>
                </a>
              );
            })}
          </div>
        </div>
      )}

      {/* No Failures Celebration */}
      {topFailingExtensions.length === 0 && matrixModel.totals.totalExtensions > 0 && (
        <div className="no-failures-home">
          <div className="success-icon-large">✓</div>
          <h2>All Builds Passing!</h2>
          <p>
            All {matrixModel.totals.totalExtensions} extensions are building successfully
            {hasFilters ? ' with current filters' : ''}.
          </p>
          <a href={matrixUrl} className="cta-button">
            View Matrix →
          </a>
        </div>
      )}

      {/* Quick Start / About */}
      <div className="info-section">
        <h2>Quick Start</h2>
        <div className="feature-list">
          <div className="feature-item">
            <strong>🔍 Filter & Search</strong>
            <p>Use filters to narrow down by platform, architecture, PHP version, and channel</p>
          </div>
          <div className="feature-item">
            <strong>📊 View Matrix</strong>
            <p>See all extensions and their build status across PHP versions at a glance</p>
          </div>
          <div className="feature-item">
            <strong>📝 Extension Details</strong>
            <p>Click any extension name to see detailed build information and failure reasons</p>
          </div>
          <div className="feature-item">
            <strong>🔗 Share Links</strong>
            <p>All filters are preserved in the URL for easy sharing and bookmarking</p>
          </div>
        </div>
      </div>
    </div>
  );
}
