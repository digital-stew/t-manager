<?php
require $_SERVER['DOCUMENT_ROOT'].'/header.php';

$id = htmlspecialchars($_GET['id']);
// SELECT all from sample table and all images from its table
$stm = $db->prepare('SELECT samples.*, sample_images.image FROM samples LEFT JOIN sample_images ON samples.rowid = sample_images.sample_id WHERE rowid = ?;');
$stm->bindValue(1,SQLite3::escapeString($id));
//$stm->bindValue(2,SQLite3::escapeString($id));
$res = $stm->execute();
$sample = $res->fetchArray(); // get all sample details including first image

$images = array(); // create array to hold image names
array_push($images, $sample['image']); // push the first image
while ($row = $res->fetchArray(SQLITE3_ASSOC)){
    array_push($images, $row['image']); // push the rest
}
?>

<div class="box sample__wrapper">
    <div class="box sample_show_titleBar">
        <span>
            <p>id:</p>
            <p><?=$sample['rowid']?></p>
        </span>
        <span>
            <p>name:</p>
            <p><?=$sample['name']?></p>
        </span>
        <span>
            <p>number:</p>
            <p><?=$sample['number']?></p>
        </span>
        <span>
            <p>date:</p>
            <p id="timestamp"><?=$sample['date']?></p>
        </span>
    </div>
    <div class="sample__imageWrapper box">
        <span id="count">1</span> <span>/<?php echo sizeof($images) ?></span>
        <button onclick="imageDown()">-</button>
        <img id="sampleImage" src="/assets/images/sample-images/<?php echo $sample['image'] ?>" alt="">
        <button onclick="imageUp()">+</button>
    </div>
    <div class="sample_show_data" id="sampleData">
        <p><?php if (strlen($sample['printdata'])) {echo '<h3>front</h3>'. $sample['printdata'];} ?></p>
        <p><?php if (strlen($sample['printdataback'])) {echo '<h3>back</h3>'. $sample['printdataback'];} ?></p>
        <p><?php if (strlen($sample['printdataother'])) {echo '<h3>other</h3>'. $sample['printdataother'];} ?></p>
        <p><?php if (strlen($sample['notes'])) {echo '<h3>notes</h3>'. $sample['notes'];} ?></p>

        <button onclick="editSample()">edit</button>
    </div>
</div>

<script>
let timestamp = document.getElementById('timestamp');
timestamp.innerText = new Date(timestamp.innerText * 1000).toLocaleDateString()

let images = JSON.parse('<?php echo json_encode($images) ?>'); // pass image array to client
let imageCountElement = document.getElementById('count')
let imageNumber = 0;
let image = document.getElementById('sampleImage');

async function removeImage() {
    let form = new FormData();
    form.append('removeImage', 'true');
    form.append('image', images[imageNumber])

    const req = await fetch('/api/samples/edit.php?id=<?=$id?>', {
        method: 'POST',
        body: form
    })
    const res = await req.text()
    console.log(res);
}

function imageUp() {
    if (imageNumber >= images.length - 1) return;
    imageNumber++;
    imageCountElement.innerText = imageNumber + 1
    image.src = "/assets/images/sample-images/" + images[imageNumber];
}

function imageDown() {
    if (imageNumber <= 0) return;
    imageNumber--;
    imageCountElement.innerText = imageNumber + 1

    image.src = "/assets/images/sample-images/" + images[imageNumber];
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
require dirname(__DIR__).'/footer.php';
?>