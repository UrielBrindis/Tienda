<?php
require 'config/config.php';
require 'config/database.php';
require 'clases/clienteFunciones.php';

$db = new Database();
$con = $db->conectar();

$errors = [];

if (!empty($_POST)) {
    $nombres = trim($_POST['nombres']);
    $apellidos = trim($_POST['apellidos']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $dni = trim($_POST['dni']);
    $usuario = trim($_POST['usuario']);
    $password = trim($_POST['password']);
    $repassword = trim($_POST['repassword']);
    $terminos = isset($_POST['terminos']) ? true : false;
    $politicas = isset($_POST['politicas']) ? true : false;
    $notificaciones = isset($_POST['notificaciones']) ? true : false;

    if (esNulo([$nombres, $apellidos, $email, $telefono, $dni, $usuario, $password, $repassword])) {
        $errors[] = "Debe llenar todos los campos.";
    }
    if (!esEmail($email)) {
        $errors[] = "La dirección de correo no es válida.";
    }
    if (!validaPassword($password, $repassword)) {
        $errors[] = "Las contraseñas no coinciden.";
    }
    if (usuarioExiste($usuario, $con)) {
        $errors[] = "El nombre de usuario $usuario ya existe.";
    }
    if (emailExiste($email, $con)) {
        $errors[] = "El correo electrónico $email ya existe.";
    }
    if (!$terminos || !$politicas || !$notificaciones) {
        $errors[] = "Debe aceptar los Términos, Políticas de privacidad y Notificaciones.";
    }

    if (count($errors) == 0) {
        $id = registraCliente([$nombres, $apellidos, $email, $telefono, $dni], $con);

        if ($id > 0) {
            $hashPassword = password_hash($password, PASSWORD_DEFAULT);
            $idUsuario = registraUsuario([$usuario, $hashPassword, $id], $con);

            if ($idUsuario > 0) {
                echo "<script>
                        alert('Registro Completado');
                        window.location.href = 'login.php';
                      </script>";
                exit;
            } else {
                $errors[] = "Error al registrar usuario.";
            }
        } else {
            $errors[] = "Error al registrar cliente.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/estilos.css" rel="stylesheet">
</head>

<body>
<header>
    <div class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a href="#" class="navbar-brand d-flex align-items-center">
                <img src="images/Designer.png" alt="Logo" width="50" height="50" class="me-2">
                <strong>Twenty One Records</strong>
            </a>
        </div>
    </div>
</header>

<main>
    <div class="container">
        <h2>Datos del cliente</h2>

        <?php mostrarMensajes($errors); ?>

        <form class="row g-3" action="registro.php" method="post" autocomplete="off">
            <!-- Campos de registro -->
            <div class="col-md-6">
                <label for="nombres">Nombres</label>
                <input type="text" name="nombres" id="nombres" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label for="apellidos">Apellidos</label>
                <input type="text" name="apellidos" id="apellidos" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label for="email">Correo electrónico</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label for="telefono">Teléfono</label>
                <input type="tel" name="telefono" id="telefono" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label for="dni">DNI</label>
                <input type="text" name="dni" id="dni" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label for="usuario">Usuario</label>
                <input type="text" name="usuario" id="usuario" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label for="password">Contraseña</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label for="repassword">Repetir contraseña</label>
                <input type="password" name="repassword" id="repassword" class="form-control" required>
            </div>

            <!-- Casillas de verificación -->
            <div class="col-md-12">
                <div class="form-check">
                    <input type="checkbox" name="terminos" id="terminos" class="form-check-input" required>
                    <label for="terminos" class="form-check-label">
                        Acepto las <a href="#" data-bs-toggle="modal" data-bs-target="#modalTerminos">Condiciones de servicio</a>.
                    </label>
                </div>
                <div class="form-check">
                    <input type="checkbox" name="politicas" id="politicas" class="form-check-input" required>
                    <label for="politicas" class="form-check-label">
                        Acepto las <a href="#" data-bs-toggle="modal" data-bs-target="#modalPoliticas">Políticas de privacidad</a>.
                    </label>
                </div>
                <div class="form-check">
                    <input type="checkbox" name="notificaciones" id="notificaciones" class="form-check-input" required>
                    <label for="notificaciones" class="form-check-label">
                        Acepto las <a href="#" data-bs-toggle="modal" data-bs-target="#modalNotificaciones">Condiciones de entrega y devolución</a>.
                    </label>
                </div>
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-primary">Registrar</button>
            </div>
        </form>
    </div>
</main>

<!-- Modales -->
<div class="modal fade" id="modalTerminos" tabindex="-1" aria-labelledby="modalTerminosLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTerminosLabel">Condiciones de servicio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body"><ul>
            <p>Al usar nuestro sitio y realizar compras, aceptas las siguientes condiciones:</p>
                <p><b>Uso del sitio web</b></p>
                <p><li>Este sitio está disponible únicamente para usuarios mayores de 18 años o bajo supervisión de un adulto.</li>
                <li>Está prohibido el uso indebido del sitio, incluyendo intentos de fraude o acceso no autorizado.</li></p>
                <p><b>Productos y precios</b></p>
                <p><li>Los precios están en dolares americanos e incluyen impuestos aplicables.</li>
                <li>Nos reservamos el derecho de modificar precios y descripciones de productos sin previo aviso.</li></p>
                <p><b>Pedidos y pagos</b></p>
                <p><li>Todo pedido está sujeto a disponibilidad y confirmación de pago.</li>
                <li>Aceptamos pagos a través de métodos seguros, como PayPal y otros especificados en el sitio.</li></p>
                <p><b>Envíos y entregas</b></p>
                <p><li>Los tiempos de envío son aproximados y pueden variar según la ubicación.</li>
                 <li>No somos responsables por retrasos causados por servicios de mensajería.</li></p>
                 <p><b>Devoluciones y reembolsos</b></p>
                 <p><li>Las solicitudes de devolución deben realizarse dentro de los 10 días posteriores a la recepción del producto.</li>
                  <li>Consulta nuestra Política de Devoluciones para más detalles.</li></p>
                 <p><b>Modificaciones</b></p>
                 <p>Nos reservamos el derecho de actualizar estas condiciones en cualquier momento. Los cambios se reflejarán en esta página.</p>
            </ul></div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPoliticas" tabindex="-1" aria-labelledby="modalPoliticasLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPoliticasLabel">Políticas de privacidad</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body"><p>En Twenty One Records, valoramos y protegemos la privacidad de nuestros usuarios. A continuación, detallamos cómo recopilamos, utilizamos y protegemos tu información personal:</p>
<b>Información que recopilamos</b>
<p><li>Datos personales: Nombre, correo electrónico, dirección de envío, y detalles de pago necesarios para procesar compras.</li>
<li>Información de navegación: Cookies y datos de sesión para mejorar tu experiencia en el sitio.</li></p>
<b>Uso de la información</b>
<p><li>Procesar y enviar pedidos.</li>
<li>Mejorar nuestro servicio y personalizar tu experiencia.</li>
<li>Comunicarnos contigo sobre promociones, actualizaciones, o problemas relacionados con tu pedido.</li></p>
<b>Protección de la información</b>
<p><li>Implementamos medidas de seguridad para proteger tus datos personales, incluyendo cifrado de datos y servidores seguros.</li>
<li>Nunca compartimos tu información con terceros, excepto cuando sea necesario para procesar pagos o envíos.</li></p>
<p><b>Cookies</b></p>
<p>Utilizamos cookies para analizar el tráfico y personalizar el contenido del sitio. Puedes gestionar tus preferencias en tu navegador.</p>
<p><b>Derechos del usuario</b></p>
<p>Tienes derecho a:</p>
<p><li>Acceder, modificar o eliminar tu información personal.</li>
<li>Retirar tu consentimiento para el uso de datos en cualquier momento.</li></p>
<p><b>Cambios en la política</b></p>
<p>Nos reservamos el derecho de actualizar estas políticas. Las modificaciones serán publicadas en esta página.</p></div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalNotificaciones" tabindex="-1" aria-labelledby="modalNotificacionesLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalNotificacionesLabel">Condiciones de entrega y devolución</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            <p><b>Entrega de pedidos</b></p>
            <p><li>Ofrecemos opciones de envío estándar y express, detalladas al momento del pago.</li>
<li>Los tiempos de entrega son estimados y dependen de la ubicación y disponibilidad del servicio de mensajería.</li>
<li>Proporcionamos un número de seguimiento para monitorear tu pedido.</li></p>
<p><b>Recepción del pedido</b></p>
<p>Verifica el estado del paquete al recibirlo. Si detectas daños visibles, repórtalo inmediatamente al mensajero y a nuestro servicio al cliente.</p>
<p><b>Políticas de devolución</b></p>
<p>Aceptamos devoluciones dentro de los [número de días] días posteriores a la recepción, siempre que el producto esté sin uso y en su empaque original.</p>
Los costos de envío de devoluciones corren a cargo del cliente, salvo en casos de error o defectos de fábrica.</p>
<p><b>Proceso de reembolso</b></p>
<p>Una vez aprobada la devolución, procesaremos el reembolso a través del método de pago original dentro de [número de días] días hábiles.</p>
<p><b>Exclusiones</b></p>
<p>No se aceptan devoluciones de productos abiertos, usados o personalizados, salvo que presenten defectos.</p></div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
