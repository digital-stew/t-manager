<?php
include $_SERVER['DOCUMENT_ROOT'] . '/models/sample.php';
$sample = new sample();
$result = $sample->search('test');
//print_r($result);
echo 'index <br/>';
//print_r($result);
foreach ($result as $sampleRes){
    echo $sampleRes['id'] . '<br/>';
}
echo 'index <br/>';
$res = $sample->get(300);
print_r($res);
?>
<div>
    html
</div>