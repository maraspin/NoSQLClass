<?php

include_once 'config.inc.php';
include_once 'utils.inc.php';

$id = $_GET['id'];

try {

  $redis = new Predis\Client();

  /*
  $redis = new PredisClient(array(
      "scheme" => "tcp",
      "host" => "127.0.0.1",
      "port" => 6379));
  */

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

  $redis->lPush("magazzino", $item['nome']);

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
