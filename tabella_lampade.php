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
$lampadaPercentuali = [];
$lampadaWattMax = [];
$lampadaTipo = [];

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

    // Aggiungi il tipoLampada (potenza della lampada)
    if (!isset($lampadaTipo[$idLampada])) {
        $lampadaTipo[$idLampada] = $tipoLampada;
    }

    // Aggiungi la percentuale per ogni lampada
    if (!isset($lampadaPercentuali[$idLampada])) {
        $lampadaPercentuali[$idLampada] = [];
    }
    $lampadaPercentuali[$idLampada][] = $percentuale;
}

// Calcolo della percentuale massima per ogni lampada e del consumo in watt
foreach ($lampadaPercentuali as $idLampada => $percentuali) {
    // Trova la percentuale massima per questa lampada
    $percentualeMassima = max($percentuali);

    // Calcola i watt utilizzati con la percentuale massima
    $wattMax = $lampadaWattMax[$idLampada];
    $wattUtilizzati = ($wattMax * $percentualeMassima) / 100;

    // Salva i dati relativi alla lampada
    $lampadaPercentuali[$idLampada] = [
        'percentualeMassima' => $percentualeMassima,
        'wattUtilizzati' => $wattUtilizzati,
        'tipoLampada' => $lampadaTipo[$idLampada]
    ];
}

$connection->close();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tabella Lampade</title>
    <link href="style.css" rel="stylesheet"/> <!--link per lo stile-->
</head>
<body>
<div class="container mt-4">
    <h1 class="mb-4">Consumo per ciascuna lampada</h1>

    <!-- Creazione della tabella per ogni lampada -->
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID Lampada</th>
                <th>Percentuale Maggiore di Consumo</th>
                <th>Tipo Lampada</th>
                <th>Consumo Finale (Watt)</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            // Variabile per calcolare la somma totale dei consumi
            $sommaTotaleConsumi = 0;

            // Ciclo attraverso ogni lampada per mostrare i dati
            foreach ($lampadaPercentuali as $idLampada => $datiLampada) {
                echo "<tr>";
                echo "<td>" . $idLampada . "</td>";
                echo "<td>" . $datiLampada['percentualeMassima'] . "%</td>";
                echo "<td>" . $datiLampada['tipoLampada'] . "</td>";
                echo "<td>" . $datiLampada['wattUtilizzati'] . " W</td>";
                echo "</tr>";

                // Somma dei consumi finali
                $sommaTotaleConsumi += $datiLampada['wattUtilizzati'];
            }
            ?>
        </tbody>
    </table>

    <!-- Tabella separata per visualizzare la somma totale dei consumi -->
    <h2>Somma Totale dei Consumi Finali delle Lampade</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Somma Consumi Totali (Watt)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?php echo $sommaTotaleConsumi . " W"; ?></td>
            </tr>
        </tbody>
    </table>
</div>
</body>
</html>