beforeEach(() => {
  //log in
  cy.visit("/stores");
  cy.get("#login_input").type("cypress");
  cy.get("#password_input").type("cypress");
  cy.get("#loginButton").click();
});

describe("header.php", () => {
  it("log in", () => {
    // Visit the PHP file in your application

    // Perform your assertions or interactions here

    // Example: Verify the existence of the burgerMenu
    // cy.get("#burgerMenu").should("exist");

    //open burger menu
    // cy.get("#burgerMenu").click();
    // Example: Trigger the login form submission (if not already logged in)

    // Wait for a specific element to be visible after login (customize as needed)
    cy.get("#user-welcome").should("be.visible");

    //can logout
    //cy.get("#logoutButton").click();
    //cy.get("#loginButton").should("be.visible");

    // Example: Test the dropdown selection
    // cy.get("#currentLocationSelect").select("New Location");

    // Example: Verify the new location is set
    // cy.get("#currentLocationSelect").should("have.value", "New Location");

    // Add more assertions or interactions based on your requirements

    // You can also check network requests if needed
    // Example: Check if a POST request was made
    // cy.intercept('POST', '/api/login.php').as('loginRequest');

    // Perform additional actions or assertions as needed

    // Example: Check if the login request was made successfully
    // cy.wait('@loginRequest').its('response.statusCode').should('eq', 200);
  });

  it("log out", () => {
    // cy.get("#login_input").type("stew");
    // cy.get("#password_input").type("15984291");
    // // cy.get("#loginButton").click();
    // cy.visit("/stores");

    // cy.get("#login_input").type("stew");
    // cy.get("#password_input").type("15984291");
    // cy.get("#loginButton").click();
    // cy.get("#user-welcome").should("be.visible");

    cy.get("#logoutButton").click();
    cy.get("#loginButton").should("be.visible");
  });

  it("change location", () => {
    cy.get("#currentLocationSelect").select("cornwall");
    cy.get("#currentLocationSelect").should("have.value", "cornwall");
  });
});
