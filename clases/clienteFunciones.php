<?php

function esNulo(array $parametros)
{
    foreach ($parametros as $parametro) {
        if (strlen(trim($parametro)) < 1) {
            return true;
     }
    }
    return false;
}

function esEmail($email)
{
   if(filter_var($email, FILTER_VALIDATE_EMAIL)){
    return true;
   }
   return false;
}

function validaPassword($password, $repassword){
    if(strcmp($password, $repassword) ===0){
    return true;
    } 
    return false;
}
function generarToken(){
return md5(uniqid(mt_rand(), false));
}

function registraCliente(array $datos, $con)
{
    $sql = $con->prepare("INSERT INTO clientes (nombres, apellidos, email, telefono, dni, 
    estatus, fecha_alta) VALUES(?,?,?,?,?, 1, now())");
    if ($sql->execute($datos)){
       return $con->lastInsertId();
    }  
    return 0;
}

function registraUsuario(array $datos, $con){
    $sql = $con->prepare("INSERT INTO usuarios (usuario, password, id_cliente) VALUES (?,?,?)");
    if($sql->execute([$datos[0], $datos[1], $datos[2]])){
        return $con->lastInsertId();
    }
    return 0;
}

function usuarioExiste($usuario, $con)
{
    $sql = $con->prepare("SELECT id FROM usuarios WHERE usuario LIKE ? LIMIT 1");
    $sql->execute([$usuario]);
     if($sql->fetchColumn() > 0){
        return true;
     }
     return false; 
}

function emailExiste($email, $con)
{
    $sql = $con->prepare("SELECT id FROM clientes WHERE email LIKE ? LIMIT 1");
    $sql->execute([$email]);
     if($sql->fetchColumn() > 0){
        return true;
     }
     return false; 
}

function mostrarMensajes(array $errors){
    if(count($errors) > 0){
       echo '<div class="alert alert-warning alert-dismissible fade show" role="alert"><ul>';
      foreach($errors as $error){
        echo '<li>'. $error .'</li>';
      }
      echo '<ul>';
      echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    }  
}


function login($usuario, $password, $con, $proceso){
    $sql = $con->prepare("SELECT id, usuario, password, id_cliente FROM usuarios WHERE usuario LIKE ? LIMIT 1");
    $sql->execute([$usuario]);
    if($row = $sql->fetch(PDO::FETCH_ASSOC)){
        if(password_verify($password,$row['password'])){
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_name'] = $row['usuario'];
            $_SESSION['user_cliente'] = $row['id_cliente'];
            if($proceso == 'pago') {
                header("Location: checkout.php");
            } else {
                header("Location: index.php");
            }
            exit;
        }
    }
    return 'El usuario y/o contraseña son incorrectos.';
}