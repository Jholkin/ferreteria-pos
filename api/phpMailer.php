<?php
require __DIR__ . '/vendor/autoload.php';

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

$host = $_ENV['MAIL_HOST'];
$username = $_ENV['MAIL_USERNAME'];
$password = $_ENV['MAIL_PASSWORD'];
$port = $_ENV['MAIL_PORT'];

try {
    //Server settings
    $mail->isSMTP();                                            // Send using SMTP
    $mail->Host       = $host;                     // Set the SMTP server to send through
    $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
    $mail->Username   = $username;               // SMTP username
    $mail->Password   = $password;                        // SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption, `ssl` also accepted
    //$mail->SMTPSecure = 'tls'; // Enable TLS encryption
    $mail->Port       = $port;                                    // TCP port to connect to
    $mail->CharSet    = 'UTF-8';                                // Set the character encoding
} catch (Exception $e) {
    echo "Error al configurar el servidor SMTP: {$mail->ErrorInfo}";
    exit;
}

function sendMail($data) {
    global $mail;

    try {
        //Recipients
        $mail->setFrom($data->from, $data->fromName);
        $mail->addAddress($data->to, $data->toName);     // Add a recipient

        // Content
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = $data->subject;
        $mail->Body    = $data->body;
        $mail->AltBody = strip_tags($data->body); // Plain text version of HTML

        $mail->send();
        return ['success' => true, 'message' => 'Correo enviado correctamente.'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => "Error al enviar el correo: {$mail->ErrorInfo}"];
    }
}