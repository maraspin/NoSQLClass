<?php

include_once 'config.inc.php';

$id = $_GET['id'];

try {

  $db = new PDO($dsn , $username, $password);
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $sql = "SELECT prodotto.*, macrocategoria.nome as macrocategoria, variante.nome as variante,
          categoria.nome as categoria FROM prodotto
          JOIN categoria on categoria.id = prodotto.categoria_id
          JOIN macrocategoria on macrocategoria.id = categoria.macrocategoria_id
          JOIN prodottovariante on prodotto.id = prodottovariante.id_prodotto
          LEFT JOIN variante on prodottovariante.id_variante = variante.id
          WHERE prodotto.id = ". $id ;

  $start = microtime(true);

  $st = $db->query($sql);
  $row = $st->fetch();
  $item = $row;
  $varianti = [];
  while ($row = $st->fetch()) {
    $varianti[] = $row['variante'];
  }
  $item['variante'] = implode(', ',$varianti);
}
  catch (PDOException $e) {
    print $e->getMessage();
}
?>
<h1>Scheda Prodotto: <?php echo $item['nome']; ?></h1>
<p>Prezzo: <?php echo $item['prezzo']; ?></p>
<p>Venduti: <?php echo $item['venduti']; ?></p>
<p>Disponibile dal: <?php echo $item['dataarrivo']; ?></p>
<?php if (count($varianti) > 0) { ?>
  <p>Varianti: <?php echo $item['variante']; ?>
<?php }
    $time_taken = microtime(true) - $start;
?>
</p>

<p>Time taken: <strong><?php echo $time_taken; ?></strong></p>

<br />
<a href="/index.php">Back</a>
