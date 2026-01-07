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
  };
}

export function setFiltersToURL(filters: FilterState): void {
  setQueryParams({
    channel: filters.channel,
    platform: filters.platform,
    pv: filters.platformVersion,
    arch: filters.arch,
    q: filters.search,
  });
}
