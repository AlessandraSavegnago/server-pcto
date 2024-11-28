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

$sql = "
    SELECT 
        sensore.idSensore, 
        lampada.idLampada, 
        sensore.percentuale,
        lampada.tipoLampada
    FROM 
        sensore
    JOIN 
        lampada_sensore ON sensore.idSensore = lampada_sensore.idSensore
    JOIN 
        lampada ON lampada_sensore.idLampada = lampada.idLampada
";

$result = $connection->query($sql);

// Array per memorizzare i dati
$sensori = [];
$lampadaPercentuali = [];
$lampadaWattMax = [];

// Organizza i dati in un array associativo
while ($row = $result->fetch_assoc()) {
    $idSensore = $row['idSensore'];
    $idLampada = $row['idLampada'];
    $percentuale = $row['percentuale'];
    $tipoLampada = $row['tipoLampada'];
    
    // Aggiungi il watt_max per ogni lampada (assumendo che la colonna "tipoLampada" rappresenti il watt_max)
    if (!isset($lampadaWattMax[$idLampada])) {
        $wattMax = (int) filter_var($tipoLampada, FILTER_SANITIZE_NUMBER_INT); 
        $lampadaWattMax[$idLampada] = $wattMax;
    }

    // Aggiungi la percentuale per ogni lampada
    if (!isset($lampadaPercentuali[$idLampada])) {
        $lampadaPercentuali[$idLampada] = [];
    }
    $lampadaPercentuali[$idLampada][] = $percentuale;

    // Aggiungi l'ID lampada alla lista delle lampade del sensore
    $sensori[$idSensore]['lampade'][] = $idLampada;
}

// Calcolo della percentuale massima per ogni lampada e del consumo in watt
foreach ($sensori as $idSensore => $datiSensore) {
    foreach ($datiSensore['lampade'] as $idLampada) {
        // Trova la percentuale massima per questa lampada
        $percentualeMassima = max($lampadaPercentuali[$idLampada]);

        // Calcola i watt utilizzati con la percentuale massima
        $wattMax = $lampadaWattMax[$idLampada];
        $wattUtilizzati = ($wattMax * $percentualeMassima) / 100;

        // Salva il consumo di watt per questa lampada nel sensore
        $sensori[$idSensore]['lampadeData'][$idLampada] = [
            'percentuale' => $percentualeMassima,
            'wattUtilizzati' => $wattUtilizzati
        ];
    }
}

$connection->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tabella Sensori e Lampade</title>
    <link href="style.css" rel="stylesheet"/> <!--link per lo stile-->
</head>
<body>
<div class="container mt-4">
    <h1 class="mb-4">Consumo totale per ogni sensore</h1>

    <!-- Creazione della tabella -->
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID Sensore</th>
                <th>ID Lampade</th>
                <th>Consumo in Watt delle Lampade</th>
                <th>Consumo Totale in Watt</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            // Ciclo attraverso ogni sensore per mostrare i dati
            foreach ($sensori as $idSensore => $datiSensore) {
                $lampade = implode(', ', array_keys($datiSensore['lampadeData'])); // ID delle lampade collegate
                $wattUtilizzatiLampade = implode(' + ', array_map(function($lampadaData) {
                    return $lampadaData['wattUtilizzati'] . 'W'; // Consumo watt per ogni lampada
                }, $datiSensore['lampadeData']));
                
                // Somma dei watt utilizzati per tutte le lampade
                $consumoTotaleWatt = array_sum(array_map(function($lampadaData) {
                    return $lampadaData['wattUtilizzati']; // Somma dei watt utilizzati per tutte le lampade
                }, $datiSensore['lampadeData']));

                echo "<tr>
                        <td>" . $idSensore . "</td>
                        <td>" . $lampade . "</td>
                        <td>" . $wattUtilizzatiLampade . "</td>
                        <td>" . $consumoTotaleWatt . " W</td>
                      </tr>";
            }
            ?>
        </tbody>
    </table>
</div>
</body>
</html>