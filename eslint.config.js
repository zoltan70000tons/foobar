import js from "@eslint/js";
import globals from "globals";
import tseslint from "typescript-eslint";

const cleanGlobals = Object.fromEntries(
    Object.entries({
      ...globals.browser,
      ...globals.node,
    }).filter(([key]) => key === key.trim())
);

export default [
  js.configs.recommended,

  ...tseslint.configs.recommendedTypeChecked,

  {
    files: ["resources/js/**/*.{ts,tsx,js,jsx}"],

    languageOptions: {
      parser: tseslint.parser,
      parserOptions: {
        project: "./tsconfig.json",
        tsconfigRootDir: process.cwd(),
      },
      globals: cleanGlobals,
    },

    rules: {
      "@typescript-eslint/no-explicit-any": "error",
      "@typescript-eslint/explicit-function-return-type": "warn",
      "@typescript-eslint/no-unused-vars": [
        "warn",
        { argsIgnorePattern: "^_" },
      ],
    },
  },
];
