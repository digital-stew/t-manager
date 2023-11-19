let images; // array of image elements
let displayImageNumber = 0; // variable to hold what image is displayed
HRtimestamp(); //convert displayed timestamps to human readable

//auto load sample modal eg. shareable link
const queryID = queryParams.get("id");
if (queryID) {
  selectSample(queryID);
}

// from user search
function updateSamplesList() {
  const timeout = setTimeout(async () => {
    let searchText = document.getElementById("search").value; //get user imputed search string
    if (searchText === "") return; //return early if empty
    let tbody = document.getElementById("show");

    const res = await fetch("/samples/search.php?search=" + searchText);
    if (res.ok) {
      const reply = await res.text();
      tbody.outerHTML = reply;
      HRtimestamp();
      history.pushState(null, "", "/samples?search=" + searchText); //update address bar for link sharing etc
    } else {
      setError();
    }
  }, 1000);

  clearTimeout(timeout - 1);
}

//click on a sample
async function selectSample(rowID) {
  displayImageNumber = 0; // select sample bug fix
  await showModal("/samples/show.php?id=" + rowID);
  images = document.querySelectorAll(".sampleImage"); //get all sample images as html elements
  displayImage(displayImageNumber);
}

function displayImage(number) {
  document.getElementById("sampleImageCount").innerText =
    displayImageNumber + 1; // display number of image

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

function uploadAnotherImage() {
  let element = document.getElementById("uploadSampleImage");
  let newNode = element.cloneNode(true);
  newNode.value = "";
  document.getElementById("uploadSampleImageContainer").appendChild(newNode);
}

//layout page displaying all images and print data
function printSample(elem) {
  var oldPage = document.body.innerHTML; //save old page

  var header_str =
    `<html><head><title>` + document.title + "</title></head><body>";
  var footer_str = "</body></html>";

  let html = ""; //variable to hold printable page

  const sampleName = document.getElementById("sampleName").innerText;
  const sampleNumber = document.getElementById("sampleNumber").innerText;
  const samplePrintData = document.getElementById("samplePrintData").outerHTML;

  let sampleNotes;

  try {
    sampleNotes = document.getElementById("sampleNotes").innerText;
  } catch (error) {}

  html += `<section>`;
  html += `<h1>${sampleName} ${sampleNumber}</h1>`;
  html += `</section>`;

  html += `<section class="printData">`;
  html += samplePrintData;
  if (sampleNotes) {
    html += `<div><h4>Notes</h4>`;
    html += sampleNotes;
    html += `</div>`;
  }
  html += `</section>`;

  //get sample images
  html += `<div class="images">`;
  document.querySelectorAll(".sampleImage").forEach((image) => {
    html += `<div><img loading="eager" class="sampleImage" src=${image.src} alt="sample"></div>`;
  });
  html += `</div>`;

  document.body.innerHTML = header_str + html + footer_str;

  window.print();

  document.body.innerHTML = oldPage; //restore old page back to user viewable
  return false;
}
