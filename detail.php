<?php

include_once 'config.inc.php';
include_once 'utils.inc.php';

$id = $_GET['id'];

try {

  $start = microtime(true);

  $redis = new Predis\Client();

  /*
  $redis = new PredisClient(array(
      "scheme" => "tcp",
      "host" => "127.0.0.1",
      "port" => 6379));
  */

  // Verificare se esiste la chiave su Redis...
  if ( COMPLETARE ) {

    $db = new PDO($dsn , $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT prodotto.*, macrocategoria.nome as macrocategoria, variante.nome as variante,
            categoria.nome as categoria FROM prodotto
            JOIN categoria on categoria.id = prodotto.categoria_id
            JOIN macrocategoria on macrocategoria.id = categoria.macrocategoria_id
            JOIN prodottovariante on prodotto.id = prodottovariante.id_prodotto
            LEFT JOIN variante on prodottovariante.id_variante = variante.id
            WHERE prodotto.id = ". $id ;

    $st = $db->query($sql);
    $row = $st->fetch();
    $item = $row;
    $varianti = [];
    while ($row = $st->fetch()) {
      $varianti[] = $row['variante'];
    }
    $item['variante'] = implode(', ',$varianti);

    // Serializziamo il dato reperito da DB...
    $data = json_encode($item, true);

    // Salviamo su Redis il dato reperito da DB
    $redis->COMPLETARE;

  } else {
    // Reperiamo da Redis il dato di nostro interesse, precedentemente cachato
    $item = json_decode($redis->COMPLETARE, true);
  }
} catch (PDOException $e) {
  handleError("Errore nella connessione con PosgreSQL: " . $e->getMessage());
} catch (Predis\Connection\ConnectionException $e) {
  handleError("Errore nella connessione con Redis: " . $e->getMessage());
} catch (\Exception $e) {
  handleError("Errore nell'esecuzione dello script: " . $e->getMessage());
}



?>
<h1>Scheda Prodotto: <?php echo $item['nome']; ?></h1>
<p>Prezzo: <?php echo $item['prezzo']; ?></p>
<p>Venduti: <?php echo $item['venduti']; ?></p>
<p>Disponibile dal: <?php echo $item['dataarrivo']; ?></p>
<?php if (strlen($item['variante']) > 0) { ?>
  <p>Varianti: <?php echo $item['variante']; ?>
<?php } else { ?>
  <p>Variante Unica</p>
<?php
}
    $time_taken = microtime(true) - $start;
?>
</p>

<p>Time taken: <strong><?php echo $time_taken; ?></strong></p>

<br />
<a href="/index.php">Back</a>
