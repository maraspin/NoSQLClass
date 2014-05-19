#!/usr/local/zend/bin/php
<?php

include_once __DIR__ . '/../config.inc.php';

try {

  // MongoDB Connection
  $m = new \MongoClient(); // connect
  $db = $m->ecommerce;
  $collection = $db->prodotti;
    
  // PostgreSQL Connection
  $db = new PDO($dsn , $username, $password);
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  
  // Macrocategorie
  // $db->exec("CREATE UNIQUE INDEX primary_key_product ON prodotto USING btree (id)");
  //$db->exec("CREATE UNIQUE INDEX primary_key_cat ON categoria USING btree (id)");
  //$db->exec("CREATE UNIQUE INDEX primary_key_macrocat ON macrocategoria USING btree (id)");
  echo "Indici creati\n";
  
  // Data gets fetched
  $sql = 'SELECT prodotto.*, variante.nome as variante, categoria.nome as categoria,
          macrocategoria.nome as macrocategoria 
          FROM prodotto join categoria on categoria.id = prodotto.categoria_id 
          join macrocategoria on macrocategoria.id = categoria.macrocategoria_id 
          join prodottovariante on prodotto.id = prodottovariante.id_prodotto join variante on prodottovariante.id_variante = variante.id
          ORDER by prodotto.dataarrivo DESC, categoria.nome, prodotto.nome';
  
  echo "\nPress a key to continue importing data into MongoDB\n";
  $handle = fopen ("php://stdin","r");
  $line = fgets($handle);
  
  $start = microtime(true);

  $i_currentId = null;
  $as_variants = array();
  
  $stmt = $db->query($sql);
  $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
  
  $as_randomAttributes = array('lunghezza', 'larghezza', 'altezza', 'peso', 'profondita', 'aperture', 'componenti');
  
  while ($row = $stmt->fetch()){  

     echo "Documento ".$row["nome"]."\n";
     if (!in_array($row['variante'], $as_variants)) {
         $as_variants[] = $row['variante'];
     }
     
    if ($row['id'] !== $i_currentId && 
        $i_currentId !== null) {
        
        unset($row['categoria_id']);
        unset($row['variante']);
        
        $document['NOME_IDX']= strtoupper($document['nome']);
        $document['CATEGORIA_IDX']= strtoupper($document['categoria']);
        $document['MACROCATEGORIA_IDX']= strtoupper($document['macrocategoria']);
        
        $document['varianti'] = $as_variants;
        
        foreach($as_randomAttributes as $s_attribute) {
            if (rand(0,5) < 4) {
                $document[$s_attribute] = rand(15, 50);
            }
        }
        
        $collection->insert($document);
        
        $as_variants = array();
        
    }
    
    $i_currentId = $row['id'];
    $document = $row;
    
  }
  
    
}
catch(PDOException $e) {
  echo 'Ahia! '.$e->getMessage()."\n";
}
