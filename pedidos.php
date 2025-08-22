<?php
$host = "localhost";
$usuario = "root";
$clave = "";
$bd = "prueba2";

$conn = new mysqli($host, $usuario, $clave, $bd);
if ($conn->connect_error) {
    die("Error en la conexión: " . $conn->connect_error);
}

// Categorías para filtros de compras
$categorias = ['Chocolate', 'Frutas', 'Tres Leches', 'Especiales', 'Sin Azúcar', 'Mini Pasteles'];

// Consulta Pedidos
$sqlPedidos = "SELECT id_pedido, telefono, email, fecha_entrega, sabores, tamano, diseno, foto_referencia
               FROM pedidos
               ORDER BY fecha_entrega DESC";
$resultPedidos = $conn->query($sqlPedidos);

// Consulta Compras con INNER JOIN a Pasteles
$sqlCompras = "SELECT c.id_compra, c.id_pastel, p.nombre, p.categoria, c.fecha_pago
               FROM compras c
               INNER JOIN pasteles p ON c.id_pastel = p.id_pastel
               ORDER BY c.fecha_pago DESC";
$resultCompras = $conn->query($sqlCompras);

function calcularEstadoPedido($fecha_entrega) {
    $hoy = new DateTime(date('Y-m-d'));
    $entrega = new DateTime($fecha_entrega);
    return ($entrega > $hoy) ? 'En proceso' : 'Entregado';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Mis Pedidos y Compras</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
<style>
/* Igual que tu diseño anterior */
body { font-family: Arial, sans-serif; background-color: #fff9fb; color: #333; margin: 30px; }
.container { max-width: 1000px; margin: 0 auto; background-color: #fff; padding: 25px; border-radius: 15px; box-shadow: 0 8px 25px rgba(232, 63, 111, 0.15); }
.header { text-align: center; margin-bottom: 30px; color: #6d213c; }
.actions { display:flex; justify-content:space-between; margin-bottom:20px; }
.actions a, .actions button { padding:10px 20px; border:none; border-radius:25px; cursor:pointer; font-weight:bold; color:white; text-decoration:none; }
.back-btn { background:#6d213c; }
.back-btn:hover { background:#50162c; }
.print-btn { background:#28a745; }
.print-btn:hover { background:#1e7e34; }

table { width: 100%; border-collapse: separate; border-spacing: 0 12px; font-size: 14px; }
th, td { padding: 12px 15px; vertical-align: middle; text-align: left; }
th { background-color: #e83f6f; color: white; font-weight: 600; border-top-left-radius: 10px; border-top-right-radius: 10px; }
tr { background-color: #fff0f3; box-shadow: 0 4px 8px rgba(232, 63, 111, 0.1); border-radius: 10px; }
tr:hover { background-color: #ffe1ea; }
td { background-color: #fff; border-radius: 8px; box-shadow: inset 0 0 5px rgba(232, 63, 111, 0.05); }
.order-image { width: 80px; height: 80px; border-radius: 10px; object-fit: cover; }
.estado { font-weight: bold; padding: 6px 14px; border-radius: 20px; display: inline-block; color: white; font-size: 0.9rem; text-align: center; min-width: 90px; }
.estado.proceso { background-color: #2196F3; }
.estado.entregado { background-color: #4CAF50; }
.empty-orders { text-align: center; color: #999; margin-top: 40px; }
.empty-orders i { font-size: 60px; margin-bottom: 10px; color: #ddd; }

/* Botones alternar tablas */
.toggle-btn { margin:0 5px; padding:10px 20px; border-radius:25px; border:none; font-weight:bold; cursor:pointer; background:#e83f6f; color:white; transition:0.3s; }
.toggle-btn.active { background:#ff9ab2; }
.toggle-btn:hover { transform: scale(1.05); }

/* CSS Compras */
.filter-card { display:inline-block; margin:5px; padding:8px 16px; border:none; border-radius:25px; cursor:pointer; font-weight:bold; background: linear-gradient(45deg,#ff9ab2,#e83f6f); color:white; transition: transform 0.2s, box-shadow 0.2s; }
.filter-card:hover { transform: scale(1.05); box-shadow:0 4px 12px rgba(232,63,111,0.4); }
.filter-card.active { box-shadow:0 4px 12px rgba(0,0,0,0.5); transform: scale(1.1); }

#tablaCompras { margin-top:15px; width:100%; border-collapse: collapse; }
#tablaCompras th, #tablaCompras td { padding:10px 12px; text-align:left; border-bottom:1px solid #eee; }
#tablaCompras th { background:#e83f6f; color:white; }
#tablaCompras tr:hover { background:#ffe1ea; }
.search-bar { width:50%; padding:8px 12px; margin-bottom:10px; border-radius:25px; border:1px solid #ddd; display:block; margin-left:auto; margin-right:auto; }
</style>
</head>
<body>
<div class="container">
<div class="header">
<h1><i class="fas fa-clipboard-list"></i> Mis Pedidos y Compras</h1>
<p>Alterna entre tus pedidos y tus compras realizadas</p>
</div>

<!-- Botones de acciones -->
<div class="actions">
    <a href="dashboard_user.php" class="back-btn"><i class="fas fa-arrow-left"></i> Regresar</a>
    <button class="print-btn" onclick="window.print()"><i class="fas fa-print"></i> Imprimir</button>
</div>

<!-- Botones alternar tablas -->
<div style="text-align:center; margin-bottom:20px;">
    <button class="toggle-btn active" onclick="mostrarTabla('pedidos')">Pedidos</button>
    <button class="toggle-btn" onclick="mostrarTabla('compras')">Compras</button>
</div>

<!-- Tabla Pedidos -->
<div id="pedidos">
<?php if ($resultPedidos && $resultPedidos->num_rows > 0): ?>
<table>
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
<tbody>
<?php while($row = $resultPedidos->fetch_assoc()): ?>
<?php $estado = calcularEstadoPedido($row['fecha_entrega']); ?>
<tr>
<td>
<?php if ($row['foto_referencia']): ?>
<img src="<?php echo $row['foto_referencia']; ?>" alt="Foto referencia" class="order-image" />
<?php else: ?>
<i class="fas fa-image" style="font-size: 40px; color: #ccc;"></i>
<?php endif; ?>
</td>
<td><?php echo htmlspecialchars($row['fecha_entrega']); ?></td>
<td><?php echo htmlspecialchars($row['telefono']); ?></td>
<td><?php echo htmlspecialchars($row['email']); ?></td>
<td><?php echo htmlspecialchars($row['sabores']); ?></td>
<td><?php echo htmlspecialchars($row['tamano']); ?></td>
<td style="white-space: pre-wrap;"><?php echo htmlspecialchars($row['diseno']); ?></td>
<td>
<span class="estado <?php echo ($estado === 'En proceso') ? 'proceso' : 'entregado'; ?>">
<?php echo $estado; ?>
</span>
</td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
<?php else: ?>
<div class="empty-orders">
<i class="fas fa-box-open"></i>
<h3>No tienes pedidos recientes</h3>
</div>
<?php endif; ?>
</div>

<!-- Tabla Compras -->
<div id="compras" style="display:none;">
<input type="text" class="search-bar" placeholder="Buscar en compras..." onkeyup="filtrarTabla(this,'tablaCompras')">
<div class="filters" style="text-align:center; margin-bottom:15px;">
<?php foreach($categorias as $cat): ?>
    <button class="filter-card" onclick="filtrarColumna('tablaCompras',3,'<?=$cat?>')"><?=$cat?></button>
<?php endforeach; ?>
<button class="filter-card" onclick="filtrarColumna('tablaCompras',null,'')">Quitar Filtros</button>
</div>

<table id="tablaCompras">
<thead>
<tr>
<th>ID Compra</th>
<th>ID Pastel</th>
<th>Nombre Pastel</th>
<th>Categoría</th>
<th>Fecha Pago</th>
</tr>
</thead>
<tbody>
<?php if($resultCompras && $resultCompras->num_rows>0): ?>
    <?php while($row=$resultCompras->fetch_assoc()): ?>
    <tr>
        <td><?=$row['id_compra']?></td>
        <td><?=$row['id_pastel']?></td>
        <td><?=htmlspecialchars($row['nombre'])?></td>
        <td><?=htmlspecialchars($row['categoria'])?></td>
        <td><?=htmlspecialchars($row['fecha_pago'])?></td>
    </tr>
    <?php endwhile; ?>
<?php else: ?>
<tr><td colspan="5">No hay compras registradas</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</div>

<script>
// Mostrar tabla Pedidos o Compras
function mostrarTabla(tabla) {
    document.getElementById('pedidos').style.display = (tabla==='pedidos')?'block':'none';
    document.getElementById('compras').style.display = (tabla==='compras')?'block':'none';
    document.querySelectorAll('.toggle-btn').forEach(btn=>btn.classList.remove('active'));
    if(tabla==='pedidos') document.querySelectorAll('.toggle-btn')[0].classList.add('active');
    else document.querySelectorAll('.toggle-btn')[1].classList.add('active');
}

// Filtrado tabla por input
function filtrarTabla(input,tablaID){
    let filter = input.value.toLowerCase();
    let table = document.getElementById(tablaID);
    let tr = table.getElementsByTagName('tr');
    for(let i=1;i<tr.length;i++){
        tr[i].style.display = tr[i].innerText.toLowerCase().includes(filter)?'':'none';
    }
}

// Filtrado por columna (categorías)
function filtrarColumna(tablaID,colIndex,value){
    let table = document.getElementById(tablaID);
    let tr = table.getElementsByTagName('tr');
    for(let i=1;i<tr.length;i++){
        if(colIndex===null){ tr[i].style.display=''; continue; }
        tr[i].style.display = (tr[i].getElementsByTagName('td')[colIndex].innerText === value) ? '' : 'none';
    }
    let buttons = document.querySelectorAll('.filter-card');
    buttons.forEach(btn => {
        btn.classList.remove('active');
        if(btn.innerText === value) btn.classList.add('active');
    });
}
</script>
</body>
</html>

<?php $conn->close(); ?>
