import { FilterState } from '../types';

interface FilterBarProps {
  filters: FilterState;
  setFilters: (updates: Partial<FilterState>) => void;
  resetFilters: () => void;
  channels: string[];
  platforms: string[];
  platformVersions: string[];
  arches: string[];
}

export function FilterBar({
  filters,
  setFilters,
  resetFilters,
  channels,
  platforms,
  platformVersions,
  arches,
}: FilterBarProps) {
  const hasActiveFilters = Object.values(filters).some(v => v !== '');

  return (
    <div className="filter-bar">
      <div className="filter-group">
        <input
          type="text"
          placeholder="Search extensions..."
          value={filters.search}
          onChange={(e) => setFilters({ search: e.target.value })}
          className="search-input"
        />
      </div>

      <div className="filter-group">
        <select
          value={filters.channel}
          onChange={(e) => setFilters({ channel: e.target.value })}
        >
          <option value="">All Channels</option>
          {channels.map(ch => (
            <option key={ch} value={ch}>{ch}</option>
          ))}
        </select>

        <select
          value={filters.platform}
          onChange={(e) => setFilters({ platform: e.target.value, platformVersion: '' })}
        >
          <option value="">All Platforms</option>
          {platforms.map(p => (
            <option key={p} value={p}>{p}</option>
          ))}
        </select>

        <select
          value={filters.platformVersion}
          onChange={(e) => setFilters({ platformVersion: e.target.value })}
          disabled={!filters.platform}
        >
          <option value="">All Versions</option>
          {platformVersions.map(pv => (
            <option key={pv} value={pv}>{pv}</option>
          ))}
        </select>

        <select
          value={filters.arch}
          onChange={(e) => setFilters({ arch: e.target.value })}
        >
          <option value="">All Architectures</option>
          {arches.map(a => (
            <option key={a} value={a}>{a}</option>
          ))}
        </select>

        {hasActiveFilters && (
          <button onClick={resetFilters} className="reset-button">
            Reset Filters
          </button>
        )}
      </div>
    </div>
  );
}
