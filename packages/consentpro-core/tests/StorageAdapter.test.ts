import { StorageAdapter } from '../src/js/StorageAdapter';
import type { ConsentData } from '../src/js/types';

describe('StorageAdapter', () => {
  let adapter: StorageAdapter;

  // Helper to create valid consent data
  const createConsentData = (overrides: Partial<ConsentData> = {}): ConsentData => ({
    version: 1,
    timestamp: Date.now(),
    geo: null,
    categories: {
      essential: true,
      analytics: true,
      marketing: false,
      personalization: false,
    },
    hash: 'abc123',
    ...overrides,
  });

  // Helper to get cookie value
  const getCookie = (name: string): string | null => {
    const match = document.cookie.match(new RegExp(`(^| )${name}=([^;]+)`));
    return match ? match[2] : null;
  };

  beforeEach(() => {
    adapter = new StorageAdapter();
    localStorage.clear();
    document.cookie = 'consentpro=; max-age=0; path=/;';
  });

  afterEach(() => {
    localStorage.clear();
    document.cookie = 'consentpro=; max-age=0; path=/;';
  });

  describe('set()', () => {
    it('should write consent data to localStorage', () => {
      const data = createConsentData();
      adapter.set(data);

      const stored = localStorage.getItem('consentpro_consent');
      expect(stored).not.toBeNull();
      expect(JSON.parse(stored!)).toEqual(data);
    });

    it('should write consent data to cookie for Safari ITP fallback', () => {
      const data = createConsentData();
      adapter.set(data);

      const cookieValue = getCookie('consentpro');
      expect(cookieValue).not.toBeNull();
      expect(JSON.parse(decodeURIComponent(cookieValue!))).toEqual(data);
    });

    it('should URI-encode cookie value', () => {
      const data = createConsentData({ hash: 'hash=with=equals&special' });
      adapter.set(data);

      const rawCookie = document.cookie;
      // The cookie should contain encoded characters
      expect(rawCookie).toContain('consentpro=');
      // Verify it can be decoded back
      const cookieValue = getCookie('consentpro');
      expect(JSON.parse(decodeURIComponent(cookieValue!))).toEqual(data);
    });

    it('should handle localStorage quota exceeded gracefully', () => {
      // Mock localStorage.setItem to throw
      const originalSetItem = localStorage.setItem;
      localStorage.setItem = jest.fn(() => {
        throw new DOMException('QuotaExceededError');
      });

      const data = createConsentData();

      // Should not throw
      expect(() => adapter.set(data)).not.toThrow();

      // Cookie should still be set
      const cookieValue = getCookie('consentpro');
      expect(cookieValue).not.toBeNull();
      expect(JSON.parse(decodeURIComponent(cookieValue!))).toEqual(data);

      localStorage.setItem = originalSetItem;
    });

    it('should set cookie with SameSite=Lax attribute', () => {
      const data = createConsentData();

      // Spy on document.cookie setter
      let setCookieValue = '';
      const originalDescriptor = Object.getOwnPropertyDescriptor(Document.prototype, 'cookie');
      Object.defineProperty(document, 'cookie', {
        get: () => setCookieValue,
        set: (val: string) => {
          setCookieValue = val;
        },
        configurable: true,
      });

      adapter.set(data);

      expect(setCookieValue).toContain('SameSite=Lax');
      expect(setCookieValue).toContain('path=/');
      expect(setCookieValue).toContain('max-age=31536000');

      // Restore original
      if (originalDescriptor) {
        Object.defineProperty(document, 'cookie', originalDescriptor);
      }
    });

    it('should store all consent data fields correctly', () => {
      const data = createConsentData({
        version: 2,
        timestamp: 1699999999999,
        geo: 'EU',
        categories: {
          essential: true,
          analytics: false,
          marketing: true,
          personalization: true,
        },
        hash: 'xyz789',
      });

      adapter.set(data);

      const stored = localStorage.getItem('consentpro_consent');
      const parsed = JSON.parse(stored!);

      expect(parsed.version).toBe(2);
      expect(parsed.timestamp).toBe(1699999999999);
      expect(parsed.geo).toBe('EU');
      expect(parsed.categories.essential).toBe(true);
      expect(parsed.categories.analytics).toBe(false);
      expect(parsed.categories.marketing).toBe(true);
      expect(parsed.categories.personalization).toBe(true);
      expect(parsed.hash).toBe('xyz789');
    });

    it('should handle CA geo region', () => {
      const data = createConsentData({ geo: 'CA' });
      adapter.set(data);

      const stored = adapter.get();
      expect(stored?.geo).toBe('CA');
    });
  });

  describe('get()', () => {
    it('should read valid JSON from localStorage', () => {
      const data = createConsentData();
      localStorage.setItem('consentpro_consent', JSON.stringify(data));

      const result = adapter.get();
      expect(result).toEqual(data);
    });

    it('should return null when localStorage is empty', () => {
      expect(adapter.get()).toBeNull();
    });

    it('should fall back to cookie when localStorage is empty', () => {
      const data = createConsentData();
      document.cookie = `consentpro=${encodeURIComponent(JSON.stringify(data))}; path=/`;

      const result = adapter.get();
      expect(result).toEqual(data);
    });

    it('should prioritize localStorage over cookie', () => {
      const localData = createConsentData({ hash: 'local' });
      const cookieData = createConsentData({ hash: 'cookie' });

      localStorage.setItem('consentpro_consent', JSON.stringify(localData));
      document.cookie = `consentpro=${encodeURIComponent(JSON.stringify(cookieData))}; path=/`;

      const result = adapter.get();
      expect(result?.hash).toBe('local');
    });

    it('should handle invalid JSON in localStorage gracefully', () => {
      localStorage.setItem('consentpro_consent', 'not valid json{{{');

      // Should not throw and return null (or cookie fallback)
      expect(() => adapter.get()).not.toThrow();
      expect(adapter.get()).toBeNull();
    });

    it('should fall back to cookie when localStorage has invalid JSON', () => {
      const cookieData = createConsentData({ hash: 'cookie-fallback' });
      localStorage.setItem('consentpro_consent', 'invalid json');
      document.cookie = `consentpro=${encodeURIComponent(JSON.stringify(cookieData))}; path=/`;

      const result = adapter.get();
      expect(result?.hash).toBe('cookie-fallback');
    });

    it('should handle invalid JSON in cookie gracefully', () => {
      document.cookie = 'consentpro=invalid%20json; path=/';

      expect(() => adapter.get()).not.toThrow();
      expect(adapter.get()).toBeNull();
    });

    it('should handle URI-encoded cookie values', () => {
      const data = createConsentData({ hash: 'special=chars&more' });
      document.cookie = `consentpro=${encodeURIComponent(JSON.stringify(data))}; path=/`;

      const result = adapter.get();
      expect(result?.hash).toBe('special=chars&more');
    });

    it('should handle localStorage access throwing (e.g., private mode)', () => {
      const originalGetItem = localStorage.getItem;
      localStorage.getItem = jest.fn(() => {
        throw new DOMException('SecurityError');
      });

      const data = createConsentData();
      document.cookie = `consentpro=${encodeURIComponent(JSON.stringify(data))}; path=/`;

      // Should fall back to cookie without throwing
      expect(() => adapter.get()).not.toThrow();
      const result = adapter.get();
      expect(result).toEqual(data);

      localStorage.getItem = originalGetItem;
    });

    it('should return null when both localStorage and cookie are empty', () => {
      expect(adapter.get()).toBeNull();
    });

    it('should return null when both localStorage and cookie have invalid data', () => {
      localStorage.setItem('consentpro_consent', '{invalid');
      document.cookie = 'consentpro=%7Binvalid; path=/';

      expect(adapter.get()).toBeNull();
    });
  });

  describe('clear()', () => {
    it('should remove localStorage item', () => {
      const data = createConsentData();
      adapter.set(data);

      expect(localStorage.getItem('consentpro_consent')).not.toBeNull();

      adapter.clear();

      expect(localStorage.getItem('consentpro_consent')).toBeNull();
    });

    it('should clear cookie with max-age=0', () => {
      const data = createConsentData();
      adapter.set(data);

      expect(getCookie('consentpro')).not.toBeNull();

      adapter.clear();

      expect(getCookie('consentpro')).toBeNull();
    });

    it('should clear both localStorage and cookie', () => {
      const data = createConsentData();
      adapter.set(data);

      adapter.clear();

      expect(localStorage.getItem('consentpro_consent')).toBeNull();
      expect(getCookie('consentpro')).toBeNull();
      expect(adapter.get()).toBeNull();
    });

    it('should handle localStorage.removeItem throwing', () => {
      const data = createConsentData();
      adapter.set(data);

      const originalRemoveItem = localStorage.removeItem;
      localStorage.removeItem = jest.fn(() => {
        throw new DOMException('SecurityError');
      });

      // Should not throw
      expect(() => adapter.clear()).not.toThrow();

      // Cookie should still be cleared
      expect(getCookie('consentpro')).toBeNull();

      localStorage.removeItem = originalRemoveItem;
    });

    it('should work when no data exists', () => {
      // Should not throw even when nothing to clear
      expect(() => adapter.clear()).not.toThrow();
    });
  });

  describe('_getCookie() (private method, tested via get())', () => {
    it('should parse cookie from document.cookie string', () => {
      const data = createConsentData();
      document.cookie = `consentpro=${encodeURIComponent(JSON.stringify(data))}; path=/`;

      const result = adapter.get();
      expect(result).toEqual(data);
    });

    it('should handle multiple cookies', () => {
      const data = createConsentData({ hash: 'target' });
      document.cookie = 'other=value; path=/';
      document.cookie = `consentpro=${encodeURIComponent(JSON.stringify(data))}; path=/`;
      document.cookie = 'another=test; path=/';

      const result = adapter.get();
      expect(result?.hash).toBe('target');
    });

    it('should not match partial cookie names', () => {
      document.cookie = 'consentpro_extra=fake; path=/';
      document.cookie = 'myconsentpro=fake; path=/';

      // Only match exact 'consentpro' cookie
      expect(adapter.get()).toBeNull();
    });

    it('should match cookie at start of string', () => {
      const data = createConsentData({ hash: 'first' });
      // Clear all cookies first
      document.cookie = 'consentpro=; max-age=0; path=/';
      document.cookie = `consentpro=${encodeURIComponent(JSON.stringify(data))}; path=/`;

      const result = adapter.get();
      expect(result?.hash).toBe('first');
    });
  });

  describe('Safari ITP fallback scenarios', () => {
    it('should work when localStorage is completely unavailable', () => {
      const data = createConsentData();

      // Simulate localStorage unavailable
      const originalSetItem = localStorage.setItem;
      const originalGetItem = localStorage.getItem;
      const originalRemoveItem = localStorage.removeItem;

      localStorage.setItem = jest.fn(() => {
        throw new DOMException('SecurityError');
      });
      localStorage.getItem = jest.fn(() => {
        throw new DOMException('SecurityError');
      });
      localStorage.removeItem = jest.fn(() => {
        throw new DOMException('SecurityError');
      });

      // Set should still work via cookie
      expect(() => adapter.set(data)).not.toThrow();

      // Get should read from cookie
      const result = adapter.get();
      expect(result).toEqual(data);

      // Clear should still work
      expect(() => adapter.clear()).not.toThrow();
      expect(adapter.get()).toBeNull();

      // Restore
      localStorage.setItem = originalSetItem;
      localStorage.getItem = originalGetItem;
      localStorage.removeItem = originalRemoveItem;
    });

    it('should read from cookie when localStorage value is cleared by ITP', () => {
      const data = createConsentData();

      // Initially set both
      adapter.set(data);

      // Simulate ITP clearing localStorage but keeping cookie
      localStorage.removeItem('consentpro_consent');

      const result = adapter.get();
      expect(result).toEqual(data);
    });
  });

  describe('edge cases', () => {
    it('should handle consent data with null geo', () => {
      const data = createConsentData({ geo: null });
      adapter.set(data);

      const result = adapter.get();
      expect(result?.geo).toBeNull();
    });

    it('should handle very large consent data', () => {
      const data = createConsentData({
        hash: 'a'.repeat(1000),
      });

      adapter.set(data);

      const result = adapter.get();
      expect(result?.hash).toBe('a'.repeat(1000));
    });

    it('should handle consent data with special characters in hash', () => {
      const data = createConsentData({
        hash: '<script>alert("xss")</script>&param=value',
      });

      adapter.set(data);

      const result = adapter.get();
      expect(result?.hash).toBe('<script>alert("xss")</script>&param=value');
    });

    it('should handle round-trip of all category combinations', () => {
      const combinations = [
        { analytics: true, marketing: true, personalization: true },
        { analytics: true, marketing: true, personalization: false },
        { analytics: true, marketing: false, personalization: true },
        { analytics: true, marketing: false, personalization: false },
        { analytics: false, marketing: true, personalization: true },
        { analytics: false, marketing: true, personalization: false },
        { analytics: false, marketing: false, personalization: true },
        { analytics: false, marketing: false, personalization: false },
      ];

      for (const cats of combinations) {
        const data = createConsentData({
          categories: { essential: true, ...cats },
        });

        adapter.set(data);
        const result = adapter.get();

        expect(result?.categories.essential).toBe(true);
        expect(result?.categories.analytics).toBe(cats.analytics);
        expect(result?.categories.marketing).toBe(cats.marketing);
        expect(result?.categories.personalization).toBe(cats.personalization);

        adapter.clear();
      }
    });

    it('should handle timestamp at epoch', () => {
      const data = createConsentData({ timestamp: 0 });
      adapter.set(data);

      const result = adapter.get();
      expect(result?.timestamp).toBe(0);
    });

    it('should handle very large timestamp', () => {
      const farFuture = Date.now() + 100 * 365 * 24 * 60 * 60 * 1000; // 100 years
      const data = createConsentData({ timestamp: farFuture });
      adapter.set(data);

      const result = adapter.get();
      expect(result?.timestamp).toBe(farFuture);
    });
  });

  describe('concurrency and consistency', () => {
    it('should handle multiple rapid set operations', () => {
      for (let i = 0; i < 100; i++) {
        const data = createConsentData({ hash: `hash-${i}` });
        adapter.set(data);
      }

      const result = adapter.get();
      expect(result?.hash).toBe('hash-99');
    });

    it('should maintain consistency between localStorage and cookie', () => {
      const data = createConsentData();
      adapter.set(data);

      const fromLocal = JSON.parse(localStorage.getItem('consentpro_consent')!);
      const fromCookie = JSON.parse(decodeURIComponent(getCookie('consentpro')!));

      expect(fromLocal).toEqual(fromCookie);
    });
  });
});
