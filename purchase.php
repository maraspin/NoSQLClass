<?php

ini_set('display_errors', 1);

include_once 'config.inc.php';
include_once 'utils.inc.php';

$id = $_GET['id'];

try {

  $redis = new Predis\Client();

  $connection = new \PhpAmqpLib\Connection\AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
  $channel = $connection->channel();
  $channel->queue_declare('magazzino', false, true, false, false);

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

  $qty = rand(1, 4);

  list($x) = explode(", ",$item['variante']);

  $message = [];
  $prodotto['id'] = $id;
  $prodotto['nome'] = $item['nome'];
  $prodotto['variante'] = $x;
  $prodotto['prezzo'] = $item['prezzo'];
  $prodotto['quantita'] = $qty;

  $utente['cognome'] = 'Del Mas';
  $utente['nome'] = 'Felix';
  $utente['indirizzo'] = 'Via dei Rizzi, 90';

  $message['id'] = uniqid();
  $message['dataora'] = date("Y-m-d H:i:s");
  $message['prodotti'] = $qty;
  $message['totale'] = $item['prezzo'] * $qty;
  $message['prodotto'] = $prodotto;
  $message['utente'] = $utente;

  $msgPayload = json_encode($message);

  $msg = new \PhpAmqpLib\Message\AMQPMessage(
                        $msgPayload,
                        array('delivery_mode' => \PhpAmqpLib\Message\AMQPMessage::DELIVERY_MODE_PERSISTENT)
                      );
  $channel->basic_publish($msg, '', 'magazzino');
  $channel->close();
  $connection->close();


} catch (PDOException $e) {
  handleError("Errore nella connessione con PosgreSQL: " . $e->getMessage());
} catch (Predis\Connection\ConnectionException $e) {
  handleError("Errore nella connessione con Redis: " . $e->getMessage());
} catch (\Exception $e) {
  handleError("Errore nell'esecuzione dello script: " . $e->getMessage());
}

?>
<h1>Congratulazioni!</h1>
<p>Oggetto <?php echo $item['nome']; ?> Acquistato</p>
<br />
<a href="/index.php">Back</a>
