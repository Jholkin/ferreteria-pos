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

use Dompdf\Dompdf;
use Dompdf\Options;


function sendMail($data) {
    global $mail;

    try {
        // Recipients
        $mail->setFrom($data->from, $data->fromName);
        $mail->addAddress($data->to, $data->toName);     // Add a recipient

        // Content
        $mail->isHTML(true);
        $mail->Subject = $data->subject;
        $mail->Body    = $data->body;
        $mail->AltBody = strip_tags($data->body);


        // === Construcción de plantilla HTML para PDF usando datos estructurados ===
        $negocio = isset($data->negocio) ? $data->negocio : [
            'nombre' => 'Mi Negocio',
            'telefono' => '',
            'direccion' => ''
        ];
        $venta = isset($data->venta) ? $data->venta : [];
        $productos = isset($venta->productos) ? $venta->productos : [];

        $productosHtml = '';
        foreach ($productos as $prod) {
            $productosHtml .= '<tr>' .
                '<td>' . htmlspecialchars($prod->nombre) . '</td>' .
                '<td>S/ ' . number_format($prod->precio, 2) . '</td>' .
                '<td>' . $prod->cantidad . '</td>' .
                '<td>S/ ' . number_format($prod->precio * $prod->cantidad, 2) . '</td>' .
            '</tr>';
        }

        $total = isset($venta->total) ? number_format($venta->total, 2) : '0.00';
        $pagado = isset($venta->pagado) ? number_format($venta->pagado, 2) : '0.00';
        $cambio = isset($venta->cambio) ? number_format($venta->cambio, 2) : '0.00';
        $nombreCliente = isset($venta->nombreCliente) ? htmlspecialchars($venta->nombreCliente) : '';
        $nombreUsuario = isset($venta->nombreUsuario) ? htmlspecialchars($venta->nombreUsuario) : '';
        $fecha = isset($venta->fecha) ? htmlspecialchars($venta->fecha) : date('Y-m-d H:i');

        $html = '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante de Venta</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; color: #222; }
        .comprobante-box { border: 1px solid #ccc; padding: 24px; border-radius: 8px; max-width: 600px; margin: 0 auto; }
        .titulo { text-align: center; font-size: 22px; font-weight: bold; margin-bottom: 12px; }
        .negocio { text-align: center; margin-bottom: 18px; }
        .datos { margin-bottom: 18px; }
        .datos span { display: inline-block; min-width: 120px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background: #f5f5f5; }
        .totales { text-align: right; margin-top: 10px; }
        .footer { text-align: center; font-size: 12px; color: #888; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="comprobante-box">
        <div class="titulo">COMPROBANTE DE VENTA</div>
        <div class="negocio">
            <div><b>' . htmlspecialchars($negocio->nombre) . '</b></div>
            <div>Tel: ' . htmlspecialchars($negocio->telefono) . '</div>
            <div>' . htmlspecialchars($negocio->direccion) . '</div>
        </div>
        <div class="datos">
            <div><span>Cliente:</span> ' . $nombreCliente . '</div>
            <div><span>Atiende:</span> ' . $nombreUsuario . '</div>
            <div><span>Fecha:</span> ' . $fecha . '</div>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Precio</th>
                    <th>Cant.</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>' . $productosHtml . '</tbody>
        </table>
        <div class="totales">
            <div><b>Total:</b> S/ ' . $total . '</div>
            <div><b>Pagado:</b> S/ ' . $pagado . '</div>
            <div><b>Cambio:</b> S/ ' . $cambio . '</div>
        </div>
        <div class="footer">
            ¡Gracias por su preferencia!
        </div>
    </div>
</body>
</html>';

        // Generar PDF con dompdf
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $pdfOutput = $dompdf->output();

        // Adjuntar PDF
        $mail->addStringAttachment($pdfOutput, 'comprobante.pdf');

        $mail->send();
        return ['success' => true, 'message' => 'Correo enviado correctamente.'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => "Error al enviar el correo: {$mail->ErrorInfo}"];
    }
}