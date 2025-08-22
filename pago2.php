<?php
$conexion = new mysqli("localhost","root","","prueba2");
if($conexion->connect_error){ die("Error: ".$conexion->connect_error); }

if(!isset($_GET['id'])) die("No se especificó el pedido.");

$pedido_id = intval($_GET['id']);
$result = $conexion->query("SELECT * FROM pedidos WHERE id_pedido=$pedido_id");
if($result->num_rows==0) die("Pedido no encontrado.");

$pedido = $result->fetch_assoc();
$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Pago Pedido</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
body {
    background: linear-gradient(135deg, #fef3e0, #f8d58c);
    min-height: 100vh;
    display: flex;
    flex-direction: column; /* apila elementos verticalmente */
    align-items: center; /* centrado horizontal */
    padding: 40px 20px;
}
h2 {
    margin-bottom:20px;
    color:#5c3a00;
    font-size:1.8rem;
}
form {
    background-color:#fff;
    padding:20px 25px;
    border-radius:15px;
    box-shadow:0 10px 25px rgba(0,0,0,0.15);
    width:100%;
    max-width:350px;
    margin-top:10px; /* espacio entre h2 y formulario */
}
label { display:block; margin-top:12px; margin-bottom:5px; font-weight:bold; color:#333; }
input[type="text"], input[type="number"], select {
    width:100%;
    padding:10px 12px;
    border-radius:6px;
    border:1px solid #ccc;
    font-size:14px;
    transition: all 0.3s ease;
}
input[type="text"]:focus, input[type="number"]:focus, select:focus {
    border-color:#f8b500;
    outline:none;
    box-shadow:0 0 5px rgba(248,181,0,0.4);
}
input[name="cvv"] { -webkit-text-security: disc; }
button {
    margin-top:18px;
    width:100%;
    padding:12px;
    background-color:#f8b500;
    border:none;
    border-radius:6px;
    color:#fff;
    font-size:15px;
    font-weight:bold;
    cursor:pointer;
    transition: background 0.3s ease, transform 0.2s ease;
}
button:hover { background-color:#e09e00; transform:translateY(-1px); }
@media (max-width:500px){ form{padding:15px;} }
</style>
</head>
<body>

<h2>Formulario de Pago</h2>
<form action="procesar_pago.php" method="POST">
    <input type="hidden" name="pedido_id" value="<?= $pedido['id_pedido'] ?>">
    <input type="hidden" name="nombre_cliente" value="<?= htmlspecialchars($pedido['nombre_cliente']) ?>">

    <label>Nombre tarjeta:</label>
    <input type="text" name="nombre_tarjeta" required>

    <label>Banco:</label>
    <select name="banco" required>
        <option value="">Seleccione</option>
        <option value="Agricola">Agrícola</option>
        <option value="Cuscatlan">Cuscatlán</option>
        <option value="Davivienda">Davivienda</option>
        <option value="Hipotecario">Hipotecario</option>
    </select>

    <label>Número tarjeta:</label>
    <input type="text" name="numero_tarjeta" maxlength="16" required>

    <label>Fecha expiración (MM/AA):</label>
    <input type="text" name="fecha_exp" required>

    <label>CVV:</label>
    <input type="text" name="cvv" maxlength="4" required>

    <label>Depositar / Pagar mitad del dinero:</label>
    <input type="number" name="monto" step="0.01" placeholder="0.00" required>

<button type="submit">Pagar</button>

<button type="button" onclick="window.location.href='personalizados.php';" 
        style="background-color:#ccc; color:#333; margin-top:10px;">
    Regresar a Diseñar pastel
</button>
</form>

</body>
</html>














