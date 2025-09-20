<?php
require 'config/config.php';
require 'config/database.php';

// Crear conexión con la base de datos
$db = new Database();
$con = $db->conectar();

// Obtener el id de la compra desde la URL
$id_compra = isset($_GET['id']) ? $_GET['id'] : 0;

// Verificar que el id_compra es válido
if ($id_compra > 0) {
    // Obtener los detalles de la compra
    $sql = $con->prepare("SELECT dc.id_producto, p.nombre, dc.cantidad, dc.precio 
                          FROM detalle_compra dc
                          JOIN productos p ON dc.id_producto = p.id
                          WHERE dc.id_compra = ?");
    $sql->execute([$id_compra]);
    $productos = $sql->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Si no se pasa un id válido, redirigir a historial
    header("Location: historial.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles de Compra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
</head>
<body>

<?php include 'menu.php'; ?>

<main class="container mt-5">
    <h1>Detalles de la Compra #<?php echo $id_compra; ?></h1>

    <!-- Tabla de detalles de la compra -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($productos as $producto) { 
                $subtotal = $producto['precio'] * $producto['cantidad'];
            ?>
                <tr>
                    <td><?php echo $producto['nombre']; ?></td>
                    <td><?php echo $producto['cantidad']; ?></td>
                    <td><?php echo MONEDA . number_format($producto['precio'], 2); ?></td>
                    <td><?php echo MONEDA . number_format($subtotal, 2); ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <!-- Botón para volver al historial -->
    <a href="historial.php" class="btn btn-secondary">Volver al Historial</a>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>
