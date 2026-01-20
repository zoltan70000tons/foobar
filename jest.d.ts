import "@testing-library/jest-dom";

declare global {
  namespace jest {
    interface Matchers<R> {
      toBeInTheDocument(): R;
      // (optional: add other matchers you use, e.g.)
      // toHaveTextContent(text: string | RegExp): R;
    }
  }
}
