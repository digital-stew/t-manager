const { defineConfig } = require("cypress");

module.exports = defineConfig({
  viewportWidth: 1501,
  viewportHeight: 4000,
  scrollBehavior: false,
  e2e: {
    baseUrl: "http://localhost:8080",
    setupNodeEvents(on, config) {
      // implement node event listeners here
    },
  },
});
