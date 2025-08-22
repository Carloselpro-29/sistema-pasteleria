<?php
// Conexión a la base de datos
$host = "localhost";
$usuario = "root";
$clave = "";
$bd = "prueba2";

$conn = new mysqli($host, $usuario, $clave, $bd);
if ($conn->connect_error) {
    die("Error en la conexión: " . $conn->connect_error);
}

// Obtener filtro y búsqueda
$categoriaFiltro = isset($_GET['categoria']) ? $_GET['categoria'] : 'Todos';
$busqueda = isset($_GET['buscar']) ? $conn->real_escape_string($_GET['buscar']) : '';

// Construir consulta SQL
$where = "1"; // siempre verdadero para concatenar condiciones

if ($categoriaFiltro !== 'Todos') {
    $where .= " AND categoria = '$categoriaFiltro'";
}

if ($busqueda !== '') {
    $where .= " AND nombre LIKE '%$busqueda%'";
}

$sql = "SELECT * FROM pasteles WHERE $where ORDER BY nombre";
$result = $conn->query($sql);

// Categorías para filtros
$categorias = ['Todos', 'Chocolate', 'Frutas', 'Tres Leches', 'Especiales', 'Sin Azúcar', 'Mini Pasteles'];

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Pasteles Disponibles - Dulce Tentación</title>
    <style>
        :root {
            --primary-color: #e83f6f;
            --secondary-color: #ff8fab;
            --light-color: #ffdde4;
            --dark-color: #6d213c;
            --success-color: #4CAF50;
            --warning-color: #FFC107;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background-color: #fff9fb;
            color: #333;
            margin: 0;
            padding: 0;
        }
        
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
            position: relative;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            position: relative;
        }
        
        .header h1 {
            color: var(--dark-color);
            margin-bottom: 10px;
        }
        
        .header p {
            color: var(--dark-color);
            opacity: 0.8;
        }
        
        /* Nuevo estilo para el botón de regresar */
        .back-btn {
            position: absolute;
            top: 0;
            right: 20px;
            padding: 8px 15px;
            background-color: var(--light-color);
            color: var(--dark-color);
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .back-btn:hover {
            background-color: var(--secondary-color);
            color: white;
        }
        
        .filter-bar {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 30px;
        }
        
        .filter-btn {
            padding: 10px 20px;
            border: 1px solid var(--primary-color);
            border-radius: 25px;
            background-color: white;
            color: var(--primary-color);
            cursor: pointer;
            transition: all 0.3s;
            font-weight: bold;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .filter-btn:hover, .filter-btn.active {
            background-color: var(--primary-color);
            color: white;
        }
        
        .search-bar {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }
        
        .search-input {
            width: 60%;
            padding: 12px 20px;
            border: 1px solid #ddd;
            border-radius: 25px 0 0 25px;
            font-size: 16px;
            outline: none;
        }
        
        .search-btn {
            padding: 12px 20px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 0 25px 25px 0;
            cursor: pointer;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .cakes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }
        
        .cake-card {
            background-color: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: all 0.3s;
            position: relative;
        }
        
        .cake-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        
        .cake-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .cake-info {
            padding: 15px;
        }
        
        .cake-title {
            font-size: 1.2rem;
            color: var(--dark-color);
            margin: 0 0 5px;
        }
        
        .cake-description {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 10px;
            min-height: 60px;
        }
        
        .cake-price {
            font-weight: bold;
            color: var(--primary-color);
            font-size: 1.1rem;
            margin-bottom: 15px;
        }
        
        .cake-actions {
            display: flex;
            justify-content: space-between;
        }
        
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 6px;
            justify-content: center;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover:not(:disabled) {
            background-color: var(--dark-color);
        }
        
        .btn-primary:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
        
        .btn-outline {
            background-color: transparent;
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
        }
        
        .btn-outline:hover {
            background-color: var(--light-color);
        }
        
        .badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
            color: white;
            text-transform: capitalize;
        }
        
        .badge-new {
            background-color: var(--success-color);
        }
        
        .badge-popular {
            background-color: var(--warning-color);
            color: #333;
        }
        
        .availability {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            font-size: 0.9rem;
        }
        
        .availability .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 5px;
        }
        
        .available {
            background-color: var(--success-color);
        }
        
        .limited {
            background-color: var(--warning-color);
        }
        
        .unavailable {
            background-color: #ccc;
        }
        
        @media (max-width: 768px) {
            .search-input {
                width: 70%;
            }
            
            .cakes-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
            
            /* Ajuste para móviles */
            .back-btn {
                position: relative;
                right: auto;
                margin: 0 auto 20px;
                display: block;
                width: fit-content;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
</head>
<body>
<div class="container">
    <!-- Botón de regresar agregado aquí -->
    <button class="back-btn" onclick="window.history.back();">
        <i class="fas fa-arrow-left"></i> Regresar
    </button>
    
    <div class="header">
        <h1><i class="fas fa-birthday-cake"></i> Pasteles Disponibles</h1>
        <p>Nuestra selección de pasteles listos para pedir</p>
    </div>
    
    <form method="GET" class="search-bar" style="justify-content:center; margin-bottom:30px;">
        <input type="text" name="buscar" class="search-input" placeholder="Buscar pasteles..." value="<?php echo htmlspecialchars($busqueda); ?>" />
        <button type="submit" class="search-btn"><i class="fas fa-search"></i> Buscar</button>
    </form>
    
    <div class="filter-bar">
        <?php foreach ($categorias as $cat): ?>
            <a href="?categoria=<?php echo urlencode($cat); ?>&buscar=<?php echo urlencode($busqueda); ?>" 
               class="filter-btn <?php echo ($categoriaFiltro == $cat) ? 'active' : ''; ?>">
               <?php echo htmlspecialchars($cat); ?>
            </a>
        <?php endforeach; ?>
    </div>
    
    <div class="cakes-grid">
        <?php
        if ($result && $result->num_rows > 0) {
            while ($cake = $result->fetch_assoc()) {
                // Badges
                $badgeHTML = '';
                if ($cake['badge'] == 'nuevo') {
                    $badgeHTML = '<div class="badge badge-new">Nuevo</div>';
                } elseif ($cake['badge'] == 'popular') {
                    $badgeHTML = '<div class="badge badge-popular">Más vendido</div>';
                } elseif ($cake['badge'] == 'sin azúcar') {
                    $badgeHTML = '<div class="badge" style="background:#888;">Sin azúcar</div>';
                }

                // Disponibilidad y texto
                $dotClass = 'unavailable';
                $dispText = 'No disponible';
                if ($cake['disponibilidad'] == 'disponible') {
                    $dotClass = 'available';
                    $dispText = 'Disponible hoy';
                } elseif ($cake['disponibilidad'] == 'limitado') {
                    $dotClass = 'limited';
                    $dispText = "Solo {$cake['stock']} disponibles";
                }
                
                // Botón habilitado o no
                $btnDisabled = ($cake['disponibilidad'] == 'no disponible') ? 'disabled' : '';
                if ($btnDisabled) {
                    $btnHTML = '<button class="btn btn-primary" disabled><i class="fas fa-clock"></i> Pre-ordenar</button>';
                } else {
                    $btnHTML = '<a href="pago.php?id=' . $cake['id_pastel'] . '" class="btn btn-primary"><i class="fas fa-shopping-cart"></i> Pedir ahora</a>';
                }

                echo '<div class="cake-card">';
                echo $badgeHTML;
                echo '<img src="' . htmlspecialchars($cake['imagen']) . '" alt="' . htmlspecialchars($cake['nombre']) . '" class="cake-img">';
                echo '<div class="cake-info">';
                echo '<h3 class="cake-title">' . htmlspecialchars($cake['nombre']) . '</h3>';
                echo '<div class="availability"><span class="dot ' . $dotClass . '"></span><span>' . $dispText . '</span></div>';
                echo '<p class="cake-description">' . htmlspecialchars($cake['descripcion']) . '</p>';
                echo '<p class="cake-price">$' . number_format($cake['precio'], 2) . ' USD</p>';
                echo '<div class="cake-actions">';
                echo $btnHTML;

                // >>> ÚNICO CAMBIO: botón de favoritos con data-id y clase 'fav-btn'
                echo '<button class="btn btn-outline fav-btn" data-id="' . $cake['id_pastel'] . '" aria-label="Agregar a favoritos"><i class="far fa-heart"></i></button>';

                echo '</div></div></div>';
            }
        } else {
            echo '<p>No se encontraron pasteles.</p>';
        }
        ?>
    </div>
</div>

<script>
    // Favoritos: guardamos SOLO el ID en localStorage
    function getFavoritos() {
        try { return JSON.parse(localStorage.getItem('favoritos')) || []; }
        catch(e) { return []; }
    }
    function setFavoritos(arr) {
        localStorage.setItem('favoritos', JSON.stringify(arr));
    }
    function toggleFavorito(id, btn) {
        let favoritos = getFavoritos();
        const idx = favoritos.indexOf(id);
        const icon = btn.querySelector('i');
        if (idx === -1) {
            favoritos.push(id);
            icon.classList.remove('far'); icon.classList.add('fas');
        } else {
            favoritos.splice(idx, 1);
            icon.classList.remove('fas'); icon.classList.add('far');
        }
        setFavoritos(favoritos);
    }

    // Inicializar corazones según favoritos guardados
    document.addEventListener('DOMContentLoaded', () => {
        const favoritos = getFavoritos();
        document.querySelectorAll('.fav-btn').forEach(btn => {
            const id = btn.dataset.id;
            if (favoritos.includes(id)) {
                const icon = btn.querySelector('i');
                icon.classList.remove('far');
                icon.classList.add('fas');
            }
            // Click en favorito
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                toggleFavorito(btn.dataset.id, btn);
            });
        });
    });
</script>
</body>
</html>
<?php
$conn->close();
?>





