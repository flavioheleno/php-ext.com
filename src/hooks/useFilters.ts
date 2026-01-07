import { useState, useEffect, useCallback } from 'react';
import { FilterState } from '../types';
import { getFiltersFromURL, setFiltersToURL } from '../utils/url';

const DEFAULT_FILTERS: FilterState = {
  channel: '',
  platform: '',
  platformVersion: '',
  arch: '',
  search: '',
  show: 'all',
  sort: 'name',
};

export function useFilters() {
  const [filters, setFiltersState] = useState<FilterState>(() => {
    return { ...DEFAULT_FILTERS, ...getFiltersFromURL() };
  });

  useEffect(() => {
    setFiltersToURL(filters);
  }, [filters]);

  const setFilters = useCallback((updates: Partial<FilterState>) => {
    setFiltersState(prev => {
      const newFilters = { ...prev, ...updates };
      
      // Clear platform_version if platform changes
      if (updates.platform !== undefined && updates.platform !== prev.platform) {
        newFilters.platformVersion = '';
      }
      
      return newFilters;
    });
  }, []);

  const resetFilters = useCallback(() => {
    setFiltersState(DEFAULT_FILTERS);
  }, []);

  const hasActiveFilters = useCallback(() => {
    return (
      filters.channel !== '' ||
      filters.platform !== '' ||
      filters.platformVersion !== '' ||
      filters.arch !== '' ||
      filters.search !== '' ||
      filters.show !== 'all' ||
      filters.sort !== 'name'
    );
  }, [filters]);

  return { filters, setFilters, resetFilters, hasActiveFilters };
}
