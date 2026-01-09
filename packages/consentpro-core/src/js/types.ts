/**
 * Consent data structure stored in localStorage/cookie
 */
export interface ConsentData {
  /** Schema version for migrations */
  version: number;
  /** Unix timestamp (ms) when consent was given */
  timestamp: number;
  /** Detected region: 'EU', 'CA', or null */
  geo: 'EU' | 'CA' | null;
  /** Category consent states */
  categories: ConsentCategories;
  /** SHA-256 hash of settings (detect config changes -> re-consent) */
  hash: string;
}

/**
 * Consent categories with boolean states
 */
export interface ConsentCategories {
  /** Always true, immutable - required for site functionality */
  essential: true;
  /** Analytics tracking (e.g., Google Analytics) */
  analytics: boolean;
  /** Marketing/advertising (e.g., Meta Pixel) */
  marketing: boolean;
  /** Personalization/preferences */
  personalization: boolean;
}

/**
 * Banner configuration passed from server via data-config attribute
 */
export interface BannerConfig {
  /** Detected geo region */
  geo: 'EU' | 'CA' | null;
  /** Whether geo-targeting is enabled */
  geoEnabled: boolean;
  /** Privacy policy URL */
  policyUrl: string;
  /** Category definitions with labels and descriptions */
  categories: CategoryDefinition[];
  /** UI text customization */
  text: BannerText;
  /** Color scheme */
  colors: BannerColors;
}

/**
 * Category definition for settings panel
 */
export interface CategoryDefinition {
  id: keyof ConsentCategories;
  name: string;
  description: string;
  required: boolean;
}

/**
 * Customizable banner text
 */
export interface BannerText {
  heading: string;
  description: string;
  acceptAll: string;
  rejectNonEssential: string;
  settings: string;
  save: string;
  back: string;
}

/**
 * Banner color scheme
 */
export interface BannerColors {
  primary: string;
  secondary: string;
  background: string;
  text: string;
}

/**
 * Custom event detail for consent events
 */
export interface ConsentEventDetail {
  categories: ConsentCategories;
  timestamp: number;
  geo: 'EU' | 'CA' | null;
}
