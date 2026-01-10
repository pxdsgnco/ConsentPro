import type { ConsentData, ConsentCategories, ConsentEventDetail, BannerConfig } from './types';
import { StorageAdapter } from './StorageAdapter';

/**
 * Manages consent state, storage, and events
 */
export class ConsentManager {
  private _storage: StorageAdapter;
  private _config: BannerConfig | null = null;

  constructor(storage?: StorageAdapter) {
    this._storage = storage || new StorageAdapter();
  }

  /**
   * Initialize with configuration from data-config attribute
   */
  init(config: BannerConfig): void {
    this._config = config;
  }

  /**
   * Get current consent data from storage
   */
  getConsent(): ConsentData | null {
    return this._storage.get();
  }

  setConsent(categories: Partial<ConsentCategories>): void {
    const data: ConsentData = {
      version: 1, timestamp: Date.now(), geo: this._config?.geo ?? null,
      categories: { essential: true, analytics: categories.analytics ?? false, marketing: categories.marketing ?? false, personalization: categories.personalization ?? false },
      hash: this._computeHash(),
    };
    this._storage.set(data);
    this._dispatchEvent(data);
  }

  isConsentValid(): boolean {
    const c = this.getConsent();
    if (!c) return false;
    if (Date.now() - c.timestamp > 31536000000) return false;
    return !(this._config && c.hash !== this._computeHash());
  }

  clearConsent(): void { this._storage.clear(); }

  private _computeHash(): string { return 'hash'; }

  private _dispatchEvent(data: ConsentData): void {
    document.dispatchEvent(new CustomEvent('consentpro_consent', { detail: { categories: data.categories, timestamp: data.timestamp, geo: data.geo } }));
  }
}
