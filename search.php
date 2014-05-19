<?php 

include_once 'config.inc.php';

$i_limit = 10; 
$s_searchTerm = strtoupper($_GET['key']);

?>
<h1>NoSQL E-Commerce</h1>

<h2>Risultati della Ricerca</h2>
<table>
    <thead>
        <td>Macrocategoria</td>
        <td>Categoria</td>
        <td>Prodotto</td>
        <td>Prezzo</td>
        <td>Acquisti</td>
        <td>Data Arrivo</td>
        <td>Dettaglio</td>
    </thead>
<?php

$m = new \MongoClient(); // connect
$db = $m->ecommerce;
$collection = $db->prodotti;

$searchKey = array('$or' => array(array('NOME_IDX' => $s_searchTerm), 
                                  array('CATEGORIA_IDX' => $s_searchTerm), 
                                  array('MACROCATEGORIA_IDX' => $s_searchTerm)
                                 )
                  );
$cursor = $collection->find($searchKey);
$start = microtime(true);

  foreach ($cursor as $doc) {
      ?>
    <tr>
    <td><?php echo $doc['macrocategoria']; ?></td>
        <td><?php echo $doc['categoria']; ?></td>
        <td><?php echo $doc['nome']; ?></td>
        <td><?php echo $doc['prezzo']; ?></td>
        <td><?php echo $doc['venduti']; ?></td>
        <td><?php echo $doc['dataarrivo']; ?></td>
        <td><a href="/detail.php?id=<?php echo $doc['id']; ?>">Vedi</a></td>
    </tr>
      <?php
  
}
  
$time_taken = microtime(true) - $start;

?>
</table>
<?php echo "Time taken: " . $time_taken; ?>