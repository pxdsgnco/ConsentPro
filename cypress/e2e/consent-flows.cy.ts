/// <reference types="cypress" />

describe('ConsentPro Consent Flows', () => {
  beforeEach(() => {
    // Clear all consent storage before each test
    cy.clearConsentStorage();

    // Visit the demo page
    cy.visit('/demo.html');

    // Wait for banner to be visible
    cy.get('#consentpro-banner').should('have.class', 'consentpro--visible');
  });

  // ===========================================
  // US-008: Accept All Button Tests
  // ===========================================
  describe('US-008: Accept All', () => {
    it('should set all categories to true in localStorage when Accept All is clicked', () => {
      // Click Accept All button
      cy.get('[data-action="accept"]').click();

      // Verify banner is hidden
      cy.get('#consentpro-banner').should('not.have.class', 'consentpro--visible');

      // Verify localStorage has all categories set to true
      cy.getStoredConsent().then((consent) => {
        expect(consent).to.not.be.null;
        expect(consent!.categories.essential).to.equal(true);
        expect(consent!.categories.analytics).to.equal(true);
        expect(consent!.categories.marketing).to.equal(true);
        expect(consent!.categories.personalization).to.equal(true);
      });
    });

    it('should set all categories to true in cookie (Safari ITP fallback) when Accept All is clicked', () => {
      // Click Accept All button
      cy.get('[data-action="accept"]').click();

      // Verify cookie has all categories set to true
      cy.getConsentCookie().then((consent) => {
        expect(consent).to.not.be.null;
        expect(consent!.categories.essential).to.equal(true);
        expect(consent!.categories.analytics).to.equal(true);
        expect(consent!.categories.marketing).to.equal(true);
        expect(consent!.categories.personalization).to.equal(true);
      });
    });

    it('should dispatch consentpro_consent event with full consent when Accept All is clicked', () => {
      // Set up event listener before clicking
      cy.window().then((win) => {
        win.document.addEventListener('consentpro_consent', (e: Event) => {
          const customEvent = e as CustomEvent;
          (win as any).__consentEventDetail = customEvent.detail;
        });
      });

      // Click Accept All button
      cy.get('[data-action="accept"]').click();

      // Verify event was dispatched with correct detail
      cy.window().then((win) => {
        const eventDetail = (win as any).__consentEventDetail;
        expect(eventDetail).to.not.be.undefined;
        expect(eventDetail.categories.essential).to.equal(true);
        expect(eventDetail.categories.analytics).to.equal(true);
        expect(eventDetail.categories.marketing).to.equal(true);
        expect(eventDetail.categories.personalization).to.equal(true);
        expect(eventDetail.timestamp).to.be.a('number');
      });
    });

    it('should include version and hash in stored consent when Accept All is clicked', () => {
      // Click Accept All button
      cy.get('[data-action="accept"]').click();

      // Verify consent metadata
      cy.getStoredConsent().then((consent) => {
        expect(consent).to.not.be.null;
        expect(consent!.version).to.equal(1);
        expect(consent!.hash).to.be.a('string');
        expect(consent!.timestamp).to.be.a('number');
        expect(consent!.timestamp).to.be.closeTo(Date.now(), 5000);
      });
    });
  });

  // ===========================================
  // US-009: Reject Non-Essential Button Tests
  // ===========================================
  describe('US-009: Reject Non-Essential', () => {
    it('should set only essential to true in localStorage when Reject is clicked', () => {
      // Click Reject Non-Essential button
      cy.get('[data-action="reject"]').click();

      // Verify banner is hidden
      cy.get('#consentpro-banner').should('not.have.class', 'consentpro--visible');

      // Verify localStorage has only essential true
      cy.getStoredConsent().then((consent) => {
        expect(consent).to.not.be.null;
        expect(consent!.categories.essential).to.equal(true);
        expect(consent!.categories.analytics).to.equal(false);
        expect(consent!.categories.marketing).to.equal(false);
        expect(consent!.categories.personalization).to.equal(false);
      });
    });

    it('should set only essential to true in cookie (Safari ITP fallback) when Reject is clicked', () => {
      // Click Reject Non-Essential button
      cy.get('[data-action="reject"]').click();

      // Verify cookie has only essential true
      cy.getConsentCookie().then((consent) => {
        expect(consent).to.not.be.null;
        expect(consent!.categories.essential).to.equal(true);
        expect(consent!.categories.analytics).to.equal(false);
        expect(consent!.categories.marketing).to.equal(false);
        expect(consent!.categories.personalization).to.equal(false);
      });
    });

    it('should dispatch consentpro_consent event with minimal consent when Reject is clicked', () => {
      // Set up event listener before clicking
      cy.window().then((win) => {
        win.document.addEventListener('consentpro_consent', (e: Event) => {
          const customEvent = e as CustomEvent;
          (win as any).__consentEventDetail = customEvent.detail;
        });
      });

      // Click Reject button
      cy.get('[data-action="reject"]').click();

      // Verify event was dispatched with correct detail
      cy.window().then((win) => {
        const eventDetail = (win as any).__consentEventDetail;
        expect(eventDetail).to.not.be.undefined;
        expect(eventDetail.categories.essential).to.equal(true);
        expect(eventDetail.categories.analytics).to.equal(false);
        expect(eventDetail.categories.marketing).to.equal(false);
        expect(eventDetail.categories.personalization).to.equal(false);
      });
    });
  });

  // ===========================================
  // US-010: Save Preferences (Layer 2) Tests
  // ===========================================
  describe('US-010: Save Preferences from Settings Panel', () => {
    beforeEach(() => {
      // Navigate to Layer 2 (settings panel)
      cy.get('[data-action="settings"]').click();

      // Verify we're in Layer 2
      cy.get('.consentpro__settings-header').should('be.visible');
      cy.get('.consentpro__categories').should('be.visible');
    });

    it('should save custom category selection to localStorage', () => {
      // Toggle analytics ON
      cy.get('[data-category="analytics"]').click();
      cy.get('[data-category="analytics"]').should('have.attr', 'aria-checked', 'true');

      // Leave marketing OFF (default is false)
      cy.get('[data-category="marketing"]').should('have.attr', 'aria-checked', 'false');

      // Toggle personalization ON
      cy.get('[data-category="personalization"]').click();
      cy.get('[data-category="personalization"]').should('have.attr', 'aria-checked', 'true');

      // Click Save
      cy.get('[data-action="save"]').click();

      // Verify banner is hidden
      cy.get('#consentpro-banner').should('not.have.class', 'consentpro--visible');

      // Verify localStorage has custom selection
      cy.getStoredConsent().then((consent) => {
        expect(consent).to.not.be.null;
        expect(consent!.categories.essential).to.equal(true);
        expect(consent!.categories.analytics).to.equal(true);
        expect(consent!.categories.marketing).to.equal(false);
        expect(consent!.categories.personalization).to.equal(true);
      });
    });

    it('should save custom category selection to cookie (Safari ITP fallback)', () => {
      // Toggle only marketing ON
      cy.get('[data-category="marketing"]').click();

      // Click Save
      cy.get('[data-action="save"]').click();

      // Verify cookie has custom selection
      cy.getConsentCookie().then((consent) => {
        expect(consent).to.not.be.null;
        expect(consent!.categories.essential).to.equal(true);
        expect(consent!.categories.analytics).to.equal(false);
        expect(consent!.categories.marketing).to.equal(true);
        expect(consent!.categories.personalization).to.equal(false);
      });
    });

    it('should dispatch consentpro_consent event with custom selection when Save is clicked', () => {
      // Set up event listener
      cy.window().then((win) => {
        win.document.addEventListener('consentpro_consent', (e: Event) => {
          const customEvent = e as CustomEvent;
          (win as any).__consentEventDetail = customEvent.detail;
        });
      });

      // Toggle analytics and personalization ON
      cy.get('[data-category="analytics"]').click();
      cy.get('[data-category="personalization"]').click();

      // Click Save
      cy.get('[data-action="save"]').click();

      // Verify event
      cy.window().then((win) => {
        const eventDetail = (win as any).__consentEventDetail;
        expect(eventDetail).to.not.be.undefined;
        expect(eventDetail.categories.analytics).to.equal(true);
        expect(eventDetail.categories.marketing).to.equal(false);
        expect(eventDetail.categories.personalization).to.equal(true);
      });
    });

    it('should persist consent across page reload', () => {
      // Toggle analytics ON
      cy.get('[data-category="analytics"]').click();

      // Click Save
      cy.get('[data-action="save"]').click();

      // Wait for banner to hide
      cy.get('#consentpro-banner').should('not.have.class', 'consentpro--visible');

      // Reload the page
      cy.reload();

      // Banner should NOT show (consent exists)
      cy.get('#consentpro-banner').should('not.have.class', 'consentpro--visible');

      // Verify stored consent is still there
      cy.getStoredConsent().then((consent) => {
        expect(consent).to.not.be.null;
        expect(consent!.categories.analytics).to.equal(true);
      });
    });

    it('should keep essential category always true and disabled', () => {
      // Essential toggle should be disabled
      cy.get('[data-category="essential"]').should('be.disabled');
      cy.get('[data-category="essential"]').should('have.attr', 'aria-checked', 'true');

      // Click Save without changing anything
      cy.get('[data-action="save"]').click();

      // Verify essential is still true
      cy.getStoredConsent().then((consent) => {
        expect(consent!.categories.essential).to.equal(true);
      });
    });

    it('should allow toggling categories multiple times before saving', () => {
      // Toggle analytics ON
      cy.get('[data-category="analytics"]').click();
      cy.get('[data-category="analytics"]').should('have.attr', 'aria-checked', 'true');

      // Toggle analytics OFF
      cy.get('[data-category="analytics"]').click();
      cy.get('[data-category="analytics"]').should('have.attr', 'aria-checked', 'false');

      // Toggle analytics ON again
      cy.get('[data-category="analytics"]').click();
      cy.get('[data-category="analytics"]').should('have.attr', 'aria-checked', 'true');

      // Click Save
      cy.get('[data-action="save"]').click();

      // Verify final state
      cy.getStoredConsent().then((consent) => {
        expect(consent!.categories.analytics).to.equal(true);
      });
    });
  });

  // ===========================================
  // Cross-cutting: Storage Synchronization
  // ===========================================
  describe('Storage Synchronization (localStorage + Cookie)', () => {
    it('should write identical data to both localStorage and cookie', () => {
      // Click Accept All
      cy.get('[data-action="accept"]').click();

      // Get both storage values
      cy.getStoredConsent().then((localConsent) => {
        cy.getConsentCookie().then((cookieConsent) => {
          expect(localConsent).to.not.be.null;
          expect(cookieConsent).to.not.be.null;

          // Categories should match
          expect(localConsent!.categories).to.deep.equal(cookieConsent!.categories);

          // Metadata should match
          expect(localConsent!.version).to.equal(cookieConsent!.version);
          expect(localConsent!.hash).to.equal(cookieConsent!.hash);
          expect(localConsent!.geo).to.equal(cookieConsent!.geo);

          // Timestamps should be very close (same write operation)
          expect(Math.abs(localConsent!.timestamp - cookieConsent!.timestamp)).to.be.lessThan(100);
        });
      });
    });
  });

  // ===========================================
  // Footer Toggle Tests
  // ===========================================
  describe('Footer Toggle', () => {
    it('should show footer toggle after consent is given', () => {
      // Initially footer toggle should be hidden
      cy.get('.consentpro-footer-toggle').should('have.class', 'consentpro-footer-toggle--hidden');

      // Click Accept All
      cy.get('[data-action="accept"]').click();

      // Footer toggle should now be visible
      cy.get('.consentpro-footer-toggle').should(
        'not.have.class',
        'consentpro-footer-toggle--hidden'
      );
    });

    it('should reopen banner when footer toggle is clicked', () => {
      // Give consent first
      cy.get('[data-action="accept"]').click();

      // Banner should be hidden
      cy.get('#consentpro-banner').should('not.have.class', 'consentpro--visible');

      // Click footer toggle
      cy.get('.consentpro-footer-toggle').click();

      // Banner should be visible again
      cy.get('#consentpro-banner').should('have.class', 'consentpro--visible');
    });
  });
});
