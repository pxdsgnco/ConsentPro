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

  /**
   * Set consent with given categories
   */
  setConsent(categories: Partial<ConsentCategories>): void {
    const data: ConsentData = {
      version: 1,
      timestamp: Date.now(),
      geo: this._config?.geo ?? null,
      categories: {
        essential: true, // Always true
        analytics: categories.analytics ?? false,
        marketing: categories.marketing ?? false,
        personalization: categories.personalization ?? false,
      },
      hash: this._computeHash(),
    };

    this._storage.set(data);
    this._dispatchEvent(data);
  }

  /**
   * Check if existing consent is valid (not expired, config unchanged)
   */
  isConsentValid(): boolean {
    const consent = this.getConsent();
    if (!consent) return false;

    // Check 12-month expiry
    const twelveMonths = 365 * 24 * 60 * 60 * 1000;
    if (Date.now() - consent.timestamp > twelveMonths) return false;

    // Check config hash match
    if (this._config && consent.hash !== this._computeHash()) return false;

    return true;
  }

  /**
   * Clear stored consent
   */
  clearConsent(): void {
    this._storage.clear();
  }

  /**
   * Compute SHA-256 hash of current config
   * Used to detect config changes that require re-consent
   */
  private _computeHash(): string {
    // Placeholder - implement SHA-256 in US-003
    // Will hash policyUrl + category definitions
    return 'hash';
  }

  /**
   * Dispatch consent event for script blockers to listen to
   */
  private _dispatchEvent(data: ConsentData): void {
    const detail: ConsentEventDetail = {
      categories: data.categories,
      timestamp: data.timestamp,
      geo: data.geo,
    };

    document.dispatchEvent(new CustomEvent('consentpro_consent', { detail }));
  }
}
