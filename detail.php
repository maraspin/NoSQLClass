<?php

include_once 'config.inc.php';

$id = $_GET['id'];

$start = microtime(true);

// Rimuovere questa parte ed interagire con MongoDB per recuperare i dati del prodotto...
$s_mockProduct = '{
  "id": 61163,
  "nome": "firepogo",
  "prezzo": "1070.00",
  "venduti": 2415,
  "dataarrivo": "2014-05-07 01:00:00+02",
  "categoria_id": 3,
  "variante": "Blu",
  "categoria": "Sport",
  "macrocategoria": "Retail",
  "varianti": [
    "Verde",
    "Rosso",
    "Blu"
  ],
  "lunghezza": 15,
  "altezza": 45,
  "peso": 47,
  "profondita": 33,
  "componenti": 37
}';
$as_prodotto = json_decode($s_mockProduct, true);

foreach($as_prodotto as $s_attribute => $m_value) {
    if (!is_array($m_value)) {
        echo ucfirst($s_attribute) . ": " . $m_value . "\n<br />";
    } 
}
    
$time_taken = microtime(true) - $start;
?>

<?php echo "Time taken: " . $time_taken; ?>

<br />
<a href="/index.php">Back</a>