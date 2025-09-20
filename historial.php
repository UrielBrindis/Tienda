<?php
require 'config/config.php';
require 'config/database.php';

// Comprobar si la sesión ya está iniciada antes de llamar a session_start
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Crear conexión con la base de datos
$db = new Database();
$con = $db->conectar();

// Obtener el id del cliente desde la sesión
$id_cliente = $_SESSION['user_cliente'] ?? 0; // Usamos un valor por defecto si no hay sesión

// Comprobar si la sesión no está iniciada o el id_cliente no está disponible
if ($id_cliente == 0) {
    echo "Debes iniciar sesión para ver tu historial de compras.";
    exit;
}

// Obtener el historial de compras (asegurarse de que el nombre de la columna y la tabla sean correctos)
$sql = $con->prepare("SELECT id, fecha, total FROM compra WHERE id_cliente = ? ORDER BY fecha DESC");
$sql->execute([$id_cliente]);
$compras = $sql->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Compras</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
</head>
<body>

<?php include 'menu.php'; ?>

<main class="container mt-5">
    <h1>Historial de Compras</h1>

    <!-- Tabla de compras -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID Compra</th>
                <th>Fecha</th>
                <th>Total</th>
                <th>Detalles</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($compras as $compra) { ?>
                <tr>
                    <td><?php echo $compra['id']; ?></td> <!-- Asegúrate de que la columna sea 'id' en lugar de 'id_compra' -->
                    <td><?php echo date('d-m-Y H:i:s', strtotime($compra['fecha'])); ?></td>
                    <td><?php echo MONEDA . number_format($compra['total'], 2); ?></td>
                    <td>
                        <!-- Botón para ver los detalles -->
                        <a href="detalle_compra.php?id=<?php echo $compra['id']; ?>" class="btn btn-primary">Ver Detalles</a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>
