const html5QrCode = new Html5Qrcode("qr-reader");
const config = { fps: 10, qrbox: { width: 130, height: 130 } };

const qrCodeSuccessCallback = (decodedText, decodedResult) => {
  /* handle success */
  html5QrCode.stop();
  document.getElementById("scannerModal").close();
  addOrderToPick(decodedText);
};

function startCam() {
  html5QrCode.start(
    { facingMode: "environment" },
    config,
    qrCodeSuccessCallback
  );
  document.getElementById("scannerModal").showModal();
}

function closeCamModal() {
  html5QrCode.stop();
  document.getElementById("scannerModal").close();
}

async function addOrderToPick(code) {
  let formData = new FormData();
  formData.append("code", code);
  // formData.target = "/fanaticOrders/pickOrder.php";
  // formData.submit();
  const req = await fetch("/fanaticOrders/pickOrder.php", {
    method: "POST",
    body: formData,
  });
  const res = await req.text();
  if (res === "ok") window.location = "/fanaticOrders/pickOrder.php";
  console.log(res);
}
