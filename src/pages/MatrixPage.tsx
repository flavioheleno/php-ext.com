import { useState, useMemo } from 'react';
import { useDatasetContext } from '../context/DatasetContext';
import { useFilters } from '../hooks/useFilters';
import { FilterBar } from '../components/FilterBar';
import { MatrixGrid } from '../components/MatrixGrid';
import { CellDrawer } from '../components/CellDrawer';
import { SchemaWarning } from '../components/SchemaWarning';
import {
  getUniqueValues,
  getUniquePHPVersions,
  filterExtensions,
  buildMatrixData,
  getExtensionDetails,
} from '../utils/data';
import { useNavigate } from 'react-router-dom';

export function MatrixPage() {
  const navigate = useNavigate();
  const { snapshot, loading, error } = useDatasetContext();
  const { filters, setFilters, resetFilters, hasActiveFilters } = useFilters();
  const [selectedCell, setSelectedCell] = useState<{ extension: string; phpVersion: string } | null>(null);

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
    navigate(`/ext/${name}`);
  };

  const closeCellDrawer = () => {
    setSelectedCell(null);
  };

  if (loading) {
    return (
      <div className="page-container">
        <div className="loading">
          <div className="spinner"></div>
          <p>Loading PHP extension data...</p>
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

  return (
    <div className="page-container">
      <SchemaWarning />
      
      <div className="page-header">
        <h1>Extension Matrix</h1>
        <p className="page-subtitle">
          {filteredExtensions.length} of {extensions.length} extensions
        </p>
      </div>

      <FilterBar
        filters={filters}
        setFilters={setFilters}
        resetFilters={resetFilters}
        channels={channels}
        platforms={platforms}
        platformVersions={platformVersions}
        arches={arches}
        hasActiveFilters={hasActiveFilters()}
      />

      <div className="matrix-container">
        <MatrixGrid
          rows={matrixData}
          phpVersions={phpVersions}
          onExtensionClick={handleExtensionClick}
          onCellClick={handleCellClick}
        />
      </div>

      {selectedCell && (
        <CellDrawer
          extensionName={selectedCell.extension}
          phpVersion={selectedCell.phpVersion}
          extension={getExtensionDetails(extensions, selectedCell.extension)}
          filters={filters}
          onClose={closeCellDrawer}
        />
      )}
    </div>
  );
}
