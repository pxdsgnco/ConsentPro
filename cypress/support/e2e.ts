// Import custom commands
import './commands';

// Type imports for global declarations
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

// Global types for custom commands
declare global {
  namespace Cypress {
    interface Chainable {
      /**
       * Clear all consent storage (localStorage + cookie)
       */
      clearConsentStorage(): Chainable<void>;

      /**
       * Get stored consent data from localStorage
       */
      getStoredConsent(): Chainable<ConsentData | null>;

      /**
       * Get stored consent data from cookie (Safari ITP fallback)
       */
      getConsentCookie(): Chainable<ConsentData | null>;
    }
  }
}

// Prevent TypeScript errors
export {};
