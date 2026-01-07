import { FilterState } from '../types';

export function getQueryParams(): URLSearchParams {
  return new URLSearchParams(window.location.search);
}

export function setQueryParams(params: Record<string, string>): void {
  const url = new URL(window.location.href);
  Object.entries(params).forEach(([key, value]) => {
    if (value) {
      url.searchParams.set(key, value);
    } else {
      url.searchParams.delete(key);
    }
  });
  window.history.replaceState({}, '', url.toString());
}

export function getFiltersFromURL(): Partial<FilterState> {
  const params = getQueryParams();
  return {
    channel: params.get('channel') || undefined,
    platform: params.get('platform') || undefined,
    platformVersion: params.get('pv') || undefined,
    arch: params.get('arch') || undefined,
    search: params.get('q') || undefined,
    show: (params.get('show') as FilterState['show']) || undefined,
    sort: (params.get('sort') as FilterState['sort']) || undefined,
  };
}

export function setFiltersToURL(filters: FilterState): void {
  const params: Record<string, string> = {};
  
  if (filters.channel) params.channel = filters.channel;
  if (filters.platform) params.platform = filters.platform;
  if (filters.platformVersion) params.pv = filters.platformVersion;
  if (filters.arch) params.arch = filters.arch;
  if (filters.search) params.q = filters.search;
  if (filters.show && filters.show !== 'all') params.show = filters.show;
  if (filters.sort && filters.sort !== 'name') params.sort = filters.sort;
  
  setQueryParams(params);
}

export function buildFilteredURL(path: string, filters: FilterState): string {
  const params = new URLSearchParams();
  
  if (filters.channel) params.set('channel', filters.channel);
  if (filters.platform) params.set('platform', filters.platform);
  if (filters.platformVersion) params.set('pv', filters.platformVersion);
  if (filters.arch) params.set('arch', filters.arch);
  if (filters.search) params.set('q', filters.search);
  if (filters.show && filters.show !== 'all') params.set('show', filters.show);
  if (filters.sort && filters.sort !== 'name') params.set('sort', filters.sort);
  
  const paramStr = params.toString();
  return `${path}${paramStr ? `?${paramStr}` : ''}`;
}
