<?php
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if (isset($_GET['search'])) {
    $sql = <<<EOD
        SELECT
            samples.rowid,
            samples.name,
            samples.number,
            samples.date,
            samples.otherref,
            (
                SELECT sample_images.webp_filename
                FROM sample_images
                WHERE sample_images.sample_id = samples.rowid
                LIMIT 1
            ) AS image
        FROM samples
        WHERE
            samples.name LIKE ? OR
            samples.otherref LIKE ? OR
            samples.number LIKE ?
        ORDER BY samples.date DESC;
    EOD;
    $stm = $db->prepare($sql) or die('sql error');
    $stm->bindValue(1, '%' . $_GET['search'] . '%', SQLITE3_TEXT) or die('sql error');
    $stm->bindValue(2, '%' . $_GET['search'] . '%', SQLITE3_TEXT) or die('sql error');
    $stm->bindValue(3, '%' . $_GET['search'] . '%', SQLITE3_TEXT) or die('sql error');
    $res = $stm->execute() or die('sql error');
} else {
    $sql = <<<EOD
    SELECT
        samples.rowid,
        samples.name,
        samples.number,
        samples.date,
        (
            SELECT sample_images.webp_filename
            FROM sample_images
            WHERE sample_images.sample_id = samples.rowid
            LIMIT 1
        ) AS image
    FROM samples
    ORDER BY samples.rowid DESC
    LIMIT 10;
    EOD;
    $res = $db->query($sql) or die('sql error');
}

?>


<div class="layout">

    <h1>Samples</h1>
    <button onclick="replaceElement('show', '/api/samples/add.php')">add new sample</button>
    <hr>
    <div class="sideBySide">
        <section class="tableSection">
            <input onkeyup="updateSamplesList()" type="search" id="search" placeholder="search..." class="border" style="margin-block: 1rem;" />
            <table id="show" class="border">
                <thead>
                    <tr>
                        <th>id</th>
                        <th>name</th>
                        <th>number</th>
                        <th>date</th>
                        <th>image</th>

                    </tr>
                </thead>
                <tbody id="searchResults">
                    <?php
                    while ($row = $res->fetchArray()) {
                        $link = '/assets/images/samples/webp/' . $row['image'];
                        echo "
                            <tr onclick='selectSample({$row['rowid']})'>
                                <td>{$row['rowid']}</td>
                                <td>{$row['name']}</td>
                                <td>{$row['number']}</td>
                                <td class='timestamp' style='width:100px;'>{$row['date']}</td>
                                <td><img src='{$link}' alt='' style='width:100px;height:100px;'></td>
                            </tr>
                            ";
                    }
                    ?>

                </tbody>
            </table>
        </section>

        <div class="sampleWrap" id="sampleWrap">
            <section id="sampleData" class="show_sample_section">
                <!-- placeholder for new data -->
            </section>
        </div>
    </div>

</div>







<script>
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

            const res = await fetch('/api/samples/search.php?search=' + searchText);
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
        await replaceElement('sampleWrap', '/api/samples/show.php?id=' + rowID);
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
        await replaceElement('sampleWrap', '/api/samples/edit.php?id=' + id);
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
        const req = await fetch('/api/samples/edit.php?id=' + queryID, {
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
</script>

<?php
$_SERVER['DOCUMENT_ROOT'] . '/footer.php';
?>