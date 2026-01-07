import { useState, useEffect, useCallback } from 'react';
import { FilterState } from '../types';
import { getFiltersFromURL, setFiltersToURL } from '../utils/url';

const DEFAULT_FILTERS: FilterState = {
  channel: '',
  platform: '',
  platformVersion: '',
  arch: '',
  search: '',
};

export function useFilters() {
  const [filters, setFiltersState] = useState<FilterState>(() => {
    return { ...DEFAULT_FILTERS, ...getFiltersFromURL() };
  });

  useEffect(() => {
    setFiltersToURL(filters);
  }, [filters]);

  const setFilters = useCallback((updates: Partial<FilterState>) => {
    setFiltersState(prev => ({ ...prev, ...updates }));
  }, []);

  const resetFilters = useCallback(() => {
    setFiltersState(DEFAULT_FILTERS);
  }, []);

  return { filters, setFilters, resetFilters };
}
