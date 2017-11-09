#!/usr/bin/php
<?php

ini_set('display_errors', 1);
$dsn = 'pgsql:host=localhost;port=55432;dbname=nosql';

try {

  $db = new PDO($dsn , 'nosql', 'nosql');
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $db->beginTransaction();

    // Macrocategorie
    $db->exec("CREATE TABLE macrocategoria (
          id integer NOT NULL,
         nome character varying(200) NOT NULL
      );");
    echo "Tabella macrocategoria creata\n";

    $db->exec("INSERT INTO macrocategoria (id, nome) VALUES (1, 'Retail')");
    $db->exec("INSERT INTO macrocategoria (id, nome) VALUES (2, 'Digital')");

    // Categorie
    $db->exec("CREATE TABLE categoria (
          id integer NOT NULL,
         nome character varying(200) NOT NULL,
         macrocategoria_id integer NOT NULL
      );");
    echo "Tabella categoria creata\n";

    $categorie = [
                  'Elettronica',
                  'Giardinaggio',
                  'Sport',
                  'Abbigliamento',
                  'Salute',
                  'Musica',
                  'Fotografia',
                  'Viaggi',
                  'Cucina',
                  'Modellismo'
                 ];
    for ($x=0; $x< count($categorie); $x++) {
            $db->exec("INSERT INTO categoria (id, nome, macrocategoria_id) VALUES (".($x+1).", '".$categorie[$x]."', 1)");
            echo "Categoria ".$categorie[$x]." creata\n";
    }

    // Varianti
    $db->exec("CREATE TABLE variante (
        id integer NOT NULL,
        nome character varying(200) NOT NULL
    );");
    echo "Tabella varianti creata\n";

    $varianti = [
                  'Rosso',
                  'Verde',
                  'Blu',
                  'Nero',
                  'Giallo',
                  'Marrone',
                  'Viola'
                ];
    for ($x=0; $x< count($varianti); $x++) {
            echo "Variante ".$varianti[$x]." creata\n";
            $db->exec("INSERT INTO variante (id, nome) VALUES (".($x+1).", '".$varianti[$x]."')");
    }


    // Prodotti
    $db->exec("CREATE TABLE prodotto (
        id integer NOT NULL,
        nome character varying NOT NULL,
        prezzo numeric(6,2) NOT NULL,
        venduti integer DEFAULT 0 NOT NULL,
        dataarrivo timestamp with time zone NOT NULL,
        categoria_id integer NOT NULL
    );
    ");
    echo "Tabella prodotti creata\n";

    // Varianti
    $db->exec("    CREATE TABLE prodottovariante (
        id integer NOT NULL,
        id_prodotto integer NOT NULL,
        id_variante integer NOT NULL
    );");
    echo "Tabella varianti/prodotto creata\n";

    $namebase = ['pingo', 'pongo', 'bum', 'bam', 'foo',
                 'baz', 'bar', 'pogo', 'dogo', 'sole',
                 'luna', 'volo', 'air', 'fire', 'tee',
                 'ping', 'lyn', 'chee', 'wire', 'shark',
                 'goo', 'goose', 'moose', 'sting', 'ray'
                ];

    $prodottovariante = 0;

    for ($x=0; $x< 200000; $x++) {

        $categoria = rand(2, (count($categorie)))-1;
        $prezzo = (rand(1, 200) * 10);
        $venduti = rand (0, 5000);
        $time = time();
        $timestamp = rand(($time - 2000000), $time);
        $dataarrivo = date("Y-m-d H:i:s", $timestamp);
        $namebaseel = count($namebase) - 1;
        $nome = $namebase[rand(0, $namebaseel)].$namebase[rand(0, $namebaseel)];
        if (rand(0,1) == 1) {
            $nome .= " ".$namebase[rand(0, $namebaseel)];
        }

       $db->exec("INSERT INTO prodotto (id, nome, prezzo, venduti, dataarrivo, categoria_id) VALUES (".
                      ($x+1).", '".$nome."',".$prezzo.",".$venduti.",'".$dataarrivo."',".$categoria.")");
       echo "Prodotto ".$nome." creato\n";

       for ($y = 0; $y < (rand(1, (count($varianti)-1))); $y++) {
            echo "Variante Prodotto ".$nome." " . $varianti[$y] ." creata\n";
            $db->exec("INSERT INTO prodottovariante (id, id_prodotto, id_variante) VALUES (".
                       (++$prodottovariante).",".$x.",".$y.")");
        }

    }
     $db->commit();

}
catch(PDOException $e) {
  echo 'Ahia! '.$e->getMessage()."\n";
}
