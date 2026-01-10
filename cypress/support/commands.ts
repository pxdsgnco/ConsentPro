/// <reference types="cypress" />

/**
 * ConsentPro Cypress Custom Commands
 * Provides helper methods for testing consent storage functionality
 */

/**
 * Consent data structure matching core types
 */
interface ConsentData {
  version: number;
  timestamp: number;
  geo: 'EU' | 'CA' | null;
  categories: {
    essential: true;
    analytics: boolean;
    marketing: boolean;
    personalization: boolean;
  };
  hash: string;
}

declare global {
  // eslint-disable-next-line @typescript-eslint/no-namespace
  namespace Cypress {
    interface Chainable {
      /**
       * Clear all consent storage (localStorage and cookie)
       * @example cy.clearConsentStorage()
       */
      clearConsentStorage(): Chainable<void>;

      /**
       * Get stored consent from localStorage
       * @example cy.getStoredConsent().then((consent) => { ... })
       */
      getStoredConsent(): Chainable<ConsentData | null>;

      /**
       * Get consent from cookie (Safari ITP fallback)
       * @example cy.getConsentCookie().then((consent) => { ... })
       */
      getConsentCookie(): Chainable<ConsentData | null>;
    }
  }
}

const CONSENT_KEY = 'consentpro_consent';
const CONSENT_COOKIE = 'consentpro';

/**
 * Clear all consent storage (localStorage and cookie)
 */
Cypress.Commands.add('clearConsentStorage', () => {
  cy.window().then((win) => {
    // Clear localStorage
    win.localStorage.removeItem(CONSENT_KEY);

    // Clear cookie
    win.document.cookie = `${CONSENT_COOKIE}=; max-age=0; path=/;`;
  });
});

/**
 * Get stored consent from localStorage
 */
Cypress.Commands.add('getStoredConsent', () => {
  return cy.window().then((win) => {
    const stored = win.localStorage.getItem(CONSENT_KEY);
    if (!stored) return null;

    try {
      return JSON.parse(stored) as ConsentData;
    } catch {
      return null;
    }
  });
});

/**
 * Get consent from cookie (Safari ITP fallback)
 */
Cypress.Commands.add('getConsentCookie', () => {
  return cy.window().then((win) => {
    const cookies = win.document.cookie.split('; ');
    const consentCookie = cookies.find((row) => row.startsWith(`${CONSENT_COOKIE}=`));

    if (!consentCookie) return null;

    try {
      const value = consentCookie.split('=')[1];
      return JSON.parse(decodeURIComponent(value)) as ConsentData;
    } catch {
      return null;
    }
  });
});

export {};
