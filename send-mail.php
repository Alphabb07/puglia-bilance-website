<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Dati dal form
    $to = "abbinanteantonio28@gmail.com";
    $nome = strip_tags(trim($_POST["nome"]));
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $messaggio = trim($_POST["messaggio"]);

    // Validazione basilare
    if (empty($nome) || empty($messaggio) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Dati non validi. Per favore, torna indietro e riprova.");
    }

    $subject = "Richiesta Assistenza da: $nome";

    // Creazione del boundary per email multipart
    $boundary = md5(time());

    // Header dell'email
    $headers = "From: $nome <$email>\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

    // Corpo del messaggio (parte testuale)
    $body = "--$boundary\r\n";
    $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $body .= "Nome / Attività: $nome\nEmail: $email\n\nMessaggio:\n$messaggio\n\r\n";

    // Elaborazione degli allegati (se presenti)
    if (isset($_FILES['allegato']) && $_FILES['allegato']['error'][0] != UPLOAD_ERR_NO_FILE) {
        for ($i = 0; $i < count($_FILES['allegato']['name']); $i++) {
            if ($_FILES['allegato']['error'][$i] == UPLOAD_ERR_OK) {
                $file_tmp = $_FILES['allegato']['tmp_name'][$i];
                $file_name = $_FILES['allegato']['name'][$i];
                $file_size = $_FILES['allegato']['size'][$i];
                $file_type = $_FILES['allegato']['type'][$i];

                $handle = fopen($file_tmp, "r");
                $content = fread($handle, $file_size);
                fclose($handle);
                $encoded_content = chunk_split(base64_encode($content));

                $body .= "--$boundary\r\n";
                $body .= "Content-Type: $file_type; name=\"$file_name\"\r\n";
                $body .= "Content-Disposition: attachment; filename=\"$file_name\"\r\n";
                $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
                $body .= $encoded_content . "\r\n\r\n";
            }
        }
    }

    $body .= "--$boundary--";

    // Invio dell'email
    if (mail($to, $subject, $body, $headers)) {
        header("Location: thank-you.html");
        exit();
    } else {
        echo "Errore nell'invio della richiesta. Riprova.";
    }
} else {
    echo "Metodo non valido.";
}
?>
