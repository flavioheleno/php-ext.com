import { FilterState } from '../types';

interface FilterSummaryProps {
  filters: FilterState;
  extensionCount: number;
  totalCount: number;
}

export function FilterSummary({ filters, extensionCount, totalCount }: FilterSummaryProps) {
  const activeFilters: string[] = [];

  if (filters.channel) activeFilters.push(`Channel: ${filters.channel}`);
  if (filters.platform) activeFilters.push(`Platform: ${filters.platform}`);
  if (filters.platformVersion) activeFilters.push(`Version: ${filters.platformVersion}`);
  if (filters.arch) activeFilters.push(`Arch: ${filters.arch}`);
  if (filters.search) activeFilters.push(`Search: "${filters.search}"`);
  if (filters.show !== 'all') activeFilters.push(`Show: ${filters.show}`);
  if (filters.sort !== 'name') activeFilters.push(`Sort: ${filters.sort}`);

  if (activeFilters.length === 0) {
    return null;
  }

  return (
    <div className="filter-summary">
      <div className="filter-summary-header">
        <strong>Active Filters</strong>
        <span className="filter-count">
          {extensionCount} of {totalCount} extensions
        </span>
      </div>
      <div className="filter-tags">
        {activeFilters.map((filter, index) => (
          <span key={index} className="filter-tag">
            {filter}
          </span>
        ))}
      </div>
    </div>
  );
}
