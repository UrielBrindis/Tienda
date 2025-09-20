<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <script src="https://www.paypal.com/sdk/js?client-id=AVLG7XFziE7RBfazm11EbUmBtixnXC4VT12A7Ogj7hNQLag-c0sYJzx_x77NxB5Tra4hfOiZaHx1pcPH&currency=USD"></script>


</head>
<body>
    
<div id="paypal-button-container"></div>

<script>
    paypal.Buttons({
        style:{
            color: 'blue',
            shape: 'pill',
            label: 'pay',
        },
        createOrder: function(data, actions){
              return actions.order.create({
                purchase_units: [{
                    amount: {
                        value: 800
                    }
                }]
              });
        },

        onApprove:function(data, actions){
            actions.order.capture().then(function (details){
                window.location.href="completado.html"
            });
        },

        onCancel: function(data){
            alert("Pago cancelado")
            console.log(data);
        }
    }).render('#paypal-button-container');
</script>

</body>
</html>