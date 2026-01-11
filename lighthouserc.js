/**
 * Lighthouse CI Configuration
 * US-040a: Performance audit configuration
 *
 * @see https://github.com/GoogleChrome/lighthouse-ci
 */
module.exports = {
  ci: {
    collect: {
      // Use the demo page for auditing
      url: ['http://localhost:3000/demo.html'],
      // Number of runs for statistical significance
      numberOfRuns: 3,
      // Start the dev server before running
      startServerCommand: 'pnpm --filter @consentpro/core serve-demo',
      startServerReadyPattern: 'Serving!',
      // Settings for the Lighthouse run
      settings: {
        // Only run performance audits for speed
        onlyCategories: ['performance', 'accessibility'],
        // Skip audits that don't apply to our use case
        skipAudits: ['uses-http2', 'redirects', 'uses-long-cache-ttl'],
      },
    },
    assert: {
      assertions: {
        // Performance score must be >= 90 (allowing <10 point impact)
        'categories:performance': ['error', { minScore: 0.9 }],
        // Accessibility score must be >= 95
        'categories:accessibility': ['error', { minScore: 0.95 }],
        // CLS must be 0 (no layout shift)
        'cumulative-layout-shift': ['error', { maxNumericValue: 0 }],
        // TBT contribution should be minimal
        'total-blocking-time': ['warn', { maxNumericValue: 150 }],
        // FCP should not be significantly impacted
        'first-contentful-paint': ['warn', { maxNumericValue: 2000 }],
      },
    },
    upload: {
      // Store results locally for now
      target: 'temporary-public-storage',
    },
  },
};
