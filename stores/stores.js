const html5QrCode = new Html5Qrcode("qr-reader");
const config = { fps: 10, qrbox: { width: 130, height: 130 } };

searchStock();

let addOrRemove = "add";

function addStockButton() {
  addOrRemove = "add";
  html5QrCode.start({ facingMode: "environment" }, config, onScanSuccess);
  document.getElementById("scannerModal").showModal();
}

function removeStockButton() {
  addOrRemove = "remove";
  html5QrCode.start({ facingMode: "environment" }, config, onScanSuccess);
  document.getElementById("scannerModal").showModal();
}

function closeCamModal() {
  html5QrCode.stop();
  document.getElementById("scannerModal").close();
}
function onScanFail() {
  //console.log("scan fail");
  return;
}

async function onScanSuccess(decodedText, decodedResult) {
  closeCamModal();
  stockLocation = document.getElementById("addRemoveLocationSelect").value;
  showModal(
    `/stores/${addOrRemove}.php?${addOrRemove}=true&code=${decodedText}&location=${stockLocation}`
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
