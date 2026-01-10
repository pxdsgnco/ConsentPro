import { ScriptBlocker } from '../src/js/ScriptBlocker';
import type { ConsentCategories, ConsentEventDetail } from '../src/js/types';

// Extend Window interface for test globals
declare global {
  interface Window {
    __testAnalyticsLoaded?: boolean;
    __testMarketingLoaded?: boolean;
    __testEssentialLoaded?: boolean;
    __testGA4Loaded?: boolean;
    __testMetaPixelLoaded?: boolean;
    __dynamicScript?: boolean;
    __executionCount?: number;
    __order?: number[];
    dataLayer?: unknown[];
    gtag?: (...args: unknown[]) => void;
    testGtag?: (...args: unknown[]) => void;
    fbq?: (...args: unknown[]) => void;
    testFbq?: () => string;
  }
}

describe('ScriptBlocker', () => {
  let blocker: ScriptBlocker;

  // Helper to create consent categories
  const createConsent = (
    overrides: Partial<ConsentCategories> = {}
  ): ConsentCategories => ({
    essential: true,
    analytics: false,
    marketing: false,
    personalization: false,
    ...overrides,
  });

  // Helper to create a blocked script element
  const createBlockedScript = (
    category: string,
    options: { src?: string; content?: string; name?: string } = {}
  ): HTMLScriptElement => {
    const script = document.createElement('script');
    script.type = 'text/plain';
    script.dataset.consentpro = category;
    if (options.src) {
      script.src = options.src;
    }
    if (options.content) {
      script.textContent = options.content;
    }
    if (options.name) {
      script.dataset.scriptName = options.name;
    }
    document.body.appendChild(script);
    return script;
  };

  // Helper to dispatch consent event
  const dispatchConsentEvent = (categories: ConsentCategories): void => {
    const detail: ConsentEventDetail = {
      categories,
      timestamp: Date.now(),
      geo: null,
    };
    document.dispatchEvent(new CustomEvent('consentpro_consent', { detail }));
  };

  beforeEach(() => {
    // Clear DOM
    document.body.innerHTML = '';
    document.head.innerHTML = '';

    // Reset window test properties (use undefined instead of delete for function properties)
    window.__testAnalyticsLoaded = undefined;
    window.__testMarketingLoaded = undefined;
    window.__testEssentialLoaded = undefined;
    window.__testGA4Loaded = undefined;
    window.__testMetaPixelLoaded = undefined;
    window.__dynamicScript = undefined;
    window.__executionCount = undefined;
    window.__order = undefined;
    window.dataLayer = undefined;
    window.gtag = undefined;
    window.testGtag = undefined;
    window.fbq = undefined;
    window.testFbq = undefined;

    blocker = new ScriptBlocker();
  });

  afterEach(() => {
    blocker.destroy();
    document.body.innerHTML = '';
  });

  describe('US-012a: Script Blocking on Page Load', () => {
    describe('scripts stay inert without consent', () => {
      it('should not execute scripts with type="text/plain"', () => {
        window.__testAnalyticsLoaded = false;
        createBlockedScript('analytics', {
          content: 'window.__testAnalyticsLoaded = true;',
        });

        blocker.init(createConsent({ analytics: false }));

        expect(window.__testAnalyticsLoaded).toBe(false);
      });

      it('should keep blocked scripts in DOM when not consented', () => {
        createBlockedScript('marketing', {
          content: 'console.log("marketing");',
        });

        blocker.init(createConsent({ marketing: false }));

        const scripts = document.querySelectorAll(
          'script[data-consentpro="marketing"]'
        );
        expect(scripts.length).toBe(1);
        expect((scripts[0] as HTMLScriptElement).type).toBe('text/plain');
      });

      it('should not execute any non-consented category scripts', () => {
        window.__testAnalyticsLoaded = false;
        window.__testMarketingLoaded = false;

        createBlockedScript('analytics', {
          content: 'window.__testAnalyticsLoaded = true;',
        });
        createBlockedScript('marketing', {
          content: 'window.__testMarketingLoaded = true;',
        });

        blocker.init(
          createConsent({ analytics: false, marketing: false })
        );

        expect(window.__testAnalyticsLoaded).toBe(false);
        expect(window.__testMarketingLoaded).toBe(false);
      });
    });

    describe('scripts execute when category consented', () => {
      it('should execute inline analytics script when analytics consented', () => {
        window.__testAnalyticsLoaded = false;
        createBlockedScript('analytics', {
          content: 'window.__testAnalyticsLoaded = true;',
          name: 'Analytics Test',
        });

        blocker.init(createConsent({ analytics: true }));

        expect(window.__testAnalyticsLoaded).toBe(true);
      });

      it('should execute inline marketing script when marketing consented', () => {
        window.__testMarketingLoaded = false;
        createBlockedScript('marketing', {
          content: 'window.__testMarketingLoaded = true;',
          name: 'Marketing Test',
        });

        blocker.init(createConsent({ marketing: true }));

        expect(window.__testMarketingLoaded).toBe(true);
      });

      it('should remove text/plain scripts after execution', () => {
        createBlockedScript('analytics', {
          content: 'console.log("test");',
        });

        blocker.init(createConsent({ analytics: true }));

        const blockedScripts = document.querySelectorAll(
          'script[type="text/plain"]'
        );
        expect(blockedScripts.length).toBe(0);
      });

      it('should execute only consented categories', () => {
        window.__testAnalyticsLoaded = false;
        window.__testMarketingLoaded = false;

        createBlockedScript('analytics', {
          content: 'window.__testAnalyticsLoaded = true;',
        });
        createBlockedScript('marketing', {
          content: 'window.__testMarketingLoaded = true;',
        });

        blocker.init(createConsent({ analytics: true, marketing: false }));

        expect(window.__testAnalyticsLoaded).toBe(true);
        expect(window.__testMarketingLoaded).toBe(false);
      });
    });

    describe('essential scripts always execute', () => {
      it('should always execute essential scripts regardless of other consent', () => {
        window.__testEssentialLoaded = false;
        createBlockedScript('essential', {
          content: 'window.__testEssentialLoaded = true;',
        });

        blocker.init(
          createConsent({ analytics: false, marketing: false })
        );

        expect(window.__testEssentialLoaded).toBe(true);
      });

      it('should execute essential scripts even with minimal consent', () => {
        window.__testEssentialLoaded = false;
        createBlockedScript('essential', {
          content: 'window.__testEssentialLoaded = true;',
        });

        // Init with only essential true (default)
        blocker.init(createConsent());

        expect(window.__testEssentialLoaded).toBe(true);
      });

      it('should execute essential alongside other consented categories', () => {
        window.__testEssentialLoaded = false;
        window.__testAnalyticsLoaded = false;
        window.__testMarketingLoaded = false;

        createBlockedScript('essential', {
          content: 'window.__testEssentialLoaded = true;',
        });
        createBlockedScript('analytics', {
          content: 'window.__testAnalyticsLoaded = true;',
        });
        createBlockedScript('marketing', {
          content: 'window.__testMarketingLoaded = true;',
        });

        blocker.init(
          createConsent({ analytics: true, marketing: false })
        );

        expect(window.__testEssentialLoaded).toBe(true);
        expect(window.__testAnalyticsLoaded).toBe(true);
        expect(window.__testMarketingLoaded).toBe(false);
      });
    });

    describe('external scripts (src attribute)', () => {
      it('should create script with src attribute when unblocking external script', () => {
        const scriptSrc = 'https://example.com/analytics.js';
        createBlockedScript('analytics', { src: scriptSrc });

        blocker.init(createConsent({ analytics: true }));

        // Original blocked script should be replaced
        const blockedScripts = document.querySelectorAll(
          'script[type="text/plain"]'
        );
        expect(blockedScripts.length).toBe(0);

        // New script with src should exist
        const executedScript = document.querySelector(
          `script[src="${scriptSrc}"]`
        );
        expect(executedScript).not.toBeNull();
        expect(executedScript?.getAttribute('type')).toBeNull(); // No type = text/javascript
      });

      it('should preserve other attributes from external scripts', () => {
        const script = createBlockedScript('analytics', {
          src: 'https://example.com/script.js',
        });
        script.setAttribute('async', '');
        script.setAttribute('crossorigin', 'anonymous');

        blocker.init(createConsent({ analytics: true }));

        const executedScript = document.querySelector(
          'script[src="https://example.com/script.js"]'
        );
        expect(executedScript).not.toBeNull();
        expect(executedScript?.hasAttribute('async')).toBe(true);
        expect(executedScript?.getAttribute('crossorigin')).toBe('anonymous');
      });
    });

    describe('GA4 and Meta Pixel patterns (Story-Specific DoD)', () => {
      it('should handle GA4 gtag script pattern', () => {
        window.dataLayer = [];
        window.gtag = undefined;

        createBlockedScript('analytics', {
          content: `
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            window.gtag = gtag;
            gtag('js', new Date());
            gtag('config', 'GA_MEASUREMENT_ID');
          `,
          name: 'GA4 Config',
        });

        blocker.init(createConsent({ analytics: true }));

        expect(window.gtag).toBeDefined();
        expect(typeof window.gtag).toBe('function');
        expect(window.dataLayer.length).toBeGreaterThan(0);
      });

      it('should handle Meta Pixel fbq script pattern', () => {
        window.fbq = undefined;

        createBlockedScript('marketing', {
          content: `
            !function(f,b,e,v,n,t,s)
            {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};
            if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
            n.queue=[];}(window,document,'script',
            'https://connect.facebook.net/en_US/fbevents.js');
          `,
          name: 'Meta Pixel',
        });

        blocker.init(createConsent({ marketing: true }));

        expect(window.fbq).toBeDefined();
        expect(typeof window.fbq).toBe('function');
      });

      it('should keep GA4 blocked when analytics not consented', () => {
        window.gtag = undefined;

        createBlockedScript('analytics', {
          content: `window.gtag = function() {};`,
          name: 'GA4',
        });

        blocker.init(createConsent({ analytics: false }));

        expect(window.gtag).toBeUndefined();
      });

      it('should keep Meta Pixel blocked when marketing not consented', () => {
        window.fbq = undefined;

        createBlockedScript('marketing', {
          content: `window.fbq = function() {};`,
          name: 'Meta Pixel',
        });

        blocker.init(createConsent({ marketing: false }));

        expect(window.fbq).toBeUndefined();
      });
    });
  });

  describe('US-012b: Dynamic Script Unblocking', () => {
    describe('consent event triggers unblocking', () => {
      it('should unblock scripts when consentpro_consent event fires', () => {
        window.__testAnalyticsLoaded = false;
        createBlockedScript('analytics', {
          content: 'window.__testAnalyticsLoaded = true;',
        });

        // Init with no analytics consent
        blocker.init(createConsent({ analytics: false }));
        expect(window.__testAnalyticsLoaded).toBe(false);

        // Dispatch consent event with analytics enabled
        dispatchConsentEvent(
          createConsent({ analytics: true })
        );

        expect(window.__testAnalyticsLoaded).toBe(true);
      });

      it('should scan and unblock all matching category scripts', () => {
        window.__testAnalyticsLoaded = false;
        window.__testMarketingLoaded = false;

        createBlockedScript('analytics', {
          content: 'window.__testAnalyticsLoaded = true;',
        });
        createBlockedScript('analytics', {
          content: 'window.__testMarketingLoaded = true;', // Second analytics script
        });

        blocker.init(createConsent({ analytics: false }));

        // Enable analytics via event
        dispatchConsentEvent(createConsent({ analytics: true }));

        expect(window.__testAnalyticsLoaded).toBe(true);
        expect(window.__testMarketingLoaded).toBe(true);
      });

      it('should only unblock newly consented categories', () => {
        window.__testAnalyticsLoaded = false;
        window.__testMarketingLoaded = false;

        createBlockedScript('analytics', {
          content: 'window.__testAnalyticsLoaded = true;',
        });
        createBlockedScript('marketing', {
          content: 'window.__testMarketingLoaded = true;',
        });

        blocker.init(createConsent({ analytics: false, marketing: false }));

        // Enable only analytics
        dispatchConsentEvent(
          createConsent({ analytics: true, marketing: false })
        );

        expect(window.__testAnalyticsLoaded).toBe(true);
        expect(window.__testMarketingLoaded).toBe(false);
      });
    });

    describe('scripts execute in DOM order', () => {
      it('should execute scripts in the order they appear in DOM', () => {
        window.__order = [];

        createBlockedScript('analytics', {
          content: 'window.__order = window.__order || []; window.__order.push(1);',
        });
        createBlockedScript('analytics', {
          content: 'window.__order.push(2);',
        });
        createBlockedScript('analytics', {
          content: 'window.__order.push(3);',
        });

        blocker.init(createConsent({ analytics: true }));

        expect(window.__order).toEqual([1, 2, 3]);
      });
    });

    describe('already-executed scripts do not re-run', () => {
      it('should not execute same script twice on multiple consent events', () => {
        window.__executionCount = 0;
        createBlockedScript('analytics', {
          content: 'window.__executionCount = (window.__executionCount || 0) + 1;',
        });

        blocker.init(createConsent({ analytics: true }));
        expect(window.__executionCount).toBe(1);

        // Fire consent event again
        dispatchConsentEvent(createConsent({ analytics: true }));

        expect(window.__executionCount).toBe(1); // Still 1, not 2
      });

      it('should not re-execute when updateConsent called multiple times', () => {
        window.__executionCount = 0;
        createBlockedScript('analytics', {
          content: 'window.__executionCount = (window.__executionCount || 0) + 1;',
        });

        blocker.init(createConsent({ analytics: true }));
        blocker.updateConsent(createConsent({ analytics: true }));
        blocker.updateConsent(createConsent({ analytics: true }));

        expect(window.__executionCount).toBe(1);
      });
    });

    describe('MutationObserver catches dynamic scripts', () => {
      it('should unblock dynamically added script if category already consented', async () => {
        window.__dynamicScript = false;

        blocker.init(createConsent({ analytics: true }));

        // Dynamically add a blocked script after init
        createBlockedScript('analytics', {
          content: 'window.__dynamicScript = true;',
        });

        // Wait for MutationObserver to process
        await new Promise((resolve) => setTimeout(resolve, 10));

        expect(window.__dynamicScript).toBe(true);
      });

      it('should not unblock dynamically added script if category not consented', async () => {
        window.__dynamicScript = false;

        blocker.init(createConsent({ analytics: false }));

        createBlockedScript('analytics', {
          content: 'window.__dynamicScript = true;',
        });

        await new Promise((resolve) => setTimeout(resolve, 10));

        expect(window.__dynamicScript).toBe(false);
      });

      it('should process scripts in nested elements', async () => {
        window.__dynamicScript = false;

        blocker.init(createConsent({ analytics: true }));

        // Create a container with nested script
        const container = document.createElement('div');
        const script = document.createElement('script');
        script.type = 'text/plain';
        script.dataset.consentpro = 'analytics';
        script.textContent = 'window.__dynamicScript = true;';
        container.appendChild(script);
        document.body.appendChild(container);

        await new Promise((resolve) => setTimeout(resolve, 10));

        expect(window.__dynamicScript).toBe(true);
      });

      it('should stop observing when stopObserver called', async () => {
        window.__dynamicScript = false;

        blocker.init(createConsent({ analytics: true }));
        blocker.stopObserver();

        createBlockedScript('analytics', {
          content: 'window.__dynamicScript = true;',
        });

        await new Promise((resolve) => setTimeout(resolve, 10));

        // Script should NOT be executed because observer was stopped
        expect(window.__dynamicScript).toBe(false);
      });
    });
  });

  describe('API methods', () => {
    describe('getBlockedScripts', () => {
      it('should return all blocked scripts', () => {
        createBlockedScript('analytics', { content: 'a' });
        createBlockedScript('marketing', { content: 'b' });
        createBlockedScript('personalization', { content: 'c' });

        const scripts = blocker.getBlockedScripts();
        expect(scripts.length).toBe(3);
      });

      it('should return empty array when no blocked scripts', () => {
        const scripts = blocker.getBlockedScripts();
        expect(scripts).toEqual([]);
      });

      it('should not include already executed scripts', () => {
        createBlockedScript('analytics', { content: 'test' });

        blocker.init(createConsent({ analytics: true }));

        const scripts = blocker.getBlockedScripts();
        expect(scripts.length).toBe(0);
      });
    });

    describe('isCategoryConsented', () => {
      it('should return true for essential without any init', () => {
        // Essential is always consented
        expect(blocker.isCategoryConsented('essential')).toBe(true);
      });

      it('should return false for non-essential without init', () => {
        expect(blocker.isCategoryConsented('analytics')).toBe(false);
      });

      it('should return correct value after init', () => {
        blocker.init(createConsent({ analytics: true, marketing: false }));

        expect(blocker.isCategoryConsented('essential')).toBe(true);
        expect(blocker.isCategoryConsented('analytics')).toBe(true);
        expect(blocker.isCategoryConsented('marketing')).toBe(false);
      });
    });

    describe('destroy', () => {
      it('should clean up event listeners', () => {
        window.__testAnalyticsLoaded = false;
        createBlockedScript('analytics', {
          content: 'window.__testAnalyticsLoaded = true;',
        });

        blocker.init(createConsent({ analytics: false }));
        blocker.destroy();

        // Fire consent event - should not trigger unblocking
        dispatchConsentEvent(createConsent({ analytics: true }));

        expect(window.__testAnalyticsLoaded).toBe(false);
      });

      it('should stop MutationObserver', async () => {
        window.__dynamicScript = false;

        blocker.init(createConsent({ analytics: true }));
        blocker.destroy();

        createBlockedScript('analytics', {
          content: 'window.__dynamicScript = true;',
        });

        await new Promise((resolve) => setTimeout(resolve, 10));

        expect(window.__dynamicScript).toBe(false);
      });
    });
  });

  describe('edge cases', () => {
    it('should handle script without parentNode gracefully', () => {
      const script = document.createElement('script');
      script.type = 'text/plain';
      script.dataset.consentpro = 'analytics';
      script.textContent = 'console.log("orphan")';
      // Don't append to document

      blocker.init(createConsent({ analytics: true }));

      // Should not throw
      expect(() => blocker.unblockScript(script)).not.toThrow();
    });

    it('should handle script with unknown category', () => {
      createBlockedScript('unknown-category' as keyof ConsentCategories, {
        content: 'console.log("unknown")',
      });

      blocker.init(createConsent());

      // Should not throw, script should remain blocked
      const scripts = blocker.getBlockedScripts();
      expect(scripts.length).toBe(1);
    });

    it('should handle empty script content', () => {
      createBlockedScript('analytics', { content: '' });

      blocker.init(createConsent({ analytics: true }));

      // Should execute without error
      const scripts = blocker.getBlockedScripts();
      expect(scripts.length).toBe(0);
    });

    it('should not initialize twice', () => {
      window.__executionCount = 0;
      createBlockedScript('analytics', {
        content: 'window.__executionCount = (window.__executionCount || 0) + 1;',
      });

      blocker.init(createConsent({ analytics: true }));
      blocker.init(createConsent({ analytics: true })); // Second init should be ignored

      expect(window.__executionCount).toBe(1);
    });

    it('should work with custom attribute name', () => {
      const customBlocker = new ScriptBlocker({
        attributeName: 'data-custom-consent',
      });

      const script = document.createElement('script');
      script.type = 'text/plain';
      script.dataset.customConsent = 'analytics';
      script.textContent = 'window.__testAnalyticsLoaded = true;';
      document.body.appendChild(script);

      window.__testAnalyticsLoaded = false;
      customBlocker.init(createConsent({ analytics: true }));

      expect(window.__testAnalyticsLoaded).toBe(true);
      customBlocker.destroy();
    });
  });
});
