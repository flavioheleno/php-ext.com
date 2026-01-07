import { SchemaStatus } from '../types';
import { useDatasetContext } from '../context/DatasetContext';

export function SchemaWarning() {
  const { schemaStatus } = useDatasetContext();

  if (schemaStatus === SchemaStatus.VALID) {
    return null;
  }

  return (
    <div className="schema-warning">
      <div className="warning-icon">⚠️</div>
      <div className="warning-content">
        <strong>Schema Version Mismatch</strong>
        <p>
          The dataset schema version does not match the expected version (1.0). 
          Some features may not work correctly.
        </p>
      </div>
    </div>
  );
}
