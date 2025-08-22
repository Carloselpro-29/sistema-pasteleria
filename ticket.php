<?php
$host = "localhost";
$usuario = "root";
$clave = "";
$bd = "prueba2";

$conn = new mysqli($host, $usuario, $clave, $bd);
if ($conn->connect_error) {
    die("Error en la conexión: " . $conn->connect_error);
}

$id_compra = isset($_GET['id']) ? intval($_GET['id']) : 0;

$sql = "SELECT c.*, p.nombre AS pastel_nombre, p.precio 
        FROM compras c
        JOIN pasteles p ON c.id_pastel = p.id_pastel
        WHERE c.id_compra = $id_compra";
$res = $conn->query($sql);
if (!$res || $res->num_rows === 0) {
    die("Compra no encontrada.");
}
$compra = $res->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Ticket de Compra</title>
<style>
    body { font-family: Arial; background: #fff9fb; padding: 20px; }
    .ticket { max-width: 400px; margin: auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px #ccc; }
    h2 { text-align: center; }
    .item { margin: 10px 0; }
</style>
</head>
<body>
<div class="ticket">
    <h2>🍰 Ticket de Compra</h2>
    <div class="item"><strong>Pastel:</strong> <?php echo htmlspecialchars($compra['pastel_nombre']); ?></div>
    <div class="item"><strong>Precio:</strong> $<?php echo number_format($compra['precio'], 2); ?> USD</div>
    <div class="item"><strong>Banco:</strong> <?php echo $compra['banco']; ?></div>
    <div class="item"><strong>Fecha y hora de pago:</strong> <?php echo $compra['fecha_pago']; ?></div>
    <hr>
    <p style="text-align:center;">Gracias por su compra 💖</p>
        <button onclick="window.location.href='dashboard_user.php'" style="background-color:#ccc; color:#333; margin-top:10px;">
    Volver al Inicio
</button>
</div>
</body>
</html>
