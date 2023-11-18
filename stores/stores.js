const html5QrCode = new Html5Qrcode("qr-reader");
const config = { fps: 10, qrbox: { width: 130, height: 130 } };

searchStock();

let addOrRemove = "add";

function addStockButton() {
  addOrRemove = "add";
  try {
    html5QrCode.start({ facingMode: "environment" }, config, onScanSuccess);
  } catch (error) {
    console.log("no camera");
  }
  document.getElementById("scannerModal").showModal();
}

function removeStockButton() {
  addOrRemove = "remove";
  try {
    html5QrCode.start({ facingMode: "environment" }, config, onScanSuccess);
  } catch (error) {
    console.log("no camera");
  }
  document.getElementById("scannerModal").showModal();
}

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
