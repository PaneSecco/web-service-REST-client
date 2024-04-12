<?php

$servername = "localhost";
$username = "BonnieTyler";
$password = "12345";
$dbname = "allaboutpc";

// Elabora header
$metodo = $_SERVER["REQUEST_METHOD"];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);

// Connessione al database
$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica della connessione
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

if ($metodo == "GET") {
    if (count($uri) == 4) {
        $tableName = $uri[3];
        $sql = "SELECT * FROM $tableName";
    } elseif (count($uri) == 5) {
        $tableName = $uri[3];
        $id = $uri[4];
        $sql = "SELECT * FROM $tableName WHERE id = $id";
    } elseif (count($uri) == 6) {
        $tableName = $uri[3];
        $id = $uri[5];
        $sql = "SELECT * FROM $tableName WHERE id = $id";
    } else {
        http_response_code(400);
        echo "URI non valido.";
        exit();
    }

    // Esegui la query
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
} elseif ($metodo == "POST") {
    $tableName = $uri[3];
    $id = $uri[4];
    $tipo = $uri[5];

    $sql = "UPDATE $tableName SET tipo = $tipo WHERE id = $id";
    if ($conn->query($sql) === TRUE) {
        echo "Record aggiornato con successo.";
    } else {
        echo "Errore durante l'aggiornamento del record: " . $conn->error;
    }
} elseif ($metodo == "PUT") {
    $tableName = $uri[3];
    $id = $uri[4];
    $tipo = $uri[5];

    $sql = "INSERT INTO $tableName (...) VALUES (...)"; //???
    if ($conn->query($sql) === TRUE) {
        echo "Record creato con successo.";
    } else {
        echo "Errore durante la creazione del record: " . $conn->error;
    }
} elseif ($metodo == "DELETE") {
    $tableName = $uri[3];
    $id = $uri[4];
    $tipo = $uri[5];
    $sql = "DELETE FROM $tableName WHERE id = $id";
    if ($conn->query($sql) === TRUE) {
        echo "Record eliminato con successo.";
    } else {
        echo "Errore durante l'eliminazione del record: " . $conn->error;
    }
} else {
    http_response_code(405);
    echo "Metodo non supportato.";
}

// chiusura connessione database la connessione al database
$conn->close();

?>
