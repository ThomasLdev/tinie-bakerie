module.exports = {
  ci: {
    collect: {
      // URLs to test - using HTTPS for accurate scoring
      url: [
        'https://local.tinie-bakerie.com',
        'https://local.tinie-bakerie.com/fr/articles',
        'https://local.tinie-bakerie.com/fr/articles/maiores-cum-dolorem-eum/quam-nostrum-quos-non',
        'https://local.tinie-bakerie.com/fr/categories/facere-fuga-enim-sed',
      ],
      // Number of times to run Lighthouse on each URL
      numberOfRuns: 3,
      settings: {
        // Lighthouse defaults to mobile - no preset needed
        // Mobile simulates slow 4G network and mobile device
        // To explicitly configure mobile:
        formFactor: 'mobile',
        throttling: {
          rttMs: 150,
          throughputKbps: 1638.4,
          requestLatencyMs: 150,
          downloadThroughputKbps: 1638.4,
          uploadThroughputKbps: 675,
          cpuSlowdownMultiplier: 4,
        },
        screenEmulation: {
          mobile: true,
          width: 375,
          height: 667,
          deviceScaleFactor: 2,
          disabled: false,
        },
        // Chrome flags for CI environment
        // --ignore-certificate-errors: Accept self-signed certs in CI
        chromeFlags: '--no-sandbox --disable-gpu --ignore-certificate-errors',
      },
    },
    assert: {
      // Start with recommended preset
      // You can customize assertions based on your needs
      preset: 'lighthouse:recommended',
      assertions: {
        // Example: Relax some assertions if needed
        // 'categories:performance': ['error', {minScore: 0.9}],
        // 'categories:accessibility': ['error', {minScore: 0.9}],
        // 'categories:best-practices': ['error', {minScore: 0.9}],
        // 'categories:seo': ['error', {minScore: 0.9}],
      },
    },
    upload: {
      // Upload reports to temporary public storage
      // This is free and requires no setup, but reports are public and temporary
      target: 'temporary-public-storage',

      // Alternative: Use filesystem for local testing
      // target: 'filesystem',
      // outputDir: './lighthouse-results',

      // Alternative: Use LHCI server (requires server setup)
      // target: 'lhci',
      // serverBaseUrl: 'https://your-lhci-server.example.com',
      // token: process.env.LHCI_TOKEN, // Use environment variable for security
    },
  },
};
