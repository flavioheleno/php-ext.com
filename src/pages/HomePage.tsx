import { Link } from 'react-router-dom';
import { useDatasetContext } from '../context/DatasetContext';
import { getUniqueValues, getUniquePHPVersions } from '../utils/data';
import { SchemaWarning } from '../components/SchemaWarning';

export function HomePage() {
  const { snapshot, loading, error } = useDatasetContext();

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

  const extensions = snapshot?.extensions || [];
  const channels = getUniqueValues(extensions, 'channel');
  const platforms = getUniqueValues(extensions, 'platform');
  const arches = getUniqueValues(extensions, 'arch');
  const phpVersions = getUniquePHPVersions(extensions);

  return (
    <div className="page-container">
      <SchemaWarning />
      
      <div className="home-hero">
        <h1>PHP Extension Matrix</h1>
        <p className="hero-subtitle">
          Comprehensive view of PHP extension availability across platforms, channels, and architectures
        </p>
        <Link to="/matrix" className="cta-button">
          View Matrix →
        </Link>
      </div>

      <div className="stats-grid">
        <div className="stat-card">
          <div className="stat-number">{extensions.length}</div>
          <div className="stat-label">Extensions</div>
        </div>
        <div className="stat-card">
          <div className="stat-number">{channels.length}</div>
          <div className="stat-label">Channels</div>
        </div>
        <div className="stat-card">
          <div className="stat-number">{platforms.length}</div>
          <div className="stat-label">Platforms</div>
        </div>
        <div className="stat-card">
          <div className="stat-number">{arches.length}</div>
          <div className="stat-label">Architectures</div>
        </div>
        <div className="stat-card">
          <div className="stat-number">{phpVersions.length}</div>
          <div className="stat-label">PHP Versions</div>
        </div>
      </div>

      <div className="info-section">
        <h2>About</h2>
        <p>
          This matrix shows the availability of PHP extensions across different platforms, 
          platform versions, architectures, and PHP versions. Use the filters to narrow down 
          to your specific environment.
        </p>
        
        <div className="feature-list">
          <div className="feature-item">
            <strong>🔍 Advanced Filters</strong>
            <p>Filter by channel, platform, platform version, and architecture</p>
          </div>
          <div className="feature-item">
            <strong>🔎 Search</strong>
            <p>Quick search across all extensions</p>
          </div>
          <div className="feature-item">
            <strong>📊 Matrix View</strong>
            <p>See all extensions and PHP version compatibility at a glance</p>
          </div>
          <div className="feature-item">
            <strong>🔗 Shareable URLs</strong>
            <p>Share filtered views via URL parameters</p>
          </div>
        </div>

        {snapshot?.timestamp && (
          <p className="data-timestamp">
            Data updated: {new Date(snapshot.timestamp).toLocaleString()}
          </p>
        )}
      </div>
    </div>
  );
}
