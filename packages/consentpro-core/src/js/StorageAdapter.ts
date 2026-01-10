import type { ConsentData } from './types';

const STORAGE_KEY = 'consentpro_consent';
const COOKIE_NAME = 'consentpro';
const MAX_AGE = 31536000; // 365 days

/**
 * Dual-write storage: localStorage + cookie fallback for Safari ITP
 */
export class StorageAdapter {
  set(data: ConsentData): void {
    const json = JSON.stringify(data);
    try { localStorage.setItem(STORAGE_KEY, json); } catch { /* ignore */ }
    document.cookie = `${COOKIE_NAME}=${encodeURIComponent(json)}; max-age=${MAX_AGE}; path=/; SameSite=Lax`;
  }

  get(): ConsentData | null {
    try {
      const ls = localStorage.getItem(STORAGE_KEY);
      if (ls) return JSON.parse(ls) as ConsentData;
    } catch { /* ignore */ }
    const c = this._getCookie(COOKIE_NAME);
    if (c) try { return JSON.parse(decodeURIComponent(c)) as ConsentData; } catch { /* ignore */ }
    return null;
  }

  clear(): void {
    try { localStorage.removeItem(STORAGE_KEY); } catch { /* ignore */ }
    document.cookie = `${COOKIE_NAME}=; max-age=0; path=/;`;
  }

  private _getCookie(name: string): string | null {
    const m = document.cookie.match(new RegExp(`(^| )${name}=([^;]+)`));
    return m ? m[2] : null;
  }
}
