<?php
session_start();

// Verificar autenticación
if (!isset($_SESSION['usuario'])) {
    header('Location: Index.php');
    exit;
}

// Verificar rol de administrador
if ($_SESSION['usuario']['email'] !== 'adminchispitas@gmail.com') {
    header('Location: acceso_denegado.php');
    exit;
}

// Datos de ejemplo para las estadísticas
$estadisticas = [
    'clientes_vip' => 12,
    'ventas_hoy' => 1250.75,
    'productos' => 45,
    'valoracion' => 4.8
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Panel Administrativo - Pasteleria Chispitas De Amor</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Montserrat:wght@300;400;500&display=swap');

  :root {
    --primary: #D4B8C7; /* Lila suave */
    --secondary: #E8D5C0; /* Beige cálido */
    --accent: #A78A7F; /* Café elegante */
    --light: #FAF7F5; 
    --dark: #4A3F3A;
    --text: #5A5350;
  }

  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Montserrat', sans-serif;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
  }

  body {
    display: flex;
    min-height: 100vh;
    background-color: var(--light);
    color: var(--text);
    background-image: url('https://images.unsplash.com/photo-1552689486-f6773047d19f?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    background-blend-mode: overlay;
    background-color: rgba(250, 247, 245, 0.9);
  }

  /* Sidebar izquierdo */
  .sidebar {
    width: 280px;
    background: rgba(212, 184, 199, 0.95);
    color: white;
    padding: 40px 0;
    position: fixed;
    left: 0;
    top: 0;
    height: 100vh;
    z-index: 100;
    border-right: 1px solid rgba(167, 138, 127, 0.3);
    backdrop-filter: blur(5px);
  }

  .sidebar-header {
    padding: 0 30px 30px;
    margin-bottom: 20px;
    border-bottom: 1px solid rgba(255,255,255,0.2);
    text-align: center;
  }

  .sidebar-header h2 {
    font-family: 'Playfair Display', serif;
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--dark);
    letter-spacing: 1px;
  }

  .sidebar-menu {
    list-style: none;
    padding: 0;
  }

  .sidebar-menu li {
    padding: 15px 30px;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    display: flex;
    align-items: center;
    gap: 15px;
    color: var(--dark);
  }

  .sidebar-menu li::after {
    content: '';
    position: absolute;
    right: 0;
    top: 0;
    width: 3px;
    height: 100%;
    background-color: var(--accent);
    transform: scaleY(0);
    transition: transform 0.3s ease;
  }

  .sidebar-menu li:hover {
    background-color: rgba(255,255,255,0.3);
  }

  .sidebar-menu li:hover::after {
    transform: scaleY(1);
  }

  .sidebar-menu li.active {
    background-color: rgba(255,255,255,0.4);
  }

  .sidebar-menu li.active::after {
    transform: scaleY(1);
  }

  .sidebar-menu li i {
    font-size: 1.1rem;
    color: var(--accent);
    transition: transform 0.3s ease;
  }

  .sidebar-menu li:hover i {
    transform: scale(1.1);
    color: var(--dark);
  }

  /* Main Content */
  .main-content {
    flex: 1;
    padding: 40px;
    margin-left: 280px;
    transition: all 0.3s ease;
    background-color: rgba(250, 247, 245, 0.85);
    backdrop-filter: blur(3px);
  }

  .welcome-section {
    background-color: rgba(255,255,255,0.8);
    border-radius: 12px;
    padding: 40px;
    margin-bottom: 40px;
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(167, 138, 127, 0.2);
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
  }

  .welcome-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 6px;
    background: linear-gradient(90deg, var(--primary), var(--secondary));
  }

  .welcome-section h1 {
    font-family: 'Playfair Display', serif;
    color: var(--dark);
    font-size: 2.4rem;
    margin-bottom: 20px;
    font-weight: 700;
  }

  .welcome-section p {
    font-size: 1.1rem;
    line-height: 1.7;
    color: var(--text);
    max-width: 800px;
  }

  .pastry-image {
    float: right;
    width: 200px;
    height: 200px;
    object-fit: cover;
    border-radius: 12px;
    margin-left: 30px;
    margin-bottom: 20px;
    border: 1px solid rgba(167, 138, 127, 0.3);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
  }

  .stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 25px;
  }

  .stat-card {
    background-color: rgba(255,255,255,0.8);
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    border: 1px solid rgba(167, 138, 127, 0.2);
    position: relative;
    overflow: hidden;
    text-align: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }

  .stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
  }

  .stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 5px;
    background: linear-gradient(90deg, var(--primary), var(--secondary));
  }

  .stat-card h3 {
    color: var(--accent);
    font-size: 1rem;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
  }

  .stat-card .value {
    font-family: 'Playfair Display', serif;
    font-size: 2.5rem;
    color: var(--dark);
    font-weight: bold;
  }

  @media (max-width: 992px) {
    body {
      flex-direction: column;
      background-attachment: scroll;
    }
    
    .main-content {
      width: 100%;
      padding: 30px;
      margin-left: 0;
    }
    
    .sidebar {
      position: relative;
      width: 100%;
      height: auto;
      border-right: none;
      backdrop-filter: none;
    }
    
    .sidebar-menu {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
    }
    
    .sidebar-menu li {
      flex: 1 1 150px;
      justify-content: center;
      padding: 12px 15px;
    }
    
    .welcome-section {
      padding: 30px;
    }
    
    .pastry-image {
      float: none;
      display: block;
      margin: 0 auto 20px;
      width: 150px;
      height: 150px;
    }
  }

  @media (max-width: 576px) {
    .stats-grid {
      grid-template-columns: 1fr;
    }
    
    .sidebar-menu li {
      flex: 1 1 120px;
    }
    
    .welcome-section h1 {
      font-size: 2rem;
    }
  }
</style>  
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
  <!-- Sidebar a la izquierda -->
  <div class="sidebar">
    <div class="sidebar-header">
      <h2>Pasteleria Chispitas</h2>
    </div>
    <ul class="sidebar-menu">
      <li class="active"><i class="fas fa-home"></i> Inicio</li>
      <li><i class="fas fa-address-book"></i> Clientes</li>
      <li><i class="fas fa-receipt"></i> Alertas</li>
      <li><i class="fas fa-warehouse"></i> Inventario</li>
      <li><i class="fas fa-utensils"></i> Estado De Pedidos</li>
      <li><i class="fas fa-chart-pie"></i> Reportes</li>
      <li><i class="fas fa-database"></i> Respaldos</li>
    </ul>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <div class="welcome-section">
      <img src="https://images.unsplash.com/photo-1562440499-64c9a111f713?ixlib=rb-1.2.1&auto=format&fit=crop&w=200&q=80" alt="Pastelería elegante" class="pastry-image">
      <h1>Bienvenido a Pasteleria Chispitas De Amor</h1>
      <p>Gestiona tu pastelería gourmet con nuestra sofisticada herramienta administrativa. Diseñada para profesionales que valoran la excelencia en repostería fina y atención al detalle.</p>
      
      <?php if(isset($_SESSION['usuario'])): ?>
        <p style="margin-top: 15px; font-size: 0.9rem; color: var(--accent);">
          <i class="fas fa-user"></i> Sesión iniciada como: <?php echo htmlspecialchars($_SESSION['usuario']['name']); ?>
          (<?php echo htmlspecialchars($_SESSION['usuario']['email']); ?>)
        </p>
      <?php endif; ?>
    </div>
    <div class="stats-grid">
      <div class="stat-card">
        <h3><i class="fas fa-user-tie"></i> Clientes VIP</h3>
        <div class="value"><?php echo $estadisticas['clientes_vip']; ?></div>
      </div>
      <div class="stat-card">
        <h3><i class="fas fa-euro-sign"></i> Ventas Hoy</h3>
        <div class="value">$<?php echo number_format($estadisticas['ventas_hoy'], 2); ?></div>
      </div>
      <div class="stat-card">
        <h3><i class="fas fa-birthday-cake"></i> Productos</h3>
        <div class="value"><?php echo $estadisticas['productos']; ?></div>
      </div>
      <div class="stat-card">
        <h3><i class="fas fa-star"></i> Valoración</h3>
        <div class="value"><?php echo $estadisticas['valoracion']; ?>/5</div>
      </div>
    </div>
  </div>

  <script>
    // Script mejorado para las redirecciones
    document.querySelectorAll('.sidebar-menu li').forEach(item => {
        item.addEventListener('click', function() {
            // Mapa de páginas con nombres de archivo correctos
            const pageMap = {
                'fa-home': 'Index.php',
                'fa-address-book': 'cliente.php',
                'fa-receipt': 'alertas.php',
                'fa-warehouse': 'inventario.php',
                'fa-utensils': 'estado_de_pedidos.php',
                'fa-chart-pie': 'reporte.php',
                'fa-database': 'respaldos.php'
            };
            
            const iconClass = this.querySelector('i').className.split(' ')[1];
            if(pageMap[iconClass]) {
                window.location.href = pageMap[iconClass];
            }
        });
    });
  </script>
</body>
</html>