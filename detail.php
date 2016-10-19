<?php

include_once 'config.inc.php';

$id = $_GET['id'];

try {

  $db = new PDO($dsn , $username, $password);
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  
  $sql = "SELECT prodotto.*, macrocategoria.nome as macrocategoria, variante.nome as variante,
          categoria.nome as categoria FROM prodotto join categoria on categoria.id = prodotto.categoria_id 
          join macrocategoria on macrocategoria.id = categoria.macrocategoria_id 
          join prodottovariante on prodotto.id = prodottovariante.id_prodotto 
          join variante on prodottovariante.id_variante = variante.id
          WHERE prodotto.id = ". $id ;

  $start = microtime(true);
  
  $st = $db->query($sql);
  $row = $st->fetch();
  $item = $row;
  $varianti = $item['variante'];
  while ($row = $st->fetch()) {
    $varianti .= ', '.$row['variante'];
  }
  $item['variante'] = $varianti;
}
  catch (PDOException $e) {
    print $e->getMessage();
}
?>
<h1>Scheda Prodotto: <?php echo $item['nome']; ?></h1>
<p>Prezzo: <?php echo $item['prezzo']; ?></p>
<p>Venduti: <?php echo $item['venduti']; ?></p>
<p>Disp. Dal: <?php echo $item['dataarrivo']; ?></p>
<p>Varianti: <?php echo $item['variante']; ?>
    
<?php  
    
    $time_taken = microtime(true) - $start;
?>
</p>

<?php echo "Time taken: " . $time_taken; ?>

<br />
<a href="/index.php">Back</a>
