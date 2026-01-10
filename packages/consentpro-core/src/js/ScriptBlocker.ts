import type { ConsentCategories, ConsentEventDetail, ScriptBlockerOptions } from './types';

/**
 * Default options for ScriptBlocker (observeRoot deferred for SSR safety)
 */
const DEFAULT_OPTIONS: Omit<Required<ScriptBlockerOptions>, 'observeRoot'> & {
  observeRoot: Element | null;
} = {
  attributeName: 'data-consentpro',
  observeDynamicScripts: true,
  observeRoot: null, // Deferred to constructor/runtime to avoid SSR issues
};

/**
 * Manages script blocking and unblocking based on consent categories.
 *
 * Scripts with type="text/plain" and data-consentpro="category" stay inert
 * until the corresponding category is consented.
 *
 * @example
 * ```html
 * <!-- In HTML: blocked script -->
 * <script type="text/plain" data-consentpro="analytics" src="https://ga.js"></script>
 * ```
 *
 * @example
 * ```typescript
 * // Initialize with current consent
 * const blocker = new ScriptBlocker();
 * blocker.init({ essential: true, analytics: true, marketing: false, personalization: false });
 * ```
 */
export class ScriptBlocker {
  private _options: Required<ScriptBlockerOptions>;
  private _executedScripts: WeakSet<HTMLScriptElement>;
  private _observer: MutationObserver | null = null;
  private _currentConsent: ConsentCategories | null = null;
  // eslint-disable-next-line no-unused-vars
  private _boundHandleConsentEvent: (event: Event) => void;
  private _initialized = false;

  constructor(options?: ScriptBlockerOptions) {
    // Set observeRoot at runtime for SSR safety
    const runtimeDefaults = {
      ...DEFAULT_OPTIONS,
      observeRoot: typeof document !== 'undefined' ? document.documentElement : null,
    };
    this._options = { ...runtimeDefaults, ...options } as Required<ScriptBlockerOptions>;
    this._executedScripts = new WeakSet();
    this._boundHandleConsentEvent = this._handleConsentEvent.bind(this);
  }

  /**
   * Initialize script blocker with current consent state.
   * Processes existing blocked scripts and sets up event listeners.
   *
   * @param categories - Current consent categories
   */
  init(categories: ConsentCategories): void {
    if (this._initialized) {
      return;
    }

    this._currentConsent = categories;
    this._initialized = true;

    // Process existing blocked scripts
    this.processBlockedScripts();

    // Listen for future consent changes
    document.addEventListener('consentpro_consent', this._boundHandleConsentEvent);

    // Start observing for dynamically added scripts
    if (this._options.observeDynamicScripts) {
      this.startObserver();
    }
  }

  /**
   * Process all blocked scripts in the DOM based on current consent.
   * Called automatically by init() and on consent events.
   */
  processBlockedScripts(): void {
    const scripts = this.getBlockedScripts();

    // Process in DOM order (querySelectorAll returns elements in document order)
    scripts.forEach((script) => {
      this.unblockScript(script);
    });
  }

  /**
   * Unblock a single script element if its category is consented.
   * Creates a new script element and replaces the original.
   *
   * @param script - The blocked script element
   * @returns true if script was unblocked, false otherwise
   */
  unblockScript(script: HTMLScriptElement): boolean {
    // Skip if already executed
    if (this._executedScripts.has(script)) {
      return false;
    }

    const category = script.getAttribute(this._options.attributeName) as
      | keyof ConsentCategories
      | null;

    if (!category) {
      return false;
    }

    // Check if category is consented
    if (!this.isCategoryConsented(category)) {
      return false;
    }

    // Execute the script
    this._executeScript(script);
    return true;
  }

  /**
   * Get all blocked scripts in the DOM.
   *
   * @returns Array of blocked script elements
   */
  getBlockedScripts(): HTMLScriptElement[] {
    const selector = `script[type="text/plain"][${this._options.attributeName}]`;
    return Array.from(document.querySelectorAll<HTMLScriptElement>(selector));
  }

  /**
   * Check if a category is consented.
   * Essential category is always considered consented.
   *
   * @param category - Category to check
   * @returns true if consented
   */
  isCategoryConsented(category: keyof ConsentCategories): boolean {
    // Essential scripts always execute
    if (category === 'essential') {
      return true;
    }

    if (!this._currentConsent) {
      return false;
    }

    return this._currentConsent[category] === true;
  }

  /**
   * Update consent state and process any newly consented scripts.
   *
   * @param categories - New consent categories
   */
  updateConsent(categories: ConsentCategories): void {
    this._currentConsent = categories;
    this.processBlockedScripts();
  }

  /**
   * Start observing for dynamically added scripts.
   * Called automatically by init() if observeDynamicScripts is true.
   */
  startObserver(): void {
    if (this._observer) {
      return;
    }

    this._observer = new MutationObserver(this._handleMutations.bind(this));
    this._observer.observe(this._options.observeRoot, {
      childList: true,
      subtree: true,
    });
  }

  /**
   * Stop observing for dynamic scripts.
   */
  stopObserver(): void {
    if (this._observer) {
      this._observer.disconnect();
      this._observer = null;
    }
  }

  /**
   * Clean up resources (observer, event listeners).
   */
  destroy(): void {
    this.stopObserver();
    document.removeEventListener('consentpro_consent', this._boundHandleConsentEvent);
    this._currentConsent = null;
    this._initialized = false;
  }

  /**
   * Handle consent event - update consent state and process scripts.
   * @private
   */
  private _handleConsentEvent(event: Event): void {
    const customEvent = event as CustomEvent<ConsentEventDetail>;
    if (customEvent.detail?.categories) {
      this.updateConsent(customEvent.detail.categories);
    }
  }

  /**
   * Handle mutations - process newly added script elements.
   * @private
   */
  private _handleMutations(mutations: MutationRecord[]): void {
    for (const mutation of mutations) {
      if (mutation.type !== 'childList') {
        continue;
      }

      for (const node of mutation.addedNodes) {
        // Check if the added node is a blocked script
        if (this._isBlockedScript(node)) {
          this.unblockScript(node as HTMLScriptElement);
        }

        // Check if the added node contains blocked scripts
        if (node instanceof Element) {
          const scripts = node.querySelectorAll<HTMLScriptElement>(
            `script[type="text/plain"][${this._options.attributeName}]`
          );
          scripts.forEach((script) => {
            this.unblockScript(script);
          });
        }
      }
    }
  }

  /**
   * Check if a node is a blocked script element.
   * @private
   */
  private _isBlockedScript(node: Node): boolean {
    return (
      node instanceof HTMLScriptElement &&
      node.type === 'text/plain' &&
      node.hasAttribute(this._options.attributeName)
    );
  }

  /**
   * Execute a script by creating a new script element.
   * Handles both inline and external scripts.
   * @private
   */
  private _executeScript(original: HTMLScriptElement): void {
    // Mark as executed before creating new script to prevent re-execution
    this._executedScripts.add(original);

    const newScript = document.createElement('script');

    // Copy attributes except type and data-consentpro
    Array.from(original.attributes).forEach((attr) => {
      if (attr.name !== 'type' && attr.name !== this._options.attributeName) {
        newScript.setAttribute(attr.name, attr.value);
      }
    });

    // Handle external vs inline scripts
    if (original.src) {
      // External script - set async=false BEFORE src to preserve DOM order execution
      // (dynamically inserted scripts default to async=true in browsers)
      newScript.async = false;
      newScript.src = original.src;
    } else {
      // Inline script - copy content
      newScript.textContent = original.textContent;
    }

    // Replace original with new script (triggers execution)
    if (original.parentNode) {
      original.parentNode.replaceChild(newScript, original);
    } else {
      // If no parent, append to body
      document.body.appendChild(newScript);
    }
  }
}
