import { useState, useMemo } from 'react';
import { useDataset } from './hooks/useDataset';
import { useFilters } from './hooks/useFilters';
import { FilterBar } from './components/FilterBar';
import { MatrixGrid } from './components/MatrixGrid';
import { CellDrawer } from './components/CellDrawer';
import { ExtensionDetail } from './components/ExtensionDetail';
import {
  getUniqueValues,
  getUniquePHPVersions,
  filterExtensions,
  buildMatrixData,
  getExtensionDetails,
} from './utils/data';

function App() {
  const { snapshot, loading, error } = useDataset();
  const { filters, setFilters, resetFilters } = useFilters();
  const [selectedCell, setSelectedCell] = useState<{ extension: string; phpVersion: string } | null>(null);
  const [selectedExtension, setSelectedExtension] = useState<string | null>(null);

  const extensions = snapshot?.extensions || [];

  const { channels, platforms, platformVersions, arches, phpVersions } = useMemo(() => {
    const filtered = filters.platform
      ? extensions.filter(ext =>
          ext.availability.some(a => a.platform === filters.platform)
        )
      : extensions;

    return {
      channels: getUniqueValues(extensions, 'channel'),
      platforms: getUniqueValues(extensions, 'platform'),
      platformVersions: getUniqueValues(filtered, 'platform_version'),
      arches: getUniqueValues(extensions, 'arch'),
      phpVersions: getUniquePHPVersions(extensions),
    };
  }, [extensions, filters.platform]);

  const filteredExtensions = useMemo(
    () => filterExtensions(extensions, filters),
    [extensions, filters]
  );

  const matrixData = useMemo(
    () => buildMatrixData(filteredExtensions, phpVersions, filters),
    [filteredExtensions, phpVersions, filters]
  );

  const handleCellClick = (extensionName: string, phpVersion: string) => {
    setSelectedCell({ extension: extensionName, phpVersion });
  };

  const handleExtensionClick = (name: string) => {
    setSelectedExtension(name);
  };

  const closeCellDrawer = () => {
    setSelectedCell(null);
  };

  const closeExtensionDetail = () => {
    setSelectedExtension(null);
  };

  if (loading) {
    return (
      <div className="app">
        <div className="loading">
          <div className="spinner"></div>
          <p>Loading PHP extension data...</p>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="app">
        <div className="error">
          <h2>Error Loading Data</h2>
          <p>{error}</p>
          <p className="error-hint">
            Make sure the dataset URL is correct and accessible.
          </p>
        </div>
      </div>
    );
  }

  return (
    <div className="app">
      <header className="header">
        <h1>PHP Extension Matrix</h1>
        <p className="subtitle">
          {filteredExtensions.length} of {extensions.length} extensions
          {snapshot?.timestamp && (
            <span className="timestamp"> • Updated {new Date(snapshot.timestamp).toLocaleDateString()}</span>
          )}
        </p>
      </header>

      <FilterBar
        filters={filters}
        setFilters={setFilters}
        resetFilters={resetFilters}
        channels={channels}
        platforms={platforms}
        platformVersions={platformVersions}
        arches={arches}
      />

      <main className="main">
        <MatrixGrid
          rows={matrixData}
          phpVersions={phpVersions}
          onExtensionClick={handleExtensionClick}
          onCellClick={handleCellClick}
        />
      </main>

      {selectedCell && (
        <CellDrawer
          extensionName={selectedCell.extension}
          phpVersion={selectedCell.phpVersion}
          extension={getExtensionDetails(extensions, selectedCell.extension)}
          filters={filters}
          onClose={closeCellDrawer}
        />
      )}

      {selectedExtension && (
        <ExtensionDetail
          extension={getExtensionDetails(extensions, selectedExtension)}
          onClose={closeExtensionDetail}
        />
      )}
    </div>
  );
}

export default App;
