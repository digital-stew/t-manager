var resultContainer = document.getElementById("qr-reader-results");
var html5QrcodeScanner = new Html5QrcodeScanner("qr-reader", {
  fps: 10,
  qrbox: 250,
});
searchStock();
//html5QrcodeScanner.render(onScanSuccess);
let addOrRemove = "add";

function addStockButton() {
  addOrRemove = "add";
  html5QrcodeScanner.render(onScanSuccess, onScanFail);
  if (html5QrcodeScanner.getState() == 3) html5QrcodeScanner.resume(); //state 3 = paused --- state 1 = ready
  document.getElementById("scannerModal").showModal();
}

function removeStockButton() {
  addOrRemove = "remove";
  html5QrcodeScanner.render(onScanSuccess, onScanFail);
  if (html5QrcodeScanner.getState() == 3) html5QrcodeScanner.resume(); //state 3 = paused --- state 1 = ready
  document.getElementById("scannerModal").showModal();
}

function onScanFail() {
  //console.log("scan fail");
  return;
}

async function onScanSuccess(decodedText, decodedResult) {
  html5QrcodeScanner.pause();
  document.getElementById("scannerModal").close();
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
