<?php
require 'config/config.php';
require 'config/database.php';

$db = new Database();
$con = $db->conectar();

// Variables para el producto enviado por "Comprar ahora"
$id = isset($_POST['id']) ? $_POST['id'] : null;
$token = isset($_POST['token']) ? $_POST['token'] : null;

$producto_unico = null; // Variable para el producto único

if ($id && $token) {
    // Validar token
    $token_tmp = hash_hmac('sha1', $id, KEY_TOKEN);
    if ($token == $token_tmp) {
        $sql = $con->prepare("SELECT id, nombre, precio, descuento FROM productos WHERE id=? AND activo=1");
        $sql->execute([$id]);
        $producto_unico = $sql->fetch(PDO::FETCH_ASSOC);
    } else {
        echo "Error: Token no válido.";
        exit;
    }
}

// Si no se recibió producto único, revisamos el carrito
$productos = isset($_SESSION['carrito']['productos']) ? $_SESSION['carrito']['productos'] : null;
$lista_carrito = array();

if ($productos != null && !$producto_unico) {
    foreach ($productos as $clave => $cantidad) {
        $sql = $con->prepare("SELECT id, nombre, precio, descuento, $cantidad AS cantidad FROM productos WHERE id=? AND activo=1");
        $sql->execute([$clave]);
        $lista_carrito[] = $sql->fetch(PDO::FETCH_ASSOC);
    }
} elseif ($producto_unico) {
    // Si se envió un producto único, lo añadimos a la lista de pago
    $producto_unico['cantidad'] = 1; // Solo 1 unidad al comprar directamente
    $lista_carrito[] = $producto_unico;
} else {
    // Si no hay productos en el carrito ni se envió un producto único
    header("Location: index.php");
    exit();
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago</title>
    <link rel="stylesheet"
     href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" 
    rel="stylesheet" 
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" 
    crossorigin="anonymous">
    <link href="css/estilos.css" rel="stylesheet">
</head>
<body>

<?php include 'menu.php'; ?>

<main>
   <div class="container">
      <div class="row">
         <div class="col-6">
            <h4>Detalles de pago</h4>
            <div id="paypal-button-container"></div>
         </div>

         <div class="col-6">
            <div class="table-responsive">
               <table class="table">
                  <thead>
                     <tr>
                        <th>Producto</th>
                        <th>Subtotal</th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php 
                     if($lista_carrito == null){
                         echo '<tr><td colspan="2" class="text-center"><b>Lista vacía</b></td></tr>';
                     } else {
                         $total = 0;
                         foreach($lista_carrito as $producto){
                             $_id = $producto['id'];
                             $nombre = $producto['nombre'];
                             $precio = $producto['precio'];
                             $descuento = $producto['descuento'];
                             $cantidad = $producto['cantidad'];
                             $precio_desc = $precio - (($precio * $descuento) / 100);
                             $subtotal = $cantidad * $precio_desc;
                             $total += $subtotal;
                     ?>
                     <tr>
                        <td><?php echo $nombre; ?></td>
                        <td><?php echo MONEDA . number_format($subtotal, 2, '.', ','); ?></td>
                     </tr>
                     <?php } ?>
                     <tr>
                        <td colspan="2">
                           <div class="d-flex justify-content-between">
                              <label for="shipping-method" class="form-label">Método de Envío:</label>
                              <select id="shipping-method" class="form-select w-50" onchange="updateTotal()">
                                 <option value="0" data-days="0">Selecciona una opción</option>
                                 <option value="10" data-days="5-6">Envío Estándar (5-6 días) - $10</option>
                                 <option value="15" data-days="2-3">Envío Express (2-3 días) - $15</option>
                              </select>
                           </div>
                        </td>
                     </tr>
                     <tr>
                        <td><b>Total</b></td>
                        <td><p class="h3" id="total"><?php echo MONEDA . number_format($total, 2, '.', ','); ?></p></td>
                     </tr>
                  </tbody>
                  <?php } ?>
               </table>
            </div>
         </div>
      </div>
   </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" 
integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" 
crossorigin="anonymous"></script>

<script src="https://www.paypal.com/sdk/js?client-id=<?php echo CLIENT_ID ?>&currency=<?php echo CURRENCY; ?>"></script>

<script>
    // Guardamos el total original en una variable para no perder su valor al sumar el envío
    let totalOriginal = <?php echo $total; ?>;

    // Función para actualizar el total al cambiar el envío
    function updateTotal() {
        let shippingCost = parseFloat(document.getElementById("shipping-method").value) || 0;
        let newTotal = totalOriginal + shippingCost;
        document.getElementById("total").textContent = "<?php echo MONEDA; ?>" + newTotal.toFixed(2);
    }

    paypal.Buttons({
        style: {
            color: 'blue',
            shape: 'pill',
            label: 'pay',
        },
        createOrder: function (data, actions) {
            // Obtenemos el costo de envío
            let shippingMethod = document.getElementById("shipping-method");
            let shippingCost = parseFloat(shippingMethod.value) || 0;

            // Verificamos si el usuario seleccionó un método de envío
            if (shippingCost === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Método de envío requerido',
                    text: 'Seleccione el método de envío antes de continuar.',
                    confirmButtonText: 'Entendido'
                });
                return; // Evita que se cree la orden si no hay un método seleccionado
            }

            let finalTotal = totalOriginal + shippingCost;

            return actions.order.create({
                purchase_units: [{
                    amount: {
                        value: finalTotal.toFixed(2)
                    }
                }]
            });
        },
        onApprove: function (data, actions) {
            let url = 'clases/captura.php';
            return actions.order.capture().then(function (details) {
                return fetch(url, {
                    method: 'POST',
                    headers: { 'content-type': 'application/json' },
                    body: JSON.stringify({ details: details })
                }).then(function (response) {
                    window.location.href = "completado.php?key=" + data.orderID;
                });
            });
        },
        onCancel: function (data) {
            Swal.fire({
                icon: 'info',
                title: 'Pago cancelado',
                text: 'El proceso de pago fue cancelado.',
                confirmButtonText: 'Entendido'
            });
        }
    }).render('#paypal-button-container');
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>