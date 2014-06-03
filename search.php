<?php 

include_once 'config.inc.php';

$s_searchTerm = $_GET['key'];

?>

<h1>NoSQL E-Commerce</h1>

<form action="search.php">
    <input name="key" value="<?php echo $s_searchTerm; ?>"/><input type="submit" value="Cerca">
</form>


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

try {
    
    $start = microtime(true);
    
    $elasticaClient = new \Elastica\Client();

    // Load index
    $elasticaIndex = $elasticaClient->getIndex('ecommerce');
    $elasticaType = $elasticaIndex->getType('prodotto');
    
    // Define a Query. We want a string query.
    $elasticaQueryString  = new \Elastica\Query\QueryString();

    //'And' or 'Or' default : 'Or'
    $elasticaQueryString->setDefaultOperator('AND');
    $elasticaQueryString->setQuery($s_searchTerm);
    
    // Create the actual search object with some data.
    $elasticaQuery = new \Elastica\Query();
    $elasticaQuery->setQuery($elasticaQueryString);
    // $elasticaQuery->setFrom(50);    // Where to start?
    $elasticaQuery->setLimit(25);   // How many?

    //Search on the index.
    $elasticaResultSet = $elasticaIndex->search($elasticaQuery);
    
    // Sostituire con 
    $elasticaResultSet = array();

  foreach($elasticaResultSet as $elasticaResult){  
    $row = $elasticaResult->getData();
      
      ?>
    <tr>
    <td><?php echo $row['macrocategoria']['nome_macrocat']; ?></td>
        <td><?php echo $row['categoria']['nome_cat']; ?></td>
        <td><?php echo $row['nome']; ?></td>
        <td><?php echo $row['prezzo']; ?></td>
        <td><?php echo $row['venduti']; ?></td>
        <td><?php echo $row['dataarrivo']; ?></td>
        <td><a href="/detail.php?id=<?php echo $row['id']; ?>">Vedi</a></td>
    </tr>
      <?php
  }
  
  $time_taken = microtime(true) - $start;
  
}
  catch (PDOException $e) {
    print $e->getMessage();
}
?>
</table>

<?php echo "Time taken: " . $time_taken; ?>