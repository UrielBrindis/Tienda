<?php
require '../config/config.php';
require '../config/database.php';

if (isset($_POST['id'])) {

    $id_producto = $_POST['id'];
    $token = $_POST['token'];
    $id_usuario = $_SESSION['id_usuario']; // ID del usuario logueado

    $token_tmp = hash_hmac('sha1', $id_producto, KEY_TOKEN);

    if ($token_tmp == $token) {
        $db = new Database();
        $con = $db->conectar();

        // Verificar si el producto ya está en el carrito del usuario
        $sql = $con->prepare("SELECT cantidad FROM carrito_temporal WHERE id_usuario = ? AND id_producto = ?");
        $sql->execute([$id_usuario, $id_producto]);
        $result = $sql->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            // Actualizar la cantidad del producto en el carrito
            $cantidad = $result['cantidad'] + 1;
            $sql = $con->prepare("UPDATE carrito_temporal SET cantidad = ? WHERE id_usuario = ? AND id_producto = ?");
            $sql->execute([$cantidad, $id_usuario, $id_producto]);
        } else {
            // Insertar el producto en el carrito
            $sql = $con->prepare("INSERT INTO carrito_temporal (id_usuario, id_producto, cantidad) VALUES (?, ?, ?)");
            $sql->execute([$id_usuario, $id_producto, 1]);
        }

        $datos['numero'] = count($_SESSION['carrito']['productos']); // Actualización opcional
        $datos['ok'] = true;
    } else {
        $datos['ok'] = false;
    }
} else {
    $datos['ok'] = false;
}

echo json_encode($datos);
