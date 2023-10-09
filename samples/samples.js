HRtimestamp();
// if GET?id send request for it and display
//const queryParams = new URLSearchParams(window.location.search);

const queryID = queryParams.get("id");
if (queryID) {
  selectSample(queryID);
}

// from user search
function updateSamplesList() {
  const timeout = setTimeout(async () => {
    let searchText = document.getElementById("search").value;
    if (searchText === "") return;
    let tbody = document.getElementById("show");

    const res = await fetch("/samples/search.php?search=" + searchText);
    if (res.ok) {
      const reply = await res.text();
      tbody.outerHTML = reply;
      HRtimestamp();
      history.pushState(null, "", "/samples?search=" + searchText);
    } else {
      setError();
    }
  }, 1000);

  clearTimeout(timeout - 1);
}

//click on a sample
let images;
let displayImageNumber = 0;
async function selectSample(rowID) {
  await showModal("/samples/show.php?id=" + rowID);
  images = document.querySelectorAll(".sampleImage");
  displayImage(displayImageNumber);
}

function displayImage(number) {
  document.getElementById("sampleImageCount").innerText =
    displayImageNumber + 1;

  images.forEach((image, index) => {
    if (number == index) {
      images[index].style.display = "block";
    } else {
      images[index].style.display = "none";
    }
  });

  //download image buttons
  let imageButtons = document.querySelectorAll(".sampleImageButton");
  imageButtons.forEach((button, index) => {
    if (number == index) {
      button.style.display = "block";
    } else {
      button.style.display = "none";
    }
  });
}

function imageUp() {
  if (displayImageNumber >= images.length - 1) return;
  displayImageNumber++;
  displayImage(displayImageNumber);
}

function imageDown() {
  if (displayImageNumber == 0) return;
  displayImageNumber--;
  displayImage(displayImageNumber);
}
function showFullScreenImage() {
  const image = document.getElementById("sampleImage").src;
  window.location = image;
}

async function deleteImage(id, imageName) {
  let formData = new FormData();
  formData.append("removeImage", imageName);

  const req = await fetch("/samples/edit.php?id=" + id, {
    method: "POST",
    body: formData,
  });

  const res = await req.text();

  if (res == "image=removed") {
    let editImages = document.querySelectorAll(".sampleEditImage");
    editImages.forEach((image, index) => {
      if (image.src.includes(imageName)) {
        editImages[index].parentElement.style.display = "none";
      }
    });
  }
}

function uploadAnotherImage(e) {
  let element = document.getElementById("uploadSampleImage");
  let newNode = element.cloneNode(true);
  newNode.value = "";
  document.getElementById("uploadSampleImageContainer").appendChild(newNode);
}

function printSample(elem) {
  var header_str =
    `<html><head><title>` + document.title + "</title></head><body>";
  var footer_str = "</body></html>";
  //save old page
  var oldPage = document.body.innerHTML;

  let html = "";

  const sampleName = document.getElementById("sampleName").innerText;
  const sampleNumber = document.getElementById("sampleNumber").innerText;
  const samplePrintData = document.getElementById("samplePrintData").outerHTML;

  html += `<section>`;
  html += `<h1>${sampleName} ${sampleNumber}</h1>`;
  html += `</section>`;

  html += `<section class="printData">`;
  html += samplePrintData;
  html += `</section>`;

  //get sample images
  html += `<div>`;
  document.querySelectorAll(".sampleImage").forEach((image) => {
    html += `<img loading="eager" class="sampleImage" src=${image.src} alt="sample">`;
  });
  html += `</div>`;

  document.body.innerHTML = header_str + html + footer_str;

  window.print();

  //restore old page
  document.body.innerHTML = oldPage;
  return false;
}
