<?php

include_once 'config.inc.php';

$id = $_GET['id'];

$start = microtime(true);

$m = new \MongoClient(); // connect
$db = $m->ecommerce;
$collection = $db->prodotti;

$m_searchKey = array('id' => (int)$id);
$as_prodotto = $collection->findOne($m_searchKey);

foreach($as_prodotto as $s_attribute => $m_value) {
    if (!is_array($m_value)) {
        echo ucfirst($s_attribute) . ": " . $m_value . "\n<br />";
    } 
}

if (array_key_exists('image_id', $as_prodotto)) {
    $m_imageKey = $as_prodotto['image_id'];
    echo '<img src="/mongoimg.php?id='. $m_imageKey.'" /><br /><br />';
}
    
$time_taken = microtime(true) - $start;
?>

<?php echo "Time taken: " . $time_taken; ?>

<br />
<a href="/index.php">Back</a>