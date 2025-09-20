<?php
require '../config/config.php';
require '../config/database.php';

$id_usuario = $_SESSION['id_usuario'];

if ($id_usuario) {
    $db = new Database();
    $con = $db->conectar();

    // Iniciar transacción
    $con->beginTransaction();

    try {
        // Insertar en la tabla compra
        $sql = $con->prepare("INSERT INTO compra (id_cliente, fecha, status, total) VALUES (?, NOW(), 'PENDIENTE', ?)");
        $total = 0; // Calcula el total según los productos en el carrito
        $sql->execute([$id_usuario, $total]);

        $id_compra = $con->lastInsertId();

        // Transferir productos del carrito temporal a detalle_compra
        $sql = $con->prepare("SELECT * FROM carrito_temporal WHERE id_usuario = ?");
        $sql->execute([$id_usuario]);
        $carrito = $sql->fetchAll(PDO::FETCH_ASSOC);

        foreach ($carrito as $producto) {
            $sql = $con->prepare("INSERT INTO detalle_compra (id_compra, id_producto, nombre, precio, cantidad) VALUES (?, ?, ?, ?, ?)");
            $sql->execute([
                $id_compra,
                $producto['id_producto'],
                $producto['nombre'],  // Ajusta esto según tu lógica
                $producto['precio'],  // Ajusta esto según tu lógica
                $producto['cantidad']
            ]);
            $total += $producto['precio'] * $producto['cantidad']; // Calcula el total
        }

        // Actualizar el total en la tabla compra
        $sql = $con->prepare("UPDATE compra SET total = ? WHERE id = ?");
        $sql->execute([$total, $id_compra]);

        // Vaciar el carrito temporal
        $sql = $con->prepare("DELETE FROM carrito_temporal WHERE id_usuario = ?");
        $sql->execute([$id_usuario]);

        // Confirmar transacción
        $con->commit();

        $datos['ok'] = true;
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $con->rollBack();
        $datos['ok'] = false;
        $datos['error'] = $e->getMessage();
    }
} else {
    $datos['ok'] = false;
}

echo json_encode($datos);
