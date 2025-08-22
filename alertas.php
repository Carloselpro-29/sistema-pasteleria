<?php
session_start();
require_once 'config.php';

// Establecer conexión
$conn = conectarDB();

// Inicializar variables
$alertas = [];
$error = '';
$exito = '';
$mostrar_mensaje = false;
$editar_id = null;
$alerta_editar = null;

// Obtener alertas desde la base de datos
$result = $conn->query("SELECT id_alerta, nombre, descripcion, fecha, hora FROM alertas ORDER BY fecha DESC, hora DESC");
if($result) {
    while($row = $result->fetch_assoc()) {
        $alertas[] = $row;
    }
    $result->free();
} else {
    $error = "Error al obtener alertas: " . $conn->error;
    $mostrar_mensaje = true;
}

// Procesar modo edición
if(isset($_GET['editar'])) {
    $editar_id = $_GET['editar'];
    $stmt = $conn->prepare("SELECT id_alerta, nombre, descripcion, fecha, hora FROM alertas WHERE id_alerta = ?");
    $stmt->bind_param("i", $editar_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $alerta_editar = $result->fetch_assoc();
    $stmt->close();
}

// Procesar formulario de nueva/editar alerta
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST['crear_alerta'])) {
        $nombre = trim($_POST['nombre']);
        $descripcion = trim($_POST['descripcion']);
        $fecha = $_POST['fecha'];
        $hora = $_POST['hora'];
        $ampm = $_POST['ampm'];
        
        // Validar y convertir hora
        $hora_valida = false;
        $hora_24 = '';
        
        if(preg_match('/^([0-9]{1,2}):([0-9]{2})$/', $hora, $matches)) {
            $hh = intval($matches[1]);
            $mm = intval($matches[2]);
            
            if($hh >= 1 && $hh <= 12 && $mm >= 0 && $mm <= 59) {
                // Convertir a formato 24 horas
                if($ampm == 'PM' && $hh < 12) {
                    $hh += 12;
                } elseif($ampm == 'AM' && $hh == 12) {
                    $hh = 0;
                }
                $hora_24 = sprintf("%02d:%02d", $hh, $mm);
                $hora_valida = true;
            }
        }
        
        if(empty($nombre) || empty($descripcion) || empty($fecha) || empty($hora) || !$hora_valida) {
            $error = "Todos los campos son obligatorios y la hora debe ser válida (formato HH:MM)";
            $mostrar_mensaje = true;
        } else {
            if(isset($_POST['editar_id'])) {
                // Modo edición
                $editar_id = $_POST['editar_id'];
                $stmt = $conn->prepare("UPDATE alertas SET nombre = ?, descripcion = ?, fecha = ?, hora = ? WHERE id_alerta = ?");
                $stmt->bind_param("ssssi", $nombre, $descripcion, $fecha, $hora_24, $editar_id);
                
                if($stmt->execute()) {
                    $exito = "¡Alerta actualizada correctamente!";
                    $mostrar_mensaje = true;
                    // Actualizar la lista
                    foreach($alertas as &$alerta) {
                        if($alerta['id_alerta'] == $editar_id) {
                            $alerta['nombre'] = $nombre;
                            $alerta['descripcion'] = $descripcion;
                            $alerta['fecha'] = $fecha;
                            $alerta['hora'] = $hora_24;
                            break;
                        }
                    }
                    $editar_id = null;
                    $alerta_editar = null;
                } else {
                    $error = "Error al actualizar alerta: " . $stmt->error;
                    $mostrar_mensaje = true;
                }
            } else {
                // Modo creación
                $stmt = $conn->prepare("INSERT INTO alertas (nombre, descripcion, fecha, hora) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $nombre, $descripcion, $fecha, $hora_24);
                
                if($stmt->execute()) {
                    $exito = "¡Alerta creada correctamente!";
                    $mostrar_mensaje = true;
                    // Actualizar la lista
                    $newId = $stmt->insert_id;
                    $alertas = array_merge([[
                        'id_alerta' => $newId,
                        'nombre' => $nombre,
                        'descripcion' => $descripcion,
                        'fecha' => $fecha,
                        'hora' => $hora_24
                    ]], $alertas);
                } else {
                    $error = "Error al crear alerta: " . $stmt->error;
                    $mostrar_mensaje = true;
                }
            }
            $stmt->close();
        }
    }
    
    // Procesar envío de alerta al local
    if(isset($_POST['enviar_alerta'])) {
        $alerta_id = $_POST['alerta_id'];
        $exito = "¡Alerta enviada al local correctamente!";
        $mostrar_mensaje = true;
    }
}

// Procesar eliminación de alerta
if(isset($_GET['eliminar'])) {
    $stmt = $conn->prepare("DELETE FROM alertas WHERE id_alerta = ?");
    $stmt->bind_param("i", $_GET['eliminar']);
    
    if($stmt->execute()) {
        $exito = "¡Alerta eliminada correctamente!";
        $mostrar_mensaje = true;
        // Actualizar la lista
        $alertas = array_filter($alertas, function($alerta) {
            return $alerta['id_alerta'] != $_GET['eliminar'];
        });
    } else {
        $error = "Error al eliminar alerta: " . $stmt->error;
        $mostrar_mensaje = true;
    }
    $stmt->close();
}

// Cerrar conexión al finalizar
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alertas - Pastelería Chispitas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600&family=Playfair+Display:wght@600&display=swap" rel="stylesheet">
    <style>
        :root {
            --color-primary: #A78A7F;
            --color-primary-dark: #8a7369;
            --color-secondary: #D4B8C7;
            --color-light: #FAF7F5;
            --color-dark: #4A3F3A;
            --color-medium: #5A5350;
            --color-success: #28a745;
            --color-success-light: #d4edda;
            --color-success-dark: #218838;
            --color-danger: #dc3545;
            --color-danger-light: #f8d7da;
            --color-danger-dark: #c82333;
            --color-warning: #ffc107;
            --color-warning-dark: #e0a800;
            --color-border: #E8D5C0;
            --shadow-sm: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: var(--color-light);
            color: var(--color-medium);
            line-height: 1.6;
            padding: 20px;
        }
        
        .header {
            background-color: var(--color-secondary);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            box-shadow: var(--shadow-sm);
        }
        
        .header h1 {
            font-family: 'Playfair Display', serif;
            color: var(--color-dark);
            margin: 0;
            font-size: 1.8em;
            font-weight: 600;
        }
        
        .crud-container {
            background: rgba(255,255,255,0.9);
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: var(--shadow-sm);
        }
        
        .crud-container h2 {
            color: var(--color-dark);
            margin-bottom: 20px;
            font-size: 1.4em;
            border-bottom: 2px solid var(--color-secondary);
            padding-bottom: 10px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 0.95em;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--color-border);
        }
        
        th {
            background-color: var(--color-secondary);
            color: var(--color-dark);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85em;
            letter-spacing: 0.5px;
        }
        
        tr:nth-child(even) {
            background-color: rgba(212, 184, 199, 0.1);
        }
        
        tr:hover {
            background-color: rgba(212, 184, 199, 0.2);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--color-dark);
            font-weight: 500;
            font-size: 0.95em;
        }
        
        input, textarea, select {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--color-border);
            border-radius: 6px;
            background-color: var(--color-light);
            font-family: 'Montserrat', sans-serif;
            font-size: 0.95em;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        
        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(167, 138, 127, 0.2);
        }
        
        textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        .btn {
            padding: 12px 18px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
            font-size: 0.95em;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn i {
            font-size: 1em;
        }
        
        .btn-primary {
            background-color: var(--color-primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--color-primary-dark);
            transform: translateY(-2px);
        }
        
        .btn-warning {
            background-color: var(--color-warning);
            color: var(--color-dark);
        }
        
        .btn-warning:hover {
            background-color: var(--color-warning-dark);
            transform: translateY(-2px);
        }
        
        .btn-danger {
            background-color: var(--color-danger);
            color: white;
        }
        
        .btn-danger:hover {
            background-color: var(--color-danger-dark);
            transform: translateY(-2px);
        }
        
        .btn-success {
            background-color: var(--color-success);
            color: white;
        }
        
        .btn-success:hover {
            background-color: var(--color-success-dark);
            transform: translateY(-2px);
        }
        
        .urgente {
            background-color: rgba(255, 0, 0, 0.1);
            font-weight: bold;
        }
        
        .proxima {
            background-color: rgba(255, 255, 0, 0.1);
        }
        
        .alert-success {
            background-color: var(--color-success-light);
            color: #155724;
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
            display: <?= $mostrar_mensaje && !empty($exito) ? 'flex' : 'none' ?>;
            align-items: center;
            gap: 12px;
            animation: fadeIn 0.5s;
        }
        
        .alert-error {
            background-color: var(--color-danger-light);
            color: #721c24;
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
            display: <?= $mostrar_mensaje && !empty($error) ? 'flex' : 'none' ?>;
            align-items: center;
            gap: 12px;
            animation: fadeIn 0.5s;
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .action-buttons form {
            margin: 0;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .hora-container {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .hora-container input {
            width: 100px;
            flex-shrink: 0;
        }
        
        .hora-container select {
            width: 80px;
            padding: 12px;
            border: 1px solid var(--color-border);
            border-radius: 6px;
            background-color: var(--color-light);
            font-family: 'Montserrat', sans-serif;
        }
        
        .hora-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .hora-input small {
            font-size: 0.8em;
            color: #666;
            display: block;
            margin-top: 5px;
        }
        
        /* NUEVO ESTILO PARA EL BOTÓN DE VOLVER (igual que en cliente.php) */
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
        
        /* Responsive */
        @media (max-width: 768px) {
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
            
            .hora-container {
                width: 100%;
            }
            
            .hora-container input {
                flex-grow: 1;
            }
            
            .btn-return {
                width: 100%;
            }
        }
        
        /* Efectos adicionales */
        .btn {
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .btn:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .crud-container {
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .crud-container:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Pastelería Chispitas - Sistema de Alertas</h1>
    </div>
    
    <!-- Botón que redirige a dashboard.php - MODIFICADO PARA SER IGUAL QUE EN cliente.php -->
    <a href="dashboard.php" class="btn-return">
        <i class="fas fa-arrow-left"></i> Volver al Panel
    </a>
    
    <?php if($mostrar_mensaje && !empty($exito)): ?>
        <div class="alert-success">
            <i class="fas fa-check-circle"></i>
            <p><?= htmlspecialchars($exito) ?></p>
        </div>
    <?php endif; ?>
    
    <?php if($mostrar_mensaje && !empty($error)): ?>
        <div class="alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <p><?= htmlspecialchars($error) ?></p>
        </div>
    <?php endif; ?>

    <div class="crud-container">
        <h2><?= $editar_id ? 'Editar Alerta' : 'Crear Nueva Alerta' ?></h2>
        <form method="POST" id="alerta-form">
            <?php if($editar_id): ?>
                <input type="hidden" name="editar_id" value="<?= $editar_id ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label for="nombre">Nombre del Cliente:</label>
                <input type="text" id="nombre" name="nombre" required 
                       value="<?= $alerta_editar ? htmlspecialchars($alerta_editar['nombre']) : (isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : '') ?>" 
                       placeholder="Nombre del cliente">
            </div>
            
            <div class="form-group">
                <label for="descripcion">Descripción:</label>
                <textarea id="descripcion" name="descripcion" required
                          placeholder="Detalles importantes sobre el pastel..."><?= $alerta_editar ? htmlspecialchars($alerta_editar['descripcion']) : (isset($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : '') ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="fecha">Fecha:</label>
                <input type="date" id="fecha" name="fecha" required 
                       value="<?= $alerta_editar ? htmlspecialchars($alerta_editar['fecha']) : (isset($_POST['fecha']) ? htmlspecialchars($_POST['fecha']) : '') ?>">
            </div>
            
            <div class="form-group">
                <label>Hora:</label>
                <div class="hora-group">
                    <div class="hora-container">
                        <input type="text" id="hora" name="hora" required 
                               pattern="[0-9]{1,2}:[0-9]{2}" 
                               placeholder="HH:MM"
                               value="<?= $alerta_editar ? date('h:i', strtotime($alerta_editar['hora'])) : (isset($_POST['hora']) ? htmlspecialchars($_POST['hora']) : '') ?>">
                        <select name="ampm" required>
                            <option value="AM" <?= ($alerta_editar && date('A', strtotime($alerta_editar['hora'])) == 'AM') || (isset($_POST['ampm']) && $_POST['ampm'] == 'AM') ? 'selected' : '' ?>>AM</option>
                            <option value="PM" <?= ($alerta_editar && date('A', strtotime($alerta_editar['hora'])) == 'PM') || (isset($_POST['ampm']) && $_POST['ampm'] == 'PM') ? 'selected' : '' ?>>PM</option>
                        </select>
                    </div>
                    <small>Ejemplo: 09:30 AM o 02:45 PM</small>
                </div>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" name="crear_alerta" class="btn btn-primary">
                    <i class="fas fa-save"></i> <?= $editar_id ? 'Actualizar Alerta' : 'Guardar Alerta' ?>
                </button>
                
                <?php if($editar_id): ?>
                    <a href="alertas.php" class="btn btn-warning">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <!-- Listado de alertas -->
    <div class="crud-container">
        <h2>Alertas Registradas</h2>
        
        <?php if(empty($alertas)): ?>
            <p>No hay alertas programadas.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($alertas as $alerta): 
                        $hoy = date('Y-m-d');
                        $fechaAlerta = $alerta['fecha'];
                        $clase = '';
                        
                        if ($fechaAlerta == $hoy) {
                            $clase = 'urgente';
                        } elseif (strtotime($fechaAlerta) - strtotime($hoy) <= 2 * 24 * 60 * 60) {
                            $clase = 'proxima';
                        }
                        
                        $horaMostrar = date('h:i A', strtotime($alerta['hora']));
                    ?>
                    <tr class="<?= $clase ?>">
                        <td><?= htmlspecialchars($alerta['nombre']) ?></td>
                        <td><?= nl2br(htmlspecialchars($alerta['descripcion'])) ?></td>
                        <td><?= date('d/m/Y', strtotime($alerta['fecha'])) ?></td>
                        <td><?= $horaMostrar ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="alertas.php?editar=<?= $alerta['id_alerta'] ?>" class="btn btn-warning">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="alerta_id" value="<?= $alerta['id_alerta'] ?>">
                                    <button type="submit" name="enviar_alerta" class="btn btn-success">
                                        <i class="fas fa-bell"></i> Alerta
                                    </button>
                                </form>
                                
                                <a href="alertas.php?eliminar=<?= $alerta['id_alerta'] ?>" class="btn btn-danger" 
                                   onclick="return confirm('¿Estás seguro de eliminar esta alerta?')">
                                    <i class="fas fa-trash"></i> Eliminar
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('fecha').min = today;
            
            // Configurar hora actual por defecto solo si no estamos editando
            <?php if(!$editar_id && !isset($_POST['hora'])): ?>
                const now = new Date();
                let hours = now.getHours() % 12;
                hours = hours ? hours : 12; // Convertir 0 a 12
                const minutes = now.getMinutes().toString().padStart(2, '0');
                
                document.getElementById('hora').value = `${hours}:${minutes}`;
                document.querySelector('select[name="ampm"]').value = now.getHours() >= 12 ? 'PM' : 'AM';
            <?php endif; ?>
            
            // Validar formato de hora
            document.getElementById('alerta-form').addEventListener('submit', function(e) {
                const horaInput = document.getElementById('hora');
                const ampmSelect = document.querySelector('select[name="ampm"]');
                
                if(!/^([0-9]{1,2}):([0-9]{2})$/.test(horaInput.value)) {
                    alert('Por favor ingresa la hora en formato HH:MM (ej. 09:30)');
                    e.preventDefault();
                    return false;
                }
                
                const [hh, mm] = horaInput.value.split(':');
                const horas = parseInt(hh);
                const minutos = parseInt(mm);
                
                if(horas < 1 || horas > 12 || minutos < 0 || minutos > 59) {
                    alert('Hora inválida. Las horas deben estar entre 1-12 y los minutos entre 00-59');
                    e.preventDefault();
                    return false;
                }
                
                return true;
            });
            
            // Mejorar usabilidad del campo de hora
            document.getElementById('hora').addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                
                if(value.length > 2) {
                    value = value.substring(0, 2) + ':' + value.substring(2, 4);
                }
                
                e.target.value = value;
            });
            
            // Auto-focus en el primer campo al cargar
            document.getElementById('nombre').focus();
        });
    </script>
</body>
</html>