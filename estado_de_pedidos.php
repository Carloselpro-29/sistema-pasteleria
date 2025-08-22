<?php
// Conexión a BD
$conn = new mysqli("localhost", "root", "", "prueba2");
if ($conn->connect_error) {
    die("Error: " . $conn->connect_error);
}

// Función para mostrar pedidos personalizados
function mostrarPedidos($conn) {
    $sql = "SELECT p.id_pedido, p.foto_referencia, p.fecha_entrega, p.telefono, p.email, 
                   p.sabores, p.tamano, p.diseno
            FROM pedidos p
            ORDER BY p.id_pedido DESC";
    $res = $conn->query($sql);
    
    if ($res->num_rows === 0) {
        echo "<p style='text-align:center; margin:20px;'>No hay pedidos registrados.</p>";
        return;
    }

    echo "<table class='tabla-estilo'>
            <thead>
                <tr>
                    <th>Foto Referencia</th>
                    <th>Fecha de Entrega</th>
                    <th>Teléfono</th>
                    <th>Email</th>
                    <th>Sabores</th>
                    <th>Tamaño</th>
                    <th>Diseño</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>";
    while ($row = $res->fetch_assoc()) {
        $foto = $row['foto_referencia'] ? "<img src='{$row['foto_referencia']}' alt='Foto' class='foto-ref'>" : "<div class='icono-foto'>📷</div>";
        echo "<tr>
                <td>$foto</td>
                <td>{$row['fecha_entrega']}</td>
                <td>{$row['telefono']}</td>
                <td>{$row['email']}</td>
                <td>{$row['sabores']}</td>
                <td>{$row['tamano']}</td>
                <td>{$row['diseno']}</td>
                <td><span class='estado-proceso'>En proceso</span></td>
              </tr>";
    }
    echo "</tbody></table>";
}

// Función para mostrar compras (tabla compras)
function mostrarCompras($conn) {
    $sql = "SELECT id_compra, id_pastel, nombre_tarjeta, banco, numero_tarjeta, 
                   fecha_expiracion, cvv, fecha_pago
            FROM compras
            ORDER BY id_compra DESC";
    $res = $conn->query($sql);

    if ($res->num_rows === 0) {
        echo "<p style='text-align:center; margin:20px;'>No hay compras registradas.</p>";
        return;
    }

    echo "<table class='tabla-estilo'>
            <thead>
                <tr>
                    <th>ID Compra</th>
                    <th>ID Pastel</th>
                    <th>Nombre en Tarjeta</th>
                    <th>Banco</th>
                    <th>Número Tarjeta</th>
                    <th>Fecha Exp.</th>
                    <th>CVV</th>
                    <th>Fecha de Pago</th>
                </tr>
            </thead>
            <tbody>";
    while ($row = $res->fetch_assoc()) {
        echo "<tr>
                <td>{$row['id_compra']}</td>
                <td>{$row['id_pastel']}</td>
                <td>{$row['nombre_tarjeta']}</td>
                <td>{$row['banco']}</td>
                <td>{$row['numero_tarjeta']}</td>
                <td>{$row['fecha_expiracion']}</td>
                <td>{$row['cvv']}</td>
                <td>{$row['fecha_pago']}</td>
              </tr>";
    }
    echo "</tbody></table>";
}

// Modo AJAX
if (isset($_GET['actualizar']) && $_GET['actualizar'] == 'pedidos') {
    mostrarPedidos($conn);
    exit;
}
if (isset($_GET['actualizar']) && $_GET['actualizar'] == 'compras') {
    mostrarCompras($conn);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Estado de Pedidos y Compras</title>
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

body {
    font-family: 'Montserrat', sans-serif;
    background-color: var(--color-light);
    color: var(--color-medium);
    line-height: 1.6;
    padding: 20px;
}

.contenedor {
    max-width: 1100px;
    margin: auto;
    background: #fff;
    padding: 25px;
    border-radius: 10px;
    box-shadow: var(--shadow-sm);
    transition: transform 0.3s, box-shadow 0.3s;
}

.contenedor:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

h2 {
    text-align: center;
    color: var(--color-dark);
    font-family: 'Playfair Display', serif;
    margin-bottom: 20px;
}

.subtitulo {
    text-align: center;
    color: var(--color-medium);
    margin-bottom: 20px;
}

.botones {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-bottom: 20px;
}

.botones-acciones {
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
}

.btn-tab {
    padding: 12px 20px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s;
    background-color: var(--color-secondary);
    color: var(--color-dark);
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    font-family: 'Montserrat', sans-serif;
}

.btn-tab.active, .btn-tab:hover {
    background-color: var(--color-primary);
    color: white;
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    transform: translateY(-2px);
}

.btn-volver {
    padding: 12px 20px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    font-weight: 500;
    background: var(--color-primary);
    color: white;
    transition: all 0.3s;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-volver:hover {
    background: var(--color-primary-dark);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.btn-imprimir {
    padding: 12px 20px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    font-weight: 500;
    background: var(--color-success);
    color: white;
    transition: all 0.3s;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-imprimir:hover {
    background: var(--color-success-dark);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.tabla-estilo {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
    font-size: 0.95em;
}

.tabla-estilo thead {
    background: var(--color-secondary);
    color: var(--color-dark);
}

.tabla-estilo th, .tabla-estilo td {
    padding: 12px 15px;
    text-align: center;
    border-bottom: 1px solid var(--color-border);
}

.tabla-estilo th {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85em;
    letter-spacing: 0.5px;
}

.tabla-estilo tr:nth-child(even) {
    background-color: rgba(212, 184, 199, 0.1);
}

.tabla-estilo tr:hover {
    background-color: rgba(212, 184, 199, 0.2);
}

.foto-ref {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 8px;
}

.icono-foto {
    font-size: 24px;
}

/* ESTILO MEJORADO PARA EL ESTADO (ÚNICO CAMBIO REALIZADO) */
.estado-proceso {
    background: var(--color-primary);
    color: white;
    padding: 8px 15px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    display: inline-block;
    min-width: 100px;
    text-align: center;
    text-transform: uppercase;
    letter-spacing: 1px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.estado-proceso:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    background: var(--color-primary-dark);
}

@media print {
    body * {
        visibility: hidden;
    }
    .contenedor, .contenedor * {
        visibility: visible;
    }
    .contenedor {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        box-shadow: none;
    }
    .botones, .botones-acciones {
        display: none;
    }
}

/* Responsive */
@media (max-width: 768px) {
    .botones {
        flex-direction: column;
    }
    
    .btn-tab {
        width: 100%;
    }
    
    .botones-acciones {
        flex-direction: column;
        gap: 10px;
    }
    
    .btn-volver, .btn-imprimir {
        width: 100%;
        justify-content: center;
    }
}
</style>
</head>
<body>
<div class="contenedor">
    <h2>Mis Pedidos y Compras</h2>
    <p class="subtitulo">Alterna entre tus pedidos y tus compras realizadas</p>
    
    <div class="botones-acciones">
        <button class="btn-volver" onclick="window.location.href='dashboard.php'"><i class="fas fa-arrow-left"></i> Volver al Panel</button>
        <button class="btn-imprimir" onclick="window.print()"><i class="fas fa-print"></i> Imprimir</button>
    </div>
    
    <div class="botones">
        <button class="btn-tab btn-pedidos active" id="btn-pedidos">Pedidos</button>
        <button class="btn-tab btn-compras" id="btn-compras">Compras</button>
    </div>
    
    <div id="contenido-pedidos"><?php mostrarPedidos($conn); ?></div>
    <div id="contenido-compras" style="display:none;"><?php mostrarCompras($conn); ?></div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
<script>
const btnPedidos = document.getElementById('btn-pedidos');
const btnCompras = document.getElementById('btn-compras');
const contPedidos = document.getElementById('contenido-pedidos');
const contCompras = document.getElementById('contenido-compras');

btnPedidos.addEventListener('click', () => {
    btnPedidos.classList.add('active');
    btnCompras.classList.remove('active');
    contPedidos.style.display = 'block';
    contCompras.style.display = 'none';
});
btnCompras.addEventListener('click', () => {
    btnCompras.classList.add('active');
    btnPedidos.classList.remove('active');
    contPedidos.style.display = 'none';
    contCompras.style.display = 'block';
});

// Actualización en tiempo real para la pestaña visible
setInterval(() => {
    if (contPedidos.style.display !== 'none') {
        fetch('?actualizar=pedidos&ts=' + Date.now())
            .then(res => res.text())
            .then(html => contPedidos.innerHTML = html);
    } else if (contCompras.style.display !== 'none') {
        fetch('?actualizar=compras&ts=' + Date.now())
            .then(res => res.text())
            .then(html => contCompras.innerHTML = html);
    }
}, 3000);
</script>
</body>
</html>
<?php $conn->close(); ?>