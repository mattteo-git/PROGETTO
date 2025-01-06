<?php
include 'config.php';

$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recupera la partita IVA
    $PIAzi = isset($_POST['PIAzi']) ? trim($_POST['PIAzi']) : null;
    
    

    // Validazione della partita IVA
    if (!$PIAzi) {
        $msg = "La partita IVA è obbligatoria.";
    } elseif (!ctype_digit($PIAzi) || strlen($PIAzi) !== 11) {
        $msg = "Partita IVA non valida.";
    } else {
        // Prepara la query SQL per cercare l'azienda con la partita IVA
        $sql = "SELECT idAzienda FROM aziende WHERE PIAzi = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $PIAzi);
        $stmt->execute();
        $stmt->bind_result($idAzienda);

        if ($stmt->fetch()) {
            // La partita IVA è valida, idAzienda trovato
            $stmt->close();

            // Recupera i dati della sede
            $nomeSede = $_POST['nomeSede'];
            $provinciaSede = $_POST['provinciaSede'];
            $comuneSede = $_POST['comuneSede'];
            $numeroCivicoSede = $_POST['numeroCivicoSede'];
            $capSede = $_POST['capSede'];
            $dvrSede = $_POST['dvrSede'];
            $note = $_POST['note'];
            $tipoSede = $_POST['tipoSede'];
            $indirizzoSede = $_POST['indirizzoSede'];

            // Se i campi sono vuoti o contengono errori
            if (empty($nomeSede) || empty($numeroCivicoSede) || empty($capSede) || empty($dvrSede) || empty($note) || empty($indirizzoSede)) {
                $msg = "Tutti i campi sono obbligatori.";
            } else {
                // Esegui la validazione dei campi
                if (is_numeric($nomeSede)) {
                    $msg = "Il nome della sede non può contenere numeri.";
                } elseif (preg_match('/[^a-zA-Z]/', $nomeSede)) {
                    $msg = "Il nome della sede può contenere solo lettere.";
                } elseif (!preg_match('/^[0-9]{5}$/', $capSede)) {
                    $msg = "Il CAP deve contenere esattamente 5 cifre e deve essere un numero.";
                } elseif (is_numeric($provinciaSede)) {
                    $msg = "La provincia non può contenere numeri.";
                } elseif (preg_match('/[^a-zA-Z]/', $provinciaSede)) {
                    $msg = "La provincia può contenere solo lettere.";
                } elseif (!preg_match('/^[0-9a-zA-Z\s\-]+$/', $numeroCivicoSede)) {
                    $msg = "Il numero civico deve contenere solo numeri, lettere, trattini e spazi!";
                } elseif (preg_match('/[a-zA-Z]{2,}/', $numeroCivicoSede)) {
                    $msg = "Il numero civico non può contenere due lettere consecutive!";
                } else {
                    // Inserisci i dati nella tabella "Sede"
                    $sql = "INSERT INTO Sede (nomeSede, provinciaSede, comuneSede, numeroCivicoSede, capSede, dvrSede, note, tipoSede, indirizzoSede, idAzienda)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssssissssi", $nomeSede, $provinciaSede, $comuneSede, $numeroCivicoSede, $capSede, $dvrSede, $note, $tipoSede, $indirizzoSede, $idAzienda);

                    if ($stmt->execute()) {
                        $msg = "Sede registrata con successo!";
                    } else {
                        $msg = "Errore durante l'inserimento: " . $stmt->error;
                    }
                    $stmt->close();
                }
            }
        } else {
            $msg = "Nessuna azienda trovata con questa partita IVA.";
        }
    }
} else {
    $msg = "Metodo non supportato.";
}

?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrazione Sede Primaria</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .form-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            background: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }
        h4 {
            color: #495057;
            margin-bottom: 20px;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 5px;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
        .alert {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <form action="" method="POST">
                <!-- Dati Sede -->
                <h4>Dati Sede Aziendale</h4>
                
                            <!-- Mostra il messaggio di errore o successo -->
            <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($msg)): ?>
    <div class="alert alert-info mt-3">
        <?php echo $msg; ?>
    </div>
<?php endif; ?>

                <div class="mb-3">
                    <label for="nomeSede" class="form-label">Nome Sede:</label>
                    <input type="text" id="nomeSede" name="nomeSede" class="form-control" required> <!--campo obbligatorio--->
                    
                    
                <?php
                    include 'config.php';

                    // Metodo per recuperare i dati delle province 
                    $sql = "SELECT sigla_provincia, denominazione_provincia FROM gi_province ORDER BY denominazione_provincia ASC";
                    $result = $conn->query($sql);
                ?>
                
                <!--- Elenco le province utlizzando un array ----> 
                <div class="mb-3">
                     <label for="provinciaSede" class="form-label">Provincia sede:</label>
                    <select id="provinciaSede" name="provinciaSede" class="form-control" required>
                    <option value="">Seleziona Provincia</option>
                    <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) { // Recupero le righe(che contengono le province)
                                echo "<option value='" . $row['sigla_provincia'] . "'>" . $row['denominazione_provincia'] . "</option>";
                                }
                                } else {
                                    echo "<option value=''>Nessuna provincia disponibile</option>";
                                    }
                     ?>
                    </select>
                </div>
                
                
                 <?php
                    include 'config.php';

                    // Recupera i comuni dal database
                    $sql = "SELECT codice_istat, denominazione_ita FROM gi_comuni ORDER BY denominazione_ita ASC";
                    $result = $conn->query($sql);
                ?>
                
                
                 <div class="mb-3">
        <label for="comuneSede" class="form-label">Comune sede:</label>
        <select id="comuneSede" name="comuneSede" class="form-control" required>
            <option value="">Seleziona Comune</option>
            <?php
            $num = 0;
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row['codice_istat'] . "'>" . $row['denominazione_ita'] . "</option>";
                }
            } else {
                echo "<option value=''>Nessun comune disponibile</option>";
            }
            ?>
        </select>
    </div>
                <div class="mb-3">
                    <label for="numeroCivicoSede" class="form-label">Numero Civico Sede:</label>
                    <input type="text" id="numeroCivicoSede" name="numeroCivicoSede" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="capSede" class="form-label">CAP Sede:</label>
                    <input type="text" id="capSede" name="capSede" class="form-control" required>
                </div>
                
                
                <div class="mb-3">
                    <label for="dvrSede" class="form-label">Allegare DVR sede:</label>
                    <input type="file" id="dvrSede" name="dvrSede" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="note" class="form-label">Note:</label>
                    <input type="text" id="note" name="note" class="form-control" required>
                </div>
                
                
                <?php
                    include 'config.php';

                    // Metodo per recuperare i dati delle province 
                    $sql = "SELECT IDSede, nomeSede FROM tipiSede ORDER BY IDSede ASC";
                    $result = $conn->query($sql);
                ?>
                
                <!--- Elenco le province utlizzando un array ----> 
                <div class="mb-3">
                     <label for="nomeSede" class="form-label">tipo sede:</label>
                    <select id="nomeSede" name="nomeSede" class="form-control" required>
                    <option value="">Seleziona tipo Sede</option>
                    <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) { // Recupero le righe(che contengono le province)
                                echo "<option value='" . $row['IDSede'] . "'>" . $row['nomeSede'] . "</option>";
                                }
                                } else {
                                    echo "<option value=''>Nessuna provincia disponibile</option>";
                                    }
                     ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="PIAzi" class="form-label">Partita Iva:</label>
                    <input type="text" id="PIAzi" name="PIAzi" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="indirizzoSede" class="form-label">Indirizzo Sede:</label>
                    <input type="text" id="indirizzoSede" name="indirizzoSede" class="form-control" required>    
                </div>
                
                <br>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <button type="submit" class="btn btn-primary">Registra Sede</button>
                    <button onclick="window.location.href='https://www.studentworld.it/GRUPPO1_BLETA/files/Muzzi/index.php';" class="btn btn-danger">Torna alla homepage</button>
                </div>
            </form>

        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>