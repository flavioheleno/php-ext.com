import { useState } from 'react';
import { useDatasetContext } from '../context/DatasetContext';
import { useFilters } from '../hooks/useFilters';
import { useMatrixModel } from '../hooks/useMatrixModel';
import { validateMatrixModel } from '../hooks/useMatrixModel';
import { FilterBar } from '../components/FilterBar';
import { MatrixGrid } from '../components/MatrixGrid';
import { CellDrawer } from '../components/CellDrawer';
import { SchemaWarning } from '../components/SchemaWarning';
import { getUniqueValuesFromResults } from '../utils/data';
import { useNavigate } from 'react-router-dom';
import { MatrixCell } from '../types';

export function MatrixPage() {
  const navigate = useNavigate();
  const { snapshot, manifest, loading, error } = useDatasetContext();
  const { filters, setFilters, resetFilters, hasActiveFilters } = useFilters();
  const [selectedCell, setSelectedCell] = useState<{ extension: string; phpVersion: string; cell: MatrixCell | null } | null>(null);

  const matrixModel = useMatrixModel(snapshot, manifest, filters);

  // Validate matrix model in development
  if (import.meta.env.DEV && matrixModel.rows.length > 0) {
    validateMatrixModel(matrixModel);
  }

  const results = snapshot?.results || [];

  // Get filter options from results if available, otherwise from extensions
  const channels = results.length > 0 
    ? getUniqueValuesFromResults(results, 'channel')
    : (manifest?.axes?.channels || []);
  
  const platforms = results.length > 0
    ? getUniqueValuesFromResults(results, 'platform')
    : (manifest?.axes?.platforms || []);
  
  const arches = results.length > 0
    ? getUniqueValuesFromResults(results, 'arch')
    : (manifest?.axes?.arches || []);

  // Get platform versions based on selected platform
  const platformVersions = results.length > 0
    ? getUniqueValuesFromResults(
        results.filter(r => !filters.platform || r.platform === filters.platform),
        'platform_version'
      )
    : [];

  const handleCellClick = (extensionName: string, phpVersion: string) => {
    // Find the cell data from the matrix model
    const row = matrixModel.rows.find(r => r.extension === extensionName);
    const cell = row?.cells.find(c => c.phpVersion === phpVersion) || null;
    
    setSelectedCell({ extension: extensionName, phpVersion, cell });
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
          {matrixModel.rows.length} extensions • {matrixModel.totals.averageReadiness.toFixed(1)}% average readiness
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

      <div className="matrix-stats">
        <div className="matrix-stat">
          <span className="stat-value">{matrixModel.totals.totalSuccesses}</span>
          <span className="stat-label">Successes</span>
        </div>
        <div className="matrix-stat">
          <span className="stat-value">{matrixModel.totals.totalFailures}</span>
          <span className="stat-label">Failures</span>
        </div>
        <div className="matrix-stat">
          <span className="stat-value">{matrixModel.totals.totalSkipped}</span>
          <span className="stat-label">Skipped</span>
        </div>
      </div>

      <div className="matrix-container">
        <MatrixGrid
          rows={matrixModel.rows}
          phpVersions={matrixModel.columns}
          onExtensionClick={handleExtensionClick}
          onCellClick={handleCellClick}
        />
      </div>

      {selectedCell && (
        <CellDrawer
          extensionName={selectedCell.extension}
          phpVersion={selectedCell.phpVersion}
          cell={selectedCell.cell}
          onClose={closeCellDrawer}
        />
      )}
    </div>
  );
}
