import type { BannerConfig } from './types';

/**
 * Geo detection from server-injected data attributes
 * Relies on Cloudflare CF-IPCountry header read by server
 * No external API calls - geo is determined server-side
 */
export class GeoDetector {
  private static readonly BANNER_SELECTOR = '#consentpro-banner';
  private static readonly CONFIG_ATTRIBUTE = 'data-config';

  static parseConfigFromDOM(selector: string = GeoDetector.BANNER_SELECTOR): Partial<BannerConfig> | null {
    const el = document.querySelector(selector);
    const attr = el?.getAttribute(GeoDetector.CONFIG_ATTRIBUTE);
    if (!attr) return null;
    try { return JSON.parse(attr) as Partial<BannerConfig>; } catch { return null; }
  }

  static getGeoFromDOM(selector: string = GeoDetector.BANNER_SELECTOR): 'EU' | 'CA' | null {
    const config = GeoDetector.parseConfigFromDOM(selector);
    return config ? GeoDetector.normalizeGeo(config.geo) : null;
  }

  private static normalizeGeo(geo: string | null | undefined): 'EU' | 'CA' | null {
    return geo === 'EU' || geo === 'CA' ? geo : null;
  }

  static getGeo(config: BannerConfig): 'EU' | 'CA' | null {
    return GeoDetector.normalizeGeo(config.geo);
  }

  static shouldShowBanner(config: BannerConfig): boolean {
    return !config.geoEnabled || config.geo === 'EU' || config.geo === 'CA';
  }

  static isEU(config: BannerConfig): boolean {
    return config.geo === 'EU';
  }

  static isCA(config: BannerConfig): boolean {
    return config.geo === 'CA';
  }
}
