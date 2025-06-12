export default {
  preset: 'ts-jest/presets/default-esm', // Use ESM preset for ts-jest
  testEnvironment: 'node',
  transform: {
    // '^.+\\.js$': 'babel-jest', // Keep if you have JS files needing Babel
    '^.+\\.ts$': ['ts-jest', { useESM: true }], // Configure ts-jest for ESM
  },
  moduleFileExtensions: ['ts', 'js', 'json', 'node'],
  // setupFilesAfterEnv: ['./tests/setupTests.ts'], // For global mocks or Prisma client setup
  clearMocks: true,
  collectCoverage: true,
  coverageDirectory: "coverage",
  collectCoverageFrom: ["src/**/*.{js,ts}"],
  testMatch: ["**/tests/**/*.test.ts", "**/tests/**/*.test.js"],
  moduleNameMapper: {
     '^@/(.*)$': '<rootDir>/src/$1',
     // Needed for ESM import errors from jest-mock-extended (often used with Prisma testing)
     // and for allowing .js extensions in imports if your tsconfig moduleResolution is node16/nodenext
     '^(\\.{1,2}/.*)\\.js$': '$1',
  },
  extensionsToTreatAsEsm: ['.ts'], // Treat .ts files as ESM
};
