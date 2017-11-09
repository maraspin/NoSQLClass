<?php

include_once 'config.inc.php';
include_once 'utils.inc.php';

?>

<h1>NoSQL E-Commerce</h1>


<form action="search.php">
    <input name="key" /><input type="submit" value="Cerca">
</form>


<?php

try {

  /*
  $db = new PDO($dsn , $username, $password);
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $sql = 'SELECT prodotto.*, macrocategoria.nome as macrocategoria,
          categoria.nome as categoria FROM prodotto join categoria on categoria.id = prodotto.categoria_id
          join macrocategoria on macrocategoria.id = categoria.macrocategoria_id
          ORDER by prodotto.dataarrivo DESC, categoria.nome, prodotto.nome LIMIT '.$itemsToShow;
  */
  $redis = new Predis\Client();

  $start = microtime(true);

  ?>

  <h2>Ultimi <?php echo $itemsToShow; ?> Articoli...</h2>
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

    $am_items2 = $redis->lRange('latest', 0, ($itemsToShow-1));
    foreach($am_items2 as $json){

      $row = json_decode($json, true);

      ?>
    <tr>
    <td><?php echo $row['macrocategoria']; ?></td>
        <td><?php echo $row['categoria']; ?></td>
        <td><?php echo $row['nome']; ?></td>
        <td><?php echo $row['prezzo']; ?></td>
        <td><?php echo $row['venduti']; ?></td>
        <td><?php echo $row['dataarrivo']; ?></td>
        <td><a href="/detail.php?id=<?php echo $row['id']; ?>">Vedi</a></td>
    </tr>
      <?php
  }

  $time_taken = microtime(true) - $start;

} catch (PDOException $e) {
  handleError("Errore nella connessione con PosgreSQL: " . $e->getMessage());
} catch (Predis\Connection\ConnectionException $e) {
  handleError("Errore nella connessione con Redis: " . $e->getMessage());
} catch (\Exception $e) {
  handleError("Errore nell'esecuzione dello script: " . $e->getMessage());
}
?>
</table>

<p>Time taken: <strong><?php echo $time_taken; ?></strong></p>