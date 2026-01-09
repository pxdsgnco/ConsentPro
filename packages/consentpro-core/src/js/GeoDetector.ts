import type { BannerConfig } from './types';

/**
 * Geo detection from server-injected data attributes
 * Relies on Cloudflare CF-IPCountry header read by server
 * No external API calls - geo is determined server-side
 */
export class GeoDetector {
  private static readonly BANNER_SELECTOR = '#consentpro-banner';
  private static readonly CONFIG_ATTRIBUTE = 'data-config';

  /**
   * Parse config from DOM data-config attribute
   * @param selector - Optional custom selector for the banner element
   * @returns Partial config or null if not found/invalid
   */
  static parseConfigFromDOM(
    selector: string = GeoDetector.BANNER_SELECTOR
  ): Partial<BannerConfig> | null {
    const element = document.querySelector(selector);
    if (!element) {
      return null;
    }

    const configAttr = element.getAttribute(GeoDetector.CONFIG_ATTRIBUTE);
    if (!configAttr) {
      return null;
    }

    try {
      const config = JSON.parse(configAttr) as Partial<BannerConfig>;
      return config;
    } catch {
      // Invalid JSON - return null
      return null;
    }
  }

  /**
   * Get geo region from DOM data-config attribute
   * @param selector - Optional custom selector for the banner element
   * @returns 'EU', 'CA', or null
   */
  static getGeoFromDOM(selector: string = GeoDetector.BANNER_SELECTOR): 'EU' | 'CA' | null {
    const config = GeoDetector.parseConfigFromDOM(selector);
    if (!config) {
      return null;
    }
    return GeoDetector.normalizeGeo(config.geo);
  }

  /**
   * Normalize geo value to valid region or null
   */
  private static normalizeGeo(geo: string | null | undefined): 'EU' | 'CA' | null {
    if (geo === 'EU' || geo === 'CA') {
      return geo;
    }
    return null;
  }

  /**
   * Read geo region from config object
   */
  static getGeo(config: BannerConfig): 'EU' | 'CA' | null {
    return GeoDetector.normalizeGeo(config.geo);
  }

  /**
   * Determine if banner should be shown based on geo settings
   * @param config - Banner configuration with geo and geoEnabled
   * @returns true if banner should be shown
   */
  static shouldShowBanner(config: BannerConfig): boolean {
    // If geo-targeting disabled, always show
    if (!config.geoEnabled) {
      return true;
    }

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

  /**
   * Check if region requires GDPR compliance (EU)
   */
  static requiresGDPR(config: BannerConfig): boolean {
    return GeoDetector.isEU(config);
  }

  /**
   * Check if region requires CCPA compliance (California)
   */
  static requiresCCPA(config: BannerConfig): boolean {
    return GeoDetector.isCA(config);
  }
}
