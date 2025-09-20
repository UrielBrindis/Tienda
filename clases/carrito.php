<?php
require '../config/config.php';
require '../config/database.php';


$db = new Database();
$con = $db->conectar();

// Leer detalles de la transacción enviados por PayPal
$json = file_get_contents('php://input');
$datos = json_decode($json, true);

if (is_array($datos)) {
    $id_transaccion = $datos['details']['id'];
    $total = $datos['details']['purchase_units'][0]['amount']['value'];
    $status = $datos['details']['status'];
    $fecha = date('Y-m-d H:i:s');
    $email = $datos['details']['payer']['email_address'];
    $productos = isset($_SESSION['carrito']['productos']) ? $_SESSION['carrito']['productos'] : null;

    // Asegúrate de que el id_cliente esté disponible en la sesión
    $id_cliente = isset($_SESSION['user_cliente']) ? $_SESSION['user_cliente'] : 0;

    // Verificar si el carrito está vacío antes de insertar la compra
    if (empty($productos)) {
        echo json_encode(['error' => 'Carrito vacío']);
        exit;
    }

    // Insertar la compra en la tabla "compra"
    $sql = $con->prepare("INSERT INTO compra (id_cliente, id_transaccion, total, status, fecha, email) VALUES (?, ?, ?, ?, ?, ?)");
    $sql->execute([$id_cliente, $id_transaccion, $total, $status, $fecha, $email]);
    $id_compra = $con->lastInsertId(); // Obtener ID de la compra

    // Insertar los detalles de los productos en la tabla "detalle_compra"
    foreach ($productos as $id_producto => $cantidad) {
        $sql = $con->prepare("SELECT nombre, precio, descuento FROM productos WHERE id=?");
        $sql->execute([$id_producto]);
        $producto = $sql->fetch(PDO::FETCH_ASSOC);

        if ($producto) {
            $precio = $producto['precio'];
            $descuento = $producto['descuento'];
            $precio_desc = $precio - (($precio * $descuento) / 100);

            $sql_detalle = $con->prepare("INSERT INTO detalle_compra (id_compra, id_producto, nombre, cantidad, precio) VALUES (?, ?, ?, ?, ?)");
            $sql_detalle->execute([$id_compra, $id_producto, $producto['nombre'], $cantidad, $precio_desc]);
        }
    }

    // Limpiar el carrito después de completar la compra
    unset($_SESSION['carrito']);

}
error_log(print_r($datos, true), 3, 'logs/debug.log');
header("HTTP/1.1 200 OK");
exit;
?>
