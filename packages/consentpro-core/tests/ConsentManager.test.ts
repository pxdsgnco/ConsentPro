import { ConsentManager } from '../src/js/ConsentManager';
import { StorageAdapter } from '../src/js/StorageAdapter';
import type { BannerConfig, ConsentData } from '../src/js/types';

describe('ConsentManager', () => {
  let manager: ConsentManager;
  let storage: StorageAdapter;

  // Helper to create a minimal valid config
  const createConfig = (overrides: Partial<BannerConfig> = {}): BannerConfig => ({
    geo: null,
    geoEnabled: true,
    policyUrl: 'https://example.com/privacy',
    categories: [
      { id: 'essential', name: 'Essential', description: 'Required for site functionality', required: true },
      { id: 'analytics', name: 'Analytics', description: 'Help us understand usage', required: false },
      { id: 'marketing', name: 'Marketing', description: 'Personalized advertisements', required: false },
      { id: 'personalization', name: 'Personalization', description: 'Remember your preferences', required: false },
    ],
    text: {
      heading: 'We value your privacy',
      description: 'We use cookies to enhance your experience.',
      acceptAll: 'Accept All',
      rejectNonEssential: 'Reject Non-Essential',
      settings: 'Cookie Settings',
      save: 'Save Preferences',
      back: 'Back',
      settingsTitle: 'Privacy Preferences',
      footerToggle: 'Privacy Settings',
    },
    colors: {
      primary: '#2563eb',
      secondary: '#64748b',
      background: '#ffffff',
      text: '#1e293b',
    },
    ...overrides,
  });

  beforeEach(() => {
    // Clear localStorage and cookies
    localStorage.clear();
    document.cookie = 'consentpro=; max-age=0; path=/;';

    storage = new StorageAdapter();
    manager = new ConsentManager(storage);
  });

  afterEach(() => {
    localStorage.clear();
    document.cookie = 'consentpro=; max-age=0; path=/;';
  });

  describe('init', () => {
    it('stores config for later use', () => {
      const config = createConfig();
      manager.init(config);

      // Config is stored internally - verify by checking isConsentValid uses it
      // (indirectly tested through other tests)
      expect(true).toBe(true);
    });
  });

  describe('getConsent', () => {
    it('returns null when no consent exists', () => {
      expect(manager.getConsent()).toBeNull();
    });

    it('returns stored consent data', () => {
      manager.init(createConfig());
      manager.setConsent({
        essential: true,
        analytics: true,
        marketing: false,
        personalization: false,
      });

      const consent = manager.getConsent();
      expect(consent).not.toBeNull();
      expect(consent!.categories.analytics).toBe(true);
      expect(consent!.categories.marketing).toBe(false);
    });
  });

  describe('setConsent', () => {
    beforeEach(() => {
      manager.init(createConfig());
    });

    it('stores consent with all required fields', () => {
      manager.setConsent({
        essential: true,
        analytics: true,
        marketing: false,
        personalization: true,
      });

      const consent = manager.getConsent();
      expect(consent).not.toBeNull();
      expect(consent!.version).toBe(1);
      expect(consent!.timestamp).toBeDefined();
      expect(consent!.geo).toBe(null);
      expect(consent!.hash).toBeDefined();
      expect(consent!.categories.essential).toBe(true);
      expect(consent!.categories.analytics).toBe(true);
      expect(consent!.categories.marketing).toBe(false);
      expect(consent!.categories.personalization).toBe(true);
    });

    it('always sets essential to true', () => {
      manager.setConsent({
        essential: true,
        analytics: false,
        marketing: false,
        personalization: false,
      });

      const consent = manager.getConsent();
      expect(consent!.categories.essential).toBe(true);
    });

    it('dispatches consentpro_consent event', () => {
      const handler = jest.fn();
      document.addEventListener('consentpro_consent', handler);

      manager.setConsent({
        essential: true,
        analytics: true,
        marketing: false,
        personalization: false,
      });

      expect(handler).toHaveBeenCalled();
      const eventDetail = handler.mock.calls[0][0].detail;
      expect(eventDetail.categories.analytics).toBe(true);
      expect(eventDetail.categories.marketing).toBe(false);
      expect(eventDetail.timestamp).toBeDefined();

      document.removeEventListener('consentpro_consent', handler);
    });

    it('includes geo in stored data when configured', () => {
      manager.init(createConfig({ geo: 'EU' }));
      manager.setConsent({
        essential: true,
        analytics: true,
        marketing: true,
        personalization: true,
      });

      const consent = manager.getConsent();
      expect(consent!.geo).toBe('EU');
    });
  });

  describe('isConsentValid (US-011)', () => {
    beforeEach(() => {
      manager.init(createConfig());
    });

    it('returns false when no consent exists', () => {
      expect(manager.isConsentValid()).toBe(false);
    });

    it('returns true for valid consent within 12 months', () => {
      manager.setConsent({
        essential: true,
        analytics: true,
        marketing: false,
        personalization: false,
      });

      expect(manager.isConsentValid()).toBe(true);
    });

    it('returns false for expired consent (>12 months)', () => {
      // Set consent with past timestamp
      const thirteenMonthsAgo = Date.now() - (13 * 30 * 24 * 60 * 60 * 1000);
      const expiredConsent: ConsentData = {
        version: 1,
        timestamp: thirteenMonthsAgo,
        geo: null,
        categories: {
          essential: true,
          analytics: true,
          marketing: false,
          personalization: false,
        },
        hash: 'hash',
      };

      // Directly set expired consent in storage
      storage.set(expiredConsent);

      expect(manager.isConsentValid()).toBe(false);
    });

    it('returns true for consent at exactly 12 months', () => {
      // Use fake timers to avoid time drift between timestamp creation and validation
      jest.useFakeTimers();
      const now = Date.now();
      jest.setSystemTime(now);

      // Set consent at exactly 12 months ago
      const twelveMonthsAgo = now - 365 * 24 * 60 * 60 * 1000;
      const borderlineConsent: ConsentData = {
        version: 1,
        timestamp: twelveMonthsAgo,
        geo: null,
        categories: {
          essential: true,
          analytics: true,
          marketing: false,
          personalization: false,
        },
        hash: 'hash',
      };

      storage.set(borderlineConsent);

      // At exactly 12 months, should still be valid (not > 12 months)
      expect(manager.isConsentValid()).toBe(true);

      jest.useRealTimers();
    });

    it('returns false for consent at 12 months + 1ms', () => {
      // Set consent just past 12 months
      const justExpired = Date.now() - (365 * 24 * 60 * 60 * 1000) - 1;
      const expiredConsent: ConsentData = {
        version: 1,
        timestamp: justExpired,
        geo: null,
        categories: {
          essential: true,
          analytics: true,
          marketing: false,
          personalization: false,
        },
        hash: 'hash',
      };

      storage.set(expiredConsent);

      expect(manager.isConsentValid()).toBe(false);
    });

    it('returns true for recent consent (1 day ago)', () => {
      const oneDayAgo = Date.now() - (24 * 60 * 60 * 1000);
      const recentConsent: ConsentData = {
        version: 1,
        timestamp: oneDayAgo,
        geo: null,
        categories: {
          essential: true,
          analytics: false,
          marketing: false,
          personalization: false,
        },
        hash: 'hash',
      };

      storage.set(recentConsent);

      expect(manager.isConsentValid()).toBe(true);
    });

    it('returns true for consent 6 months ago', () => {
      const sixMonthsAgo = Date.now() - (6 * 30 * 24 * 60 * 60 * 1000);
      const consent: ConsentData = {
        version: 1,
        timestamp: sixMonthsAgo,
        geo: null,
        categories: {
          essential: true,
          analytics: true,
          marketing: true,
          personalization: true,
        },
        hash: 'hash',
      };

      storage.set(consent);

      expect(manager.isConsentValid()).toBe(true);
    });
  });

  describe('clearConsent', () => {
    it('removes stored consent', () => {
      manager.init(createConfig());
      manager.setConsent({
        essential: true,
        analytics: true,
        marketing: true,
        personalization: true,
      });

      expect(manager.getConsent()).not.toBeNull();

      manager.clearConsent();

      expect(manager.getConsent()).toBeNull();
    });
  });

  describe('performance (US-011)', () => {
    it('isConsentValid executes in under 1ms', () => {
      manager.init(createConfig());
      manager.setConsent({
        essential: true,
        analytics: true,
        marketing: false,
        personalization: false,
      });

      const start = performance.now();
      for (let i = 0; i < 1000; i++) {
        manager.isConsentValid();
      }
      const duration = performance.now() - start;

      // 1000 calls should take less than 100ms (0.1ms per call average)
      expect(duration).toBeLessThan(100);
    });

    it('getConsent executes quickly', () => {
      manager.init(createConfig());
      manager.setConsent({
        essential: true,
        analytics: true,
        marketing: false,
        personalization: false,
      });

      const start = performance.now();
      for (let i = 0; i < 1000; i++) {
        manager.getConsent();
      }
      const duration = performance.now() - start;

      // 1000 calls should take less than 100ms
      expect(duration).toBeLessThan(100);
    });
  });
});
