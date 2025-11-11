module.exports = {
  ci: {
    collect: {
      // URLs to test - add more pages as needed
      url: [
        'http://localhost/',
        'http://localhost/en/',
        'http://localhost/en/posts',
        'http://localhost/en/about',
      ],
      // Number of times to run Lighthouse on each URL
      numberOfRuns: 3,
      settings: {
        // Lighthouse settings
        preset: 'desktop',
        // Add Chrome flags if needed (e.g., --no-sandbox for CI environments)
        // chromeFlags: '--no-sandbox',
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
