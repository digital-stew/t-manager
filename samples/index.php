<?php
require dirname(__DIR__).'/header.php';

 $sql = 'SELECT * FROM samples ORDER BY rowid DESC LIMIT 10 ';
 $res = $db->query(SQLite3::escapeString($sql)); 
 //$db->close()
 ?>

<body onload="clear()">
    <div>
        <input onkeyup="updateSamplesList()" type="search" id="search" placeholder="search..." />
    </div>
    <div class="error"></div>
    <table id="table">
        <thead>
            <tr>
                <th onclick="">id</th>
                <th>name</th>
                <th>number</th>
                <th>date</th>

            </tr>
        </thead>
        <tbody id="searchResults">
            <?php 
        while ($row = $res->fetchArray()){ 
            echo "
            <tr onclick='selectSample({$row['rowid']})'>
                <td>{$row['rowid']}</td>
                <td>{$row['name']}</td>
                <td>{$row['number']}</td>
                <td>{$row['date']}</td>
            </tr>    
                "; 
            } 
                ?>

        </tbody>
    </table>

    <h1>hello world</h1>
</body>





<script>
function clear() {
    document.getElementById('search').value = '';
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
</script>

<?php
require dirname(__DIR__).'/footer.php';
?>