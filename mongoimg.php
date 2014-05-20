<?php

$id = $_GET['id'];

$m = new \MongoClient(); // connect
$db = $m->ecommerce;
$gridfs = $db->getGridFS();                    // Initialize GridFS
     
$file = $gridfs->findOne(array('_id' => new MongoID($id)));

header('Content-Type: image/jpeg');
echo $file->getBytes();