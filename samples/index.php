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
    //$stm = $db->prepare("SELECT * FROM samples WHERE name LIKE ? ");
    $stm = $db->prepare($sql);
    $stm->bindValue(1, '%' . SQLite3::escapeString($_GET['search']) . '%', SQLITE3_TEXT);
    $stm->bindValue(2, '%' . SQLite3::escapeString($_GET['search']) . '%', SQLITE3_TEXT);
    $stm->bindValue(3, '%' . SQLite3::escapeString($_GET['search']) . '%', SQLITE3_TEXT);
    $res = $stm->execute();
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


<div class="box samples_index_search-wrapper"
    style="display:flex;margin-block: 1rem;flex-direction: column;gap: 0.5rem;">
    <input onkeyup="updateSamplesList()" type="search" id="search" placeholder="search..." />
    <button onclick="replaceElement('tableWrapper', '/api/samples/add.php')">add new sample</button>
</div>
<div id="tableWrapper" class="box" style="padding-inline: 2rem;">
    <h2 id="recentSamples">recent samples</h2>
    <table id="table">
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
                <td>{$row['original_filename']}</td>
                <td class='timestamp'>{$row['date']}</td>
                <td><img src='{$link}' alt='' style='width:100px;height:100px;'>
            </td>
            </tr>";
            }
            ?>

        </tbody>
    </table>
</div>







<script>
function clear() {
    //document.getElementById('search').value = '';
}

function setError() {
    let element = document.querySelector('.error')
    element.innerText = 'error';
    element.classList.add('animate');
    setTimeout(() => {
        element.innerText = '';
        element.classList.remove('animate');
    }, 2000)

}

function updateSamplesList() {
    const timeout = setTimeout(async () => {
        let searchText = document.getElementById('search').value;
        if (searchText === '') return;
        let tbody = document.getElementById('searchResults');

        const res = await fetch('/api/samples/search.php?search=' + searchText);
        if (res.ok) {
            const reply = await res.text();
            tbody.innerHTML = reply;
            document.getElementById('recentSamples').innerText = '';
            HRtimestamp();
            history.pushState(null, "", "/samples?search=" + searchText);

        } else {
            setError();
        }
    }, 1000);

    clearTimeout(timeout - 1)
}


function selectSample(rowID) {
    console.log('click!! ' + rowID);
    window.location.href = '/samples/show.php?id=' + rowID;
}

function addSample(e) {
    e.preventDefault();
    console.log('ADD SAMPLE');
}
</script>

<?php
require dirname(__DIR__) . '/footer.php';
?>