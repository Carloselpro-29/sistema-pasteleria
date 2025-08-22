<?php
$conexion = new mysqli("localhost","root","","prueba2");
if($conexion->connect_error){ die("Error: ".$conexion->connect_error); }

$sabores = isset($_POST['flavor']) ? implode(", ", $_POST['flavor']) : "";
$foto = null;

// Si hay URL
if (!empty($_POST['referenceUrl'])) {
    $foto = $_POST['referenceUrl'];
}
// Si hay archivo subido
elseif (!empty($_FILES['referencePhoto']['name'])) {
    $nombreFoto = time() . "_" . basename($_FILES["referencePhoto"]["name"]);
    if(!is_dir("uploads")){ mkdir("uploads",0777,true); }
    move_uploaded_file($_FILES["referencePhoto"]["tmp_name"], "uploads/".$nombreFoto);
    $foto = "uploads/".$nombreFoto; // guardamos ruta completa
}

$stmt = $conexion->prepare("INSERT INTO pedidos (nombre_cliente, telefono, email, fecha_entrega, sabores, tamano, diseno, foto_referencia) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssssss", $_POST['fullName'], $_POST['phone'], $_POST['email'], $_POST['deliveryDate'], $sabores, $_POST['size'], $_POST['design'], $foto);
$stmt->execute();

$pedido_id = $stmt->insert_id;
$stmt->close();
$conexion->close();

header("Location: pago2.php?id=$pedido_id");
exit();
?>