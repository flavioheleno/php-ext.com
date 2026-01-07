import { Extension } from '../types';

interface ExtensionDetailProps {
  extension: Extension | undefined;
  onClose: () => void;
}

export function ExtensionDetail({ extension, onClose }: ExtensionDetailProps) {
  if (!extension) return null;

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
    <div className="drawer-overlay" onClick={onClose}>
      <div className="drawer drawer-large" onClick={(e) => e.stopPropagation()}>
        <div className="drawer-header">
          <h2>{extension.name}</h2>
          <button onClick={onClose} className="close-button">×</button>
        </div>
        <div className="drawer-content">
          <h3>Extension Details</h3>
          <p className="extension-summary">
            Available on {extension.availability.length} platform configuration(s) 
            across {phpVersions.length} PHP version(s)
          </p>

          {phpVersions.map(phpVer => (
            <div key={phpVer} className="php-section">
              <h4>PHP {phpVer}</h4>
              <div className="availability-grid">
                {groupedByPHP[phpVer].map((avail, idx) => (
                  <div key={idx} className="availability-card">
                    <div className="availability-badge">{avail.channel}</div>
                    <div><strong>Platform:</strong> {avail.platform}</div>
                    <div><strong>Version:</strong> {avail.platform_version}</div>
                    <div><strong>Arch:</strong> {avail.arch}</div>
                  </div>
                ))}
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}
