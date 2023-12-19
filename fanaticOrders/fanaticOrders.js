const html5QrCode = new Html5Qrcode("qr-reader");
const config = { fps: 10, qrbox: { width: 130, height: 130 } };

//*************scan and pick fanatic order */

async function qrCodeSuccessCallback(decodedText, decodedResult) {
  html5QrCode.stop();
  document.getElementById("scannerModal").close();

  const result = await addOrderToPick(decodedText);
  if (parseInt(result) > 0)
    //returns new order id
    window.location = "/fanaticOrders/pickOrder.php?id=" + result;
}

async function addOrderToPick(code) {
  let formData = new FormData();
  formData.append("code", code);
  const req = await fetch("/fanaticOrders/pickOrder.php", {
    method: "POST",
    body: formData,
  });
  const res = await req.text();
  if (parseFloat(res) == res) {
    return res; //new order id
    // window.location = "/fanaticOrders/pickOrder.php?id=" + res;
  } else return false;
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

  document.getElementById("scannerModal").showModal();
}

//*************batch add fanatic orders */
async function batchQrCodeSuccessCallback(decodedText, decodedResult) {
  html5QrCode.pause();
  console.log("pause");

  const result = await addOrderToPick(decodedText);
  if (parseInt(result) > 0) batchShowUser(decodedText);
  else batchShowUser("error");

  setTimeout(() => {
    html5QrCode.resume();
    console.log("resume");
  }, 3000);
}

function batchAddOrders() {
  try {
    html5QrCode.start(
      { facingMode: "environment" },
      config,
      batchQrCodeSuccessCallback
    );
  } catch (error) {
    console.log("no camera");
  }
  document.getElementById("scannerModal").showModal();
}

function batchShowUser(string) {
  document.getElementById("qr-reader-results").innerText = string;
  setTimeout(() => {
    document.getElementById("qr-reader-results").innerText = "";
  }, 3000);
}

//************replace rejects/short****** */
function replaceRejectsShorts() {
  try {
    html5QrCode.start(
      { facingMode: "environment" },
      config,
      replaceRejectsShortsCallback
    );
  } catch (error) {
    console.log("no camera");
  }
  document.getElementById("scannerModal").showModal();
}

async function replaceRejectsShortsCallback() {
  html5QrCode.stop();
  document.getElementById("scannerModal").close();
}
//*************************************** */

function closeCamModal() {
  html5QrCode.stop();
  document.getElementById("scannerModal").close();
}
