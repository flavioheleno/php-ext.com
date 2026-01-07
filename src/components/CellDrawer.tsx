import { useState, useEffect } from 'react';
import { MatrixCell } from '../types';

interface CellDrawerProps {
  extensionName: string;
  phpVersion: string;
  cell: MatrixCell | null;
  onClose: () => void;
}

function formatDuration(startedAt?: string, finishedAt?: string): string | null {
  if (!startedAt || !finishedAt) return null;
  
  const start = new Date(startedAt).getTime();
  const finish = new Date(finishedAt).getTime();
  const durationMs = finish - start;
  
  if (durationMs < 0) return null;
  
  const seconds = Math.floor(durationMs / 1000);
  const minutes = Math.floor(seconds / 60);
  const hours = Math.floor(minutes / 60);
  
  if (hours > 0) {
    return `${hours}h ${minutes % 60}m`;
  } else if (minutes > 0) {
    return `${minutes}m ${seconds % 60}s`;
  } else {
    return `${seconds}s`;
  }
}

function formatTimestamp(timestamp?: string): string {
  if (!timestamp) return 'N/A';
  const date = new Date(timestamp);
  return date.toLocaleString();
}

function getStatusColor(status: string): string {
  switch (status) {
    case 'success':
      return 'var(--success)';
    case 'failure':
      return 'var(--danger)';
    case 'skipped':
    case 'unknown':
      return 'var(--gray-500)';
    default:
      return 'var(--gray-400)';
  }
}

function copyToClipboard(text: string): Promise<boolean> {
  if (navigator.clipboard && window.isSecureContext) {
    return navigator.clipboard.writeText(text).then(() => true).catch(() => false);
  } else {
    // Fallback for older browsers
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    try {
      document.execCommand('copy');
      textArea.remove();
      return Promise.resolve(true);
    } catch (error) {
      textArea.remove();
      return Promise.resolve(false);
    }
  }
}

export function CellDrawer({ extensionName, phpVersion, cell, onClose }: CellDrawerProps) {
  const [copySuccess, setCopySuccess] = useState(false);

  useEffect(() => {
    const handleEscape = (e: KeyboardEvent) => {
      if (e.key === 'Escape') {
        onClose();
      }
    };
    
    window.addEventListener('keydown', handleEscape);
    return () => window.removeEventListener('keydown', handleEscape);
  }, [onClose]);

  if (!cell) {
    return (
      <div className="drawer-overlay" onClick={onClose}>
        <div className="drawer drawer-right" onClick={(e) => e.stopPropagation()}>
          <div className="drawer-header">
            <h2>{extensionName} - PHP {phpVersion}</h2>
            <button onClick={onClose} className="close-button" aria-label="Close drawer">×</button>
          </div>
          <div className="drawer-content">
            <p className="no-data-message">No build data available for this configuration.</p>
          </div>
        </div>
      </div>
    );
  }

  const duration = formatDuration(cell.startedAt, cell.finishedAt);
  
  const handleCopyPermalink = async () => {
    const url = new URL(window.location.href);
    url.hash = `${extensionName}-${phpVersion}`;
    const success = await copyToClipboard(url.toString());
    if (success) {
      setCopySuccess(true);
      setTimeout(() => setCopySuccess(false), 2000);
    }
  };

  return (
    <div className="drawer-overlay" onClick={onClose}>
      <div className="drawer drawer-right" onClick={(e) => e.stopPropagation()}>
        <div className="drawer-header">
          <h2>{extensionName} - PHP {phpVersion}</h2>
          <button onClick={onClose} className="close-button" aria-label="Close drawer">×</button>
        </div>
        <div className="drawer-content">
          {/* Extension info */}
          <div className="detail-section">
            <h3>Extension</h3>
            <div className="detail-grid">
              <div className="detail-item">
                <span className="detail-label">Name</span>
                <span className="detail-value">{extensionName}</span>
              </div>
              {cell.extensionVersion && (
                <div className="detail-item">
                  <span className="detail-label">Version</span>
                  <span className="detail-value">{cell.extensionVersion}</span>
                </div>
              )}
            </div>
          </div>

          {/* Build status */}
          <div className="detail-section">
            <h3>Build Status</h3>
            <div className="detail-grid">
              <div className="detail-item">
                <span className="detail-label">Status</span>
                <span 
                  className="detail-value status-badge" 
                  style={{ backgroundColor: getStatusColor(cell.status), color: 'white' }}
                >
                  {cell.status}
                </span>
              </div>
              {cell.category && (
                <div className="detail-item">
                  <span className="detail-label">Category</span>
                  <span className="detail-value">{cell.category}</span>
                </div>
              )}
            </div>
          </div>

          {/* Timestamps */}
          {(cell.startedAt || cell.finishedAt) && (
            <div className="detail-section">
              <h3>Timing</h3>
              <div className="detail-grid">
                {cell.startedAt && (
                  <div className="detail-item">
                    <span className="detail-label">Started</span>
                    <span className="detail-value">{formatTimestamp(cell.startedAt)}</span>
                  </div>
                )}
                {cell.finishedAt && (
                  <div className="detail-item">
                    <span className="detail-label">Finished</span>
                    <span className="detail-value">{formatTimestamp(cell.finishedAt)}</span>
                  </div>
                )}
                {duration && (
                  <div className="detail-item">
                    <span className="detail-label">Duration</span>
                    <span className="detail-value">{duration}</span>
                  </div>
                )}
              </div>
            </div>
          )}

          {/* Links */}
          {(cell.runUrl || cell.artifactUrl) && (
            <div className="detail-section">
              <h3>Links</h3>
              <div className="detail-links">
                {cell.runUrl && (
                  <a 
                    href={cell.runUrl} 
                    target="_blank" 
                    rel="noopener noreferrer"
                    className="detail-link"
                  >
                    <span className="link-icon">🔗</span>
                    View Workflow Run
                  </a>
                )}
                {cell.artifactUrl && (
                  <a 
                    href={cell.artifactUrl} 
                    target="_blank" 
                    rel="noopener noreferrer"
                    className="detail-link"
                  >
                    <span className="link-icon">📦</span>
                    Download Artifact
                  </a>
                )}
              </div>
            </div>
          )}

          {/* Copy permalink */}
          <div className="detail-section">
            <button 
              onClick={handleCopyPermalink}
              className="copy-permalink-button"
              aria-label="Copy permalink to this cell"
            >
              {copySuccess ? '✓ Copied!' : '🔗 Copy Permalink'}
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}
