<?php
// Include il file di configurazione
include 'config.php';
// Variabile per i messaggi
$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $PIAzi = isset($_POST['PIAzi']) ? trim($_POST['PIAzi']) : null;

    if (!$PIAzi) {
        $msg = "La partita IVA è obbligatoria.";
    } elseif (!ctype_digit($PIAzi) || strlen($PIAzi) !== 11) {
        $msg = "Partita IVA non valida.";
    } else {
        // Prepara la query SQL per cercare l'azienda con la partita IVA fornita
        $sql = "SELECT idAzienda FROM aziende WHERE PIAzi = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("s", $PIAzi);
            $stmt->execute();
            $stmt->bind_result($idAzienda);

            if ($stmt->fetch()) {
                $msg = "ID Azienda trovata: " . htmlspecialchars($idAzienda);
            } else {
                $msg = "Nessuna azienda trovata con questa partita IVA.";
            }

            $stmt->close();
        } else {
            $msg = "Errore nella preparazione della query: " . $conn->error;
        }
    }
} 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recupera i dati inviati dal modulo
    $cognomeReferente = $_POST['cognomeRefAzi'];
    $nomeReferente = $_POST['nomeRefAzi'];
    $telefonoReferente = $_POST['telRefAzi'];
    $statoReferente = $_POST['stato'];
    $codFiscRef = $_POST['codFiscRef'];
    $nomeRuolo = $_POST['nomeRuolo'];
    
    // Controllo se i campi sono vuoti
    if (empty($cognomeReferente) || empty($nomeReferente) || empty($telefonoReferente) || empty($statoReferente) || empty($codFiscRef)) {
        $msg = "Tutti i campi sono obbligatori";
    }
    // Controllo sul numero di telefono (deve avere esattamente 10 cifre)
    elseif (!preg_match('/^[0-9]{10}$/', $telefonoReferente)) {
        $msg = "Il numero di telefono deve contenere esattamente 10 cifre!";
    }elseif (!preg_match('/^[a-zA-Zà-ùÀ-Ù\s]+$/', $cognomeReferente)) {
        $msg = "Il cognome deve contenere solo lettere!";
    }
    elseif (!preg_match('/^[a-zA-Zà-ùÀ-Ù\s]+$/', $nomeReferente)) {
        $msg = "Il nome deve contenere solo lettere!";
    }else if (!preg_match('/[a-z]{6}[0-9]{2}[abcdehlmprst]{1}[0-9]{2}[a-z]{1}[0-9]{3}[a-z]{1}/i', $codFiscRef)){   
        $msg = "Errore nel codice Fiscale";
    }else {
        // Query SQL per l'inserimento
        $sql = "INSERT INTO referentiAziende (cognomeRefAzi, nomeRefAzi, telRefAzi, stato, idUtente, idAzienda, codFiscRef)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssiiis", $cognomeReferente, $nomeReferente, $telefonoReferente, $statoReferente, $idUtente, $idAzienda, $codFiscRef);

        if ($stmt->execute()) {
            $msg = "Referente registrato con successo!";
        } else {
            $msg = "Errore durante l'inserimento: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrazione Referente</title>
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
                <!-- Dati referente -->
                <h4>Dati Referente Aziendale</h4>
                
                            <?php if (!empty($msg)): ?>
                <div class="alert alert-info mt-3">
                    <?php echo $msg; ?>
                </div>
            <?php endif; ?>

                <div class="mb-3">
                    <label for="cognomeRefAzi" class="form-label">Cognome Referente:</label>
                    <input type="text" id="cognomeRefAzi" name="cognomeRefAzi" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="nomeRefAzi" class="form-label">Nome Referente:</label>
                    <input type="text" id="nomeRefAzi" name="nomeRefAzi" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="telRefAzi" class="form-label">Telefono Referente:</label>
                    <input type="text" id="telRefAzi" name="telRefAzi" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="stato" class="form-label">Stato Referente:</label>
                    <select id="stato" name="stato" class="form-control" required>
                        <option value="">Seleziona Stato</option>
                        <option value="1" <?php echo (isset($statoReferente) && $statoReferente == 1) ? 'selected' : ''; ?>>Attivo</option>
                        <option value="0" <?php echo (isset($statoReferente) && $statoReferente == 0) ? 'selected' : ''; ?>>Inattivo</option>
                    </select>
                </div>
                
                
                <?php
                    include 'config.php';

                    // Metodo per recuperare i dati delle province 
                    $sql = "SELECT IDRuolo, nomeRuolo FROM ruoli ORDER BY IDRuolo ASC";
                    $result = $conn->query($sql);
                ?>
                
                <!--- Elenco le province utlizzando un array ----> 
                <div class="mb-3">
                     <label for="nomeRuolo" class="form-label">Ruolo:</label>
                    <select id="nomeRuolo" name="nomeRuolo" class="form-control" required>
                    <option value="">Seleziona Ruolo</option>
                    <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) { // Recupero le righe(che contengono le province)
                                echo "<option value='" . $row['IDRuolo'] . "'>" . $row['nomeRuolo'] . "</option>";
                                }
                                } else {
                                    echo "<option value=''>Nessuna provincia disponibile</option>";
                                    }
                     ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="codFiscRef" class="form-label">Codice fiscale Referente:</label>
                    <input type="text" id="codFiscRef" name="codFiscRef" class="form-control" required>
                </div>
                
                
                <div class="mb-3">
                    <label for="PIAzi" class="form-label">Partita IVA:</label> 
                    <input type="number" id="PIAzi" name="PIAzi" class="form-control" required>
                </div>
                
                <br>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <button type="submit" class="btn btn-primary">Registra Referente</button>
                    <button onclick="window.location.href='https://www.studentworld.it/GRUPPO1_BLETA/files/Muzzi/index.php';" class="btn btn-danger">Torna alla homepage</button>
                </div>
            </form>

            <!-- Mostra il messaggio di errore o successo -->
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>