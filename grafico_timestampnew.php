<?php
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
        lampada_sensore.idLampada,
        lampada_sensore.timestamp,
        sensore.percentuale,
        lampada.tipoLampada
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
$lampadaData = [];
$timestamps = [];
$lampadaNames = []; // Array per i nomi delle lampade
$lampadaTypes = []; // Array per i tipi delle lampade
while ($row = $result->fetch_assoc()) {
    $idLampada = $row['idLampada'];
    $timestamp = $row['timestamp'];
    $percentuale = $row['percentuale'];
    $watt = (int) $row['tipoLampada']; // Watt massimo della lampada

    // Calcola il consumo in watt basato sulla percentuale
    $consumoWatt = ($percentuale / 100) * $watt;

    // Aggiungi il timestamp alla lista se non esiste
    if (!in_array($timestamp, $timestamps)) {
        $timestamps[] = $timestamp;
    }

    // Salva il consumo per lampada e timestamp
    if (!isset($lampadaData[$idLampada])) {
        $lampadaData[$idLampada] = [];
    }
    $lampadaData[$idLampada][$timestamp] = $consumoWatt;

    // Memorizza i nomi delle lampade e i tipi
    if (!isset($lampadaNames[$idLampada])) {
        $lampadaNames[$idLampada] = "Lampada " . $idLampada; // Nome generico
        $lampadaTypes[$idLampada] = $row['tipoLampada']; // Tipo della lampada
    }
}

$connection->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grafico Consumi Lampade</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background-color: #ffffff;
        }
        canvas {
            display: block;
            max-width: 100%;
            height: auto;
            padding-bottom: 100px;
        }
        .dropdown-container {
            margin: 20px auto;
            width: 300px;
            text-align: center;
        }
        select {
            padding: 10px;
            font-size: 16px;
            width: 100%;
        }
    </style>
</head>
<body id="graphBody">

<!-- Dropdown per selezionare la lampada -->
<div class="dropdown-container">
    <label for="lampadaSelect">Seleziona Lampada:</label>
    <select id="lampadaSelect" onchange="updateChart()">
        <option value="all">Mostra Tutte</option>
        <?php foreach ($lampadaNames as $id => $name): ?>
            <option value="<?php echo $id; ?>"><?php echo $name; ?></option>
        <?php endforeach; ?>
    </select>
</div>

<!-- Dropdown per selezionare i gruppi di lampade -->
<div class="dropdown-container">
    <label for="groupSelect">Seleziona Gruppo di Lampade:</label>
    <select id="groupSelect" onchange="updateChart()">
        <option value="all">Mostra Tutti i Gruppi</option>
        <?php foreach ($lampadaTypes as $type): ?>
            <option value="<?php echo $type; ?>">Gruppo <?php echo $type; ?></option>
        <?php endforeach; ?>
    </select>
</div>

<div style="width: 90%; max-width: 1500px; height: 800px; margin: auto; overflow: hidden;">
    <h1>Grafico Consumi Lampade nel Tempo</h1>
    <canvas id="lampadaChart"></canvas>
</div>

<script>
    const ctx = document.getElementById('lampadaChart').getContext('2d');

    // Dati dal PHP
    const timestamps = <?php echo json_encode($timestamps); ?>; // Timestamp sull'asse X
    const lampadaData = <?php echo json_encode($lampadaData); ?>; // Dati delle lampade (ID -> Consumi per timestamp)
    const lampadaNames = <?php echo json_encode($lampadaNames); ?>;
    const lampadaTypes = <?php echo json_encode($lampadaTypes); ?>;

    // Prepara i dataset per il grafico
    let datasets = Object.keys(lampadaData).map((idLampada, index) => {
        const consumi = timestamps.map(timestamp => lampadaData[idLampada][timestamp] || 0); // Consumi per ogni timestamp (0 se assente)
        const colors = [
            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'
        ]; // Colori predefiniti per linee
        return {
            label: lampadaNames[idLampada], // Nome della lampada
            data: consumi,
            borderColor: colors[index % colors.length], // Ciclo tra i colori
            backgroundColor: `rgba(0, 0, 0, 0)`, // Sfondo trasparente
            borderWidth: 2,
            tension: 0.4 // Linee curve
        };
    });

    // Creazione del grafico
    let chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: timestamps, // Timestamp sull'asse X
            datasets: datasets // Dati delle lampade
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // Disabilita il mantenimento del rapporto di aspetto
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        font: {
                            size: 14
                        }
                    }
                },
                title: {
                    display: false,
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Timestamp'
                    },
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45,
                        font: {
                            size: 12
                        }
                    },
                    grid: {
                        display: false
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'Consumo (Watt)'
                    },
                    ticks: {
                        font: {
                            size: 12
                        }
                    },
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(200, 200, 200, 0.2)'
                    }
                }
            }
        }
    });

    // Funzione per aggiornare il grafico in base alla selezione
    function updateChart() {
        const lampadaSelect = document.getElementById('lampadaSelect').value;
        const groupSelect = document.getElementById('groupSelect').value;

        let filteredDatasets = datasets.filter(dataset => {
            if (lampadaSelect !== 'all' && dataset.label !== lampadaNames[lampadaSelect]) {
                return false;
            }
            if (groupSelect !== 'all' && lampadaTypes[lampadaSelect] !== groupSelect) {
                return false;
            }
            return true;
       