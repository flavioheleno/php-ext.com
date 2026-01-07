import { FilterState, ShowFilter, SortOption } from '../types';

interface FilterBarProps {
  filters: FilterState;
  setFilters: (updates: Partial<FilterState>) => void;
  resetFilters: () => void;
  channels: string[];
  platforms: string[];
  platformVersions: string[];
  arches: string[];
  hasActiveFilters?: boolean;
}

const SHOW_OPTIONS: { value: ShowFilter; label: string }[] = [
  { value: 'all', label: 'All Extensions' },
  { value: 'failing', label: 'Failing Only' },
  { value: 'regressions', label: 'Regressions' },
];

const SORT_OPTIONS: { value: SortOption; label: string }[] = [
  { value: 'name', label: 'Name' },
  { value: 'readiness', label: 'Readiness' },
  { value: 'failures', label: 'Failures' },
];

export function FilterBar({
  filters,
  setFilters,
  resetFilters,
  channels,
  platforms,
  platformVersions,
  arches,
  hasActiveFilters = false,
}: FilterBarProps) {
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
          onChange={(e) => setFilters({ platform: e.target.value })}
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
      </div>

      <div className="filter-group">
        <label className="filter-label">Show:</label>
        <select
          value={filters.show}
          onChange={(e) => setFilters({ show: e.target.value as ShowFilter })}
          className="filter-select-inline"
        >
          {SHOW_OPTIONS.map(opt => (
            <option key={opt.value} value={opt.value}>{opt.label}</option>
          ))}
        </select>

        <label className="filter-label">Sort:</label>
        <select
          value={filters.sort}
          onChange={(e) => setFilters({ sort: e.target.value as SortOption })}
          className="filter-select-inline"
        >
          {SORT_OPTIONS.map(opt => (
            <option key={opt.value} value={opt.value}>{opt.label}</option>
          ))}
        </select>

        {hasActiveFilters && (
          <button onClick={resetFilters} className="reset-button">
            Reset All Filters
          </button>
        )}
      </div>
    </div>
  );
}
