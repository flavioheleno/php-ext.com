import { Extension, FilterState } from '../types';

interface CellDrawerProps {
  extensionName: string;
  phpVersion: string;
  extension: Extension | undefined;
  filters: FilterState;
  onClose: () => void;
}

export function CellDrawer({ extensionName, phpVersion, extension, filters, onClose }: CellDrawerProps) {
  if (!extension) return null;

  const relevantAvailability = extension.availability.filter(avail => {
    const matchesFilters = 
      (!filters.channel || avail.channel === filters.channel) &&
      (!filters.platform || avail.platform === filters.platform) &&
      (!filters.platformVersion || avail.platform_version === filters.platformVersion) &&
      (!filters.arch || avail.arch === filters.arch);
    
    return matchesFilters && avail.php_versions.includes(phpVersion);
  });

  return (
    <div className="drawer-overlay" onClick={onClose}>
      <div className="drawer" onClick={(e) => e.stopPropagation()}>
        <div className="drawer-header">
          <h2>{extensionName} - PHP {phpVersion}</h2>
          <button onClick={onClose} className="close-button">×</button>
        </div>
        <div className="drawer-content">
          {relevantAvailability.length === 0 ? (
            <p>Not available with current filters for PHP {phpVersion}</p>
          ) : (
            <div className="availability-list">
              <h3>Available on:</h3>
              {relevantAvailability.map((avail, idx) => (
                <div key={idx} className="availability-item">
                  <div><strong>Channel:</strong> {avail.channel}</div>
                  <div><strong>Platform:</strong> {avail.platform}</div>
                  <div><strong>Version:</strong> {avail.platform_version}</div>
                  <div><strong>Architecture:</strong> {avail.arch}</div>
                  <div><strong>PHP Versions:</strong> {avail.php_versions.join(', ')}</div>
                </div>
              ))}
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
