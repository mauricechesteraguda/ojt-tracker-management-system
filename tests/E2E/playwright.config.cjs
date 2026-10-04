const path = require('path');

module.exports = {
  testDir: __dirname,
  testMatch: /ticket10_happy_flow\.spec\.js/,
  fullyParallel: false,
  workers: 1,
  retries: 0,
  timeout: 45000,
  expect: { timeout: 10000 },
  reporter: [['line']],
  outputDir: process.env.OJT_EVIDENCE_DIR || path.join(require('os').tmpdir(), 'ojt-ticket10-evidence'),
  use: {
    baseURL: process.env.OJT_BASE_URL || 'http://127.0.0.1:8088',
    headless: true,
    screenshot: 'off',
    video: 'off',
    trace: 'off',
  },
};
