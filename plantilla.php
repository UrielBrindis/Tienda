<?php

require 'config/config.php';
require 'config/database.php';
require 'clases/clienteFunciones.php';

$db = new Database();
$con = $db->conectar();

$errors = [];

if(!empty($_POST)){

    $nombres = trim($_POST['nombres']);
    $apellidos = trim($_POST['apellidos']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $dni = trim($_POST['dni']);
    $usuario = trim($_POST['usuario']);
    $password = trim($_POST['password']);
    $repassword = trim($_POST['repassword']);

    if(esNulo([$nombres, $apellidos, $email, $telefono, $dni, $usuario, $repassword])){
        $errors[] = "Debe llenar todos los campos";
    }
    if(!esEmail($email)){
     $errors[] ="La direccion de correo no es valida";
    }

    if(!validaPassword($password, $repassword)){
        $errors[] ="Las contraseñas no coinciden";
    }

    if(usuarioExiste($usuario, $con)){
        $errors[] ="El nombre de usuario $usuario ya existe";
    }

    if(emailExiste($email, $con)){
        $errors[] ="El correo electronico $email ya existe";
    }

}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Twenty One Records</title>
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
<header>
  <div class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
      <a href="#" class="navbar-brand d-flex align-items-center">
        <img src="images/Designer.png" alt="Logo" width="50" height="50" class="me-2">
        <strong>Twenty One Records</strong>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" 
      data-bs-target="#navbarHeader" aria-controls="navbarHeader" 
      aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarHeader">
          <ul class="navbar-nav me-auto mb-2 mb-lg-0">
             <li class="nav-item">
                 <a href="index.php" class="nav-link active">Catalogo</a>
             </li>
             <li class="nav-item">
                 <a href="#" class="nav-link active">Contacto</a>
             </li>
          </ul>
          <a href="checkout.php" class="btn btn-primary">
            <i class="fas fa-shopping-cart"></i> Carrito 
            <span id="num_cart" class="badge bg-secondary"><?php echo $num_cart; ?></span>
          </a>
      </div>
    </div>
  </div>
</header>

<main>
   <div class="container">
   </div>  
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
 integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" 
 crossorigin="anonymous"></script>


</body>
</html>