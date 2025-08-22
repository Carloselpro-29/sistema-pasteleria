
<?php
session_start();
require_once 'config.php';


// Establecer conexión
$conn = conectarDB();
// Inicializar variables
$inventario = [];
$item_editar = null;
$error = '';

// Obtener inventario desde la base de datos
$sql = "SELECT * FROM inventario ORDER BY nombre";
$result = $conn->query($sql);
if($result) {
    while($row = $result->fetch_assoc()) {
        $inventario[] = $row;
    }
    $result->free();
} else {
    $error = "Error al obtener inventario: " . $conn->error;
}

// Procesar formulario para agregar/actualizar item
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST['agregar_item'])) {
        $stmt = $conn->prepare("INSERT INTO inventario 
                              (nombre, categoria, cantidad, unidad, proveedor, stock_minimo, ultima_actualizacion) 
                              VALUES (?, ?, ?, ?, ?, ?, CURDATE())");
        if(!$stmt){
            die("Error en prepare: " . $conn->error);
        }
        $stmt->bind_param("ssdssd", 
            $_POST['nombre'],
            $_POST['categoria'],
            $_POST['cantidad'],
            $_POST['unidad'],
            $_POST['proveedor'],
            $_POST['stock_minimo']
        );
        
        if($stmt->execute()) {
            header("Location: inventario.php?exito=Item agregado correctamente");
            exit();
        } else {
            $error = "Error al agregar item: " . $stmt->error;
        }
        $stmt->close();
    }
    elseif(isset($_POST['actualizar_item'])) {
        $stmt = $conn->prepare("UPDATE inventario SET 
                              nombre = ?, 
                              categoria = ?, 
                              cantidad = ?, 
                              unidad = ?, 
                              proveedor = ?, 
                              stock_minimo = ?, 
                              ultima_actualizacion = CURDATE() 
                              WHERE id_inventario = ?");
        if(!$stmt){
            die("Error en prepare: " . $conn->error);
        }
        $stmt->bind_param("ssdssds",
            $_POST['nombre'],
            $_POST['categoria'],
            $_POST['cantidad'],
            $_POST['unidad'],
            $_POST['proveedor'],
            $_POST['stock_minimo'],
            $_POST['id_inventario']
        );
        
        if($stmt->execute()) {
            header("Location: inventario.php?exito=Item actualizado correctamente");
            exit();
        } else {
            $error = "Error al actualizar item: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Procesar eliminación de item
if(isset($_GET['eliminar'])) {
    $stmt = $conn->prepare("DELETE FROM inventario WHERE id_inventario = ?");
    if(!$stmt){
        die("Error en prepare: " . $conn->error);
    }
    $stmt->bind_param("s", $_GET['eliminar']);
    
    if($stmt->execute()) {
        header("Location: inventario.php?exito=Item eliminado correctamente");
        exit();
    } else {
        $error = "Error al eliminar item: " . $stmt->error;
    }
    $stmt->close();
}

// Obtener item para editar
if(isset($_GET['editar'])) {
    $stmt = $conn->prepare("SELECT * FROM inventario WHERE id_inventario = ?");
    if(!$stmt){
        die("Error en prepare: " . $conn->error);
    }
    $stmt->bind_param("s", $_GET['editar']);
    
    if($stmt->execute()) {
        $result = $stmt->get_result();
        $item_editar = $result->fetch_assoc();
    } else {
        $error = "Error al obtener item: " . $stmt->error;
    }
    $stmt->close();
}

// Cerrar conexión
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario - Pastelería Chispitas</title>
    <style>
        body { font-family: 'Montserrat', sans-serif; background-color: #FAF7F5; color: #5A5350; margin: 0; padding: 20px; }
        .header { background-color: #D4B8C7; padding: 20px; border-radius: 10px; margin-bottom: 20px; text-align: center; }
        .header h1 { font-family: 'Playfair Display', serif; color: #4A3F3A; margin: 0; }
        .crud-container { background: rgba(255,255,255,0.9); padding: 20px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #E8D5C0; }
        th { background-color: #D4B8C7; color: #4A3F3A; }
        tr:nth-child(even) { background-color: rgba(212, 184, 199, 0.1); }
        tr:hover { background-color: rgba(212, 184, 199, 0.2); }
        .error { color: #dc3545; margin-bottom: 15px; }
        .exito { color: #28a745; margin-bottom: 15px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; }
        .form-group input, .form-group select { width: 100%; padding: 8px; border: 1px solid #E8D5C0; border-radius: 4px; }
        .btn { padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-primary { background-color: #D4B8C7; color: white; }
        .btn-danger { background-color: #dc3545; color: white; }
        .btn-success { background-color: #28a745; color: white; }
        .badge { padding: 3px 8px; border-radius: 10px; font-size: 12px; }
        .badge-success { background-color: #28a745; color: white; }
        .badge-warning { background-color: #ffc107; color: black; }
        .badge-danger { background-color: #dc3545; color: white; }
        /* Estilo para el botón de volver */
        .btn-return {
            background-color: #8a7369;
            color: white;
            padding: 12px 20px;
            text-decoration: none;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            transition: all 0.3s;
            font-weight: 500;
            margin-bottom: 20px;
        }
        .btn-return:hover {
            background-color: #6d5b54;
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .btn-return i {
            margin-right: 8px;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="header">
        <h1>Pastelería Chispitas - Gestión de Inventario</h1>
    </div>
    
    <a href="dashboard.php" class="btn-return"><i class="fas fa-arrow-left"></i> Volver al Panel</a>
    
    <?php if(!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php elseif(isset($_GET['exito'])): ?>
        <div class="exito"><?= htmlspecialchars($_GET['exito']) ?></div>
    <?php endif; ?>

    <!-- Formulario para agregar/editar item -->
    <div class="crud-container">
        <h2><?= $item_editar ? 'Editar' : 'Agregar' ?> Item de Inventario</h2>
        <form method="POST">
            <?php if($item_editar): ?>
                <input type="hidden" name="id_inventario" value="<?= htmlspecialchars($item_editar['id_inventario']) ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" required 
                       value="<?= htmlspecialchars($item_editar['nombre'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label for="categoria">Categoría:</label>
                <select id="categoria" name="categoria" required>
                    <option value="materia_prima" <?= ($item_editar['categoria'] ?? '') == 'materia_prima' ? 'selected' : '' ?>>Materia Prima</option>
                    <option value="ingredientes" <?= ($item_editar['categoria'] ?? '') == 'ingredientes' ? 'selected' : '' ?>>Ingredientes</option>
                    <option value="envases" <?= ($item_editar['categoria'] ?? '') == 'envases' ? 'selected' : '' ?>>Envases</option>
                    <option value="decoracion" <?= ($item_editar['categoria'] ?? '') == 'decoracion' ? 'selected' : '' ?>>Decoración</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="cantidad">Cantidad:</label>
                <input type="number" id="cantidad" name="cantidad" step="0.01" min="0" required 
                       value="<?= htmlspecialchars($item_editar['cantidad'] ?? '0') ?>">
            </div>
            
            <div class="form-group">
                <label for="unidad">Unidad de Medida:</label>
                <select id="unidad" name="unidad" required>
                    <option value="kg" <?= ($item_editar['unidad'] ?? '') == 'kg' ? 'selected' : '' ?>>Kilogramos (kg)</option>
                    <option value="g" <?= ($item_editar['unidad'] ?? '') == 'g' ? 'selected' : '' ?>>Gramos (g)</option>
                    <option value="l" <?= ($item_editar['unidad'] ?? '') == 'l' ? 'selected' : '' ?>>Litros (l)</option>
                    <option value="ml" <?= ($item_editar['unidad'] ?? '') == 'ml' ? 'selected' : '' ?>>Mililitros (ml)</option>
                    <option value="unidades" <?= ($item_editar['unidad'] ?? '') == 'unidades' ? 'selected' : '' ?>>Unidades</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="proveedor">Proveedor:</label>
                <input type="text" id="proveedor" name="proveedor" 
                       value="<?= htmlspecialchars($item_editar['proveedor'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label for="stock_minimo">Stock Mínimo:</label>
                <input type="number" id="stock_minimo" name="stock_minimo" step="0.01" min="0" required 
                       value="<?= htmlspecialchars($item_editar['stock_minimo'] ?? '0') ?>">
            </div>
            
            <button type="submit" name="<?= $item_editar ? 'actualizar_item' : 'agregar_item' ?>" class="btn btn-primary">
                <i class="fas fa-save"></i> <?= $item_editar ? 'Actualizar' : 'Guardar' ?>
            </button>
            
            <?php if($item_editar): ?>
                <a href="inventario.php" class="btn">Cancelar</a>
            <?php endif; ?>
        </form>
    </div>
    
    <!-- Listado de inventario -->
    <div class="crud-container">
        <h2>Listado de Inventario</h2>
        
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th>Cantidad</th>
                    <th>Unidad</th>
                    <th>Proveedor</th> <!-- NUEVO -->
                    <th>Stock Mínimo</th>
                    <th>Última Actualización</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($inventario as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['nombre']) ?></td>
                    <td><?= ucfirst(str_replace('_', ' ', htmlspecialchars($item['categoria']))) ?></td>
                    <td><?= number_format($item['cantidad'], 2) ?></td>
                    <td><?= htmlspecialchars($item['unidad']) ?></td>
                    <td><?= htmlspecialchars($item['proveedor']) ?></td> <!-- NUEVO -->
                    <td><?= number_format($item['stock_minimo'], 2) ?></td>
                    <td><?= date('d/m/Y', strtotime($item['ultima_actualizacion'])) ?></td>
                    <td>
                        <a href="inventario.php?editar=<?= $item['id_inventario'] ?>" class="btn btn-primary" style="margin-right: 5px;">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <a href="inventario.php?eliminar=<?= $item['id_inventario'] ?>" class="btn btn-danger" 
                           onclick="return confirm('¿Eliminar este item del inventario?')">
                            <i class="fas fa-trash"></i> Eliminar
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>