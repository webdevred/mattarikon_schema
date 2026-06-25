import { defineConfig } from "eslint/config";
import js from "@eslint/js";

export default defineConfig([
  js.configs.recommended,

  {
    files: ["**/*.js"],
    languageOptions: {
      globals: {
        window: "readonly",
        document: "readonly",
        fetch: "readonly",
        console: "readonly",
        URLSearchParams: "readonly",
        Date: "readonly",
      },
    },
    rules: {
      eqeqeq: ["error", "always"],
      "no-console": "warn",
      "no-unused-vars": ["warn", { argsIgnorePattern: "^_" }],
    },
  },

  {
    ignores: ["node_modules/**", "dist/**"],
  },
]);
