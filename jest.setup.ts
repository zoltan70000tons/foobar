import "@testing-library/jest-dom";

import { TextDecoder, TextEncoder } from "util";

// Polyfill missing encoders for Next.js utilities in Jest
if (!global.TextEncoder) {
  // @ts-expect-error - exposed on global for tests
  global.TextEncoder = TextEncoder;
}

if (!global.TextDecoder) {
  // @ts-expect-error - exposed on global for tests
  global.TextDecoder = TextDecoder;
}

// Mock React's useId hook for consistent IDs in tests
let idCounter = 0;
jest.mock("react", () => {
  const actualReact = jest.requireActual("react");
  return {
    ...actualReact,
    useId: () => `test-id-${++idCounter}`,
  };
});

// Reset ID counter before each test
beforeEach(() => {
  idCounter = 0;
});
