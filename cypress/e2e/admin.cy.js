beforeEach(() => {
  //log in
  cy.visit("/admin");
  cy.get("#login_input").type("cypress");
  cy.get("#password_input").type("cypress");
  cy.get("#loginButton").click();
});
describe("admin", () => {
  // ******************users*************************
  it("add new user", () => {
    cy.get("#addNewUser-button").should("be.visible").click();
    cy.wait(100);
    cy.get("[placeholder='Name']").type("cypress");
    cy.get("[placeholder='Email']").type("cypress@tux-systems.co.uk");
    cy.get("[name='password1']").type("cypress");
    cy.get("[name='password2']").type("cypress");
    cy.get("#addNewUser-submit").should("be.visible").click();
    cy.get("#modal").should("contain.text", "user saved");
    cy.visit("/admin/log.php");
    cy.get("table")
      .find("tr")
      .eq(1)
      .find("td")
      .eq(4)
      .should("contain.text", "new user: cypress");
  });

  it("edit user", () => {
    cy.get("#usersTable")
      .find("tr")
      .eq(1)
      .find("td")
      .eq(1)
      .should("contain.text", "cypress")
      .click();
    cy.get("#editUser-button").should("be.visible").click();
    cy.get("[name='department']").select("Stores");
    cy.get("#editUser-submit").should("be.visible").click();
    cy.wait(100);
    cy.get("#modal").should("contain.text", "user saved");
    cy.visit("/admin/log.php");
    cy.get("table")
      .find("tr")
      .eq(1)
      .find("td")
      .eq(0)
      .should("contain.text", "EDIT");
  });

  it("delete user", () => {
    cy.get("#usersTable")
      .find("tr")
      .eq(1)
      .find("td")
      .eq(1)
      .should("contain.text", "cypress")
      .click();
    cy.get("#deleteUser-button").should("be.visible").click();
    cy.get("#modal").should("contain.text", "user deleted");
    cy.visit("/admin/log.php");
    cy.get("table")
      .find("tr")
      .eq(1)
      .find("td")
      .eq(4)
      .should("contain.text", "delete user");
  });

  // ******************stock types*************************
  it("add stock type", () => {
    cy.get("#stockTypesAdd-button").should("be.visible").click();
    cy.get("[name='newCode']").type("NEW1");
    cy.get("[name='oldCode']").type("NEW2");
    cy.get("[name='type']").type("new description cypress");
    cy.get("[name='addType']").should("be.visible").click();
    cy.get("#modal").should("contain.text", "New type added");
    cy.visit("/admin/log.php");
    cy.get("table")
      .find("tr")
      .eq(1)
      .find("td")
      .eq(4)
      .should(
        "contain.text",
        "new code: NEW1 - old code: NEW2 - type: new description cypress"
      );
  });

  it("delete stock type", () => {
    cy.get("#stockTypes-table")
      .find("tr")
      .eq(1)
      .find("td")
      .eq(2)
      .should("contain.text", "new description cypress")
      .click();
    cy.get("[name='deleteType']").should("be.visible").click(); // and click alert
    cy.get("#modal").should("contain.text", "Type deleted");
    cy.visit("/admin/log.php");
    cy.get("table")
      .find("tr")
      .eq(1)
      .find("td")
      .eq(4)
      .should("contain.text", "delete stock type");
  });

  // ******************stock sizes*************************
  it("add stock size", () => {
    cy.get("#stockSizeAdd-button").should("be.visible").click();
    cy.get("[name='code']").type("6XL");
    cy.get("[name='size']").type("6XL");
    cy.get("[name='addSize']").should("be.visible").click();
    cy.get("#modal").should("contain.text", "New size added");
    cy.visit("/admin/log.php");
    cy.get("table")
      .find("tr")
      .eq(1)
      .find("td")
      .eq(4)
      .should("contain.text", "new size: 6XL - code: 6XL");
  });

  it("delete stock size", () => {
    cy.get("#stockSizes-table")
      .find("tr")
      .eq(1)
      .find("td")
      .eq(1)
      .should("contain.text", "6XL")
      .click();
    cy.get("[name='deleteSize']").should("be.visible").click(); // and click alert
    cy.get("#modal").should("contain.text", "Size deleted");
    cy.visit("/admin/log.php");
    cy.get("table")
      .find("tr")
      .eq(1)
      .find("td")
      .eq(4)
      .should("contain.text", "delete stock size");
  });

  // ******************stock colors************************
  it("add stock type", () => {
    cy.get("#stockColorAdd-button").should("be.visible").click();
    cy.get("[name='newCode']").type("NEW1");
    cy.get("[name='oldCode']").type("NEW2");
    cy.get("[name='color']").type("cypress");
    cy.get("[name='addColor']").should("be.visible").click();
    cy.get("#modal").should("contain.text", "New color added");
    cy.visit("/admin/log.php");
    cy.get("table")
      .find("tr")
      .eq(1)
      .find("td")
      .eq(4)
      .should(
        "contain.text",
        "new code: NEW1 - old code: NEW2 - color: cypress"
      );
  });

  it("delete stock type", () => {
    cy.get("#stockColor-table")
      .find("tr")
      .eq(1)
      .find("td")
      .eq(2)
      .should("contain.text", "cypress")
      .click();
    cy.get("[name='deleteColor']").should("be.visible").click(); // and click alert
    cy.get("#modal").should("contain.text", "Color deleted");
    cy.visit("/admin/log.php");
    cy.get("table")
      .find("tr")
      .eq(1)
      .find("td")
      .eq(4)
      .should("contain.text", "delete stock color");
  });

  // ******************reason to remove stock**************
  it("add reason to remove stock", () => {
    cy.get("#addNewReason-button").should("be.visible").click();
    cy.get("[name='reason']").type("cypress");
    cy.get("[name='addNewReason']").should("be.visible").click();
    cy.get("#modal").should("contain.text", "New reason added");
    cy.visit("/admin/log.php");
    cy.get("table")
      .find("tr")
      .eq(1)
      .find("td")
      .eq(4)
      .should("contain.text", "new reason: cypress");
  });

  it("delete reason to remove stock", () => {
    cy.get("#reason-table")
      .find("tr")
      .eq(1)
      .find("td")
      .eq(0)
      .should("contain.text", "cypress")
      .click();
    cy.get("[name='deleteReason']").should("be.visible").click(); // and click alert
    cy.get("#modal").should("contain.text", "deleted");
    cy.visit("/admin/log.php");
    cy.get("table")
      .find("tr")
      .eq(1)
      .find("td")
      .eq(4)
      .should("contain.text", "delete remove stock reason");
  });

  // ******************auto locations**********************
  it("add auto location", () => {
    cy.get("#addNewAutoLocation-button").should("be.visible").click();
    cy.get("[name='ipAddress']").type("1.1.1.1");
    cy.get("[name='location']").select("cornwall");
    cy.get("[name='addNewAutoLocation']").click();
    cy.get("#modal").should("contain.text", "New auto location added");
    cy.visit("/admin/log.php");
    cy.get("table")
      .find("tr")
      .eq(1)
      .find("td")
      .eq(4)
      .should("contain.text", "ip: 1.1.1.1 - location: cornwall");
  });

  it("delete auto location", () => {
    cy.get("#autoLocation-table")
      .find("tr")
      .eq(1)
      .find("td")
      .eq(1)
      .should("contain.text", "cornwall")
      .click();
    cy.get("[name='deleteAutoLocation']").should("be.visible").click(); // and click alert
    cy.get("#modal").should("contain.text", "deleted");
    cy.visit("/admin/log.php");
    cy.get("table")
      .find("tr")
      .eq(1)
      .find("td")
      .eq(4)
      .should("contain.text", "delete auto location");
  });
});
