<?php

include_once 'config.inc.php';
include_once 'utils.inc.php';

$id = $_GET['id'];

try {

  $start = microtime(true);

  $connection = new PhpAmqpLib\Connection\AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
  $channel = $connection->channel();

  // Dichiariamo l'exchange
  $channel->exchange_declare('visitatori', 'fanout', false, false, false);

  $redis = new Predis\Client();

  if (!$redis->exists($id)) {

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

    $data = json_encode($item, true);
    $redis->set($id, $data);

  } else {
    $item = json_decode($redis->get($id), true);
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

    $msg = new PhpAmqpLib\Message\AMQPMessage(
                        "Qualcuno sta guardando l'oggetto ". $item['nome'],
                        array('delivery_mode' => PhpAmqpLib\Message\AMQPMessage::DELIVERY_MODE_PERSISTENT)
                      );
    $channel->basic_publish($msg, 'visitatori');
    $channel->close();
    $connection->close();

?>
</p>
<a href="./purchase.php?id=<?php echo $id; ?>">Compra Oggetto</a>

<p>Time taken: <strong><?php echo $time_taken; ?></strong></p>

<br />
<a href="/index.php">Back</a>
