<?php
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

$id = htmlspecialchars($_GET['id']);
// SELECT all from sample table and all images from its table
$sql = <<<EOD
    SELECT samples.*, sample_images.webp_filename
    FROM samples
    LEFT JOIN sample_images
    ON samples.rowid = sample_images.sample_id
    WHERE rowid = ?;
EOD;
$stm = $db->prepare($sql);
$stm->bindValue(1, SQLite3::escapeString($id));
$res = $stm->execute();

$sample = $res->fetchArray(); // get all sample details including first image
$images = array(); // create array to hold image names
array_push($images, $sample['webp_filename']); // push the first image
while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
    array_push($images, $row['webp_filename']); // push the rest
}
?>

<div class="box sample__wrapper">
    <div class="box sample_show_titleBar">
        <span>
            <p>id:</p>
            <p><?= $sample['rowid'] ?></p>
        </span>
        <span>
            <p>name:</p>
            <p><?= $sample['name'] ?></p>
        </span>
        <span>
            <p>number:</p>
            <p><?= $sample['number'] ?></p>
        </span>
        <span>
            <p>date:</p>
            <p class="timestamp"><?= $sample['date'] ?></p>
        </span>
    </div>
    <div class="sample__imageWrapper box">
        <div class="imageCount">

            <span id="count">1</span> <span id="imageAmount">/<?= sizeof($images) ?></span>
        </div>
        <button class="prev" onclick="imageDown()">&lt;</button>
        <img id="sampleImage" src="/assets/images/samples/webp/<?= $sample['webp_filename'] ?>" alt="">
        <button class="next" onclick="imageUp()">&gt;</button>
    </div>
    <div class="sample_show_data" id="sampleData">
        <p><?php if (strlen($sample['printdata'])) {
                echo '<h3>front</h3>' . $sample['printdata'];
            } ?></p>
        <p><?php if (strlen($sample['printdataback'])) {
                echo '<h3>back</h3>' . $sample['printdataback'];
            } ?></p>
        <p><?php if (strlen($sample['printdataother'])) {
                echo '<h3>other</h3>' . $sample['printdataother'];
            } ?></p>
        <p><?php if (strlen($sample['notes'])) {
                echo '<h3>notes</h3>' . $sample['notes'];
            } ?></p>

        <button onclick="editSample()">edit</button>
    </div>
</div>
<script>
let images = JSON.parse('<?= json_encode($images) ?>'); // pass image array to client
let imageCountElement = document.getElementById('count')
let imageNumber = 0;
let image = document.getElementById('sampleImage');

async function removeImage() {
    let form = new FormData();
    form.append('removeImage', 'true');
    form.append('image', images[imageNumber])

    const req = await fetch('/api/samples/edit.php?id=<?= $id ?>', {
        method: 'POST',
        body: form
    })
    const res = await req.text()

    if (res === "image=removed") images = images.map((image, index) => {
        if (imageNumber == index) return image;
    })
    image.src = '';
    let imageAmount = document.getElementById('imageAmount')
    imageAmount.innerText = '/' + (imageAmount.innerText.slice(1) - 1);
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
async function editSample() {
    let div = document.getElementById('sampleData')
    const res = await fetch("/api/samples/edit.php?id=<?php echo $id; ?>")
    if (res.ok) {
        const reply = await res.text();
        div.innerHTML = reply;
    } else {
        //setError();
        console.log('error');
    }
}

function updateSample(x) {
    console.log('update sample');

    console.log(x);
}
</script>

<?php
require dirname(__DIR__) . '/footer.php';
?>