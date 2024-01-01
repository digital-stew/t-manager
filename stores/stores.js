//const html5QrCode = new Html5Qrcode("qr-reader");
const config = { fps: 10, qrbox: { width: 130, height: 130 } };

searchStock();

/*********** add stock *********/
const addQrReader = new Html5Qrcode("addStockModal-qrReader");

function addStockButton() {
  const userLocation = document.getElementById("currentLocationSelect").value;
  document.getElementById("addStockModal-userLocation").innerText =
    userLocation;
  document.getElementById("addStockModal-hiddenLocationInput").value =
    userLocation;
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
const removeQrReader = new Html5Qrcode("removeStockModal-qrReader");

function removeStockButton() {
  const userLocation = document.getElementById("currentLocationSelect").value;
  document.getElementById("removeStockModal-userLocation").innerText =
    userLocation;
  document.getElementById("removeStockModal-hiddenLocationInput").value =
    userLocation;

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

        setTimeout(() => {
          showUser.innerText = "";
        }, 1000);
      }
    );
  });
}
function closeRemoveStockModal() {
  try {
    removeQrReader.stop();
  } catch (error) {
    console.log("cant stop none running camera ");
  }
  document.getElementById("removeStockModal").close();
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
