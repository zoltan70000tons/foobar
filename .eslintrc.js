require("@rushstack/eslint-patch/modern-module-resolution");

module.exports = {
    root: true,
    env: {
        browser: true,
        node: true,
        es6: true,
        jest: true,
    },
    extends: [
        "eslint:recommended",
        "plugin:@typescript-eslint/recommended",
        "plugin:import/recommended",
        "plugin:import/typescript",
    ],
    parser: "@typescript-eslint/parser",
    plugins: ["@typescript-eslint", "import"],
    rules: {
        "object-curly-spacing": ["error", "always"],
        "import/export": "error", // good practice
        "import/extensions": "off", // optional
    },
    settings: {
        "import/resolver": {
            typescript: {
                project: "./tsconfig.json",
            },
        },
    },
};
