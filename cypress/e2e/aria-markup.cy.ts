/// <reference types="cypress" />

/**
 * US-039a: ARIA Markup Audit
 *
 * Acceptance Criteria:
 * - role="dialog" on container
 * - aria-labelledby points to heading ID
 * - aria-modal="false" (non-blocking)
 * - Toggles: role="switch", aria-checked
 * - Buttons: meaningful labels
 *
 * Story-Specific DoD: aXe DevTools scan passes
 */
describe('US-039a: ARIA Markup Audit', () => {
  beforeEach(() => {
    cy.clearConsentStorage();
    cy.visit('/demo.html');
    cy.get('#consentpro-banner').should('have.class', 'consentpro--visible');
  });

  // ===========================================
  // role="dialog" on container
  // ===========================================
  describe('Dialog role on container', () => {
    it('should have role="dialog" on banner container', () => {
      cy.get('#consentpro-banner').should('have.attr', 'role', 'dialog');
    });

    it('should maintain role="dialog" in Layer 2', () => {
      cy.get('[data-action="settings"]').click();
      cy.get('.consentpro__settings-header').should('be.visible');

      cy.get('#consentpro-banner').should('have.attr', 'role', 'dialog');
    });
  });

  // ===========================================
  // aria-labelledby points to heading ID
  // ===========================================
  describe('aria-labelledby references heading', () => {
    it('should have aria-labelledby pointing to heading in Layer 1', () => {
      cy.get('#consentpro-banner').should('have.attr', 'aria-labelledby', 'consentpro-heading');

      // Verify the heading exists with that ID
      cy.get('#consentpro-heading').should('exist').and('be.visible');
    });

    it('should update aria-labelledby for Layer 2 settings title', () => {
      cy.get('[data-action="settings"]').click();
      cy.get('.consentpro__settings-header').should('be.visible');

      cy.get('#consentpro-banner').should(
        'have.attr',
        'aria-labelledby',
        'consentpro-settings-title'
      );

      // Verify the settings title exists
      cy.get('#consentpro-settings-title').should('exist').and('be.visible');
    });
  });

  // ===========================================
  // aria-modal="false" (non-blocking)
  // ===========================================
  describe('aria-modal is false (non-blocking)', () => {
    it('should have aria-modal="false" in Layer 1', () => {
      cy.get('#consentpro-banner').should('have.attr', 'aria-modal', 'false');
    });

    it('should have aria-modal="false" in Layer 2', () => {
      cy.get('[data-action="settings"]').click();
      cy.get('.consentpro__settings-header').should('be.visible');

      cy.get('#consentpro-banner').should('have.attr', 'aria-modal', 'false');
    });
  });

  // ===========================================
  // aria-describedby for additional context
  // ===========================================
  describe('aria-describedby provides context', () => {
    it('should have aria-describedby in Layer 1', () => {
      cy.get('#consentpro-banner').should(
        'have.attr',
        'aria-describedby',
        'consentpro-description'
      );

      // Verify the description exists
      cy.get('#consentpro-description').should('exist');
    });

    it('should have aria-describedby in Layer 2', () => {
      cy.get('[data-action="settings"]').click();
      cy.get('.consentpro__settings-header').should('be.visible');

      cy.get('#consentpro-banner').should(
        'have.attr',
        'aria-describedby',
        'consentpro-settings-desc'
      );

      // Verify the description exists (visually hidden)
      cy.get('#consentpro-settings-desc').should('exist');
    });
  });

  // ===========================================
  // Toggles: role="switch", aria-checked
  // ===========================================
  describe('Toggle switches have proper ARIA', () => {
    beforeEach(() => {
      cy.get('[data-action="settings"]').click();
      cy.get('.consentpro__settings-header').should('be.visible');
    });

    it('should have role="switch" on all category toggles', () => {
      cy.get('.consentpro__toggle').each(($toggle) => {
        cy.wrap($toggle).should('have.attr', 'role', 'switch');
      });
    });

    it('should have aria-checked attribute on all toggles', () => {
      cy.get('.consentpro__toggle').each(($toggle) => {
        cy.wrap($toggle).should('have.attr', 'aria-checked');
      });
    });

    it('should have aria-checked="true" on essential toggle', () => {
      cy.get('[data-category="essential"]').should('have.attr', 'aria-checked', 'true');
    });

    it('should have aria-checked="false" on non-essential toggles by default', () => {
      cy.get('[data-category="analytics"]').should('have.attr', 'aria-checked', 'false');
      cy.get('[data-category="marketing"]').should('have.attr', 'aria-checked', 'false');
      cy.get('[data-category="personalization"]').should('have.attr', 'aria-checked', 'false');
    });

    it('should update aria-checked when toggle is clicked', () => {
      cy.get('[data-category="analytics"]').should('have.attr', 'aria-checked', 'false');

      cy.get('[data-category="analytics"]').click();

      cy.get('[data-category="analytics"]').should('have.attr', 'aria-checked', 'true');
    });

    it('should have aria-labelledby pointing to category name', () => {
      cy.get('.consentpro__toggle').each(($toggle) => {
        const category = $toggle.attr('data-category');
        cy.wrap($toggle).should('have.attr', 'aria-labelledby', `category-${category}-label`);

        // Verify the label exists
        cy.get(`#category-${category}-label`).should('exist');
      });
    });

    it('should have aria-describedby pointing to category description', () => {
      cy.get('.consentpro__toggle').each(($toggle) => {
        const category = $toggle.attr('data-category');
        cy.wrap($toggle).should('have.attr', 'aria-describedby', `category-${category}-desc`);

        // Verify the description exists
        cy.get(`#category-${category}-desc`).should('exist');
      });
    });
  });

  // ===========================================
  // Buttons have meaningful labels
  // ===========================================
  describe('Buttons have meaningful labels', () => {
    it('should have visible text labels on Layer 1 buttons', () => {
      cy.get('[data-action="accept"]').should('contain.text', 'Accept');
      cy.get('[data-action="reject"]').should('contain.text', 'Reject');
      cy.get('[data-action="settings"]').should('contain.text', 'Settings');
    });

    it('should have aria-label on back button (icon-only)', () => {
      cy.get('[data-action="settings"]').click();
      cy.get('.consentpro__settings-header').should('be.visible');

      cy.get('.consentpro__back').should('have.attr', 'aria-label').and('not.be.empty');
    });

    it('should have visible text label on Save button', () => {
      cy.get('[data-action="settings"]').click();
      cy.get('.consentpro__settings-header').should('be.visible');

      cy.get('[data-action="save"]').should('contain.text', 'Save');
    });

    it('should have aria-label on footer toggle', () => {
      // Give consent first
      cy.get('[data-action="accept"]').click();

      cy.get('.consentpro-footer-toggle')
        .should('have.attr', 'aria-label')
        .and('contain', 'privacy');
    });
  });

  // ===========================================
  // Icons are hidden from screen readers
  // ===========================================
  describe('Icons are hidden from assistive technology', () => {
    it('should have aria-hidden="true" on SVG icons', () => {
      cy.get('[data-action="settings"]').click();
      cy.get('.consentpro__settings-header').should('be.visible');

      // Back button SVG should be hidden
      cy.get('.consentpro__back svg').should('have.attr', 'aria-hidden', 'true');
    });

    it('should have aria-hidden="true" on footer toggle icon', () => {
      // Give consent first
      cy.get('[data-action="accept"]').click();

      cy.get('.consentpro-footer-toggle svg').should('have.attr', 'aria-hidden', 'true');
    });
  });

  // ===========================================
  // Live region for announcements
  // ===========================================
  describe('Live region for screen reader announcements', () => {
    it('should have aria-live region in Layer 1', () => {
      cy.get('#consentpro-live-region')
        .should('exist')
        .should('have.attr', 'aria-live', 'polite')
        .should('have.attr', 'aria-atomic', 'true');
    });

    it('should have aria-live region in Layer 2', () => {
      cy.get('[data-action="settings"]').click();
      cy.get('.consentpro__settings-header').should('be.visible');

      cy.get('#consentpro-live-region')
        .should('exist')
        .should('have.attr', 'aria-live', 'polite')
        .should('have.attr', 'aria-atomic', 'true');
    });

    it('should announce "Preferences saved" when saving', () => {
      cy.get('[data-action="accept"]').click();

      // Check the live region was updated
      // Note: The banner hides quickly, so we check before it's removed
      cy.get('#consentpro-live-region').should('contain.text', 'Preferences saved');
    });
  });

  // ===========================================
  // Button group semantics
  // ===========================================
  describe('Button group semantics', () => {
    it('should have role="group" on actions container with aria-label', () => {
      cy.get('.consentpro__actions')
        .should('have.attr', 'role', 'group')
        .should('have.attr', 'aria-label')
        .and('not.be.empty');
    });
  });

  // ===========================================
  // Categories list semantics
  // ===========================================
  describe('Categories list semantics', () => {
    it('should have role="list" on categories container', () => {
      cy.get('[data-action="settings"]').click();
      cy.get('.consentpro__settings-header').should('be.visible');

      cy.get('.consentpro__categories').should('have.attr', 'role', 'list');
    });

    it('should use list items for categories', () => {
      cy.get('[data-action="settings"]').click();
      cy.get('.consentpro__settings-header').should('be.visible');

      cy.get('.consentpro__categories li.consentpro__category').should('have.length', 4);
    });
  });

  // ===========================================
  // Disabled state accessibility
  // ===========================================
  describe('Disabled state accessibility', () => {
    it('should have disabled attribute on essential toggle', () => {
      cy.get('[data-action="settings"]').click();
      cy.get('.consentpro__settings-header').should('be.visible');

      cy.get('[data-category="essential"]').should('be.disabled');
    });

    it('should have (Always active) indicator for essential category', () => {
      cy.get('[data-action="settings"]').click();
      cy.get('.consentpro__settings-header').should('be.visible');

      cy.get('.consentpro__category-required').should('contain.text', 'Always active');
    });
  });

  // ===========================================
  // Link accessibility
  // ===========================================
  describe('Link accessibility', () => {
    it('should have target="_blank" with rel="noopener" on external links', () => {
      cy.get('.consentpro__link')
        .should('have.attr', 'target', '_blank')
        .should('have.attr', 'rel')
        .and('contain', 'noopener');
    });

    it('should have proper link text for Learn more', () => {
      cy.get('.consentpro__link').should('contain.text', 'Learn more');
    });

    it('should have proper link text for Privacy Policy', () => {
      cy.get('[data-action="settings"]').click();
      cy.get('.consentpro__settings-header').should('be.visible');

      cy.get('.consentpro__policy-link').should('contain.text', 'Privacy Policy');
    });
  });

  // ===========================================
  // Headings hierarchy
  // ===========================================
  describe('Headings hierarchy', () => {
    it('should use h2 for main heading in Layer 1', () => {
      cy.get('#consentpro-heading').should('match', 'h2');
    });

    it('should use h2 for settings title in Layer 2', () => {
      cy.get('[data-action="settings"]').click();
      cy.get('.consentpro__settings-header').should('be.visible');

      cy.get('#consentpro-settings-title').should('match', 'h2');
    });
  });
});
