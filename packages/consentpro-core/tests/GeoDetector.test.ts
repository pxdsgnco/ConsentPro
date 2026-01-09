import { GeoDetector } from '../src/js/GeoDetector';
import type { BannerConfig } from '../src/js/types';

describe('GeoDetector', () => {
  // Helper to create a minimal valid config
  const createConfig = (overrides: Partial<BannerConfig> = {}): BannerConfig => ({
    geo: null,
    geoEnabled: true,
    policyUrl: 'https://example.com/privacy',
    categories: [],
    text: {
      heading: 'We value your privacy',
      description: 'We use cookies...',
      acceptAll: 'Accept All',
      rejectNonEssential: 'Reject Non-Essential',
      settings: 'Cookie Settings',
      save: 'Save Preferences',
      back: 'Back',
    },
    colors: {
      primary: '#2563eb',
      secondary: '#64748b',
      background: '#ffffff',
      text: '#1e293b',
    },
    ...overrides,
  });

  // Helper to setup DOM with banner element
  const setupDOM = (config: object | null, selector = '#consentpro-banner') => {
    document.body.innerHTML = '';
    const element = document.createElement('div');
    element.id = selector.replace('#', '');
    if (config !== null) {
      element.setAttribute('data-config', JSON.stringify(config));
    }
    document.body.appendChild(element);
    return element;
  };

  beforeEach(() => {
    document.body.innerHTML = '';
  });

  describe('parseConfigFromDOM', () => {
    it('returns null when element is not found', () => {
      const result = GeoDetector.parseConfigFromDOM('#non-existent');
      expect(result).toBeNull();
    });

    it('returns null when data-config attribute is missing', () => {
      const element = document.createElement('div');
      element.id = 'consentpro-banner';
      document.body.appendChild(element);

      const result = GeoDetector.parseConfigFromDOM();
      expect(result).toBeNull();
    });

    it('returns null for invalid JSON', () => {
      const element = document.createElement('div');
      element.id = 'consentpro-banner';
      element.setAttribute('data-config', '{invalid json}');
      document.body.appendChild(element);

      const result = GeoDetector.parseConfigFromDOM();
      expect(result).toBeNull();
    });

    it('returns parsed config for valid JSON', () => {
      const config = { geo: 'EU', geoEnabled: true };
      setupDOM(config);

      const result = GeoDetector.parseConfigFromDOM();
      expect(result).toEqual(config);
    });

    it('works with custom selector', () => {
      const config = { geo: 'CA', geoEnabled: true };
      const element = document.createElement('div');
      element.id = 'custom-banner';
      element.setAttribute('data-config', JSON.stringify(config));
      document.body.appendChild(element);

      const result = GeoDetector.parseConfigFromDOM('#custom-banner');
      expect(result).toEqual(config);
    });
  });

  describe('getGeoFromDOM', () => {
    it('returns null when element is not found', () => {
      const result = GeoDetector.getGeoFromDOM();
      expect(result).toBeNull();
    });

    it('returns EU when geo is EU', () => {
      setupDOM({ geo: 'EU' });
      const result = GeoDetector.getGeoFromDOM();
      expect(result).toBe('EU');
    });

    it('returns CA when geo is CA', () => {
      setupDOM({ geo: 'CA' });
      const result = GeoDetector.getGeoFromDOM();
      expect(result).toBe('CA');
    });

    it('returns null when geo is null', () => {
      setupDOM({ geo: null });
      const result = GeoDetector.getGeoFromDOM();
      expect(result).toBeNull();
    });

    it('returns null for invalid geo values', () => {
      setupDOM({ geo: 'US' });
      const result = GeoDetector.getGeoFromDOM();
      expect(result).toBeNull();
    });

    it('returns null when geo is undefined', () => {
      setupDOM({});
      const result = GeoDetector.getGeoFromDOM();
      expect(result).toBeNull();
    });
  });

  describe('getGeo', () => {
    it('returns EU for EU region', () => {
      const config = createConfig({ geo: 'EU' });
      expect(GeoDetector.getGeo(config)).toBe('EU');
    });

    it('returns CA for California', () => {
      const config = createConfig({ geo: 'CA' });
      expect(GeoDetector.getGeo(config)).toBe('CA');
    });

    it('returns null for null geo', () => {
      const config = createConfig({ geo: null });
      expect(GeoDetector.getGeo(config)).toBeNull();
    });
  });

  describe('shouldShowBanner', () => {
    describe('when geoEnabled is false', () => {
      it('returns true regardless of geo region', () => {
        expect(GeoDetector.shouldShowBanner(createConfig({ geoEnabled: false, geo: null }))).toBe(
          true
        );
        expect(GeoDetector.shouldShowBanner(createConfig({ geoEnabled: false, geo: 'EU' }))).toBe(
          true
        );
        expect(GeoDetector.shouldShowBanner(createConfig({ geoEnabled: false, geo: 'CA' }))).toBe(
          true
        );
      });
    });

    describe('when geoEnabled is true', () => {
      it('returns true for EU region', () => {
        const config = createConfig({ geoEnabled: true, geo: 'EU' });
        expect(GeoDetector.shouldShowBanner(config)).toBe(true);
      });

      it('returns true for CA region', () => {
        const config = createConfig({ geoEnabled: true, geo: 'CA' });
        expect(GeoDetector.shouldShowBanner(config)).toBe(true);
      });

      it('returns false for null geo (non-EU/CA)', () => {
        const config = createConfig({ geoEnabled: true, geo: null });
        expect(GeoDetector.shouldShowBanner(config)).toBe(false);
      });
    });
  });

  describe('isEU', () => {
    it('returns true for EU region', () => {
      const config = createConfig({ geo: 'EU' });
      expect(GeoDetector.isEU(config)).toBe(true);
    });

    it('returns false for CA region', () => {
      const config = createConfig({ geo: 'CA' });
      expect(GeoDetector.isEU(config)).toBe(false);
    });

    it('returns false for null geo', () => {
      const config = createConfig({ geo: null });
      expect(GeoDetector.isEU(config)).toBe(false);
    });
  });

  describe('isCA', () => {
    it('returns true for CA region', () => {
      const config = createConfig({ geo: 'CA' });
      expect(GeoDetector.isCA(config)).toBe(true);
    });

    it('returns false for EU region', () => {
      const config = createConfig({ geo: 'EU' });
      expect(GeoDetector.isCA(config)).toBe(false);
    });

    it('returns false for null geo', () => {
      const config = createConfig({ geo: null });
      expect(GeoDetector.isCA(config)).toBe(false);
    });
  });

  describe('requiresGDPR', () => {
    it('returns true for EU region', () => {
      const config = createConfig({ geo: 'EU' });
      expect(GeoDetector.requiresGDPR(config)).toBe(true);
    });

    it('returns false for CA region', () => {
      const config = createConfig({ geo: 'CA' });
      expect(GeoDetector.requiresGDPR(config)).toBe(false);
    });

    it('returns false for null geo', () => {
      const config = createConfig({ geo: null });
      expect(GeoDetector.requiresGDPR(config)).toBe(false);
    });
  });

  describe('requiresCCPA', () => {
    it('returns true for CA region', () => {
      const config = createConfig({ geo: 'CA' });
      expect(GeoDetector.requiresCCPA(config)).toBe(true);
    });

    it('returns false for EU region', () => {
      const config = createConfig({ geo: 'EU' });
      expect(GeoDetector.requiresCCPA(config)).toBe(false);
    });

    it('returns false for null geo', () => {
      const config = createConfig({ geo: null });
      expect(GeoDetector.requiresCCPA(config)).toBe(false);
    });
  });

  describe('edge cases', () => {
    it('handles empty string geo as null', () => {
      setupDOM({ geo: '' });
      expect(GeoDetector.getGeoFromDOM()).toBeNull();
    });

    it('handles numeric geo as null', () => {
      setupDOM({ geo: 123 });
      expect(GeoDetector.getGeoFromDOM()).toBeNull();
    });

    it('handles lowercase region codes as null', () => {
      setupDOM({ geo: 'eu' });
      expect(GeoDetector.getGeoFromDOM()).toBeNull();
    });

    it('handles partial config in DOM', () => {
      setupDOM({ geo: 'EU' });
      const config = GeoDetector.parseConfigFromDOM();
      expect(config).toEqual({ geo: 'EU' });
    });
  });
});
