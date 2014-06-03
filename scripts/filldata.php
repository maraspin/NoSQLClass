#!/usr/local/zend/bin/php
<?php

include(__DIR__ . "/../config.inc.php");

try {

  $db = new PDO($dsn , $username, $password);
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $db->beginTransaction();

  $elasticaClient = new \Elastica\Client();

       // Load index
       $elasticaIndex = $elasticaClient->getIndex('ecommerce');

       // Create the index new
       $elasticaIndex->create(
            array(
                'number_of_shards' => 1,
                'number_of_replicas' => 1,
                'analysis' => array(
                    'analyzer' => array(
                        'indexAnalyzer' => array(
                            'type' => 'custom',
                            'tokenizer' => 'standard',
                            'filter' => array('standard','lowercase', 'mySnowball')
                        ),
                        'searchAnalyzer' => array(
                            'type' => 'custom',
                            'tokenizer' => 'standard',
                            'filter' => array('standard', 'lowercase', 'mySnowball')
                        )
                    ),
                    'filter' => array(
                        'mySnowball' => array(
                            'type' => 'snowball',
                            'language' => 'Italian'
                        )
                    )
                )
            ),
            true
        );

       $elasticaType = $elasticaIndex->getType('prodotto');

       // Define mapping
       $mapping = new \Elastica\Type\Mapping();
       $mapping->setType($elasticaType);
       $mapping->setParam('index_analyzer', 'indexAnalyzer');
       $mapping->setParam('search_analyzer', 'searchAnalyzer');

       // Definisce il field che funge da "booster" - deprecato in Lucene
       $mapping->setParam('_boost', array('name' => '_boost', 'null_value' => 1.0));

       // Set mapping
       $mapping->setProperties(array(
           'id'      => array('type' => 'integer', 'include_in_all' => FALSE),
           'macrocategoria'    => array(
                'type' => 'object',
                'properties' => array(
                    'id'      => array('type' => 'integer', 'include_in_all' => FALSE),
                    'nome_macrocat'  => array('type' => 'string', 'include_in_all' => TRUE)
                ),
            ), 
           'categoria'    => array(
                'type' => 'object',
                'properties' => array(
                    'id'      => array('type' => 'integer', 'include_in_all' => FALSE),
                    'nome_cat'  => array('type' => 'string', 'include_in_all' => TRUE)
                ),
            ),
           'nome'     => array('type' => 'string', 'include_in_all' => TRUE),
           'descrizione'     => array('type' => 'string', 'include_in_all' => FALSE),
           'dataarrivo'  => array('type' => 'date', 'include_in_all' => FALSE),
           'venduti'=> array('type' => 'integer', 'include_in_all' => TRUE),
           'prezzo'=> array('type' => 'integer', 'include_in_all' => TRUE),
           '_boost'  => array('type' => 'float', 'include_in_all' => FALSE)
    ));

    // Send mapping to type
    $mapping->send();
   
  
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

    $categorie = array('Elettronica', 
                       'Giardinaggio', 
                       'Sport', 
                       'Abbigliamento', 
                       'Salute', 
                       'Musica', 
                       'Fotografia', 
                       'Viaggi', 
                       'Cucina', 
                       'Modellismo');
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

    $varianti = array('Rosso', 
                       'Verde', 
                       'Blu', 
                       'Nero', 
                       'Giallo', 
                       'Marrone', 
                       'Viola'
                );
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
        descrizione character varying NULL,
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
    
    $namebase = array('pingo', 'pongo', 'bum', 'bam', 'foo', 
                      'baz', 'bar', 'pogo', 'dogo', 'sole',
                      'luna', 'volo', 'air', 'fire', 'tee');
    
    $prodottovariante = 0;
    
    $am_esDocuments = array();
  
    for ($x=0; $x< 2500; $x++) {
        
        $descrizione = '';
        $categoria = rand(2, (count($categorie)))-1;
        $prezzo = (rand(1, 200) * 10);
        $venduti = rand (0, 5000);
        $I_inserimento = new \DateTime();
        $I_inserimento->setTime(date('h'), rand(0,59));
        $dataarrivo = $I_inserimento->format("Y-m-d H:i");
        $namebaseel = count($namebase) - 1;
        $nome = $namebase[rand(0, $namebaseel)].$namebase[rand(0, $namebaseel)];
        if (rand(0,1) == 1) {
            $nome .= " ".$namebase[rand(0, $namebaseel)];
        }
        
        $chance = rand(0,20);
        switch ($chance) {
            case 5:
               $descrizione = 'Bistecchiera a contatto dalla potenza di 1800w. 5 posizioni di cottura : barbecue e tostiera. Piastre rimovibili e e vaschetta raccogligrassi lavabile, anche in lavastoviglie. Termostato regolabile a 5 posizioni.';
               break;
            case 10:
               $descrizione = 'Vuoi preparare delle buonissime crêpe fatte in casa? Ora è possibile, con il Maxi Crêpes ABC di XYZ! La super piastra per crêpe (diametro 55 cm) è amovibile e in ghisa con rivestimento antiaderente in ottone. Con una potenza di 1600W, la maxi Crêpes prepara crêpe sia dolci che salate!';
               break;
            case 15:
                $descrizione = 'Tenda da campeggio impermeabile da 8 posti della AKEL. Doppio telo, ripiegata occupa uno spazio minimo, così da poter essere portata comodamente anche nello zaino. Comoda e veloce da montare, anche se piove.';
                break;
            case 20:
                $descrizione = 'Con Party Grill potrete cucinare carne a volontà alla griglia, sulla piastra antiaderente o direttamente sulla fiamma. La griglia è facile da pulire, lavabile anche in lavastoviglie. Pratico anche da trasportare in camping. ';
                break;
        }
       
       $db->exec("INSERT INTO prodotto (id, nome, prezzo, venduti, descrizione, dataarrivo, categoria_id) VALUES (".
                      ($x+1).", '".$nome."',".$prezzo.",".$venduti.",'".$descrizione."','".$dataarrivo."',".$categoria.")");
       echo "Prodotto ".$nome." creato\n";
            
       for ($y = 0; $y < (rand(1, (count($varianti)-1))); $y++) {
            echo "Variante Prodotto ".$nome." " . $varianti[$y] ." creata\n";
            $db->exec("INSERT INTO prodottovariante (id, id_prodotto, id_variante) VALUES (".
                       (++$prodottovariante).",".$x.",".$y.")");
            $as_varianti[$y] = $varianti[$y];
       }
       
    // Create a document
    $am_prodDoc = array(
        'id'      => ($x+1),
        'nome'      => $nome,
        'descrizione'      => $descrizione,
        'macrocategoria'    => array(
            'id'      => 1,
            'nome_macrocat'  => 'Retail',
        ),
        'categoria'    => array(
            'id'      => $categoria,
            'nome_cat'  => $categorie[$categoria]
        ),
        'prezzo'     => $prezzo,
        'dataarrivo'  => $I_inserimento->getTimestamp(),
        'venduti'=> $venduti,
        'varianti' => implode(', ', $as_varianti),
        '_boost'  => 1.0
    );
        
    $as_varianti = array();

    // First parameter is the id of document.
    $am_esDoc = new \Elastica\Document(($x+1), $am_prodDoc);
    $am_esDocuments[] = $am_esDoc;
    
    if (($x % 500) == 0 || $x == 9999) {
      $elasticaType->addDocuments($am_esDocuments);
      $am_esDocuments = array();
    }
  
  }
  
  $elasticaType->getIndex()->refresh();
  $db->commit();
  
}
catch(PDOException $e) {
  echo 'Ahia! '.$e->getMessage()."\n";
}
