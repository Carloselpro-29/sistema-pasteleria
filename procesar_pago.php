<?php
$conn = new mysqli("localhost","root","","prueba2");
if($conn->connect_error){ die("Error: ".$conn->connect_error); }

$pedido_id = $_POST['pedido_id'];
$nombre_cliente = $_POST['nombre_cliente'];
$nombre_tarjeta = $_POST['nombre_tarjeta'];
$banco = $_POST['banco'];
$numero_tarjeta = $_POST['numero_tarjeta'];
$fecha_exp = $_POST['fecha_exp'];
$cvv = $_POST['cvv'];

// Aseguramos que monto sea un número decimal válido
$monto = isset($_POST['monto']) && is_numeric($_POST['monto']) ? floatval($_POST['monto']) : 0;

$fecha_pago = date("Y-m-d H:i:s");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Ticket de Pago</title>
<style>
body {
    background: #f2f2f2;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    padding: 40px 20px;
    font-family: 'Courier New', Courier, monospace;
}
.ticket {
    background: #fff;
    width: 350px;
    padding: 20px;
    border: 1px dashed #333;
    border-radius: 5px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
.ticket h1 {
    text-align: center;
    font-size: 1.5rem;
    margin-bottom: 10px;
    letter-spacing: 1px;
}
.ticket .separator {
    border-top: 1px dashed #333;
    margin: 10px 0;
}
.ticket .info {
    font-size: 0.9rem;
    margin-bottom: 5px;
}
.ticket .info strong { width: 120px; display: inline-block; }
.ticket .amount {
    text-align: center;
    font-size: 1.2rem;
    margin: 15px 0;
    font-weight: bold;
}
.ticket .thanks {
    text-align: center;
    font-size: 1rem;
    margin-top: 10px;
}
button {
    margin-top: 15px;
    width: 100%;
    padding: 10px;
    background: #333;
    color: #fff;
    border: none;
    border-radius: 3px;
    font-weight: bold;
    cursor: pointer;
    transition: background 0.3s ease;
}
button:hover { background: #555; }
@media(max-width:400px){ .ticket { width: 100%; } }
</style>
</head>
<body>
<div class="ticket">
<?php
$stmt = $conn->prepare("INSERT INTO pago (pedido_id, nombre_cliente, nombre_tarjeta, banco, numero_tarjeta, fecha_exp, cvv, monto, fecha_pago) VALUES (?,?,?,?,?,?,?,?,?)");
$stmt->bind_param("issssssds", $pedido_id, $nombre_cliente, $nombre_tarjeta, $banco, $numero_tarjeta, $fecha_exp, $cvv, $monto, $fecha_pago);

if($stmt->execute()):
?>
    <h1>Dulce Tentación</h1>
    <div class="separator"></div>

    <div class="info"><strong>Pedido #:</strong> <?= $pedido_id ?></div>
    <div class="info"><strong>Cliente:</strong> <?= htmlspecialchars($nombre_cliente) ?></div>
    <div class="info"><strong>Banco:</strong> <?= $banco ?></div>
    <div class="info"><strong>Tarjeta:</strong> **** **** **** <?= substr($numero_tarjeta, -4) ?></div>
    <div class="info"><strong>Fecha y hora:</strong> <?= date("d/m/Y H:i:s") ?></div>

    <div class="separator"></div>
    <div class="amount">Monto pagado: $<?= number_format($monto,2) ?></div>
    <div class="separator"></div>

    <div class="thanks">¡Gracias por tu compra!</div>
    <button onclick="window.print()">Imprimir Ticket</button>
    <button onclick="window.location.href='dashboard_user.php'" style="background-color:#ccc; color:#333; margin-top:10px;">
    Volver al Inicio
</button>
<?php
else:
    echo "<p style='color:red; text-align:center;'>Error al registrar el pago: ".$stmt->error."</p>";
endif;
$stmt->close();
$conn->close();
?>
</div>
</body>
</html>






