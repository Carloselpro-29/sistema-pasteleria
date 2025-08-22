<?php
session_start();
require_once 'db.php';
$conn = conectarDB();

// Verificar sesión
if(!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// Obtener datos para estadísticas
$mes_actual = date('F');
$anio_actual = date('Y');

// Consultas para estadísticas
$sql_clientes = "SELECT COUNT(*) as total FROM users";
$res_clientes = $conn->query($sql_clientes);
$total_clientes = $res_clientes->fetch_assoc()['total'];

$sql_pedidos = "SELECT COUNT(*) as total FROM pedidos WHERE MONTH(fecha_entrega) = MONTH(CURRENT_DATE())";
$res_pedidos = $conn->query($sql_pedidos);
$total_pedidos = $res_pedidos->fetch_assoc()['total'];

$sql_ventas = "SELECT SUM(monto) as total FROM pago WHERE MONTH(fecha_pago) = MONTH(CURRENT_DATE())";
$res_ventas = $conn->query($sql_ventas);
$total_ventas = $res_ventas->fetch_assoc()['total'] ?? 0;

// Nueva consulta para compras directas
$sql_compras_directas = "SELECT SUM(p.precio) as total 
                         FROM compras c 
                         JOIN pasteles p ON c.id_pastel = p.id_pastel
                         WHERE MONTH(c.fecha_pago) = MONTH(CURRENT_DATE())";
$res_compras_directas = $conn->query($sql_compras_directas);
$total_compras_directas = $res_compras_directas->fetch_assoc()['total'] ?? 0;

$sql_inventario = "SELECT COUNT(*) as bajos FROM inventario WHERE cantidad < stock_minimo";
$res_inventario = $conn->query($sql_inventario);
$bajos_inventario = $res_inventario->fetch_assoc()['bajos'];

// Datos para gráficas
$ventas_mensuales = [];
$sql_ventas_mensuales = "SELECT 
    MONTHNAME(pg.fecha_pago) as mes, 
    SUM(pg.monto) as ventas,
    (SELECT SUM(p.precio) 
     FROM compras c 
     JOIN pasteles p ON c.id_pastel = p.id_pastel
     WHERE MONTH(c.fecha_pago) = MONTH(pg.fecha_pago)
     AND YEAR(c.fecha_pago) = YEAR(pg.fecha_pago)) as compras_directas,
    COUNT(*) as pedidos 
    FROM pago pg
    WHERE YEAR(fecha_pago) = YEAR(CURRENT_DATE())
    GROUP BY MONTH(pg.fecha_pago) 
    ORDER BY MONTH(pg.fecha_pago) DESC LIMIT 6";
$res_ventas_mensuales = $conn->query($sql_ventas_mensuales);
while($row = $res_ventas_mensuales->fetch_assoc()) {
    $ventas_mensuales[] = $row;
}

$productos_populares = [];
$sql_productos = "SELECT 
    p.nombre, 
    COUNT(*) as ventas, 
    SUM(p.precio) as ingresos 
    FROM compras c 
    JOIN pasteles p ON c.id_pastel = p.id_pastel 
    GROUP BY p.nombre 
    ORDER BY ventas DESC 
    LIMIT 5";
$res_productos = $conn->query($sql_productos);
while($row = $res_productos->fetch_assoc()) {
    $productos_populares[] = $row;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - Pastelería Chispitas</title>
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
        
        .report-container {
            background: rgba(255,255,255,0.9);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            page-break-inside: avoid;
        }
        
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #D4B8C7;
            padding-bottom: 10px;
        }
        
        .report-title {
            font-family: 'Playfair Display', serif;
            color: #4A3F3A;
            margin: 0;
        }
        
        .report-date {
            color: #A78A7F;
            font-weight: 500;
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
        
        .chart-container {
            height: 300px;
            margin: 30px 0;
            position: relative;
        }
        
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .summary-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-left: 5px solid #A78A7F;
        }
        
        .summary-card h3 {
            margin-top: 0;
            color: #A78A7F;
            font-size: 1.1rem;
        }
        
        .summary-card .value {
            font-size: 2rem;
            font-weight: bold;
            color: #4A3F3A;
            margin: 10px 0;
        }
        
        .summary-card .comparison {
            color: #28a745;
            font-weight: 500;
        }
        
        .comparison.negative {
            color: #dc3545;
        }
        
        .btn {
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background-color: #A78A7F;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #8a7369;
        }
        
        .btn-print {
            background-color: #6c757d;
            color: white;
            margin-left: 10px;
        }
        
        .btn-print:hover {
            background-color: #5a6268;
        }
        
        .nav-buttons {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }
        
        .nav-buttons a {
            text-decoration: none;
        }
        
        .tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #E8D5C0;
        }
        
        .tab {
            padding: 10px 20px;
            cursor: pointer;
            border-bottom: 3px solid transparent;
        }
        
        .tab.active {
            border-bottom-color: #A78A7F;
            font-weight: 500;
            color: #4A3F3A;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .low-stock {
            color: red;
            font-weight: bold;
        }
        
        .print-only {
            display: none;
        }
        
        @media print {
            body {
                background-color: white;
                color: black;
                padding: 0;
            }
            
            .header, .report-container {
                box-shadow: none;
                border: 1px solid #ddd;
            }
            
            .nav-buttons, .tabs {
                display: none;
            }
            
            .tab-content {
                display: block !important;
                page-break-after: always;
            }
            
            .print-only {
                display: block;
            }
            
            .no-print {
                display: none;
            }
            
            .chart-container {
                height: 250px;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
   <div class="header">
    <h1>Pastelería Chispitas - Reportes y Estadísticas</h1>
    <p class="report-date">Reporte generado el <?= strftime('%d de %B de %Y a las %H:%M', strtotime(date('Y-m-d H:i'))) ?></p>
</div>
    
    <div class="nav-buttons no-print">
        <a href="dashboard.php" class="btn btn-primary">
            <i class="fas fa-home"></i> Volver
        </a>
        <button class="btn btn-print" onclick="window.print()">
            <i class="fas fa-print"></i> Imprimir Reporte
        </button>
    </div>

    <div class="tabs no-print">
        <div class="tab active" onclick="changeTab('resumen')">Resumen</div>
        <div class="tab" onclick="changeTab('clientes')">Clientes</div>
        <div class="tab" onclick="changeTab('alertas')">Alertas</div>
        <div class="tab" onclick="changeTab('inventario')">Inventario</div>
        <div class="tab" onclick="changeTab('pedidos')">Pedidos</div>
        <div class="tab" onclick="changeTab('compras')">Compras</div>
    </div>

    <!-- Resumen General -->
    <div id="resumen" class="tab-content active">
        <div class="summary-cards">
            <div class="summary-card">
                <h3>Clientes Registrados</h3>
                <div class="value"><?= $total_clientes ?></div>
                <div class="comparison">Total en el sistema</div>
            </div>
            
            <div class="summary-card">
                <h3>Pedidos este Mes</h3>
                <div class="value"><?= $total_pedidos ?></div>
                <div class="comparison"><?= $mes_actual ?></div>
            </div>
            
            <div class="summary-card">
                <h3>Ventas por Pedidos</h3>
                <div class="value">$<?= number_format($total_ventas, 2) ?></div>
                <div class="comparison">Total acumulado</div>
            </div>
            
            <div class="summary-card">
                <h3>Compras Directas</h3>
                <div class="value">$<?= number_format($total_compras_directas, 2) ?></div>
                <div class="comparison"><?= $mes_actual ?></div>
            </div>
            
            <div class="summary-card">
                <h3>Productos Bajos en Stock</h3>
                <div class="value"><?= $bajos_inventario ?></div>
                <div class="comparison <?= $bajos_inventario > 15? 'positive' : '' ?>">
                    <?= $bajos_inventario > 10 ? 'Necesita atención' : 'Todo en orden' ?>
                </div>
            </div>
        </div>

        <div class="report-container">
            <div class="report-header">
                <h2 class="report-title">Tendencia de Ingresos</h2>
                <span class="report-date">Este mes</span>
            </div>
            
            <div class="chart-container">
                <canvas id="ventasChart"></canvas>
            </div>
        </div>

        <div class="report-container">
            <div class="report-header">
                <h2 class="report-title">Productos Más Populares</h2>
                <span class="report-date"><?= "$mes_actual $anio_actual" ?></span>
            </div>
            
            <div class="chart-container">
                <canvas id="productosChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Clientes -->
    <div id="clientes" class="tab-content">
        <div class="report-container">
            <div class="report-header">
                <h2 class="report-title">Clientes Registrados</h2>
                <span class="report-date"><?= "$mes_actual $anio_actual" ?></span>
            </div>
            <table>
                <thead>
                    <tr><th>Fecha</th><th>Nombre</th><th>Email</th><th>Tipo</th></tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT DATE(created_at) AS fecha, email, name,
                                   CASE WHEN email LIKE '%admin%' THEN 'Administrador' ELSE 'Usuario Normal' END AS tipo
                            FROM users ORDER BY fecha DESC";
                    $res = $conn->query($sql);
                    if ($res->num_rows > 0) {
                        while($row = $res->fetch_assoc()) {
                            echo "<tr>
                                    <td>{$row['fecha']}</td>
                                    <td>{$row['name']}</td>
                                    <td>{$row['email']}</td>
                                    <td>{$row['tipo']}</td>
                                  </tr>";
                        }
                    } else { echo "<tr><td colspan='4'>No hay clientes registrados</td></tr>"; }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Alertas -->
    <div id="alertas" class="tab-content">
        <div class="report-container">
            <div class="report-header">
                <h2 class="report-title">Alertas</h2>
                <span class="report-date"><?= "$mes_actual $anio_actual" ?></span>
            </div>
            <table>
                <thead><tr><th>Fecha</th><th>Hora</th><th>Nombre</th><th>Descripción</th></tr></thead>
                <tbody>
                    <?php
                    $sql = "SELECT * FROM alertas ORDER BY fecha DESC, hora DESC";
                    $res = $conn->query($sql);
                    if ($res->num_rows > 0) {
                        while($row = $res->fetch_assoc()) {
                            echo "<tr>
                                    <td>{$row['fecha']}</td>
                                    <td>{$row['hora']}</td>
                                    <td>{$row['nombre']}</td>
                                    <td>{$row['descripcion']}</td>
                                  </tr>";
                        }
                    } else { echo "<tr><td colspan='4'>No hay alertas</td></tr>"; }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Inventario -->
    <div id="inventario" class="tab-content">
        <div class="report-container">
            <div class="report-header">
                <h2 class="report-title">Inventario</h2>
                <span class="report-date"><?= "$mes_actual $anio_actual" ?></span>
            </div>
            <table>
                <thead><tr><th>Nombre</th><th>Categoría</th><th>Cantidad</th><th>Unidad</th><th>Proveedor</th><th>Stock Mínimo</th><th>Última Actualización</th></tr></thead>
                <tbody>
                    <?php
                    $sql = "SELECT * FROM inventario ORDER BY ultima_actualizacion DESC";
                    $res = $conn->query($sql);
                    if ($res->num_rows > 0) {
                        while($row = $res->fetch_assoc()) {
                            $alerta = ($row['cantidad'] < $row['stock_minimo']) ? "⚠️ Bajo" : "";
                            echo "<tr>
                                    <td>{$row['nombre']}</td>
                                    <td>{$row['categoria']}</td>
                                    <td>{$row['cantidad']}</td>
                                    <td>{$row['unidad']}</td>
                                    <td>{$row['proveedor']}</td>
                                    <td>{$row['stock_minimo']} <span class='low-stock'>$alerta</span></td>
                                    <td>{$row['ultima_actualizacion']}</td>
                                  </tr>";
                        }
                    } else { echo "<tr><td colspan='7'>Inventario vacío</td></tr>"; }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pedidos -->
    <div id="pedidos" class="tab-content">
        <div class="report-container">
            <div class="report-header">
                <h2 class="report-title">Pedidos Personalizados</h2>
                <span class="report-date"><?= "$mes_actual $anio_actual" ?></span>
            </div>
            <table>
                <thead><tr><th>Cliente</th><th>Email</th><th>Teléfono</th><th>Fecha Entrega</th><th>Sabor</th><th>Tamaño</th><th>Diseño</th></tr></thead>
                <tbody>
                    <?php
                    $sql = "SELECT * FROM pedidos ORDER BY fecha_entrega DESC";
                    $res = $conn->query($sql);
                    if ($res->num_rows > 0) {
                        while($row = $res->fetch_assoc()) {
                            echo "<tr>
                                    <td>{$row['nombre_cliente']}</td>
                                    <td>{$row['email']}</td>
                                    <td>{$row['telefono']}</td>
                                    <td>{$row['fecha_entrega']}</td>
                                    <td>{$row['sabores']}</td>
                                    <td>{$row['tamano']}</td>
                                    <td>{$row['diseno']}</td>
                                  </tr>";
                        }
                    } else { echo "<tr><td colspan='7'>No hay pedidos</td></tr>"; }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Compras y Pagos -->
    <div id="compras" class="tab-content">
        <div class="report-container">
            <div class="report-header">
                <h2 class="report-title">Compras y Pagos</h2>
                <span class="report-date"><?= "$mes_actual $anio_actual" ?></span>
            </div>
            <h3>Compras Directas</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pastel</th>
                        <th>Precio</th>
                        <th>Nombre en Tarjeta</th>
                        <th>Banco</th>
                        <th>Fecha Pago</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT c.id_compra, p.nombre AS pastel, p.precio, c.nombre_tarjeta, c.banco, c.fecha_pago
                            FROM compras c JOIN pasteles p ON c.id_pastel=p.id_pastel ORDER BY c.fecha_pago DESC";
                    $res = $conn->query($sql);
                    if ($res->num_rows > 0) {
                        while($row = $res->fetch_assoc()) {
                            echo "<tr>
                                    <td>{$row['id_compra']}</td>
                                    <td>{$row['pastel']}</td>
                                    <td>\${$row['precio']}</td>
                                    <td>{$row['nombre_tarjeta']}</td>
                                    <td>{$row['banco']}</td>
                                    <td>{$row['fecha_pago']}</td>
                                  </tr>";
                        }
                    } else { echo "<tr><td colspan='6'>No hay compras</td></tr>"; }
                    ?>
                </tbody>
            </table>

            <h3>Pagos de Pedidos</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Fecha Entrega</th>
                        <th>Monto</th>
                        <th>Banco</th>
                        <th>Fecha Pago</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT pg.id_pago, pd.nombre_cliente, pd.fecha_entrega, pg.monto, pg.banco, pg.fecha_pago
                            FROM pago pg JOIN pedidos pd ON pg.pedido_id = pd.id_pedido ORDER BY pg.fecha_pago DESC";
                    $res = $conn->query($sql);
                    if ($res->num_rows > 0) {
                        while($row = $res->fetch_assoc()) {
                            echo "<tr>
                                    <td>{$row['id_pago']}</td>
                                    <td>{$row['nombre_cliente']}</td>
                                    <td>{$row['fecha_entrega']}</td>
                                    <td>\${$row['monto']}</td>
                                    <td>{$row['banco']}</td>
                                    <td>{$row['fecha_pago']}</td>
                                  </tr>";
                        }
                    } else { echo "<tr><td colspan='6'>No hay pagos</td></tr>"; }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Sección de impresión -->
    <div class="print-only">
        <h2 style="text-align: center; margin-top: 30px;">Reporte Completo - Pastelería Chispitas</h2>
        <p style="text-align: center;">Generado el <?= date('d/m/Y H:i') ?></p>
    </div>

    <script>
        // Cambiar pestañas
        function changeTab(tabId) {
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            document.querySelector(`.tab[onclick="changeTab('${tabId}')"]`).classList.add('active');
            document.getElementById(tabId).classList.add('active');
        }
        
        // Gráfico de ventas
        const ventasCtx = document.getElementById('ventasChart').getContext('2d');
        const ventasChart = new Chart(ventasCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($ventas_mensuales, 'mes')) ?>,
                datasets: [
                    {
                        label: 'Ventas por Pedidos',
                        data: <?= json_encode(array_column($ventas_mensuales, 'ventas')) ?>,
                        backgroundColor: 'rgba(167, 138, 127, 0.7)',
                        borderColor: 'rgba(167, 138, 127, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Compras Directas',
                        data: <?= json_encode(array_column($ventas_mensuales, 'compras_directas')) ?>,
                        backgroundColor: 'rgba(212, 184, 199, 0.7)',
                        borderColor: 'rgba(212, 184, 199, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        stacked: false
                    },
                    x: {
                        stacked: false
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Ingresos Mensuales',
                        font: {
                            size: 16
                        }
                    },
                    tooltip: {
                        callbacks: {
                            afterBody: function(context) {
                                const total = context[0].parsed.y + (context[1]?.parsed.y || 0);
                                return 'Total: $' + total.toFixed(2);
                            }
                        }
                    }
                }
            }
        });
        
        // Gráfico de productos
        const productosCtx = document.getElementById('productosChart').getContext('2d');
        const productosChart = new Chart(productosCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($productos_populares, 'nombre')) ?>,
                datasets: [{
                    label: 'Ventas',
                    data: <?= json_encode(array_column($productos_populares, 'ingresos')) ?>,
                    backgroundColor: [
                        'rgba(212, 184, 199, 0.7)',
                        'rgba(167, 138, 127, 0.7)',
                        'rgba(232, 213, 192, 0.7)',
                        'rgba(74, 63, 58, 0.7)',
                        'rgba(90, 83, 80, 0.7)'
                    ],
                    borderColor: [
                        'rgba(212, 184, 199, 1)',
                        'rgba(167, 138, 127, 1)',
                        'rgba(232, 213, 192, 1)',
                        'rgba(74, 63, 58, 1)',
                        'rgba(90, 83, 80, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Productos Más Populares',
                        font: {
                            size: 16
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>