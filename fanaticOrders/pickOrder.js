const html5QrCode = new Html5Qrcode("qr-reader");
const config = { fps: 10, qrbox: { width: 130, height: 130 } };
startCam();

// Callback for successful QR code scan
async function qrCodeSuccessCallback(decodedText, decodedResult) {
  console.log(decodedText);
  //   if (document.getElementById("codeToScan").innerText != decodedText) {
  //     return setScanError();
  //   }
  //console.log(amount[sizes[iterator]]);

  if (document.getElementById("targetCode").innerText != decodedText) {
    return setScanError();
  }
  document.getElementById("confirm").showModal();
}

async function uploadPick() {
  const orderId = document.getElementById("orderId").innerText;
  const pickAmount = document.getElementById("pickAmount").innerText;
  const size = document.getElementById("targetSize").innerText;
  const targetCode = document.getElementById("targetCode").innerText;
  //console.log(orderId);
  let formData = new FormData();
  formData.append("pick", true);
  formData.append("pickedAmount", pickAmount);
  formData.append("orderId", orderId);
  formData.append("size", size);
  formData.append("stockCode", targetCode);

  const url = `/fanaticOrders/pickOrder.php?id=${orderId}&continue=true`;
  const req = await fetch(url, { method: "POST", body: formData });

  const res = await req.text();
  if (res == "ok") window.location = url;
}

async function skipPick() {
  //to do
  const orderId = document.getElementById("orderId").innerText;
  let formData = new FormData();
  formData.append("skipPick", true);
  const url = `/fanaticOrders/pickOrder.php?id=${orderId}&continue=true`;
  const req = await fetch(url, { method: "POST", body: formData });
  const res = await req.text();
  if (res == "ok") window.location = url;
}

function setScanError() {
  html5QrCode.pause();
  console.log("SCAN ERROR!!");
  document.getElementById("scanTarget").style.color = "red";
  setTimeout(() => {
    document.getElementById("scanTarget").style.color = "unset";

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
