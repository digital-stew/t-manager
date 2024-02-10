const config = { fps: 10, qrbox: { width: 130, height: 130 } };

//*************scan and pick fanatic order */
let html5QrCodeAdd;
try {
  html5QrCodeAdd = new Html5Qrcode("qr-reader-add");
} catch (error) {
  console.log("no camera");
}

function startCamAdd() {
  try {
    html5QrCodeAdd.start(
      { facingMode: "environment" },
      config,
      (decodedText, decodedResult) => {
        html5QrCodeAdd.stop();
        document.getElementById("orderInputStringAdd").value = decodedText;
        document.getElementById("addOrderSubmitButton").click();
      }
    );
  } catch (error) {
    console.log("no camera");
  }
}

function closeAddModal() {
  html5QrCodeAdd.stop();
  document.getElementById("addAndPickOrder-modal").close();
}
//*************************************** */

//*************batch add fanatic orders */
let html5QrCodeBatchAdd;
try {
  html5QrCodeBatchAdd = new Html5Qrcode("qr-reader-batchAdd");
} catch (error) {
  console.log("no camera");
}

function startCamBatchAdd() {
  try {
    html5QrCodeBatchAdd.start(
      { facingMode: "environment" },
      config,
      (decodedText, decodedResult) => {
        html5QrCodeBatchAdd.stop();
        document.getElementById("orderInputStringBatchAdd").value = decodedText;
        document.getElementById("batchAddSubmitButton").click();
      }
    );
  } catch (error) {
    console.log("no camera");
  }
}

function closeBatchAddModal() {
  html5QrCodeBatchAdd.stop();
  document.getElementById("batchAddOrders-modal").close();
}
const queryParamsFanaticOrders = new URLSearchParams(window.location.search);
const batchAddOrder = queryParamsFanaticOrders.get("batchAddOrder");
if (batchAddOrder) {
  document.getElementById("batchAddOrders-modal").showModal();
  startCamBatchAdd();
}
//remove all get params
queryParamsFanaticOrders.delete("batchAddOrder");
const newUrl = `${window.location.pathname}`;
window.history.pushState({}, "", newUrl);
//*************************************** */
