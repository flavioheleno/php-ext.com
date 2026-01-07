import { CSSProperties, memo } from 'react';
import { FixedSizeList as List } from 'react-window';
import { ExtensionRow } from '../types';

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

const Row = memo(({ index, style, data }: RowProps) => {
  const { rows, phpVersions, onExtensionClick, onCellClick } = data;
  const row = rows[index];

  return (
    <div style={style} className="matrix-row">
      <div 
        className="extension-name"
        onClick={() => onExtensionClick(row.name)}
        role="button"
        tabIndex={0}
      >
        {row.name}
      </div>
      <div className="cells">
        {row.cells.map((cell, i) => (
          <div
            key={phpVersions[i]}
            className={`cell ${cell.available ? 'available' : 'unavailable'}`}
            onClick={() => onCellClick(row.name, cell.phpVersion)}
            role="button"
            tabIndex={0}
            title={`${row.name} - PHP ${cell.phpVersion}: ${cell.available ? 'Available' : 'Not Available'}`}
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
            <div key={version} className="cell-header">
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
