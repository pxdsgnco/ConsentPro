import type { BannerConfig, ConsentCategories } from './types';
import { ConsentManager } from './ConsentManager';

/**
 * DOM rendering for Layer 1 (quick actions) and Layer 2 (settings panel)
 */
export class BannerUI {
  private _container: HTMLElement | null = null;
  private _config: BannerConfig | null = null;
  private _manager: ConsentManager;
  private _currentLayer: 1 | 2 = 1;

  constructor(manager: ConsentManager) {
    this._manager = manager;
  }

  /**
   * Initialize banner with configuration
   */
  init(containerId: string, config: BannerConfig): void {
    this._container = document.getElementById(containerId);
    this._config = config;
    this._manager.init(config);

    // Dispatch ready event
    document.dispatchEvent(
      new CustomEvent('consentpro_ready', {
        detail: { config },
      })
    );
  }

  /**
   * Show the banner (Layer 1)
   */
  show(): void {
    if (!this._container) return;
    this._currentLayer = 1;
    this._renderLayer1();
    this._container.classList.add('consentpro--visible');
  }

  /**
   * Hide the banner
   */
  hide(): void {
    if (!this._container) return;
    this._container.classList.remove('consentpro--visible');
  }

  /**
   * Get current consent state
   */
  getConsent(): ReturnType<ConsentManager['getConsent']> {
    return this._manager.getConsent();
  }

  /**
   * Render Layer 1 - Quick actions banner
   */
  private _renderLayer1(): void {
    // Placeholder - implement in US-006a
    // Will render: heading, description, Accept All, Reject Non-Essential, Settings buttons
  }

  /**
   * Render Layer 2 - Settings panel with toggles
   */
  private _renderLayer2(): void {
    // Placeholder - implement in US-007a
    // Will render: category list with toggles, Save Preferences, Back buttons
  }

  /**
   * Switch to Layer 2 settings panel
   */
  private _showSettings(): void {
    this._currentLayer = 2;
    this._renderLayer2();
  }

  /**
   * Switch back to Layer 1
   */
  private _showBanner(): void {
    this._currentLayer = 1;
    this._renderLayer1();
  }

  /**
   * Handle Accept All button click
   */
  private _handleAcceptAll(): void {
    this._manager.setConsent({
      essential: true,
      analytics: true,
      marketing: true,
      personalization: true,
    });
    this.hide();
  }

  /**
   * Handle Reject Non-Essential button click
   */
  private _handleRejectNonEssential(): void {
    this._manager.setConsent({
      essential: true,
      analytics: false,
      marketing: false,
      personalization: false,
    });
    this.hide();
  }

  /**
   * Handle Save Preferences from Layer 2
   */
  private _handleSavePreferences(_categories: Partial<ConsentCategories>): void {
    this._manager.setConsent(_categories);
    this.hide();
  }
}
