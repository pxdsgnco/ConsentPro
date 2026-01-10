/// <reference types="cypress" />

/**
 * Cypress E2E Tests for Script Blocking (US-012a & US-012b)
 *
 * Tests verify that:
 * - Scripts with type="text/plain" data-consentpro="category" stay inert
 * - On consent, matching scripts execute
 * - Essential scripts always execute
 * - Scripts execute in DOM order
 * - MutationObserver catches dynamically added scripts
 */

declare global {
  interface Window {
    __analyticsLoaded?: boolean;
    __marketingLoaded?: boolean;
    __personalizationLoaded?: boolean;
    __consentEventDetail?: {
      categories: {
        essential: true;
        analytics: boolean;
        marketing: boolean;
        personalization: boolean;
      };
      timestamp: number;
      geo: string | null;
    };
    testGtag?: (...args: unknown[]) => void;
    testFbq?: () => string;
  }
}

describe('ConsentPro Script Blocking (US-012a & US-012b)', () => {
  beforeEach(() => {
    // Clear all consent storage before each test
    cy.clearConsentStorage();

    // Visit the demo page
    cy.visit('/demo.html');

    // Wait for banner to be visible
    cy.get('#consentpro-banner').should('have.class', 'consentpro--visible');
  });

  // ===========================================
  // US-012a: Script Blocking on Page Load
  // ===========================================
  describe('US-012a: Script Blocking on Page Load', () => {
    it('should keep scripts blocked when no consent given', () => {
      // Demo page has blocked scripts for analytics, marketing, personalization
      cy.window().then((win) => {
        expect(win.__analyticsLoaded).to.be.undefined;
        expect(win.__marketingLoaded).to.be.undefined;
        expect(win.__personalizationLoaded).to.be.undefined;
      });
    });

    it('should have scripts with type="text/plain" before consent', () => {
      cy.get('script[type="text/plain"][data-consentpro="analytics"]').should(
        'exist'
      );
      cy.get('script[type="text/plain"][data-consentpro="marketing"]').should(
        'exist'
      );
      cy.get(
        'script[type="text/plain"][data-consentpro="personalization"]'
      ).should('exist');
    });

    it('should keep scripts inert even after page interaction', () => {
      // Interact with page without giving consent
      cy.get('[data-action="settings"]').click();
      cy.get('[data-action="back"]').click();

      // Scripts should still be blocked
      cy.window().then((win) => {
        expect(win.__analyticsLoaded).to.be.undefined;
        expect(win.__marketingLoaded).to.be.undefined;
      });
    });
  });

  // ===========================================
  // US-012b: Dynamic Script Unblocking
  // ===========================================
  describe('US-012b: Dynamic Script Unblocking', () => {
    it('should unblock all scripts when Accept All clicked', () => {
      // Verify scripts are initially blocked
      cy.window().then((win) => {
        expect(win.__analyticsLoaded).to.be.undefined;
        expect(win.__marketingLoaded).to.be.undefined;
        expect(win.__personalizationLoaded).to.be.undefined;
      });

      // Click Accept All
      cy.get('[data-action="accept"]').click();

      // Verify all scripts executed
      cy.window().then((win) => {
        expect(win.__analyticsLoaded).to.equal(true);
        expect(win.__marketingLoaded).to.equal(true);
        expect(win.__personalizationLoaded).to.equal(true);
      });
    });

    it('should NOT unblock any scripts when Reject clicked', () => {
      cy.get('[data-action="reject"]').click();

      // All non-essential scripts should remain blocked
      cy.window().then((win) => {
        expect(win.__analyticsLoaded).to.be.undefined;
        expect(win.__marketingLoaded).to.be.undefined;
        expect(win.__personalizationLoaded).to.be.undefined;
      });
    });

    it('should selectively unblock based on settings panel choices', () => {
      // Go to settings
      cy.get('[data-action="settings"]').click();

      // Enable only analytics (click toggle to turn on)
      cy.get('[data-category="analytics"]').click();

      // Verify analytics is now checked
      cy.get('[data-category="analytics"]').should(
        'have.attr',
        'aria-checked',
        'true'
      );

      // Verify marketing and personalization are still off
      cy.get('[data-category="marketing"]').should(
        'have.attr',
        'aria-checked',
        'false'
      );
      cy.get('[data-category="personalization"]').should(
        'have.attr',
        'aria-checked',
        'false'
      );

      // Save preferences
      cy.get('[data-action="save"]').click();

      // Only analytics should be unblocked
      cy.window().then((win) => {
        expect(win.__analyticsLoaded).to.equal(true);
        expect(win.__marketingLoaded).to.be.undefined;
        expect(win.__personalizationLoaded).to.be.undefined;
      });
    });

    it('should unblock multiple selected categories', () => {
      // Go to settings
      cy.get('[data-action="settings"]').click();

      // Enable analytics and marketing
      cy.get('[data-category="analytics"]').click();
      cy.get('[data-category="marketing"]').click();

      // Save preferences
      cy.get('[data-action="save"]').click();

      // Analytics and marketing should be unblocked
      cy.window().then((win) => {
        expect(win.__analyticsLoaded).to.equal(true);
        expect(win.__marketingLoaded).to.equal(true);
        expect(win.__personalizationLoaded).to.be.undefined;
      });
    });

    it('should fire consentpro_consent event when consent given', () => {
      // Set up event listener before clicking
      cy.window().then((win) => {
        win.document.addEventListener('consentpro_consent', (e: Event) => {
          const customEvent = e as CustomEvent;
          win.__consentEventDetail = customEvent.detail;
        });
      });

      // Click Accept All
      cy.get('[data-action="accept"]').click();

      // Verify event was dispatched
      cy.window().then((win) => {
        expect(win.__consentEventDetail).to.not.be.undefined;
        expect(win.__consentEventDetail!.categories.analytics).to.equal(true);
        expect(win.__consentEventDetail!.categories.marketing).to.equal(true);
      });
    });

    it('should remove blocked scripts from DOM after execution', () => {
      // Click Accept All
      cy.get('[data-action="accept"]').click();

      // Original blocked scripts should be removed
      cy.get('script[type="text/plain"][data-consentpro]').should('not.exist');
    });
  });

  // ===========================================
  // Consent Persistence and Script State
  // ===========================================
  describe('Script State Persistence', () => {
    it('should keep scripts unblocked after page reload if consent exists', () => {
      // Give consent
      cy.get('[data-action="accept"]').click();

      // Verify scripts executed
      cy.window().then((win) => {
        expect(win.__analyticsLoaded).to.equal(true);
      });

      // Reload page
      cy.reload();

      // Wait for page to load (banner should not be visible)
      cy.get('#consentpro-banner').should(
        'not.have.class',
        'consentpro--visible'
      );

      // Scripts should have executed on reload
      cy.window().then((win) => {
        expect(win.__analyticsLoaded).to.equal(true);
        expect(win.__marketingLoaded).to.equal(true);
        expect(win.__personalizationLoaded).to.equal(true);
      });
    });

    it('should respect saved category preferences after reload', () => {
      // Go to settings and enable only analytics
      cy.get('[data-action="settings"]').click();
      cy.get('[data-category="analytics"]').click();
      cy.get('[data-action="save"]').click();

      // Reload page
      cy.reload();

      // Only analytics should be executed
      cy.window().then((win) => {
        expect(win.__analyticsLoaded).to.equal(true);
        expect(win.__marketingLoaded).to.be.undefined;
        expect(win.__personalizationLoaded).to.be.undefined;
      });
    });
  });

  // ===========================================
  // GA4 and Meta Pixel Patterns (Story DoD)
  // ===========================================
  describe('Story-Specific DoD: GA4 & Meta Pixel patterns', () => {
    it('should properly unblock GA4-style gtag scripts', () => {
      // Add GA4 pattern script to page
      cy.window().then((win) => {
        const script = win.document.createElement('script');
        script.type = 'text/plain';
        script.dataset.consentpro = 'analytics';
        script.textContent = `
          window.testGtag = function() {
            return 'gtag-loaded';
          };
        `;
        win.document.body.appendChild(script);
      });

      // Click Accept All
      cy.get('[data-action="accept"]').click();

      // Verify gtag function exists and works
      cy.window().then((win) => {
        expect(win.testGtag).to.be.a('function');
        expect(win.testGtag!()).to.equal('gtag-loaded');
      });
    });

    it('should properly unblock Meta Pixel-style fbq scripts', () => {
      // Add Meta Pixel pattern script to page
      cy.window().then((win) => {
        const script = win.document.createElement('script');
        script.type = 'text/plain';
        script.dataset.consentpro = 'marketing';
        script.textContent = `
          window.testFbq = function() {
            return 'fbq-loaded';
          };
        `;
        win.document.body.appendChild(script);
      });

      // Click Accept All
      cy.get('[data-action="accept"]').click();

      // Verify fbq function exists and works
      cy.window().then((win) => {
        expect(win.testFbq).to.be.a('function');
        expect(win.testFbq!()).to.equal('fbq-loaded');
      });
    });

    it('should keep GA4 scripts blocked when analytics not consented', () => {
      // Add GA4 pattern script
      cy.window().then((win) => {
        const script = win.document.createElement('script');
        script.type = 'text/plain';
        script.dataset.consentpro = 'analytics';
        script.textContent = `window.testGtag = function() { return 'loaded'; };`;
        win.document.body.appendChild(script);
      });

      // Click Reject (no analytics consent)
      cy.get('[data-action="reject"]').click();

      // Verify gtag was NOT loaded
      cy.window().then((win) => {
        expect(win.testGtag).to.be.undefined;
      });
    });

    it('should keep Meta Pixel blocked when marketing not consented', () => {
      // Add Meta Pixel script
      cy.window().then((win) => {
        const script = win.document.createElement('script');
        script.type = 'text/plain';
        script.dataset.consentpro = 'marketing';
        script.textContent = `window.testFbq = function() { return 'loaded'; };`;
        win.document.body.appendChild(script);
      });

      // Enable only analytics in settings
      cy.get('[data-action="settings"]').click();
      cy.get('[data-category="analytics"]').click();
      cy.get('[data-action="save"]').click();

      // Verify fbq was NOT loaded (only analytics consented)
      cy.window().then((win) => {
        expect(win.testFbq).to.be.undefined;
      });
    });
  });

  // ===========================================
  // Edge Cases
  // ===========================================
  describe('Edge Cases', () => {
    it('should handle rapid consent changes', () => {
      // Quickly change consent multiple times
      cy.get('[data-action="accept"]').click();

      // Open footer toggle and change consent
      cy.get('.consentpro-footer__toggle').click();
      cy.get('[data-action="settings"]').click();

      // Toggle analytics off
      cy.get('[data-category="analytics"]').click();
      cy.get('[data-action="save"]').click();

      // Scripts that were already executed should still be loaded
      cy.window().then((win) => {
        // Analytics was executed on first Accept All
        expect(win.__analyticsLoaded).to.equal(true);
      });
    });

    it('should not crash on scripts without content', () => {
      // Add empty script
      cy.window().then((win) => {
        const script = win.document.createElement('script');
        script.type = 'text/plain';
        script.dataset.consentpro = 'analytics';
        script.textContent = '';
        win.document.body.appendChild(script);
      });

      // Should not throw error
      cy.get('[data-action="accept"]').click();

      // Page should still function
      cy.get('#consentpro-banner').should(
        'not.have.class',
        'consentpro--visible'
      );
    });
  });
});
