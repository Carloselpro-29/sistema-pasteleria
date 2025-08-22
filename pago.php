<?php
$host = "localhost";
$usuario = "root";
$clave = "";
$bd = "prueba2";

$conn = new mysqli($host, $usuario, $clave, $bd);
if ($conn->connect_error) {
    die("Error en la conexión: " . $conn->connect_error);
}

$id_pastel = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Obtener datos del pastel
$sql = "SELECT * FROM pasteles WHERE id_pastel = $id_pastel";
$res = $conn->query($sql);
if (!$res || $res->num_rows === 0) {
    die("Pastel no encontrado.");
}
$pastel = $res->fetch_assoc();

// Si el formulario se envió
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_tarjeta = $_POST['nombre_tarjeta'];
    $banco = $_POST['banco'];
    $numero_tarjeta = $_POST['numero_tarjeta'];
    $fecha_exp = $_POST['fecha_exp'];
    $cvv = $_POST['cvv'];

    $stmt = $conn->prepare("INSERT INTO compras (id_pastel, nombre_tarjeta, banco, numero_tarjeta, fecha_expiracion, cvv, fecha_pago) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("isssss", $id_pastel, $nombre_tarjeta, $banco, $numero_tarjeta, $fecha_exp, $cvv);
    $stmt->execute();

    $id_compra = $stmt->insert_id;
    header("Location: ticket.php?id=$id_compra");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Pago de Pastel</title>
<style>
    body { font-family: Arial; background: #fff9fb; padding: 20px; }
    .form { max-width: 400px; margin: auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px #ccc; }
    input, select { width: 100%; padding: 10px; margin: 8px 0; border: 1px solid #ccc; border-radius: 5px; }
    button { background: #e83f6f; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer; }
    button:hover { background: #6d213c; }
</style>
</head>
<body>
<div class="form">
    <h2>Pago - <?php echo htmlspecialchars($pastel['nombre']); ?></h2>
    <form method="POST">
        <label>Nombre en la tarjeta</label>
        <input type="text" name="nombre_tarjeta" required>

        <label>Banco</label>
        <select name="banco" required>
            <option value="">Seleccione...</option>
            <option value="Agricola">Agrícola</option>
            <option value="Cuscatlan">Cuscatlán</option>
            <option value="Davivienda">Davivienda</option>
            <option value="Hipotecario">Hipotecario</option>
        </select>

        <label>Número de tarjeta</label>
        <input type="text" name="numero_tarjeta" maxlength="16" required>

        <label>Fecha de expiración (MM/AA)</label>
        <input type="text" name="fecha_exp" maxlength="5" placeholder="MM/AA" required>

        <label>CVV</label>
        <input type="text" name="cvv" maxlength="3" required>

        <button type="submit">Pagar</button>
        <button type="button" onclick="window.location.href='pasteles_disponibles.php';" 
        style="background-color:#ccc; color:#333; margin-top:10px;">
    Regresar a Pasteles Disponibles
    </form>
</div>
</body>
</html>
