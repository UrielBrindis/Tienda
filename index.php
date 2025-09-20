<?php
require 'config/config.php';
require 'config/database.php';

$db = new Database();
$con = $db->conectar();

// Variables para filtros
$id_genero = isset($_GET['id_genero']) ? intval($_GET['id_genero']) : 0;
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';

// Construcción de la consulta
$query = "SELECT id, nombre, artista, precio, id_genero FROM productos WHERE activo=1";
$params = [];

// Filtro por género
if ($id_genero > 0) {
    $query .= " AND id_genero = :id_genero";
    $params[':id_genero'] = $id_genero;
}

// Filtro por búsqueda
if (!empty($busqueda)) {
    $query .= " AND (nombre LIKE :busqueda_nombre OR artista LIKE :busqueda_artista)";
    $params[':busqueda_nombre'] = '%' . $busqueda . '%';
    $params[':busqueda_artista'] = '%' . $busqueda . '%';
}




try {
    $sql = $con->prepare($query);
    $sql->execute($params);
    $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error en la consulta: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Twenty One Records</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/estilos.css" rel="stylesheet">
</head>
<body>
<?php include 'menu.php'; ?>

<main>
  <div class="container my-4">
    <form class="d-flex justify-content-center mb-4" method="GET" action="index.php">
      <a href="index.php?id_genero=0" class="btn btn-dark btn-genero me-2 <?= $id_genero === 0 ? 'active' : ''; ?>">Todos</a>
      <a href="index.php?id_genero=1" class="btn btn-dark btn-genero me-2 <?= $id_genero === 1 ? 'active' : ''; ?>">Rap/Hip Hop</a>
      <a href="index.php?id_genero=2" class="btn btn-dark btn-genero me-2 <?= $id_genero === 2 ? 'active' : ''; ?>">Pop</a>
      <a href="index.php?id_genero=3" class="btn btn-dark btn-genero me-2 <?= $id_genero === 3 ? 'active' : ''; ?>">Rock</a>
      <a href="index.php?id_genero=4" class="btn btn-dark btn-genero me-2 <?= $id_genero === 4 ? 'active' : ''; ?>">Latina</a>
      <a href="index.php?id_genero=5" class="btn btn-dark btn-genero me-2 <?= $id_genero === 5 ? 'active' : ''; ?>">EDM</a>
      <a href="index.php?id_genero=6" class="btn btn-dark btn-genero me-2 <?= $id_genero === 6 ? 'active' : ''; ?>">R&B/Soul</a>
      <input type="text" name="busqueda" class="form-control ms-2" placeholder="Buscar..." value="<?= htmlspecialchars($busqueda); ?>">
      <button type="submit" class="btn btn-primary ms-2">Buscar</button>
    </form>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3">
      <?php if (count($resultado) > 0): ?>
        <?php foreach ($resultado as $row): ?>
          <div class="col">
            <div class="card shadow-sm">
              <?php
                $id = $row['id'];
                $imagen = "images/productos/" . $id . "/principal.jpg";
                if (!file_exists($imagen)) $imagen = "images/no-photo.jpg";
              ?>
              <img src="<?= $imagen; ?>" class="card-img-top" alt="<?= $row['nombre']; ?>">
              <div class="card-body">
                <h5 class="card-title"><?= $row['nombre']; ?></h5>
                <p class="card-title"><?= $row['artista']; ?></p>
                <p class="card-text">$ <?= number_format($row['precio'], 2, '.', ','); ?></p>
                <div class="d-flex justify-content-between align-items-center">
                  <a href="details.php?id=<?= $row['id']; ?>&token=<?= hash_hmac('sha1', $row['id'], KEY_TOKEN); ?>" class="btn btn-primary">Detalles</a>
                  <button class="btn btn-outline-success" type="button" onclick="addProducto(<?= $row['id']; ?>, '<?= hash_hmac('sha1', $row['id'], KEY_TOKEN); ?>')">Agregar al carrito</button>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="alert alert-warning text-center">Sin resultados</div>
      <?php endif; ?>
    </div>
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  function addProducto(id, token) {
    let url = 'clases/carrito.php';
    let formData = new FormData();
    formData.append('id', id);
    formData.append('token', token);

    fetch(url, {
      method: 'POST',
      body: formData,
      mode: 'cors'
    }).then(response => response.json())
      .then(data => {
        if (data.ok) {
          let elemento = document.getElementById("num_cart");
          elemento.innerHTML = data.numero;
        }
      });
  }
</script>
</body>
</html>
