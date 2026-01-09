import type { BannerConfig } from './types';

/**
 * Geo detection from server-injected data attributes
 * Relies on Cloudflare CF-IPCountry header read by server
 */
export class GeoDetector {
  /**
   * Read geo region from config
   */
  static getGeo(config: BannerConfig): 'EU' | 'CA' | null {
    return config.geo;
  }

  /**
   * Determine if banner should be shown based on geo settings
   */
  static shouldShowBanner(config: BannerConfig): boolean {
    // If geo-targeting disabled, always show
    if (!config.geoEnabled) return true;

    // If geo-targeting enabled, show only for EU/CA
    return config.geo === 'EU' || config.geo === 'CA';
  }

  /**
   * Check if region is EU
   */
  static isEU(config: BannerConfig): boolean {
    return config.geo === 'EU';
  }

  /**
   * Check if region is California
   */
  static isCA(config: BannerConfig): boolean {
    return config.geo === 'CA';
  }
}
