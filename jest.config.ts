import type { Config } from "jest";

const config: Config = {
  preset: "ts-jest",
  testEnvironment: "jsdom",
  setupFilesAfterEnv: ["<rootDir>/jest.setup.ts"],

  moduleNameMapper: {
    "\\.(jpg|jpeg|png|gif|svg|webp)$": "<rootDir>/jest/__mocks__/imageMock.js",
    "^@/store/(.*)$": "<rootDir>/resources/js/store/$1",
    "^@/enums/(.*)$": "<rootDir>/resources/js/enums/$1",
    "^@/Providers/(.*)$": "<rootDir>/resources/js/Providers/$1",
    "^@/(.*)$": "<rootDir>/resources/js/$1",
    "\\.(css|less|scss|sass)$": "identity-obj-proxy",
  },

  transform: {
    "^.+\\.(ts|tsx|js|jsx)$": [
      "ts-jest",
      {
        tsconfig: "tsconfig.jest.json",
      },
    ],
  },

  transformIgnorePatterns: ["node_modules/(?!(next-intl|use-intl|@formatjs)/)"],
  testPathIgnorePatterns: ["/node_modules/", "/.next/", "/e2e/"],
};

export default config;
