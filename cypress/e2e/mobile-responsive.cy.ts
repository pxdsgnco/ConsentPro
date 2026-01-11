/// <reference types="cypress" />

/**
 * US-037a: Mobile Responsive Layout Tests
 *
 * Acceptance Criteria:
 * - Full-width banner on viewport <768px
 * - Stacked buttons (vertical)
 * - Layer 2 scrolls if content exceeds viewport
 * - No horizontal scroll ever
 * - Test: iPhone SE, iPhone 14, Pixel 5
 */
describe('US-037a: Mobile Responsive Layout', () => {
  // Device viewport configurations
  const devices = {
    iPhoneSE: { width: 375, height: 667, name: 'iPhone SE' },
    iPhone14: { width: 390, height: 844, name: 'iPhone 14' },
    Pixel5: { width: 393, height: 851, name: 'Pixel 5' },
    desktop: { width: 1280, height: 720, name: 'Desktop' },
  };

  beforeEach(() => {
    cy.clearConsentStorage();
  });

  // ===========================================
  // Full-width banner on mobile (<768px)
  // ===========================================
  describe('Full-width banner on mobile viewports', () => {
    Object.entries(devices).forEach(([key, device]) => {
      if (device.width < 768) {
        it(`should display full-width banner on ${device.name} (${device.width}x${device.height})`, () => {
          cy.viewport(device.width, device.height);
          cy.visit('/demo.html');
          cy.get('#consentpro-banner').should('have.class', 'consentpro--visible');

          // Banner should span full width
          cy.get('.consentpro').then(($banner) => {
            const bannerWidth = $banner[0].getBoundingClientRect().width;
            expect(bannerWidth).to.equal(device.width);
          });
        });
      }
    });
  });

  // ===========================================
  // Stacked buttons (vertical) on mobile
  // ===========================================
  describe('Stacked buttons on mobile', () => {
    Object.entries(devices).forEach(([key, device]) => {
      if (device.width < 768) {
        it(`should stack buttons vertically on ${device.name}`, () => {
          cy.viewport(device.width, device.height);
          cy.visit('/demo.html');
          cy.get('#consentpro-banner').should('have.class', 'consentpro--visible');

          // Get all action buttons and verify they're stacked
          cy.get('.consentpro__actions').then(($actions) => {
            const computedStyle = window.getComputedStyle($actions[0]);
            expect(computedStyle.flexDirection).to.equal('column');
          });

          // Verify buttons are stacked (each button's top should be below previous)
          cy.get('.consentpro__btn').then(($buttons) => {
            const positions = Array.from($buttons).map((btn) => btn.getBoundingClientRect().top);

            for (let i = 1; i < positions.length; i++) {
              expect(positions[i]).to.be.greaterThan(positions[i - 1]);
            }
          });
        });
      }
    });

    it('should display buttons horizontally on desktop', () => {
      cy.viewport(devices.desktop.width, devices.desktop.height);
      cy.visit('/demo.html');
      cy.get('#consentpro-banner').should('have.class', 'consentpro--visible');

      // Get all action buttons and verify they're in a row
      cy.get('.consentpro__actions').then(($actions) => {
        const computedStyle = window.getComputedStyle($actions[0]);
        // On desktop, flex-direction should be row (default) or wrap
        expect(computedStyle.flexDirection).to.not.equal('column');
      });
    });
  });

  // ===========================================
  // Layer 2 scrolls if content exceeds viewport
  // ===========================================
  describe('Layer 2 scrolling on mobile', () => {
    Object.entries(devices).forEach(([key, device]) => {
      if (device.width < 768) {
        it(`should allow scrolling in Layer 2 on ${device.name} when content exceeds viewport`, () => {
          cy.viewport(device.width, device.height);
          cy.visit('/demo.html');
          cy.get('#consentpro-banner').should('have.class', 'consentpro--visible');

          // Navigate to Layer 2
          cy.get('[data-action="settings"]').click();
          cy.get('.consentpro__settings-header').should('be.visible');

          // Check that the banner has overflow-y: auto
          cy.get('.consentpro').then(($banner) => {
            const computedStyle = window.getComputedStyle($banner[0]);
            expect(computedStyle.overflowY).to.equal('auto');
          });

          // Check categories list can scroll if needed
          cy.get('.consentpro__categories').then(($categories) => {
            const computedStyle = window.getComputedStyle($categories[0]);
            expect(computedStyle.overflowY).to.equal('auto');
          });

          // Verify all categories are accessible (can scroll to them)
          cy.get('.consentpro__category').each(($category) => {
            cy.wrap($category).scrollIntoView().should('be.visible');
          });
        });
      }
    });
  });

  // ===========================================
  // No horizontal scroll ever
  // ===========================================
  describe('No horizontal scroll', () => {
    Object.entries(devices).forEach(([key, device]) => {
      it(`should not have horizontal scroll on ${device.name}`, () => {
        cy.viewport(device.width, device.height);
        cy.visit('/demo.html');
        cy.get('#consentpro-banner').should('have.class', 'consentpro--visible');

        // Check banner doesn't cause horizontal overflow
        cy.get('.consentpro').then(($banner) => {
          const computedStyle = window.getComputedStyle($banner[0]);
          expect(computedStyle.overflowX).to.equal('hidden');
        });

        // Check document body doesn't have horizontal scroll
        cy.document().then((doc) => {
          const body = doc.body;
          const documentElement = doc.documentElement;

          // No horizontal scrollbar should exist
          expect(body.scrollWidth).to.be.lte(documentElement.clientWidth + 1); // +1 for rounding
        });

        // Navigate to Layer 2 and check again
        cy.get('[data-action="settings"]').click();
        cy.get('.consentpro__settings-header').should('be.visible');

        cy.document().then((doc) => {
          const body = doc.body;
          const documentElement = doc.documentElement;
          expect(body.scrollWidth).to.be.lte(documentElement.clientWidth + 1);
        });
      });
    });
  });

  // ===========================================
  // Screenshot capture for documentation
  // ===========================================
  describe('Screenshot capture', () => {
    Object.entries(devices).forEach(([key, device]) => {
      it(`should capture Layer 1 screenshot on ${device.name}`, () => {
        cy.viewport(device.width, device.height);
        cy.visit('/demo.html');
        cy.get('#consentpro-banner').should('have.class', 'consentpro--visible');

        // Wait for animations to complete
        cy.wait(400);

        cy.screenshot(`us-037a/layer1-${key}`, {
          capture: 'viewport',
          overwrite: true,
        });
      });

      it(`should capture Layer 2 screenshot on ${device.name}`, () => {
        cy.viewport(device.width, device.height);
        cy.visit('/demo.html');
        cy.get('#consentpro-banner').should('have.class', 'consentpro--visible');

        // Navigate to Layer 2
        cy.get('[data-action="settings"]').click();
        cy.get('.consentpro__settings-header').should('be.visible');

        // Wait for animations to complete
        cy.wait(400);

        cy.screenshot(`us-037a/layer2-${key}`, {
          capture: 'viewport',
          overwrite: true,
        });
      });
    });
  });

  // ===========================================
  // Touch target size verification
  // ===========================================
  describe('Touch target sizes (44px minimum)', () => {
    Object.entries(devices).forEach(([key, device]) => {
      if (device.width < 768) {
        it(`should have 44px minimum touch targets on ${device.name}`, () => {
          cy.viewport(device.width, device.height);
          cy.visit('/demo.html');
          cy.get('#consentpro-banner').should('have.class', 'consentpro--visible');

          // Check all buttons have min-height >= 44px
          cy.get('.consentpro__btn').each(($btn) => {
            const rect = $btn[0].getBoundingClientRect();
            expect(rect.height).to.be.gte(44);
          });

          // Navigate to Layer 2
          cy.get('[data-action="settings"]').click();
          cy.get('.consentpro__settings-header').should('be.visible');

          // Check toggles have min-height >= 44px
          cy.get('.consentpro__toggle').each(($toggle) => {
            const rect = $toggle[0].getBoundingClientRect();
            expect(rect.height).to.be.gte(44);
          });

          // Check back button
          cy.get('.consentpro__back').then(($back) => {
            const rect = $back[0].getBoundingClientRect();
            expect(rect.width).to.be.gte(44);
            expect(rect.height).to.be.gte(44);
          });
        });
      }
    });
  });

  // ===========================================
  // Footer toggle positioning on mobile
  // ===========================================
  describe('Footer toggle positioning', () => {
    it('should center footer toggle on mobile', () => {
      cy.viewport(devices.iPhoneSE.width, devices.iPhoneSE.height);
      cy.visit('/demo.html');

      // Give consent first
      cy.get('[data-action="accept"]').click();

      // Footer toggle should be centered
      cy.get('.consentpro-footer-toggle').then(($toggle) => {
        const rect = $toggle[0].getBoundingClientRect();
        const viewportCenter = devices.iPhoneSE.width / 2;
        const toggleCenter = rect.left + rect.width / 2;

        // Should be approximately centered (within 5px tolerance)
        expect(Math.abs(toggleCenter - viewportCenter)).to.be.lessThan(5);
      });
    });

    it('should position footer toggle at bottom-left on desktop', () => {
      cy.viewport(devices.desktop.width, devices.desktop.height);
      cy.visit('/demo.html');

      // Give consent first
      cy.get('[data-action="accept"]').click();

      // Footer toggle should be at bottom-left
      cy.get('.consentpro-footer-toggle').then(($toggle) => {
        const rect = $toggle[0].getBoundingClientRect();

        // Should be near left edge (within 20px of left edge)
        expect(rect.left).to.be.lessThan(30);
      });
    });
  });
});
