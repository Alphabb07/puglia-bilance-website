<?php
/************************************************************
 * backend.php
 * - GET: mostra lo stesso contenuto di index.html (se lo desideri).
 * - POST: invia l'email a "abbinanteantonio28@gmail.com" utilizzando mail().
 ************************************************************/

$paginaPrincipale = 'index.html';

// 1) Se la richiesta è GET, mostriamo l'HTML (opzionale)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (file_exists($paginaPrincipale)) {
        readfile($paginaPrincipale);
    } else {
        http_response_code(404);
        echo "<h1>404 - Pagina non trovata</h1>";
    }
    exit;
}

// 2) Se la richiesta è POST, gestiamo l'invio
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Leggiamo il body JSON inviato
    $rawData = file_get_contents('php://input');
    $json = json_decode($rawData, true);

    // Estraiamo i campi
    $nome = isset($json['nome']) ? trim($json['nome']) : '';
    $email = isset($json['email']) ? trim($json['email']) : '';
    $messaggio = isset($json['messaggio']) ? trim($json['messaggio']) : '';

    // Validazione minima
    if ($nome === '' || $email === '' || $messaggio === '') {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "Campi obbligatori mancanti."
        ]);
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "Email non valida."
        ]);
        exit;
    }

    // Destinatario
    $destinatario = "abbinanteantonio28@gmail.com";
    $oggetto = "Nuovo messaggio dal sito Puglia Bilance";
    $corpo   = "Hai ricevuto un nuovo messaggio.\n"
             . "Nome: $nome\n"
             . "Email: $email\n"
             . "Messaggio:\n$messaggio\n";

    // Header
    $headers = "From: $email\r\n"
             . "Reply-To: $email\r\n"
             . "Content-Type: text/plain; charset=UTF-8\r\n";

    // Invio
    if (mail($destinatario, $oggetto, $corpo, $headers)) {
        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "message" => "Email inviata correttamente!"
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "message" => "Invio fallito: problema con mail()"
        ]);
    }
    exit;
}

// Se è altro metodo, 405
http_response_code(405);
echo "<h1>405 - Method Not Allowed</h1>";
