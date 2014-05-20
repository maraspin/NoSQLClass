#!/usr/local/zend/bin/php
<?php

include_once __DIR__ . '/../config.inc.php';

try {

  // MongoDB Connection
  $m = new \MongoClient(); // connect
  $mongo = $m->ecommerce;
  $collection = $mongo->prodotti;
    
  // PostgreSQL Connection
  $db = new PDO($dsn , $username, $password);
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  
  // $db->exec("CREATE UNIQUE INDEX primary_key_product ON prodotto USING btree (id)");
  // $db->exec("CREATE UNIQUE INDEX primary_key_cat ON categoria USING btree (id)");
  // $db->exec("CREATE UNIQUE INDEX primary_key_macrocat ON macrocategoria USING btree (id)");
  echo "Indici creati\n";
  
  // Data gets fetched
  $sql = 'SELECT prodotto.*, variante.nome as variante, categoria.nome as categoria,
          macrocategoria.nome as macrocategoria 
          FROM prodotto join categoria on categoria.id = prodotto.categoria_id 
          join macrocategoria on macrocategoria.id = categoria.macrocategoria_id 
          join prodottovariante on prodotto.id = prodottovariante.id_prodotto join variante on prodottovariante.id_variante = variante.id
          ORDER by prodotto.id ASC, categoria.nome';
  
  $start = microtime(true);

  $i_currentId = null;
  $as_variants = array();
  
  $stmt = $db->query($sql);
  $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
  
  $as_randomAttributes = array('lunghezza', 'larghezza', 'altezza', 'peso', 'profondita', 'aperture', 'componenti');
  
  while ($row = $stmt->fetch()){  

     echo "Documento ".$row["id"]." - ".$row["nome"]."\n";
     if (!in_array($row['variante'], $as_variants)) {
         $as_variants[] = $row['variante'];
     }
     
    if ($row['id'] !== $i_currentId && 
        $i_currentId !== null) {
        
        unset($row['categoria_id']);
        unset($row['variante']);
        
        $document['varianti'] = $as_variants;
        
        foreach($as_randomAttributes as $s_attribute) {
            if (rand(0,5) < 4) {
                $document[$s_attribute] = rand(15, 50);
            }
        }

        if ($document['id'] == 1) {
        
            $I_gridfs = $mongo->getGridFS();
            $s_filename = $document['id'].'.jpg';
            $m_storedfile_id = $I_gridfs->storeFile(__DIR__.'/../data/monocle.jpg',
                                                    array("metadata" => array("filename" => $s_filename, 
                                                                              "contentType" => 'image/jpeg')
                                                         ));
            $document['image_id'] = $m_storedfile_id;
            
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
