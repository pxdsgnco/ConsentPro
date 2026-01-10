import type { BannerConfig, ConsentCategories, CategoryDefinition } from './types';
import { ConsentManager } from './ConsentManager';

/**
 * DOM rendering for Layer 1 (quick actions) and Layer 2 (settings panel)
 */
export class BannerUI {
  private _container: HTMLElement | null = null;
  private _config: BannerConfig | null = null;
  private _manager: ConsentManager;
  private _currentLayer: 1 | 2 = 1;
  private _footerToggle: HTMLButtonElement | null = null;
  private _triggerElement: Element | null = null;
  private _liveRegion: HTMLElement | null = null;
  // eslint-disable-next-line no-unused-vars
  private _boundEscapeHandler: ((e: KeyboardEvent) => void) | null = null;
  // eslint-disable-next-line no-unused-vars
  private _boundFocusTrapHandler: ((e: KeyboardEvent) => void) | null = null;

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

    // Store trigger element for focus return (US-014a)
    this._triggerElement = document.activeElement;

    this._currentLayer = 1;
    this._renderLayer1();
    this._container.classList.add('consentpro--visible');
    this._hideFooterToggle();

    // Set up keyboard handlers (US-014a)
    this._setupKeyboardHandlers();

    // Focus first interactive element
    this._focusFirstElement();
  }

  /**
   * Hide the banner
   */
  hide(): void {
    if (!this._container) return;
    this._container.classList.remove('consentpro--visible');
    this._showFooterToggle();

    // Clean up keyboard handlers (US-014a)
    this._removeKeyboardHandlers();

    // Return focus to trigger element (US-014a)
    this._returnFocus();
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
    if (!this._container || !this._config) return;

    const { text, policyUrl } = this._config;

    // Set ARIA attributes on container
    this._container.setAttribute('role', 'dialog');
    this._container.setAttribute('aria-labelledby', 'consentpro-heading');
    this._container.setAttribute('aria-describedby', 'consentpro-description');
    this._container.setAttribute('aria-modal', 'false');

    this._container.innerHTML = `
      <div class="consentpro__container">
        <div class="consentpro__content">
          <h2 id="consentpro-heading" class="consentpro__heading">
            ${this._escapeHtml(text.heading)}
          </h2>
          <p id="consentpro-description" class="consentpro__description">
            ${this._escapeHtml(text.description)}
            <a href="${this._escapeHtml(policyUrl)}" class="consentpro__link" target="_blank" rel="noopener">
              Learn more
            </a>
          </p>
        </div>
        <div class="consentpro__actions" role="group" aria-label="Cookie consent options">
          <button type="button" class="consentpro__btn consentpro__btn--secondary" data-action="reject">
            ${this._escapeHtml(text.rejectNonEssential)}
          </button>
          <button type="button" class="consentpro__btn consentpro__btn--secondary" data-action="settings">
            ${this._escapeHtml(text.settings)}
          </button>
          <button type="button" class="consentpro__btn consentpro__btn--primary" data-action="accept">
            ${this._escapeHtml(text.acceptAll)}
          </button>
        </div>
        <div class="visually-hidden" aria-live="polite" aria-atomic="true" id="consentpro-live-region"></div>
      </div>
    `;

    // Store reference to live region (US-014b)
    this._liveRegion = this._container.querySelector('#consentpro-live-region');

    this._attachEventListeners();
  }

  /**
   * Escape HTML special characters to prevent XSS
   */
  private _escapeHtml(str: string): string {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  }

  /**
   * Attach click event listeners to Layer 1 buttons
   */
  private _attachEventListeners(): void {
    if (!this._container) return;

    this._container
      .querySelector('[data-action="accept"]')
      ?.addEventListener('click', () => this._handleAcceptAll());

    this._container
      .querySelector('[data-action="reject"]')
      ?.addEventListener('click', () => this._handleRejectNonEssential());

    this._container
      .querySelector('[data-action="settings"]')
      ?.addEventListener('click', () => this._showSettings());
  }

  /**
   * Render Layer 2 - Settings panel with toggles
   */
  private _renderLayer2(): void {
    if (!this._container || !this._config) return;

    const { text, policyUrl, categories } = this._config;

    // Update ARIA attributes for settings panel
    this._container.setAttribute('aria-labelledby', 'consentpro-settings-title');
    this._container.setAttribute('aria-describedby', 'consentpro-settings-desc');

    const settingsTitle = text.settingsTitle || 'Privacy Preferences';

    this._container.innerHTML = `
      <div class="consentpro__container">
        <header class="consentpro__settings-header">
          <button type="button" class="consentpro__back" aria-label="${this._escapeHtml(text.back)}" data-action="back">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M19 12H5M12 19l-7-7 7-7"/>
            </svg>
          </button>
          <h2 id="consentpro-settings-title" class="consentpro__settings-title">
            ${this._escapeHtml(settingsTitle)}
          </h2>
        </header>
        <p id="consentpro-settings-desc" class="visually-hidden">
          Manage your cookie preferences by category. Essential cookies are always enabled.
        </p>
        <ul class="consentpro__categories" role="list">
          ${categories.map((cat) => this._renderCategory(cat)).join('')}
        </ul>
        <footer class="consentpro__settings-footer">
          <a href="${this._escapeHtml(policyUrl)}" class="consentpro__policy-link" target="_blank" rel="noopener noreferrer">
            Privacy Policy
          </a>
          <button type="button" class="consentpro__btn consentpro__btn--primary" data-action="save">
            ${this._escapeHtml(text.save)}
          </button>
        </footer>
        <div class="visually-hidden" aria-live="polite" aria-atomic="true" id="consentpro-live-region"></div>
      </div>
    `;

    // Store reference to live region (US-014b)
    this._liveRegion = this._container.querySelector('#consentpro-live-region');

    this._attachLayer2EventListeners();
  }

  /**
   * Render a single category toggle item
   */
  private _renderCategory(category: CategoryDefinition): string {
    const labelId = `category-${category.id}-label`;
    const descId = `category-${category.id}-desc`;
    const isRequired = category.required;
    const currentConsent = this._manager.getConsent();
    const isChecked = isRequired || (currentConsent?.categories[category.id] ?? false);

    return `
      <li class="consentpro__category">
        <div class="consentpro__category-info">
          <div class="consentpro__category-header">
            <span class="consentpro__category-name" id="${labelId}">${this._escapeHtml(category.name)}</span>
            ${isRequired ? '<span class="consentpro__category-required">(Always active)</span>' : ''}
          </div>
          <p class="consentpro__category-desc" id="${descId}">${this._escapeHtml(category.description)}</p>
        </div>
        <button
          type="button"
          role="switch"
          class="consentpro__toggle"
          aria-checked="${isChecked}"
          aria-labelledby="${labelId}"
          aria-describedby="${descId}"
          ${isRequired ? 'disabled' : ''}
          data-category="${category.id}"
        ></button>
      </li>
    `;
  }

  /**
   * Attach event listeners for Layer 2
   */
  private _attachLayer2EventListeners(): void {
    if (!this._container) return;

    // Back button
    this._container
      .querySelector('[data-action="back"]')
      ?.addEventListener('click', () => this._showBanner());

    // Save button
    this._container
      .querySelector('[data-action="save"]')
      ?.addEventListener('click', () => this._handleSaveFromToggles());

    // Toggle switches - click and keyboard
    this._container.querySelectorAll('.consentpro__toggle:not(:disabled)').forEach((toggle) => {
      toggle.addEventListener('click', () => this._handleToggleClick(toggle as HTMLButtonElement));
      toggle.addEventListener('keydown', (e) =>
        this._handleToggleKeydown(e as KeyboardEvent, toggle as HTMLButtonElement)
      );
    });
  }

  /**
   * Handle toggle click - flip aria-checked state
   */
  private _handleToggleClick(toggle: HTMLButtonElement): void {
    const isChecked = toggle.getAttribute('aria-checked') === 'true';
    toggle.setAttribute('aria-checked', String(!isChecked));
  }

  /**
   * Handle toggle keydown - Space/Enter to toggle
   */
  private _handleToggleKeydown(e: KeyboardEvent, toggle: HTMLButtonElement): void {
    if (e.key === ' ' || e.key === 'Enter') {
      e.preventDefault();
      this._handleToggleClick(toggle);
    }
  }

  /**
   * Collect toggle states and save preferences
   */
  private _handleSaveFromToggles(): void {
    const categories: Partial<ConsentCategories> = { essential: true };

    this._container?.querySelectorAll('.consentpro__toggle').forEach((toggle) => {
      const category = (toggle as HTMLElement).dataset.category as keyof ConsentCategories;
      const isChecked = toggle.getAttribute('aria-checked') === 'true';
      if (category !== 'essential') {
        categories[category] = isChecked;
      }
    });

    this._handleSavePreferences(categories);
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
    this._announce('Preferences saved');
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
    this._announce('Preferences saved');
    this.hide();
  }

  /**
   * Handle Save Preferences from Layer 2
   */
  private _handleSavePreferences(_categories: Partial<ConsentCategories>): void {
    this._manager.setConsent(_categories);
    this._announce('Preferences saved');
    this.hide();
  }

  /**
   * Render footer privacy toggle (shown after consent)
   */
  renderFooterToggle(containerId?: string): void {
    if (!this._config) return;

    const parent = containerId ? document.getElementById(containerId) : document.body;
    if (!parent) return;

    this._footerToggle = document.createElement('button');
    this._footerToggle.type = 'button';
    this._footerToggle.className = 'consentpro-footer-toggle consentpro-footer-toggle--hidden';
    this._footerToggle.setAttribute('aria-label', 'Manage privacy preferences');
    this._footerToggle.dataset.action = 'reopen';

    const text = this._config.text.footerToggle || 'Privacy Settings';
    this._footerToggle.innerHTML = `
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
      </svg>
      <span>${this._escapeHtml(text)}</span>
    `;

    this._footerToggle.addEventListener('click', () => this.show());
    parent.appendChild(this._footerToggle);
  }

  /**
   * Show footer toggle (after consent saved)
   */
  private _showFooterToggle(): void {
    this._footerToggle?.classList.remove('consentpro-footer-toggle--hidden');
  }

  /**
   * Hide footer toggle (when banner is open)
   */
  private _hideFooterToggle(): void {
    this._footerToggle?.classList.add('consentpro-footer-toggle--hidden');
  }

  /**
   * Set up keyboard event handlers (US-014a)
   * - Escape key to close without saving
   * - Focus trap to cycle Tab within banner
   */
  private _setupKeyboardHandlers(): void {
    // Escape key handler
    this._boundEscapeHandler = (event: KeyboardEvent): void => {
      if (event.key === 'Escape') {
        event.preventDefault();
        this.hide();
      }
    };
    document.addEventListener('keydown', this._boundEscapeHandler);

    // Focus trap handler
    this._boundFocusTrapHandler = (event: KeyboardEvent): void => {
      if (event.key === 'Tab') {
        this._handleFocusTrap(event);
      }
    };
    this._container?.addEventListener('keydown', this._boundFocusTrapHandler);
  }

  /**
   * Remove keyboard event handlers (US-014a)
   */
  private _removeKeyboardHandlers(): void {
    if (this._boundEscapeHandler) {
      document.removeEventListener('keydown', this._boundEscapeHandler);
      this._boundEscapeHandler = null;
    }
    if (this._boundFocusTrapHandler) {
      this._container?.removeEventListener('keydown', this._boundFocusTrapHandler);
      this._boundFocusTrapHandler = null;
    }
  }

  /**
   * Handle focus trap - cycle Tab/Shift+Tab within banner (US-014a)
   */
  private _handleFocusTrap(e: KeyboardEvent): void {
    if (!this._container) return;

    const focusableElements = this._getFocusableElements();
    if (focusableElements.length === 0) return;

    const firstElement = focusableElements[0];
    const lastElement = focusableElements[focusableElements.length - 1];
    const activeElement = document.activeElement;

    if (e.shiftKey) {
      // Shift+Tab: if at first element, wrap to last
      if (activeElement === firstElement) {
        e.preventDefault();
        lastElement.focus();
      }
    } else {
      // Tab: if at last element, wrap to first
      if (activeElement === lastElement) {
        e.preventDefault();
        firstElement.focus();
      }
    }
  }

  /**
   * Get all focusable elements within the banner (US-014a)
   */
  private _getFocusableElements(): HTMLElement[] {
    if (!this._container) return [];

    const selector =
      'button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';
    return Array.from(this._container.querySelectorAll<HTMLElement>(selector));
  }

  /**
   * Focus the first interactive element in the banner (US-014a)
   */
  private _focusFirstElement(): void {
    const focusableElements = this._getFocusableElements();
    if (focusableElements.length > 0) {
      // Small delay to ensure DOM is fully rendered
      requestAnimationFrame(() => {
        focusableElements[0].focus();
      });
    }
  }

  /**
   * Return focus to the trigger element that opened the banner (US-014a)
   */
  private _returnFocus(): void {
    if (this._triggerElement && this._triggerElement instanceof HTMLElement) {
      // Small delay to ensure banner hide animation completes
      requestAnimationFrame(() => {
        (this._triggerElement as HTMLElement).focus();
      });
    }
  }

  /**
   * Announce message to screen readers via live region (US-014b)
   */
  private _announce(message: string): void {
    if (this._liveRegion) {
      this._liveRegion.textContent = message;
    }
  }
}
