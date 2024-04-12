<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$servername = "localhost";
$username = "BonnieTyler";
$password = "12345";
$dbname = "allaboutpc";

// Connessione al database
$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica della connessione
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

//elabora header
$metodo=$_SERVER["REQUEST_METHOD"];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode( '/', $uri );

//in caso si abbia aggiunto uno slash inutile in più
if (!empty($uri)) {
    if (end($uri) === '') {
        array_pop($uri);
    }
}

/*
//controllo sull'URI
$num=1;
foreach ($uri as $ur);{
    echo "numero ".$num.":".$ur;
    echo "<br>";
    $num++;
}
echo $num;
echo "numero: ".count($uri);
*/

//legge il tipo di contenuto inviato dal client
$ct = isset($_SERVER["CONTENT_TYPE"]) ? $_SERVER["CONTENT_TYPE"] : null;
if ($ct) {
    $type = explode("/", $ct);
} else {
    // Se l'indice "CONTENT_TYPE" non è definito
    $type = ['application', 'json']; // Imposta il tipo di contenuto predefinito a "application/json"
}


//legge il tipo di contenuto di ritorno richiesto dal client
$retct=$_SERVER["HTTP_ACCEPT"];
$ret=explode("/",$retct);

if ($metodo=="GET"){
    $sql ="";
    if (count($uri) == 3 && $uri[2]==null) {
        // se non mette niente stampo tutto
        $tables_result = $conn->query("SHOW TABLES");

        $tables_data = array();

        if ($tables_result->num_rows > 0) {
            // Per ogni tabella, ottiene i contenuti e crea un array associativo
            while ($table_row = $tables_result->fetch_row()) {
                $table_name = $table_row[0];
                
                $table_content_result = $conn->query("SELECT * FROM $table_name");
                $table_data = array();
                
                if ($table_content_result->num_rows > 0) {
                    while ($row = $table_content_result->fetch_assoc()) {
                        $table_data[] = $row;
                    }
                }
                $tables_data[$table_name] = $table_data;
            }
        }

    // Converto l'array associativo in formato JSON
    $json_data = json_encode($tables_data);
    echo $json_data;
    } else{
        
        if (count($uri) == 3) {
    //Solo la tabella
        $tableName = $uri[2];
        $sql = "SELECT * FROM $tableName";
    } elseif (count($uri) == 4) {
    //tabella e id
        $tableName = $uri[2];
        $id = $uri[3];
        $sql = "SELECT * FROM $tableName WHERE id = $id";
    } else {
        http_response_code(400);
        echo "URI non valido.";
        exit();
    }

    //binding dei parametri
    /*
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    */

    // Esegue la query
    $result = $conn->query($sql);

    if ($result) {
        if ($result->num_rows > 0) {
            $rows = array();
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
            echo json_encode($rows);
        } else {
            echo "Nessun dato trovato.";
        }
    } else {
        echo "Errore durante l'esecuzione della query: " . $conn->error;
    }       
}
}
if ($metodo=="POST"){
    echo "post\n";
    echo "<br>";

    $body=file_get_contents('php://input');
   // echo $body
   
   //converte in array associativo
    if ($type[1]=="json"){
        $data = json_decode($body,true);
    }
    if ($type[1]=="xml"){
        $xml = simplexml_load_string($body);
        $json = json_encode($xml);
        $data = json_decode($json, true);
    }
    
    if(is_array($data) && 
    array_key_exists('nome_tabella', $data) &&
    array_key_exists('id', $data) &&
    count($data) >= 3){
        // L'array non contiene tutti i requisiti
        http_response_code(400);
        echo "L'array non contiene tutti i requisiti";
        exit();
    }

    //elabora i dati o interagisce con il database (solo per la tabella computer)
    $sql="INSERT INTO ".$data["nome_tabella"];
    //faccio in modo che prenda solo gli elementi necessari (lascio fuori il nome della tabella)
    $array_dati=$data;
    unset($array_dati['nome_tabella']);
    unset($array_dati['id']);

    $chiavi_stringa = "";
    $valori_stringa ="";
    //aggiungo i campi della query
    foreach($array_dati as $chiave => $valore){
        $chiavi_stringa .= $chiave . ", ";
        $valori_stringa .= "'".$valore . "', ";
    }
    $chiavi_stringa = rtrim($chiavi_stringa, ", ");
    $valori_stringa = rtrim($valori_stringa, ", ");
    $sql=$sql." (".$chiavi_stringa.") VALUES (".$valori_stringa.");";

    //binding dei parametri
    $stmt = $conn->prepare($sql);
    $params = array_values($array_dati);
    $types = str_repeat('s', count($params)); // 's' sta per string
    $stmt->bind_param($types, ...$params);
    $stmt->execute();

    echo $sql;
    $result = $conn->query($sql);
    
    //settaggio dei campi dell'header
    header("Content-Type: ".$retct);    
    //restituisce i dati convertiti nel formato richiesto
    if ($ret[1]=="json"){
        echo json_encode($data);
    }
    if ($ret[1]=="xml"){
        $xml = new SimpleXMLElement('<root/>');
        array_walk_recursive($data, array ($xml, 'addChild'));    
        echo $xml->asXML();
    }
   
}
if ($metodo=="PUT"){
    echo "put";
    echo "<br>";
   
        //recupera i dati dall'header
   $body=file_get_contents('php://input');
   
   //converte in array associativo
    if ($type[1]=="json"){
        $data = json_decode($body,true);
        foreach ($data as $dat){
            echo $dat;
            echo "<br>";
        }
    }
    if ($type[1]=="xml"){
        $xml = simplexml_load_string($body);
        $json = json_encode($xml);
        $data = json_decode($json, true);
    }
    
    //controlla se i dati mandati sono idonei
    if (is_array($data) && 
        array_key_exists('nome_tabella', $data) &&
        array_key_exists('id', $data) &&
        count($data) >= 3) {

        // L'array contiene almeno nome_tabella come prima chiave, id come seconda chiave e almeno una terza chiave
        echo "L'array contiene almeno nome_tabella, id e almeno una terza chiave.";

        //copia l'array e toglie nome_tabella e id
        $array_dati=$data;
        unset($array_dati['nome_tabella']);
        unset($array_dati['id']);

        $stringa_chiave_valore=""; 
        foreach ($array_dati as $chiave => $valore) {
            $stringa_chiave_valore .= $chiave . "=" ."'". $valore ."'". ", ";
        }
        $stringa_chiave_valore = rtrim($stringa_chiave_valore, ", ");

        $sql="UPDATE ".$data["nome_tabella"]." SET ".$stringa_chiave_valore." WHERE id=".$data["id"].";";

        //binding dei parametri
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $data["id"]);
        $stmt->execute();

        echo $sql;
        $result = $conn->query($sql);

        //settaggio dei campi dell'header
        header("Content-Type: ".$retct);    
        //restituisce i dati convertiti nel formato richiesto
        if ($ret[1]=="json"){
            echo json_encode($data);
        }
        if ($ret[1]=="xml"){
            $xml = new SimpleXMLElement('<root/>');
            array_walk_recursive($data, array ($xml, 'addChild'));    
            echo $xml->asXML();
        }

    } else {
        // L'array non contiene tutti i requisiti
        http_response_code(400);
        echo "L'array non contiene tutti i requisiti";
        exit();
    }

}
if ($metodo=="DELETE"){
    echo "delete";

    $body=file_get_contents('php://input');
    // echo $body
    
    //converte in array associativo
     if ($type[1]=="json"){
         $data = json_decode($body,true);
     }
     if ($type[1]=="xml"){
         $xml = simplexml_load_string($body);
         $json = json_encode($xml);
         $data = json_decode($json, true);
     }
     
     if(is_array($data) && 
     array_key_exists('nome_tabella', $data) &&
     array_key_exists('id', $data) &&
     count($data) >= 3){
         // L'array non contiene tutti i requisiti
         http_response_code(400);
         echo "L'array non contiene tutti i requisiti";
         exit();
     }

     //elabora i dati o interagisce con il database (solo per la tabella computer)
     $sql = "DELETE FROM ".$data["nome_tabella"]. " WHERE id = ".$data["id"].";";

    //binding dei parametri
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $data["id"]);
    $stmt->execute();

     echo $sql;
     $result = $conn->query($sql);
     
     //settaggio dei campi dell'header
     header("Content-Type: ".$retct);    
     echo $retct;
     //restituisce i dati convertiti nel formato richiesto
     if ($ret[1]=="json"){
         echo json_encode($data);
     }
     if ($ret[1]=="xml"){
         $xml = new SimpleXMLElement('<root/>');
         array_walk_recursive($data, array ($xml, 'addChild'));    
         echo $xml->asXML();
     }
}

$conn->close();
?>