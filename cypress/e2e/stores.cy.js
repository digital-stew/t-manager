beforeEach(() => {
  //log in
  cy.visit("/stores");
  cy.get("#login_input").type("cypress");
  cy.get("#password_input").type("cypress");
  cy.get("#loginButton").click();
});

describe("stores", () => {
  it("add new stock with code input", () => {
    cy.get("#addStockButton").should("be.visible").click();
    cy.get("#addStockModal-stockCode").type("208M05015XL"); //purple / dark purple 5XL
    cy.get("#addStockModal-amount").type("1");
    cy.get("#addStockSubmitButton").click();
    cy.get("#modal").should("contain.text", "stock added");
    cy.visit("/admin/log.php");
    cy.get("table")
      .find("tr")
      .eq(1)
      .find("td")
      .eq(4)
      .should(
        "contain.text",
        "new entry - code: 208M05015XL location: hawkins amount: 1"
      );
  });

  it("add stock using manual inputs (dropdown menus)", () => {
    cy.get("#addStockButton").should("be.visible").click();
    cy.wait(100);
    cy.get("#addStockModal-manualButton").should("be.visible").click();
    cy.wait(100);
    cy.get("#addStockSelectType").select("Polo");
    cy.get("#addStockSelectColor").select("Navy");
    cy.get("#addStockSelectSize").select("5XL");
    cy.get("#addStockModal-manual-amount").type("1");
    cy.get("#addStockModal-manual-submit").should("be.visible").click();
    cy.get("#modal").should("contain.text", "stock added");
    cy.visit("/admin/log.php");
    cy.get("table")
      .find("tr")
      .eq(1)
      .find("td")
      .eq(4)
      .should(
        "contain.text",
        "edit entry - code: 20A6044B5XL location: hawkins amount: 1"
      );
  });

  it("remove stock with code input reason=other and no order name input", () => {
    cy.get("#removeStockButton").should("be.visible").click();
    cy.get("#removeStockModal-stockCode").type("208M05015XL"); //purple / dark purple 5XL
    cy.get("#removeStockModal-amount").type("1");
    cy.get("#reason-select").select("other");
    cy.get("#removeStock-submit").click();
    cy.get("#modal").should("contain.text", "stock removed");
    cy.visit("/admin/log.php");
    cy.get("table")
      .find("tr")
      .eq(1)
      .find("td")
      .eq(4)
      .should(
        "contain.text",
        "code: 208M05015XL - size: 5XL - amount: 1 - reason: other"
      );
  });

  it("remove stock with code input reason=reject ", () => {
    cy.get("#removeStockButton").should("be.visible").click();
    cy.get("#removeStockModal-stockCode").type("20A6044B5XL"); //purple / dark purple 5XL
    cy.get("#removeStockModal-order").type("Off2Dom2024-49"); // must be valid order
    cy.get("#removeStockModal-amount").type("1");
    cy.get("#reason-select").select("reject");
    cy.get("#removeStock-submit").click();
    cy.wait(100);
    cy.get("#modal").should("contain.text", "stock removed");
    cy.visit("/admin/log.php");
    cy.get("table")
      .find("tr")
      .eq(1)
      .find("td")
      .eq(4)
      .should(
        "contain.text",
        "code: 20A6044B5XL - size: 5XL - amount: 1 - reason: reject"
      );
  });

  it("transfer stock", () => {
    cy.get("#transferStockButton").should("be.visible").click();
    cy.get("#stockCodeInput").type("20A6044B5XL"); //purple / dark purple 5XL
    cy.get("#amountInput").type("1");
    cy.get("#transferFromSelect").select("hawkins");
    cy.get("#transferToSelect").select("cornwall");
    cy.get("#transfer-submit").click();
    cy.wait(100);
    cy.get("#modal").should("contain.text", "stock transferred");
    cy.visit("/admin/log.php");

    cy.get("table")
      .find("tr")
      .eq(1)
      .find("td")
      .eq(4)
      .should(
        "contain.text",
        "new entry - code: 20A6044B5XL location: cornwall amount: 1"
      );

    cy.get("table")
      .find("tr")
      .eq(2)
      .find("td")
      .eq(4)
      .should(
        "contain.text",
        "code: 20A6044B5XL - size: 5XL - amount: 1 - reason: transfer from: hawkins"
      );
  });

  it("batch add stock", () => {
    cy.get("#batchAddStockButton").should("be.visible").click();
    cy.get("#batchAddStyle").select("Polo");
    cy.get("#batchAddColor").select("Game Red");
    cy.get("[placeholder='XL']").type("100");
    cy.get("#batchAddStock-submit").click();
    cy.get("#modal").should("contain.text", "stock added");
    cy.visit("/admin/log.php");

    cy.get("table")
      .find("tr")
      .eq(1)
      .find("td")
      .eq(4)
      .should(
        "contain.text",
        "edit entry - code: 20A606DLXL0 location: hawkins amount: 100"
      );
  });
});
