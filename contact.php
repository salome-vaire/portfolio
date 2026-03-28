<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = htmlspecialchars(trim($_POST["nom"] ?? ""));
    $email = filter_var(trim($_POST["email"] ?? ""), FILTER_VALIDATE_EMAIL);
    $message = htmlspecialchars(trim($_POST["message"] ?? ""));

    if (!$nom || !$email || !$message) {
        echo "<!DOCTYPE html>
        <html lang='fr'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Erreur</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
        </head>
        <body class='bg-light d-flex align-items-center' style='min-height:100vh;'>
            <div class='container'>
                <div class='alert alert-danger shadow-sm'>
                    Tous les champs doivent être remplis correctement.
                    <br><br>
                    <a href='index.html#contact' class='btn btn-outline-danger btn-sm'>Retour au formulaire</a>
                </div>
            </div>
        </body>
        </html>";
        exit;
    }

    $destinataire = "salome.vaire0@gmail.com";
    $sujet = "Nouveau message depuis le portfolio de Salomé Vaire";

    $contenu = "Nom : $nom\n";
    $contenu .= "Email : $email\n\n";
    $contenu .= "Message :\n$message\n";

    $headers = "From: $email\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    if (mail($destinataire, $sujet, $contenu, $headers)) {
        echo "<!DOCTYPE html>
        <html lang='fr'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Message envoyé</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
        </head>
        <body class='bg-light d-flex align-items-center' style='min-height:100vh;'>
            <div class='container'>
                <div class='alert alert-success shadow-sm'>
                    Votre message a bien été envoyé.
                    <br><br>
                    <a href='index.html#contact' class='btn btn-outline-success btn-sm'>Retour au site</a>
                </div>
            </div>
        </body>
        </html>";
    } else {
        echo "<!DOCTYPE html>
        <html lang='fr'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Erreur</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
        </head>
        <body class='bg-light d-flex align-items-center' style='min-height:100vh;'>
            <div class='container'>
                <div class='alert alert-danger shadow-sm'>
                    Une erreur est survenue lors de l'envoi du message.
                    <br><br>
                    <a href='index.html#contact' class='btn btn-outline-danger btn-sm'>Retour au formulaire</a>
                </div>
            </div>
        </body>
        </html>";
    }
} else {
    header("Location: index.html#contact");
    exit;
}
?>