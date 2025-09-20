<?php

use PHPMailer\PHPMailer\{PHPMailer, SMTP, Exception};

require '../phpmailer/src/PHPMailer.php';
require '../phpmailer/src/SMTP.php';
require '../phpmailer/src/Exception.php';

// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "tiende2");

// Verificar conexión
if ($conexion->connect_error) {
    die("Error al conectar a la base de datos: " . $conexion->connect_error);
}

// Obtener los datos del usuario y la compra
$id_cliente = $_SESSION['id_cliente']; // Asume que el ID del cliente está en la sesión
$query = "SELECT c.email, co.id_transaccion 
          FROM clientes c 
          JOIN compra co ON c.id_cliente = co.id_cliente 
          WHERE c.id_cliente = ?";
$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $id_cliente);
$stmt->execute();
$result = $stmt->get_result();

// Verificar si se obtuvieron resultados
if ($result->num_rows === 0) {
    die("No se encontró información del cliente o la compra.");
}

// Asignar datos
$datos = $result->fetch_assoc();
$email = $datos['email'];
$id_transaccion = $datos['id_transaccion'];

// Cerrar la conexión a la base de datos
$stmt->close();
$conexion->close();

// Iniciar PHPMailer
$mail = new PHPMailer(true);

try {
    // Configuración del servidor SMTP
    $mail->SMTPDebug = SMTP::DEBUG_OFF; // Cambiar a DEBUG_SERVER para depuración
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'noreplay.twentyonerecords@gmail.com';  // Correo que envía
    $mail->Password   = 'idxrmuitbricimsi';                     // Contraseña o App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 587;

    // Remitente y destinatario
    $mail->setFrom('noreplay.twentyonerecords@gmail.com', 'Twenty One Records');
    $mail->addAddress($email); // Correo del cliente

    // Configuración del contenido
    $mail->isHTML(true);
    $mail->Subject = 'Detalles de su compra';
    $mail->CharSet = 'UTF-8';

    // Cuerpo del mensaje
    $cuerpo = '<h4>Gracias por su compra</h4>';
    $cuerpo .= '<p>El ID de su compra es: <strong>' . htmlspecialchars($id_transaccion, ENT_QUOTES, 'UTF-8') . '</strong></p>';
    $mail->Body    = $cuerpo;
    $mail->AltBody = 'Gracias por su compra. El ID de su compra es: ' . $id_transaccion;

    // Configuración de idioma
    $mail->setLanguage('es', '../phpmailer/language/phpmailer.lang-es.php');

    // Enviar el correo
    $mail->send();
    echo '<script>console.log("Correo de confirmación enviado con éxito.")</script>';
} catch (Exception $e) {
    // Imprimir errores en la consola del navegador
    echo '<script>';
    echo 'console.error("Error al enviar el correo: ' . addslashes($mail->ErrorInfo) . '");';
    echo 'alert("Hubo un problema al enviar el correo. Por favor, intente nuevamente.");';
    echo '</script>';
}
?>
