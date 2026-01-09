import type { ConsentData } from './types';

const STORAGE_KEY = 'consentpro_consent';
const COOKIE_NAME = 'consentpro';
const MAX_AGE = 31536000; // 365 days

/**
 * Dual-write storage adapter for localStorage + cookie fallback
 * Safari ITP limits localStorage lifespan, so we use both mechanisms
 */
export class StorageAdapter {
  /**
   * Store consent data in localStorage and cookie
   */
  set(data: ConsentData): void {
    const json = JSON.stringify(data);

    // Primary: localStorage
    try {
      localStorage.setItem(STORAGE_KEY, json);
    } catch {
      // Quota exceeded or private mode - cookie fallback will handle it
    }

    // Fallback: Cookie for Safari ITP
    document.cookie = `${COOKIE_NAME}=${encodeURIComponent(json)}; max-age=${MAX_AGE}; path=/; SameSite=Lax`;
  }

  /**
   * Get consent data from localStorage or cookie fallback
   */
  get(): ConsentData | null {
    // Try localStorage first
    try {
      const ls = localStorage.getItem(STORAGE_KEY);
      if (ls) {
        return JSON.parse(ls) as ConsentData;
      }
    } catch {
      // Invalid JSON or blocked
    }

    // Fallback to cookie
    const cookie = this._getCookie(COOKIE_NAME);
    if (cookie) {
      try {
        return JSON.parse(decodeURIComponent(cookie)) as ConsentData;
      } catch {
        // Invalid JSON
      }
    }

    return null;
  }

  /**
   * Clear consent from both storage mechanisms
   */
  clear(): void {
    try {
      localStorage.removeItem(STORAGE_KEY);
    } catch {
      // Blocked
    }
    document.cookie = `${COOKIE_NAME}=; max-age=0; path=/;`;
  }

  /**
   * Get cookie value by name
   */
  private _getCookie(name: string): string | null {
    const match = document.cookie.match(new RegExp(`(^| )${name}=([^;]+)`));
    return match ? match[2] : null;
  }
}
