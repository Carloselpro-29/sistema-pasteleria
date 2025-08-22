<?php
session_start();
require_once 'config.php';

// Conexión a la base de datos
$conn = conectarDB();
$error = '';
$exito = '';

// Inicializar variables para modo edición
$usuario_editar = null;
$modo_edicion = false;

// Procesar operaciones CRUD
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST['accion'])) {
        if($_POST['accion'] === 'crear') {
            // Crear nuevo usuario
            $name = trim($_POST['name']);
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);
            
            // Validaciones
            if(empty($name) || empty($email) || empty($password)) {
                $error = "Todos los campos son obligatorios";
            } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Correo electrónico no válido";
            } elseif(strlen($password) < 6) {
                $error = "La contraseña debe tener al menos 6 caracteres";
            } else {
                // Verificar si el email ya existe
                $stmt = $conn->prepare("SELECT id_users FROM users WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $stmt->store_result();
                
                if($stmt->num_rows > 0) {
                    $error = "El correo ya está registrado";
                } else {
                    // Hash de la contraseña
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Insertar nuevo usuario
                    $insert = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
                    $insert->bind_param("sss", $name, $email, $hash);
                    
                    if($insert->execute()) {
                        $exito = "Usuario creado exitosamente";
                        $_POST = array(); // Limpiar campos
                    } else {
                        $error = "Error al crear usuario: " . $insert->error;
                    }
                    $insert->close();
                }
                $stmt->close();
            }
        } elseif($_POST['accion'] === 'actualizar') {
            // Actualizar usuario existente
            $id_users = $_POST['id_users'];
            $name = $_POST['name'];
            $email = $_POST['email'];
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Correo electrónico no válido";
            } else {
                $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id_users = ?");
                $stmt->bind_param("ssi", $name, $email, $id_users);
                
                if($stmt->execute()) {
                    $exito = "Usuario actualizado correctamente";
                    // Limpiar el modo edición
                    $modo_edicion = false;
                    $usuario_editar = null;
                    // Redirigir para limpiar parámetros GET
                    header("Location: cliente.php?exito=".urlencode("Usuario actualizado correctamente"));
                    exit();
                } else {
                    $error = "Error al actualizar usuario: " . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
}

// Procesar eliminación
if(isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    
    if(isset($_SESSION['user_id']) && $id == $_SESSION['user_id']) {
        $error = "No puedes eliminar tu propio usuario";
    } else {
        $stmt = $conn->prepare("DELETE FROM users WHERE id_users = ?");
        $stmt->bind_param("i", $id);
        
        if($stmt->execute()) {
            header("Location: cliente.php?exito=".urlencode("Usuario eliminado correctamente"));
            exit();
        } else {
            $error = "Error al eliminar usuario: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Mostrar mensajes de éxito desde URL
if(isset($_GET['exito'])) {
    $exito = urldecode($_GET['exito']);
}

// Obtener todos los usuarios
$usuarios = [];
$result = $conn->query("SELECT id_users, email, name, created_at FROM users ORDER BY created_at DESC");
if($result) {
    while($row = $result->fetch_assoc()) {
        $usuarios[] = $row;
    }
    $result->free();
} else {
    $error = "Error al obtener usuarios: " . $conn->error;
}

// Obtener usuario para editar SOLO si viene el parámetro en la URL
if(isset($_GET['editar'])) {
    $id = $_GET['editar'];
    $stmt = $conn->prepare("SELECT id_users, email, name FROM users WHERE id_users = ?");
    $stmt->bind_param("i", $id);
    
    if($stmt->execute()) {
        $result = $stmt->get_result();
        $usuario_editar = $result->fetch_assoc();
        $modo_edicion = true;
    } else {
        $error = "Error al obtener usuario: " . $stmt->error;
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes - Pastelería Chispitas</title>
    <style>
        body { 
            font-family: 'Montserrat', sans-serif; 
            background-color: #FAF7F5; 
            color: #5A5350; 
            margin: 0; 
            padding: 20px; 
        }
        
        .header { 
            background-color: #D4B8C7; 
            padding: 20px; 
            border-radius: 10px; 
            margin-bottom: 20px; 
            text-align: center; 
        }
        
        .header h1 { 
            font-family: 'Playfair Display', serif; 
            color: #4A3F3A; 
            margin: 0; 
        }
        
        .crud-container { 
            background: rgba(255,255,255,0.9); 
            padding: 20px; 
            border-radius: 10px; 
            margin-bottom: 20px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
        }
        
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 20px 0; 
        }
        
        th, td { 
            padding: 12px 15px; 
            text-align: left; 
            border-bottom: 1px solid #E8D5C0; 
        }
        
        th { 
            background-color: #D4B8C7; 
            color: #4A3F3A; 
        }
        
        tr:nth-child(even) { 
            background-color: rgba(212, 184, 199, 0.1); 
        }
        
        tr:hover { 
            background-color: rgba(212, 184, 199, 0.2); 
        }
        
        .error { 
            color: #dc3545; 
            margin-bottom: 15px; 
            padding: 10px;
            background-color: #f8d7da;
            border-radius: 4px;
        }
        
        .exito { 
            color: #28a745; 
            margin-bottom: 15px; 
            padding: 10px;
            background-color: #d4edda;
            border-radius: 4px;
        }
        
        .form-group { 
            margin-bottom: 15px; 
        }
        
        .form-group label { 
            display: block; 
            margin-bottom: 5px; 
            font-weight: 600;
        }
        
        .form-group input, 
        .form-group select { 
            width: 100%; 
            padding: 10px; 
            border: 1px solid #E8D5C0; 
            border-radius: 4px; 
            font-family: 'Montserrat', sans-serif;
        }
        
        .form-group input:focus, 
        .form-group select:focus { 
            outline: none; 
            border-color: #A78A7F;
            box-shadow: 0 0 0 3px rgba(167, 138, 127, 0.2);
        }
        
        .btn { 
            padding: 10px 15px; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s;
        }
        
        .btn-primary { 
            background-color: #D4B8C7; 
            color: white; 
        }
        
        .btn-primary:hover { 
            background-color: #c0a4b5;
            transform: translateY(-2px);
        }
        
        .btn-danger { 
            background-color: #dc3545; 
            color: white; 
        }
        
        .btn-danger:hover { 
            background-color: #c82333;
            transform: translateY(-2px);
        }
        
        .btn-success { 
            background-color: #28a745; 
            color: white; 
        }
        
        .btn-success:hover { 
            background-color: #218838;
            transform: translateY(-2px);
        }
        
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
        
        @media (max-width: 768px) {
            table {
                display: block;
                overflow-x: auto;
            }
            
            .form-actions {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="header">
        <h1>Pastelería Chispitas - Gestión de Clientes</h1>
    </div>
    
    <a href="dashboard.php" class="btn-return"><i class="fas fa-arrow-left"></i> Volver al Panel</a>
    
    <?php if(!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php elseif(isset($_GET['exito'])): ?>
        <div class="exito"><?= htmlspecialchars($_GET['exito']) ?></div>
    <?php endif; ?>

    <!-- Formulario para agregar/editar usuario -->
    <div class="crud-container">
        <h2><?= $modo_edicion ? 'Editar Usuario' : 'Agregar Nuevo Usuario' ?></h2>
        <form method="POST">
            <input type="hidden" name="accion" value="<?= $modo_edicion ? 'actualizar' : 'crear' ?>">
            <?php if($modo_edicion && $usuario_editar): ?>
                <input type="hidden" name="id_users" value="<?= $usuario_editar['id_users'] ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="name"><i class="fas fa-user"></i> Nombre:</label>
                <input type="text" id="name" name="name" required 
                       value="<?= htmlspecialchars($_POST['name'] ?? ($usuario_editar['name'] ?? '')) ?>">
            </div>

            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email:</label>
                <input type="email" id="email" name="email" required 
                       value="<?= htmlspecialchars($_POST['email'] ?? ($usuario_editar['email'] ?? '')) ?>">
            </div>

            <?php if(!$modo_edicion): ?>
            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Contraseña:</label>
                <input type="password" id="password" name="password" required>
                <small style="color: #6c757d; font-size: 13px;">La contraseña debe tener al menos 6 caracteres</small>
            </div>
            <?php endif; ?>

            <div class="form-actions">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-<?= $modo_edicion ? 'sync' : 'save' ?>"></i> 
                    <?= $modo_edicion ? 'Actualizar' : 'Guardar' ?>
                </button>

                <?php if($modo_edicion): ?>
                    <a href="cliente.php" class="btn btn-danger">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <!-- Listado de usuarios -->
    <div class="crud-container">
        <h2>Listado de Clientes Registrados</h2>
        
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Fecha Registro</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($usuarios as $usuario): ?>
                <tr>
                    <td><?= htmlspecialchars($usuario['name'] ?? 'Sin nombre') ?></td>
                    <td><?= htmlspecialchars($usuario['email']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($usuario['created_at'])) ?></td>
                    <td>
                        <a href="cliente.php?editar=<?= $usuario['id_users'] ?>" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <a href="cliente.php?eliminar=<?= $usuario['id_users'] ?>" class="btn btn-danger" 
                           onclick="return confirm('¿Estás seguro de eliminar este usuario?')">
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