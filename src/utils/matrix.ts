import { BuildResult } from '../types';

export function compareSemver(a: string, b: string): number {
  const partsA = a.split('.').map(Number);
  const partsB = b.split('.').map(Number);
  
  for (let i = 0; i < Math.max(partsA.length, partsB.length); i++) {
    const numA = partsA[i] || 0;
    const numB = partsB[i] || 0;
    
    if (numA > numB) return 1;
    if (numA < numB) return -1;
  }
  
  return 0;
}

export function selectBestResult(results: BuildResult[], channel: string): BuildResult | null {
  if (results.length === 0) return null;
  if (results.length === 1) return results[0];

  if (channel === 'release' || channel === 'stable') {
    return results.reduce((best, current) => {
      const cmp = compareSemver(current.extension_version, best.extension_version);
      return cmp > 0 ? current : best;
    });
  }

  return results.reduce((best, current) => {
    if (!current.finished_at) return best;
    if (!best.finished_at) return current;
    
    return new Date(current.finished_at) > new Date(best.finished_at) ? current : best;
  });
}

export function matchesFilters(
  result: BuildResult,
  channel: string,
  platform: string,
  platformVersion: string,
  arch: string
): boolean {
  if (channel && result.channel !== channel) return false;
  if (platform && result.platform !== platform) return false;
  if (platformVersion && result.platform_version !== platformVersion) return false;
  if (arch && result.arch !== arch) return false;
  return true;
}
