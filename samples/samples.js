HRtimestamp();
// if GET?id send request for it and display
const queryParams = new URLSearchParams(window.location.search);
const queryID = queryParams.get('id');
if (queryID && document.getElementById('sampleData').innerText == '') selectSample(queryID)

// from user search
function updateSamplesList() {
    const timeout = setTimeout(async () => {
        let searchText = document.getElementById('search').value;
        if (searchText === '') return;
        let tbody = document.getElementById('show');

        const res = await fetch('/samples/search.php?search=' + searchText);
        if (res.ok) {
            const reply = await res.text();
            tbody.outerHTML = reply;
            HRtimestamp();
            history.pushState(null, "", "/samples?search=" + searchText);

        } else {
            setError();
        }
    }, 1000);

    clearTimeout(timeout - 1)
}

//click on a sample
async function selectSample(rowID) {
    await replaceElement('sampleWrap', '/samples/show.php?id=' + rowID);
    getSampleImages();
    moveToCenter();

    //Get the request parameters
    const queryParams = new URLSearchParams(window.location.search);
    const queryID = queryParams.get('id');
    queryParams.set('id', rowID);
    // Replace the current query string with the updated parameters
    const newUrl = `${window.location.pathname}?${queryParams.toString()}`;
    // Change the URL without triggering a page refresh
    window.history.pushState({}, '', newUrl);
}

//============== sample data show (right side) =================
// initialize global variables
let imageNumber = 0; // what image to show
let images // array to hold image names as strings
let imageCountElement // html element holding number of images in array
let image // image element

function getSampleImages() {
    try {
        images = JSON.parse(document.getElementById('sampleData').dataset.images); // pass image array to client

    } catch (error) {
        return
    }
    imageCountElement = document.getElementById('count')
    image = document.getElementById('sampleImage');
}

function imageUp() {
    if (imageNumber >= images.length - 1) return;
    imageNumber++;
    imageCountElement.innerText = imageNumber + 1
    image.src = "/assets/images/samples/webp/" + images[imageNumber];
}

function imageDown() {
    if (imageNumber <= 0) return;
    imageNumber--;
    imageCountElement.innerText = imageNumber + 1

    image.src = "/assets/images/samples/webp/" + images[imageNumber];
}

// moved clicked on sample into user view regardless of Y window position
function moveToCenter() {
    let wrapper = document.getElementById('sampleWrap');
    if (!wrapper) return
    let offset = window.scrollY;
    if (offset > 100) offset -= 100
    wrapper.style.top = offset + "px";
}

// click edit sample button
async function getEditSample(id) {
    await replaceElement('sampleWrap', '/samples/edit.php?id=' + id);
    getSampleImages() // decode the new json
}

async function deleteImage() {
    const queryParams = new URLSearchParams(window.location.search);
    const queryID = queryParams.get('id');
    // get link to image
    const image = document.getElementById('sampleImage').src
    // Split the inputString into an array of substrings
    const substrings = image.split("/");
    // Get the last result using array indexing
    const filename = substrings[substrings.length - 1];

    let formData = new FormData();
    formData.append('removeImage', filename)
    const req = await fetch('/samples/edit.php?id=' + queryID, {
        method: 'POST',
        body: formData
    })

    const res = await req.text();
    if (res == 'image=removed') {
        images[imageNumber] = "Cross_red_circle.svg"
        document.getElementById('sampleImage').src = "/assets/images/samples/webp/Cross_red_circle.svg"
        document.getElementById('sampleImage').style.width = 500 + 'px'
    }
    return false
}