<?php
session_start();

// Configuración de la base de datos
$db_host = '127.0.0.1';
$db_name = 'prueba2';
$db_user = 'root';
$db_pass = '';

// Directorio donde se guardarán los respaldos (ruta absoluta)
$backup_dir = $_SERVER['DOCUMENT_ROOT'] . '/pastel/backups/';
$backup_log = $backup_dir . 'backups_prueba2.log';

// Crear directorio si no existe
if (!file_exists($backup_dir)) {
    if (!mkdir($backup_dir, 0777, true)) {
        die("Error: No se pudo crear el directorio de backups.");
    }
}

// Inicializar archivo de log si no existe
if (!file_exists($backup_log)) {
    file_put_contents($backup_log, json_encode([]));
}

// Función para formatear bytes a formato legible
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    
    return round($bytes, $precision) . ' ' . $units[$pow];
}

// Función para leer el log de respaldos
function leerLogRespaldos($backup_log) {
    if (file_exists($backup_log)) {
        $contenido = file_get_contents($backup_log);
        return json_decode($contenido, true) ?: [];
    }
    return [];
}

// Función para guardar en el log de respaldos
function guardarLogRespaldos($backup_log, $datos) {
    $respaldos = leerLogRespaldos($backup_log);
    $respaldos[] = $datos;
    file_put_contents($backup_log, json_encode($respaldos, JSON_PRETTY_PRINT));
}

// Función para realizar respaldo real de la BD
function realizarRespaldo($host, $user, $pass, $dbname, $backup_dir, $backup_log, $tipo = 'manual') {
    $fecha = date('Ymd_His');
    $backup_file = $backup_dir . 'respaldo_' . $dbname . '_' . $fecha . '.sql';
    
    // Comando para mysqldump (con ruta completa para XAMPP)
    $command = "C:\\xampp\\mysql\\bin\\mysqldump.exe --host={$host} --user={$user} --password={$pass} {$dbname} > \"{$backup_file}\" 2>&1";
    
    // Ejecutar comando
    system($command, $output);
    
    if ($output === 0 && file_exists($backup_file)) {
        $tamaño = filesize($backup_file);
        $tamaño_formateado = formatBytes($tamaño);
        
        $info_respaldo = [
            'id' => time() . rand(100, 999),
            'nombre' => basename($backup_file),
            'fecha' => date('Y-m-d H:i:s'),
            'tamaño' => $tamaño,
            'tamaño_formateado' => $tamaño_formateado,
            'tipo' => 'Completo',
            'origen' => $tipo,
            'ruta' => $backup_file,
            'estado' => 'completado'
        ];
        
        // Guardar en el log
        guardarLogRespaldos($backup_log, $info_respaldo);
        
        return $info_respaldo;
    } else {
        // Guardar error en el log
        $error_info = [
            'id' => time() . rand(100, 999),
            'nombre' => 'error_' . $fecha,
            'fecha' => date('Y-m-d H:i:s'),
            'tamaño' => 0,
            'tamaño_formateado' => '0 B',
            'tipo' => 'Error',
            'origen' => $tipo,
            'ruta' => '',
            'estado' => 'error',
            'mensaje_error' => 'Error al ejecutar mysqldump. Código: ' . $output
        ];
        
        guardarLogRespaldos($backup_log, $error_info);
        return false;
    }
}

// Función para programar respaldo automático
function programarRespaldoAutomatico($intervalo, $backup_dir, $backup_log) {
    $programacion_file = $backup_dir . 'programacion.json';
    $programacion = [];
    
    if (file_exists($programacion_file)) {
        $programacion = json_decode(file_get_contents($programacion_file), true) ?: [];
    }
    
    $programacion = [
        'automatico' => true,
        'intervalo' => $intervalo,
        'ultima_ejecucion' => date('Y-m-d H:i:s'),
        'proxima_ejecucion' => date('Y-m-d H:i:s', strtotime("+$intervalo hours"))
    ];
    
    file_put_contents($programacion_file, json_encode($programacion, JSON_PRETTY_PRINT));
    return $programacion;
}

// Función para desactivar respaldo automático
function desactivarRespaldoAutomatico($backup_dir) {
    $programacion_file = $backup_dir . 'programacion.json';
    $programacion = ['automatico' => false];
    file_put_contents($programacion_file, json_encode($programacion, JSON_PRETTY_PRINT));
    return $programacion;
}

// Función para verificar y ejecutar respaldo automático
function verificarRespaldoAutomatico($host, $user, $pass, $dbname, $backup_dir, $backup_log) {
    $programacion_file = $backup_dir . 'programacion.json';
    
    if (file_exists($programacion_file)) {
        $programacion = json_decode(file_get_contents($programacion_file), true) ?: [];
        
        if (isset($programacion['automatico']) && $programacion['automatico'] === true) {
            $proxima_ejecucion = strtotime($programacion['proxima_ejecucion']);
            
            if (time() >= $proxima_ejecucion) {
                // Es hora de ejecutar el respaldo automático
                realizarRespaldo($host, $user, $pass, $dbname, $backup_dir, $backup_log, 'automatico');
                
                // Actualizar programación
                $programacion['ultima_ejecucion'] = date('Y-m-d H:i:s');
                $programacion['proxima_ejecucion'] = date('Y-m-d H:i:s', strtotime("+{$programacion['intervalo']} hours"));
                file_put_contents($programacion_file, json_encode($programacion, JSON_PRETTY_PRINT));
            }
        }
    }
}

// Función para obtener respaldos desde el log
function obtenerRespaldos($backup_log) {
    $respaldos = leerLogRespaldos($backup_log);
    
    // Ordenar por fecha (más reciente primero)
    usort($respaldos, function($a, $b) {
        return strtotime($b['fecha']) - strtotime($a['fecha']);
    });
    
    return $respaldos;
}

// Verificar y ejecutar respaldo automático si es necesario
verificarRespaldoAutomatico($db_host, $db_user, $db_pass, $db_name, $backup_dir, $backup_log);

// Crear nuevo respaldo manual
if(isset($_GET['crear_respaldo'])) {
    $nuevo_respaldo = realizarRespaldo($db_host, $db_user, $db_pass, $db_name, $backup_dir, $backup_log, 'manual');
    
    if($nuevo_respaldo) {
        $_SESSION['mensaje_exito'] = "Respaldo creado exitosamente: " . $nuevo_respaldo['nombre'];
    } else {
        $_SESSION['mensaje_error'] = "Error al crear el respaldo. Verifique la configuración de la base de datos.";
    }
    
    header("Location: respaldos.php");
    exit();
}

// Programar respaldo automático
if(isset($_POST['programar_automatico'])) {
    $intervalo = intval($_POST['intervalo']);
    
    if($intervalo > 0) {
        programarRespaldoAutomatico($intervalo, $backup_dir, $backup_log);
        $_SESSION['mensaje_exito'] = "Respaldo automático programado cada {$intervalo} horas.";
    } else {
        $_SESSION['mensaje_error'] = "El intervalo debe ser mayor a 0.";
    }
    
    header("Location: respaldos.php");
    exit();
}

// Desactivar respaldo automático
if(isset($_GET['desactivar_automatico'])) {
    desactivarRespaldoAutomatico($backup_dir);
    $_SESSION['mensaje_exito'] = "Respaldo automático desactivado.";
    header("Location: respaldos.php");
    exit();
}

// Eliminar respaldo
if(isset($_GET['eliminar'])) {
    $id_eliminar = $_GET['eliminar'];
    $respaldos = obtenerRespaldos($backup_log);
    $nuevos_respaldos = [];
    $eliminado = false;
    
    foreach ($respaldos as $respaldo) {
        if ($respaldo['id'] == $id_eliminar) {
            if (!empty($respaldo['ruta']) && file_exists($respaldo['ruta'])) {
                if (unlink($respaldo['ruta'])) {
                    $eliminado = true;
                    $_SESSION['mensaje_exito'] = "Respaldo eliminado: " . $respaldo['nombre'];
                    continue; // No agregar a la nueva lista
                } else {
                    $_SESSION['mensaje_error'] = "Error al eliminar el archivo de respaldo.";
                }
            } else {
                // Es solo un registro de error, no tiene archivo
                $eliminado = true;
                $_SESSION['mensaje_exito'] = "Registro de error eliminado.";
                continue;
            }
        }
        $nuevos_respaldos[] = $respaldo;
    }
    
    if ($eliminado) {
        file_put_contents($backup_log, json_encode($nuevos_respaldos, JSON_PRETTY_PRINT));
    } else {
        $_SESSION['mensaje_error'] = "No se encontró el respaldo especificado.";
    }
    
    header("Location: respaldos.php");
    exit();
}

// Descargar respaldo
if(isset($_GET['descargar'])) {
    $id_descargar = $_GET['descargar'];
    $respaldos = obtenerRespaldos($backup_log);
    
    foreach ($respaldos as $respaldo) {
        if ($respaldo['id'] == $id_descargar && !empty($respaldo['ruta']) && file_exists($respaldo['ruta'])) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($respaldo['ruta']) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($respaldo['ruta']));
            readfile($respaldo['ruta']);
            exit;
        }
    }
    
    $_SESSION['mensaje_error'] = "Archivo de respaldo no encontrado.";
    header("Location: respaldos.php");
    exit();
}

// Restaurar respaldo
if(isset($_POST['restaurar'])) {
    if(isset($_FILES['archivo_respaldo']) && $_FILES['archivo_respaldo']['error'] === UPLOAD_ERR_OK) {
        $archivo_tmp = $_FILES['archivo_respaldo']['tmp_name'];
        // Usar la ruta completa de mysql.exe para XAMPP
        $comando = "C:\\xampp\\mysql\\bin\\mysql.exe --host={$db_host} --user={$db_user} --password={$db_pass} {$db_name} < \"{$archivo_tmp}\" 2>&1";
        
        system($comando, $salida);
        
        if($salida === 0) {
            $_SESSION['mensaje_exito'] = "Respaldo restaurado exitosamente.";
            
            // Registrar la restauración en el log
            $restauracion_info = [
                'id' => time() . rand(100, 999),
                'nombre' => $_FILES['archivo_respaldo']['name'],
                'fecha' => date('Y-m-d H:i:s'),
                'tipo' => 'Restauración',
                'origen' => 'manual',
                'estado' => 'completado',
                'accion' => 'restauracion'
            ];
            
            guardarLogRespaldos($backup_log, $restauracion_info);
        } else {
            $_SESSION['mensaje_error'] = "Error al restaurar el respaldo. Verifique el archivo.";
            
            // Registrar error en el log
            $error_info = [
                'id' => time() . rand(100, 999),
                'nombre' => $_FILES['archivo_respaldo']['name'],
                'fecha' => date('Y-m-d H:i:s'),
                'tipo' => 'Error',
                'origen' => 'manual',
                'estado' => 'error',
                'accion' => 'restauracion',
                'mensaje_error' => 'Error al restaurar. Código: ' . $salida
            ];
            
            guardarLogRespaldos($backup_log, $error_info);
        }
    } else {
        $_SESSION['mensaje_error'] = "Error al subir el archivo de respaldo.";
    }
    
    header("Location: respaldos.php");
    exit();
}

// Obtener respaldos existentes
$respaldos = obtenerRespaldos($backup_log);

// Obtener información de programación automática
$programacion_file = $backup_dir . 'programacion.json';
$programacion = ['automatico' => false];
if (file_exists($programacion_file)) {
    $programacion = json_decode(file_get_contents($programacion_file), true) ?: ['automatico' => false];
}

// Calcular uso de almacenamiento
$uso_almacenamiento = 0;
$total_almacenamiento = 350 * 1024 * 1024; // 350 MB

foreach ($respaldos as $respaldo) {
    if (!empty($respaldo['ruta']) && file_exists($respaldo['ruta'])) {
        $uso_almacenamiento += filesize($respaldo['ruta']);
    }
}

$porcentaje_uso = ($uso_almacenamiento / $total_almacenamiento) * 100;
$uso_formateado = formatBytes($uso_almacenamiento);
$total_formateado = formatBytes($total_almacenamiento);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Respaldos - Pastelería Chispitas</title>
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
            position: relative;
        }
        
        .header h1 {
            font-family: 'Playfair Display', serif;
            color: #4A3F3A;
            margin: 0;
        }
        
        .real-time-clock {
            position: absolute;
            top: 10px;
            right: 20px;
            background: rgba(255, 255, 255, 0.8);
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .crud-container {
            background: rgba(255,255,255,0.9);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .backup-actions {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
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
        
        .btn {
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
        }
        
        .btn-primary {
            background-color: #A78A7F;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #8a7369;
        }
        
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c82333;
        }
        
        .btn-success {
            background-color: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background-color: #218838;
        }
        
        .btn-warning {
            background-color: #ffc107;
            color: #212529;
        }
        
        .btn-warning:hover {
            background-color: #e0a800;
        }
        
        .btn-info {
            background-color: #17a2b8;
            color: white;
        }
        
        .btn-info:hover {
            background-color: #138496;
        }
        
        .btn-group {
            display: flex;
            gap: 5px;
        }
        
        .badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: 500;
            display: inline-block;
        }
        
        .badge-completo {
            background-color: #A78A7F;
            color: white;
        }
        
        .badge-incremental {
            background-color: #6c757d;
            color: white;
        }
        
        .badge-automatico {
            background-color: #17a2b8;
            color: white;
        }
        
        .badge-manual {
            background-color: #28a745;
            color: white;
        }
        
        .badge-error {
            background-color: #dc3545;
            color: white;
        }
        
        .badge-restauracion {
            background-color: #ffc107;
            color: #212529;
        }
        
        .nav-buttons {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .storage-info {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-left: 5px solid #A78A7F;
        }
        
        .storage-info h3 {
            margin-top: 0;
            color: #A78A7F;
        }
        
        .progress-bar {
            height: 20px;
            background-color: #E8D5C0;
            border-radius: 10px;
            margin: 10px 0;
            overflow: hidden;
        }
        
        .progress {
            height: 100%;
            background-color: #D4B8C7;
        }
        
        .storage-details {
            display: flex;
            justify-content: space-between;
            font-size: 0.9em;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-weight: 500;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 8px;
            border: 1px solid #E8D5C0;
            border-radius: 4px;
            box-sizing: border-box;
        }
        
        .form-inline {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .auto-backup-status {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-left: 5px solid #17a2b8;
        }
        
        .auto-backup-status h3 {
            margin-top: 0;
            color: #17a2b8;
        }
        
        .status-active {
            color: #28a745;
            font-weight: bold;
        }
        
        .status-inactive {
            color: #dc3545;
            font-weight: bold;
        }
        
        @media (max-width: 768px) {
            .backup-actions, .nav-buttons, .form-inline {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .btn-group {
                flex-direction: column;
            }
            
            .real-time-clock {
                position: static;
                margin-top: 10px;
                text-align: center;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="header">
        <h1>Pastelería Chispitas - Gestión de Respaldos</h1>
        <div class="real-time-clock">
            <i class="fas fa-clock"></i> 
            <span id="real-time"><?php echo date('H:i:s'); ?></span>
        </div>
    </div>
    
    <div class="nav-buttons">
        <a href="dashboard.php" class="btn btn-primary">
            <i class="fas fa-home"></i> Volver al Panel
        </a>
      <!--  <a href="reportes.php" class="btn btn-primary">
           <i class="fas fa-chart-pie"></i> Reportes
        </a>
        <a href="configuracion.php" class="btn btn-primary">
            <i class="fas fa-cog"></i> Configuración
        </a>-->
    </div>

    <!-- Mostrar mensajes -->
    <?php if(isset($_SESSION['mensaje_exito'])): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> <?= $_SESSION['mensaje_exito'] ?>
    </div>
    <?php unset($_SESSION['mensaje_exito']); endif; ?>
    
    <?php if(isset($_SESSION['mensaje_error'])): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i> <?= $_SESSION['mensaje_error'] ?>
    </div>
    <?php unset($_SESSION['mensaje_error']); endif; ?>

    <!-- Información de almacenamiento -->
    <div class="storage-info">
        <h3>Almacenamiento de Respaldos</h3>
        <div class="progress-bar">
            <div class="progress" style="width: <?= $porcentaje_uso ?>%;"></div>
        </div>
        <div class="storage-details">
            <span><?= round($porcentaje_uso, 2) ?>% utilizado</span>
            <span><?= $uso_formateado ?> de <?= $total_formateado ?></span>
        </div>
    </div>

    <!-- Estado de respaldo automático -->
    <div class="auto-backup-status">
        <h3>Respaldo Automático</h3>
        <?php if ($programacion['automatico']): ?>
            <p>Estado: <span class="status-active">ACTIVO</span></p>
            <p>Intervalo: Cada <?= $programacion['intervalo'] ?> horas</p>
            <p>Última ejecución: <?= $programacion['ultima_ejecucion'] ?></p>
            <p>Próxima ejecución: <?= $programacion['proxima_ejecucion'] ?></p>
            <a href="respaldos.php?desactivar_automatico=1" class="btn btn-danger">
                <i class="fas fa-times-circle"></i> Desactivar Respaldo Automático
            </a>
        <?php else: ?>
            <p>Estado: <span class="status-inactive">INACTIVO</span></p>
            <form action="respaldos.php" method="post" class="form-inline">
                <div class="form-group">
                    <label for="intervalo">Programar respaldo automático cada:</label>
                    <input type="number" id="intervalo" name="intervalo" min="1" max="168" value="24" class="form-control" style="width: 80px;" required> horas
                </div>
                <button type="submit" name="programar_automatico" class="btn btn-success">
                    <i class="fas fa-clock"></i> Activar Respaldo Automático
                </button>
            </form>
        <?php endif; ?>
    </div>

    <!-- Acciones principales -->
    <div class="backup-actions">
        <a href="respaldos.php?crear_respaldo=1" class="btn btn-success">
            <i class="fas fa-plus-circle"></i> Crear Nuevo Respaldo Manual
        </a>
        
        <form action="respaldos.php" method="post" enctype="multipart/form-data" style="display: flex; gap: 10px; align-items: center;">
            <input type="file" name="archivo_respaldo" accept=".sql" required style="padding: 8px;">
            <button type="submit" name="restaurar" class="btn btn-primary" onclick="return confirm('¿Está seguro de restaurar este respaldo? Esto sobrescribirá todos los datos actuales.')">
                <i class="fas fa-upload"></i> Restaurar Respaldo
            </button>
        </form>
    </div>

    <!-- Listado de respaldos -->
    <div class="crud-container">
        <h2>Historial de Respaldos y Operaciones</h2>
        
        <?php if (count($respaldos) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Fecha</th>
                    <th>Tamaño</th>
                    <th>Tipo/Origen</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($respaldos as $respaldo): ?>
                <tr>
                    <td><?= $respaldo['nombre'] ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($respaldo['fecha'])) ?></td>
                    <td><?= $respaldo['tamaño_formateado'] ?? $respaldo['tamaño'] ?></td>
                    <td>
                        <span class="badge badge-<?= strtolower($respaldo['tipo'] ?? 'completo') ?>">
                            <?= $respaldo['tipo'] ?? 'Completo' ?>
                        </span>
                        <span class="badge badge-<?= $respaldo['origen'] ?? 'manual' ?>">
                            <?= $respaldo['origen'] ?? 'manual' ?>
                        </span>
                        <?php if (isset($respaldo['accion'])): ?>
                        <span class="badge badge-<?= $respaldo['accion'] ?>">
                            <?= $respaldo['accion'] ?>
                        </span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge badge-<?= $respaldo['estado'] ?>">
                            <?= $respaldo['estado'] ?>
                        </span>
                    </td>
                    <td>
                        <div class="btn-group">
                            <?php if (($respaldo['tipo'] ?? '') !== 'Error' && ($respaldo['accion'] ?? '') !== 'restauracion' && !empty($respaldo['ruta'])): ?>
                            <a href="respaldos.php?descargar=<?= $respaldo['id'] ?>" class="btn btn-primary">
                                <i class="fas fa-download"></i> Descargar
                            </a>
                            <?php endif; ?>
                            <a href="respaldos.php?eliminar=<?= $respaldo['id'] ?>" class="btn btn-danger"
                               onclick="return confirm('¿Eliminar permanentemente este registro?')">
                                <i class="fas fa-trash"></i> Eliminar
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p>No hay respaldos disponibles. Cree el primer respaldo haciendo clic en el botón "Crear Nuevo Respaldo".</p>
        <?php endif; ?>
    </div>

    <!-- Información del sistema -->
    <div class="crud-container">
        <h2>Información del Sistema</h2>
        <p><strong>Base de datos:</strong> <?= $db_name ?></p>
        <p><strong>Servidor:</strong> <?= $db_host ?></p>
        <p><strong>Directorio de respaldos:</strong> <?= realpath($backup_dir) ? realpath($backup_dir) : $backup_dir ?></p>
        <p><strong>Archivo de registro:</strong> <?= $backup_log ?></p>
        
        <?php
        // Verificar si mysqldump está disponible usando la ruta completa
        $mysqldump_available = false;
        $output = [];
        exec('C:\\xampp\\mysql\\bin\\mysqldump.exe --version', $output, $return_val);
        if ($return_val === 0) {
            $mysqldump_available = true;
        }
        ?>
        
        <p><strong>mysqldump disponible:</strong> 
            <?= $mysqldump_available ? 
                '<span style="color: green;"><i class="fas fa-check-circle"></i> Sí</span>' : 
                '<span style="color: red;"><i class="fas fa-times-circle"></i> No</span>' ?>
        </p>
        
        <?php if (!$mysqldump_available): ?>
        <div class="alert alert-error">
            <strong>Advertencia:</strong> La herramienta mysqldump no está disponible en el sistema. 
            Los respaldos automáticos no funcionarán correctamente. Contacte al administrador del servidor.
        </div>
        <?php endif; ?>
    </div>

    <script>
        // Función para actualizar el reloj en tiempo real
        function updateClock() {
            const now = new Date();
            const hours = now.getHours().toString().padStart(2, '0');
            const minutes = now.getMinutes().toString().padStart(2, '0');
            const seconds = now.getSeconds().toString().padStart(2, '0');
            
            // Actualizar el texto del elemento
            document.getElementById('real-time').textContent = `${hours}:${minutes}:${seconds}`;
        }
        
        // Actualizar el reloj cada segundo
        setInterval(updateClock, 1000);
        
        // Iniciar el reloj inmediatamente
        updateClock();
    </script>
</body>
</html>