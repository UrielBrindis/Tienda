<?php

require 'config/config.php';
require 'config/database.php';
$db = new Database();
$con = $db->conectar();

$id = isset($_GET['id']) ? $_GET['id'] : '';
$token = isset($_GET['token']) ? $_GET['token'] : '';

if ($id == '' || $token == '') {
    echo 'Error al procesar la peticion';
    exit;
} else {

     $token_tmp = hash_hmac('sha1', $id, KEY_TOKEN);

     if($token == $token_tmp){

      $sql = $con->prepare("SELECT count(id) FROM productos WHERE id=? AND activo=1");
      $sql->execute([$id]);
      if ($sql->fetchColumn() > 0) {
 
        $sql = $con->prepare("SELECT nombre, artista, descripcion, tracklist, precio, descuento FROM productos WHERE id=? AND activo=1 LIMIT 1");
        $sql->execute([$id]);
        $row = $sql->fetch(PDO::FETCH_ASSOC);
        $nombre = $row['nombre'];
        $artista = $row['artista'];
        $descripcion = $row['descripcion'];
        $tracklist = $row['tracklist'];
        $precio = $row['precio'];
        $descuento = $row['descuento'];
        $precio_desc = $precio - (($precio * $descuento) / 100);
        $dir_images = 'images/productos/'. $id . '/';

        $rutaImg = $dir_images . 'principal.jpg';

        if(!file_exists($rutaImg)) {
          $rutaImg = 'images/no-photo.jpg';
        }

        $imagenes = array();
        if(file_exists($dir_images))
        {
        $dir = dir($dir_images);

        while(($archivo = $dir->read()) != false){
          if($archivo != 'principal.jpg' && (strpos($archivo, 'jpg') || strpos($archivo, 'jpeg'))){
            $imagenes[] = $dir_images . $archivo; 
           }
          }
         $dir->close();
        }   
      }

     } else {
      echo 'Error al procesar la peticion';
    exit;
     }

}



?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles</title>
    <link rel="stylesheet"
     href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" 
    rel="stylesheet" 
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" 
    crossorigin="anonymous">
  </head>
    <link href="css/estilos.css" rel="stylesheet">
</head>
<body>

<?php include'menu.php'; ?>

<main>
   <div class="container">
     <div class="row">
       <div class="col-md-6 order-md-1">
       <div id="carouselImages" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
         <div class="carousel-item active">
           <img src="<?php echo $rutaImg; ?>" class="d-block w-100">
         </div>

         <?php foreach($imagenes as $img) { ?>
           <div class="carousel-item">
              <img src="<?php echo $img; ?>" class="d-block w-100">
           </div>
         <?php } ?>

  </div>
  <button class="carousel-control-prev" type="button" data-bs-target="#carouselImages" data-bs-slide="prev">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Previous</span>
  </button>
  <button class="carousel-control-next" type="button" data-bs-target="#carouselImages" data-bs-slide="next">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Next</span>
  </button>
</div>

       
       </div>
       <div class="col-md-6 order-md-2">
    <h2 style="margin-bottom: 15px;"><?php echo $nombre; ?></h2>
    <p style="font-weight: normal; font-size: 24px; margin-bottom: 15px;">Por:<b> <?php echo $artista; ?></b></p>

    <?php if($descuento > 0) { ?>
        <p style="margin-bottom: 15px;">
            <del><?php echo MONEDA . number_format($precio, 2, '.', ','); ?></del>
        </p>
        <h2 style="margin-bottom: 15px;">
            <?php echo MONEDA . number_format($precio_desc, 2, '.', ','); ?>
            <small class="text-success"><?php echo $descuento; ?>% descuento</small>
        </h2>
    <?php } else { ?>
        <h2 style="margin-bottom: 15px;"><?php echo MONEDA . number_format($precio, 2, '.', ','); ?></h2>
    <?php } ?>

    <!-- Título "Descripción" -->
    <h3>Descripción</h3>
    <p class="lead" style="margin-bottom: 25px;">
        <?php echo $descripcion; ?>
    </p>

<!-- Título "Tracklist" -->
<h3>Tracklist</h3>
<ul class="lead" style="margin-bottom: 25px;">
    <?php 
    $tracks = explode("\n", $tracklist); 
    foreach($tracks as $track) {
        echo "<li>" . htmlspecialchars($track) . "</li>";
    }
    ?>
</ul>
<div class="d-grid gap-3 col-10 mx-auto">
    <!-- Formulario para "Comprar ahora" -->
    <?php if (isset($_SESSION['user_cliente'])) { ?>
        <form action="pago.php" method="POST">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <input type="hidden" name="token" value="<?php echo $token_tmp; ?>">
            <button class="btn btn-primary btn-comprar" type="submit">Comprar ahora</button>
        </form>
    <?php } else { ?>
        <a href="login.php?redirect=pago" class="btn btn-primary btn-comprar">Comprar ahora</a>
    <?php } ?>
    
    <!-- Botón para agregar al carrito -->
    <button class="btn btn-outline-primary btn-agregar" type="button" onclick="addProducto(<?php echo $id; ?>, '<?php echo $token_tmp; ?>')">Agregar al carrito</button>
</div>


</div>

      </div>

   </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
 integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" 
 crossorigin="anonymous"></script>

  <script>
    function addProducto(id, token){
      let url = 'clases/carrito.php'
      let formData = new FormData()
      formData.append('id', id)
      formData.append('token', token)

      fetch(url, {
        method: 'POST',
        body: formData,
        mode: 'cors'
      }).then(response => response.json())
      .then(data => {
        if(data.ok){
           let elemento = document.getElementById("num_cart")
           elemento.innerHTML = data.numero
        }
      })

    }
  </script>

</body>
</html>