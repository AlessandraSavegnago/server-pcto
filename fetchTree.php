<?php 
// Connessione al database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "DeviceTreeDatabase"; 

//connessione al database
$connection = new mysqli($servername, $username, $password, $dbname) or die("Unable to connect");

// Query per recuperare i dati
$sql = "SELECT id, name, id_sup FROM devices";
$result = $connection->query($sql);

// Costruire l'albero come un array associativo
$nodes = [];
while ($row = $result->fetch_assoc()) {
    $nodes[$row['id']] = $row;  // Salva ogni nodo
    $nodes[$row['id']]['children'] = []; // Prepara un array per i figli
}

// Collegare i nodi genitori con i figli
$tree = [];
foreach ($nodes as $id => &$node) {
    if ($node['id_sup'] === null) {
        $tree[] = &$node; // Nodo radice
    } else {
        $nodes[$node['id_sup']]['children'][] = &$node; // Aggiungi il nodo come figlio
    }
}

$connection->close();

// Stampa la struttura dell'albero come JSON
header('Content-Type: application/json');
echo json_encode($tree);
?>

