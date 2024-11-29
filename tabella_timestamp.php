<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Connessione al database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "SensorDatabase"; 

$connection = new mysqli($servername, $username, $password, $dbname);

if ($connection->connect_error) {
    die("Connessione fallita: " . $connection->connect_error);
}

// Query per ottenere i dati con timestamp
$sql = "
    SELECT 
        lampada_sensore.idSensore,
        lampada_sensore.timestamp,
        sensore.percentuale,
        lampada.idLampada
    FROM 
        lampada_sensore
    JOIN 
        sensore ON lampada_sensore.idSensore = sensore.idSensore
    JOIN 
        lampada ON lampada_sensore.idLampada = lampada.idLampada
    ORDER BY lampada_sensore.timestamp ASC
";

$result = $connection->query($sql);

// Array per organizzare i dati
$data = [];
$timestamps = [];
$lampadaSensorePercentuali = []; // Per memorizzare la percentuale per ogni lampada e sensore

// Organizzare i dati
while ($row = $result->fetch_assoc()) {
    $idSensore = $row['idSensore'];
    $idLampada = $row['idLampada'];
    $timestamp = $row['timestamp'];
    $percentuale = $row['percentuale'];

    // Aggiungi il timestamp alla lista se non esiste
    if (!in_array($timestamp, $timestamps)) {
        $timestamps[] = $timestamp;
    }

    // Associa ogni lampada ai sensori connessi e registra la percentuale
    if (!isset($lampadaSensorePercentuali[$idLampada])) {
        $lampadaSensorePercentuali[$idLampada] = [];
    }
    $lampadaSensorePercentuali[$idLampada][$idSensore] = $percentuale;

    // Salva il consumo percentuale per sensore e timestamp
    if (!isset($data[$idSensore][$timestamp])) {
        $data[$idSensore][$timestamp] = [];
    }
    $data[$idSensore][$timestamp]['lampada'] = $idLampada;
}

// Calcolare la percentuale massima per ciascuna lampada
$lampadaMassimaPercentuale = [];
foreach ($lampadaSensorePercentuali as $idLampada => $sensoriPercentuali) {
    $lampadaMassimaPercentuale[$idLampada] = max($sensoriPercentuali); // Trova la percentuale massima tra i sensori collegati
}

// Applicare la percentuale massima calcolata a tutti i sensori collegati
foreach ($data as $idSensore => &$timestampData) {
    foreach ($timestampData as $timestamp => &$info) {
        $idLampada = $info['lampada'];
        $info['percentualeMax'] = $lampadaMassimaPercentuale[$idLampada]; // Percentuale massima associata alla lampada
    }
}
unset($timestampData, $info); // Per evitare riferimenti residui

$connection->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consumo Sensori per Timestamp</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Consumo Sensori per Timestamp</h1>

    <!-- Creazione della tabella -->
    <table>
        <thead>
            <tr>
                <th>ID Sensore</th>
                <?php foreach ($timestamps as $timestamp): ?>
                    <th><?php echo htmlspecialchars($timestamp); ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data as $idSensore => $timestampData): ?>
                <tr>
                    <td><?php echo htmlspecialchars($idSensore); ?></td>
                    <?php foreach ($timestamps as $timestamp): ?>
                        <td>
                            <?php 
                            if (isset($timestampData[$timestamp])) {
                                echo $timestampData[$timestamp]['percentualeMax'] . '%';
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>