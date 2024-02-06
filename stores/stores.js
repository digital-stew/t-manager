//const html5QrCode = new Html5Qrcode("qr-reader");
const config = { fps: 10, qrbox: { width: 130, height: 130 } };

searchStock();
/*********** add stock *********/
let addQrReader = null;

function addStockButton() {
  addQrReader = new Html5Qrcode("addStockModal-qrReader");
  // const userLocation = document.getElementById("currentLocationSelect").value;
  // document.getElementById("addStockModal-userLocation").innerText =
  // userLocation;
  // document.getElementById("addStockModal-hiddenLocationInput").value =
  // userLocation;
  document.getElementById("addStockModal").showModal();

  try {
    addQrReader.start(
      { facingMode: "environment" },
      config,
      (decodedText, decodedResult) => {
        addQrReader.pause();
        document.getElementById("addStockModal-stockCode").value = decodedText;
        setTimeout(() => {
          addQrReader.resume();
        }, 1000);
      }
    );
  } catch (error) {
    console.log("no camera");
  }
}
function closeAddStockModal() {
  try {
    addQrReader.stop();
  } catch (error) {
    console.log("cant stop none running camera ");
  }
  document.getElementById("addStockModal").close();
}

/*********** remove stock *********/
let removeQrReader = null;
function removeStockButton() {
  removeQrReader = new Html5Qrcode("removeStockModal-qrReader");
  // const userLocation = document.getElementById("currentLocationSelect").value;
  // document.getElementById("removeStockModal-userLocation").innerText =
  // userLocation;
  // document.getElementById("removeStockModal-hiddenLocationInput").value =
  // userLocation;

  let showUser = document.getElementById("removeStockModal-showUser");

  document.getElementById("removeStockModal").showModal();

  let prom = new Promise((resolve, reject) => {
    showUser.innerText = "scan stock";
    removeQrReader.start(
      { facingMode: "environment" },
      config,
      (decodedText, decodedResult) => {
        removeQrReader.stop();

        document.getElementById("removeStockModal-stockCode").value =
          decodedText;

        setTimeout(() => {
          resolve();
        }, 1000);
      }
    );
  }).then(() => {
    showUser.innerText = "scan order";
    removeQrReader.start(
      { facingMode: "environment" },
      config,
      (decodedText, decodedResult) => {
        removeQrReader.stop();

        document.getElementById("removeStockModal-order").value =
          decodedText.split("¦")[0];
        document.getElementById("removeStockModal-amount").focus();

        setTimeout(() => {
          showUser.innerText = "";
        }, 1000);
      }
    );
  });
}

function removeStockManualInput() {
  closeRemoveStockModal();
  document.getElementById("removeStockModal-manual").showModal();
  return;
}

function addStockManualInput() {
  closeAddStockModal();
  document.getElementById("addStockModal-manual").showModal();
  return;
}

function updateStockCode_type() {
  let select = document.getElementById("removeStockSelectType");
  let stockCode = document.getElementById("removeStockModal-stockCode");
  codeArray = [...stockCode.value.padEnd(11, "0")];
  console.log(codeArray);
}
function updateStockCode_color() {
  let select = document.getElementById("removeStockSelectColor");
  let stockCode = document.getElementById("removeStockModal-stockCode");
  stockCode.value = select.value + stockCode.value.substring(4, -1);
}
function updateStockCode_size() {}

function closeRemoveStockModal() {
  try {
    removeQrReader.stop();
  } catch (error) {
    console.log("cant stop none running camera ");
  }
  document.getElementById("removeStockModal").close();
}
/*********** ******* *********/

/*********** transfer stock *********/
let transferQrReader = null;
function transferStockButton() {
  transferQrReader = new Html5Qrcode("transferStockModal-qrReader");
  document.getElementById("transferStockModal").showModal();
  try {
    transferQrReader.start(
      { facingMode: "environment" },
      config,
      (decodedText, decodedResult) => {
        transferQrReader.pause();
        if (stockCodeIsValid(decodedText))
          document.getElementById("stockCodeInput").value = decodedText;
        setTimeout(() => {
          transferQrReader.resume();
        }, 1000);
      }
    );
  } catch (error) {
    console.log("no camera");
  }
}
function stockCodeIsValid(inputCode) {
  return typeof inputCode === "string" && inputString.length === 11;
}
function closeTransferStockModal() {
  try {
    transferQrReader.stop();
  } catch (error) {
    console.log("cant stop none running camera ");
  }
  document.getElementById("transferStockModal").close();
}
/*********** ******* *********/

/*********** batch add stock *********/
function batchAddStockButton() {
  document.getElementById("batchAddStockModal").showModal();
}

function closeBatchAddStockModal() {
  document.getElementById("batchAddStockModal").close();
}
/*********** ******* *********/

function closeCamModal() {
  try {
    html5QrCode.stop();
  } catch (error) {
    console.log("cant stop none running camera ");
  }
  document.getElementById("scannerModal").close();
}

function onScanFail() {
  return;
}

async function onScanSuccess(decodedText, decodedResult) {
  closeCamModal();
  stockLocation = document.getElementById("currentLocationSelect").value;
  showModal(
    `/stores/${addOrRemove}.php?${addOrRemove}=true&code=${decodedText}&location=${stockLocation}`
  );
}

async function manualInput() {
  closeCamModal();
  const code = document.getElementById("manualInputCode").value;
  stockLocation = document.getElementById("currentLocationSelect").value;
  showModal(
    `/stores/${addOrRemove}.php?${addOrRemove}=true&code=${code}&location=${stockLocation}`
  );
  if (addOrRemove == "remove") {
    try {
      html5QrCode.start({ facingMode: "environment" }, config, onScanSuccess);
    } catch (error) {
      console.log("no camera");
    }
  }
}

async function searchStock() {
  const color = document.getElementById("colorSelect").value;
  const size = document.getElementById("sizeSelect").value;
  const type = document.getElementById("typeSelect").value;
  const location = document.getElementById("locationSelect").value;

  const req = await fetch(
    `/stores/search.php?color=${color}&size=${size}&type=${type}&location=${location}`
  );
  const res = await req.text();
  document.getElementById("searchResults").innerHTML = res;
}
