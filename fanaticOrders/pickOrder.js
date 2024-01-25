const html5QrCode = new Html5Qrcode("qr-reader");
const config = { fps: 10, qrbox: { width: 130, height: 130 } };
startCam();
//highlightColumn();
// Callback for successful QR code scan
async function qrCodeSuccessCallback(decodedText, decodedResult) {
  if (document.getElementById("targetCode").innerText != decodedText) {
    return setScanError();
  }
  document.getElementById("confirm").showModal();
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
  try {
    html5QrCode.start(
      { facingMode: "environment" },
      config,
      qrCodeSuccessCallback
    );
  } catch (error) {
    console.log("no camera");
  }
}
