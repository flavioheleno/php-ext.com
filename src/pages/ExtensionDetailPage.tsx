import { useParams, Link, useNavigate } from 'react-router-dom';
import { useDatasetContext } from '../context/DatasetContext';
import { getExtensionDetails } from '../utils/data';
import { SchemaWarning } from '../components/SchemaWarning';

export function ExtensionDetailPage() {
  const { name } = useParams<{ name: string }>();
  const navigate = useNavigate();
  const { snapshot, loading, error } = useDatasetContext();

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

  const extensions = snapshot?.extensions || [];
  const extension = name ? getExtensionDetails(extensions, name) : undefined;

  if (!extension) {
    return (
      <div className="page-container">
        <div className="error">
          <h2>Extension Not Found</h2>
          <p>The extension "{name}" was not found in the dataset.</p>
          <Link to="/matrix" className="button">← Back to Matrix</Link>
        </div>
      </div>
    );
  }

  const groupedByPHP: Record<string, typeof extension.availability> = {};
  
  extension.availability.forEach(avail => {
    avail.php_versions.forEach(phpVer => {
      if (!groupedByPHP[phpVer]) {
        groupedByPHP[phpVer] = [];
      }
      groupedByPHP[phpVer].push(avail);
    });
  });

  const phpVersions = Object.keys(groupedByPHP).sort();

  return (
    <div className="page-container">
      <SchemaWarning />
      
      <div className="page-header">
        <button onClick={() => navigate(-1)} className="back-button">
          ← Back
        </button>
        <h1>{extension.name}</h1>
        <p className="page-subtitle">
          Available on {extension.availability.length} platform configuration(s) 
          across {phpVersions.length} PHP version(s)
        </p>
      </div>

      <div className="extension-detail-content">
        {phpVersions.map(phpVer => (
          <div key={phpVer} className="php-section">
            <h2>PHP {phpVer}</h2>
            <div className="availability-grid">
              {groupedByPHP[phpVer].map((avail, idx) => (
                <div key={idx} className="availability-card">
                  <div className="availability-badge">{avail.channel}</div>
                  <div><strong>Platform:</strong> {avail.platform}</div>
                  <div><strong>Version:</strong> {avail.platform_version}</div>
                  <div><strong>Architecture:</strong> {avail.arch}</div>
                </div>
              ))}
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}
