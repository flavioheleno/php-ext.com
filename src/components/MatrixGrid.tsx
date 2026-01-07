import { CSSProperties, memo } from 'react';
import { FixedSizeList as List } from 'react-window';
import { ExtensionRow, MatrixCell } from '../types';

interface MatrixGridProps {
  rows: ExtensionRow[];
  phpVersions: string[];
  onExtensionClick: (name: string) => void;
  onCellClick: (extensionName: string, phpVersion: string) => void;
}

interface RowProps {
  index: number;
  style: CSSProperties;
  data: {
    rows: ExtensionRow[];
    phpVersions: string[];
    onExtensionClick: (name: string) => void;
    onCellClick: (extensionName: string, phpVersion: string) => void;
  };
}

function getCellClassName(cell: MatrixCell): string {
  const baseClass = 'cell';
  switch (cell.status) {
    case 'success':
      return `${baseClass} cell-success`;
    case 'failure':
      return `${baseClass} cell-failure`;
    case 'skipped':
    case 'unknown':
      return `${baseClass} cell-skipped`;
    default:
      return baseClass;
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

const Row = memo(({ index, style, data }: RowProps) => {
  const { rows, phpVersions, onExtensionClick, onCellClick } = data;
  const row = rows[index];

  return (
    <div style={style} className="matrix-row">
      <div 
        className="extension-name"
        onClick={() => onExtensionClick(row.extension)}
        role="button"
        tabIndex={0}
        onKeyDown={(e) => {
          if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            onExtensionClick(row.extension);
          }
        }}
        aria-label={`View details for ${row.extension}`}
      >
        {row.extension}
      </div>
      <div className="cells">
        {row.cells.map((cell, i) => (
          <div
            key={phpVersions[i]}
            className={getCellClassName(cell)}
            onClick={() => onCellClick(row.extension, cell.phpVersion)}
            role="button"
            tabIndex={0}
            onKeyDown={(e) => {
              if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                onCellClick(row.extension, cell.phpVersion);
              }
            }}
            title={getCellTooltip(row.extension, cell)}
            aria-label={getCellAriaLabel(row.extension, cell)}
          />
        ))}
      </div>
    </div>
  );
});

Row.displayName = 'Row';

export function MatrixGrid({ rows, phpVersions, onExtensionClick, onCellClick }: MatrixGridProps) {
  if (rows.length === 0) {
    return (
      <div className="empty-state">
        <p>No extensions match the current filters.</p>
      </div>
    );
  }

  return (
    <div className="matrix-grid">
      <div className="matrix-header">
        <div className="extension-name-header">Extension</div>
        <div className="cells">
          {phpVersions.map(version => (
            <div key={version} className="cell-header" title={`PHP ${version}`}>
              PHP {version}
            </div>
          ))}
        </div>
      </div>
      <List
        height={600}
        itemCount={rows.length}
        itemSize={40}
        width="100%"
        itemData={{ rows, phpVersions, onExtensionClick, onCellClick }}
      >
        {Row}
      </List>
    </div>
  );
}
