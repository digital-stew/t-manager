function updateSelectElements() {
  let selectElements = document.querySelectorAll(`[data-embellishmentSelect]`);
  let checkboxElements = document.querySelectorAll(`[data-embellishment]`);

  checkboxElements.forEach((checkbox) => {
    selectElements.forEach((selectE) => {
      if (
        checkbox.checked &&
        !selectE.innerHTML.includes(checkbox.dataset.embellishment)
      ) {
        selectE.add(
          new Option(
            checkbox.dataset.embellishment,
            checkbox.dataset.embellishment
          )
        );
      }
      if (!checkbox.checked) {
        [...selectE.options]
          .filter((o) => o.value == checkbox.dataset.embellishment)
          .forEach((o) => o.remove());
      }
    });
  });
}

function cloneInput(e) {
  let newNode = e.parentElement.cloneNode(true);
  newNode.childNodes.forEach((element) => {
    element.value = "";
  });
  newNode.dataset.inputNumber++;
  e.parentElement.parentElement.appendChild(newNode);
  e.remove();
}
