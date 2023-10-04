const html5QrCode = new Html5Qrcode("qr-reader");
const config = { fps: 10, qrbox: { width: 130, height: 130 } };
startCam();
let selectedSize = "";
let selected = 0;
let iterator = 0;
let amountAvailable = 0;

const sizeCodes = ["XS0", "S00", "M00", "L00", "XL0", "2XL"];
const sizes = ["XS", "S", "M", "L", "XL", "2XL"];
// move this to the server in php. use the session????
// Initialize the first size
setCodeToScan(stockCode + sizeCodes[iterator]);
highlightColumn(sizes[iterator]);
selectedSize = sizes[iterator];
setSizeAmount(amount[sizes[iterator]]);

// Callback for successful QR code scan
function qrCodeSuccessCallback(decodedText, decodedResult) {
  console.log(decodedText);
  //   if (document.getElementById("codeToScan").innerText != decodedText) {
  //     return setScanError();
  //   }
  //console.log(amount[sizes[iterator]]);
  showConfirm();
}

// Proceed to the next size
async function next() {
  document.getElementById("confirm").close();

  pickSize(stockCode + sizeCodes[iterator], amount[sizes[iterator]]);

  //if all is well
  html5QrCode.resume();
  iterator++;
  setCodeToScan(stockCode + sizeCodes[iterator]);
  setSizeAmount(amount[sizes[iterator]]);
  highlightColumn(sizes[iterator]);
  selectedSize = sizes[iterator];
}

async function pickSize(sizeCode, amount) {
  console.log("pick size: " + sizeCode + " : " + amount);
}

function setScanError() {
  html5QrCode.pause();
  console.log("SCAN ERROR!!");
  setTimeout(() => {
    html5QrCode.resume();
  }, 2000);
}

function startCam() {
  html5QrCode.start(
    { facingMode: "environment" },
    config,
    qrCodeSuccessCallback
  );
}

// Highlight the selected size column
function highlightColumn(sizes) {
  let divs = document.querySelectorAll("td");
  divs.forEach((div) => {
    // console.log(div.dataset.size);
    if (div.dataset.size == sizes) div.style = "background-color: red;";
    else div.style = "background-color: unset;";
  });
}

function setSizeAmount(amount) {
  document.getElementById("modalAmount").value = amount;
}

function setCodeToScan(text) {
  document.getElementById("codeToScan").innerText = text;
  document.getElementById("modalCodeToScan").innerText = text;
}

function showConfirm() {
  html5QrCode.pause();
  let modal = document.getElementById("confirm");
  modal.showModal();
}
