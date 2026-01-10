import type { ConsentCategories, ConsentEventDetail, ScriptBlockerOptions } from './types';

const ATTR = 'data-consentpro';

export class ScriptBlocker {
  private _options: Required<ScriptBlockerOptions>;
  private _executedScripts = new WeakSet<HTMLScriptElement>();
  private _observer: MutationObserver | null = null;
  private _currentConsent: ConsentCategories | null = null;
  // eslint-disable-next-line no-unused-vars
  private _boundHandleConsentEvent: (event: Event) => void;
  private _initialized = false;

  constructor(options?: ScriptBlockerOptions) {
    this._options = {
      attributeName: ATTR,
      observeDynamicScripts: true,
      observeRoot: typeof document !== 'undefined' ? document.documentElement : null,
      ...options,
    } as Required<ScriptBlockerOptions>;
    this._boundHandleConsentEvent = this._handleConsentEvent.bind(this);
  }

  init(categories: ConsentCategories): void {
    if (this._initialized) return;
    this._currentConsent = categories;
    this._initialized = true;
    this.processBlockedScripts();
    document.addEventListener('consentpro_consent', this._boundHandleConsentEvent);
    if (this._options.observeDynamicScripts) this.startObserver();
  }

  processBlockedScripts(): void {
    this.getBlockedScripts().forEach((s) => this.unblockScript(s));
  }

  unblockScript(script: HTMLScriptElement): boolean {
    if (this._executedScripts.has(script)) return false;
    const cat = script.getAttribute(this._options.attributeName) as keyof ConsentCategories | null;
    if (!cat || !this.isCategoryConsented(cat)) return false;
    this._executeScript(script);
    return true;
  }

  getBlockedScripts(): HTMLScriptElement[] {
    return Array.from(
      document.querySelectorAll<HTMLScriptElement>(
        `script[type="text/plain"][${this._options.attributeName}]`
      )
    );
  }

  isCategoryConsented(category: keyof ConsentCategories): boolean {
    return category === 'essential' || this._currentConsent?.[category] === true;
  }

  updateConsent(categories: ConsentCategories): void {
    this._currentConsent = categories;
    this.processBlockedScripts();
  }

  startObserver(): void {
    if (this._observer) return;
    this._observer = new MutationObserver(this._handleMutations.bind(this));
    this._observer.observe(this._options.observeRoot, { childList: true, subtree: true });
  }

  stopObserver(): void {
    this._observer?.disconnect();
    this._observer = null;
  }

  destroy(): void {
    this.stopObserver();
    document.removeEventListener('consentpro_consent', this._boundHandleConsentEvent);
    this._currentConsent = null;
    this._initialized = false;
  }

  private _handleConsentEvent(event: Event): void {
    const e = event as CustomEvent<ConsentEventDetail>;
    if (e.detail?.categories) this.updateConsent(e.detail.categories);
  }

  private _handleMutations(mutations: MutationRecord[]): void {
    const sel = `script[type="text/plain"][${this._options.attributeName}]`;
    for (const m of mutations) {
      if (m.type !== 'childList') continue;
      for (const n of m.addedNodes) {
        if (
          n instanceof HTMLScriptElement &&
          n.type === 'text/plain' &&
          n.hasAttribute(this._options.attributeName)
        ) {
          this.unblockScript(n);
        }
        if (n instanceof Element) {
          n.querySelectorAll<HTMLScriptElement>(sel).forEach((s) => this.unblockScript(s));
        }
      }
    }
  }

  private _executeScript(original: HTMLScriptElement): void {
    this._executedScripts.add(original);
    const s = document.createElement('script');
    Array.from(original.attributes).forEach((a) => {
      if (a.name !== 'type' && a.name !== this._options.attributeName)
        s.setAttribute(a.name, a.value);
    });
    if (original.src) {
      s.async = false;
      s.src = original.src;
    } else {
      s.textContent = original.textContent;
    }
    original.parentNode
      ? original.parentNode.replaceChild(s, original)
      : document.body.appendChild(s);
  }
}
