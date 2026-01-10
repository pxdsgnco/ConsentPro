// Type definitions for consent data
interface ConsentCategories {
  essential: true;
  analytics: boolean;
  marketing: boolean;
  personalization: boolean;
}

interface ConsentData {
  version: number;
  timestamp: number;
  geo: 'EU' | 'CA' | null;
  categories: ConsentCategories;
  hash: string;
}

const STORAGE_KEY = 'consentpro_consent';
const COOKIE_NAME = 'consentpro';

/**
 * Clear all consent storage (localStorage and cookie)
 */
Cypress.Commands.add('clearConsentStorage', () => {
  cy.window().then((win) => {
    win.localStorage.removeItem(STORAGE_KEY);
  });
  cy.clearCookie(COOKIE_NAME);
});

/**
 * Get stored consent data from localStorage
 */
Cypress.Commands.add('getStoredConsent', () => {
  return cy.window().then((win) => {
    const stored = win.localStorage.getItem(STORAGE_KEY);
    if (stored) {
      try {
        return JSON.parse(stored) as ConsentData;
      } catch {
        return null;
      }
    }
    return null;
  });
});

/**
 * Get stored consent data from cookie (Safari ITP fallback)
 */
Cypress.Commands.add('getConsentCookie', () => {
  return cy.getCookie(COOKIE_NAME).then((cookie) => {
    if (cookie?.value) {
      try {
        return JSON.parse(decodeURIComponent(cookie.value)) as ConsentData;
      } catch {
        return null;
      }
    }
    return null;
  });
});
