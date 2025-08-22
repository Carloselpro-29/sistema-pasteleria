<?php
$host = "localhost";
$usuario = "root";
$clave = "";
$bd = "prueba2";

$conn = new mysqli($host, $usuario, $clave, $bd);
if ($conn->connect_error) die("Error en la conexión: " . $conn->connect_error);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mis Pasteles Favoritos</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
<style>
/* --- Mantener mismo CSS que pasteles disponibles --- */
:root {
    --primary-color: #e83f6f;
    --secondary-color: #ff8fab;
    --light-color: #ffdde4;
    --dark-color: #6d213c;
    --success-color: #4CAF50;
    --warning-color: #FFC107;
}
body { font-family: 'Arial', sans-serif; background-color: #fff9fb; color: #333; margin:0; padding:0; }
.container { max-width: 1200px; margin:30px auto; padding:20px; position: relative; }
.header { text-align:center; margin-bottom:30px; }
.header h1 { color: var(--dark-color); margin-bottom:10px; }
.header p { color: var(--dark-color); opacity:0.8; }
.cakes-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:25px; }
.cake-card { background-color:white; border-radius:15px; overflow:hidden; box-shadow:0 4px 8px rgba(0,0,0,0.1); transition: all 0.3s; position:relative; }
.cake-card:hover { transform:translateY(-5px); box-shadow:0 6px 12px rgba(0,0,0,0.15); }
.cake-img { width:100%; height:200px; object-fit:cover; }
.cake-info { padding:15px; }
.cake-title { font-size:1.2rem; color:var(--dark-color); margin:0 0 5px; }
.cake-description { color:#666; font-size:0.9rem; margin-bottom:10px; min-height:60px; }
.cake-price { font-weight:bold; color:var(--primary-color); font-size:1.1rem; margin-bottom:15px; }
.cake-actions { display:flex; justify-content:space-between; gap:10px; }
.btn-outline, .btn-primary { padding:10px 20px; border-radius:25px; cursor:pointer; font-weight:bold; display:flex; align-items:center; gap:8px; transition:all 0.3s; }
.btn-outline { background-color:transparent; color:var(--primary-color); border:1px solid var(--primary-color); }
.btn-outline:hover { background-color:var(--light-color); }
.btn-primary { background-color:var(--primary-color); color:white; border:none; text-decoration:none; justify-content:center; }
.btn-primary:hover { background-color:var(--dark-color); }
.empty-favorites { text-align:center; color:#999; margin-top:40px; }
.empty-favorites i { font-size:60px; margin-bottom:10px; color:#ddd; }

/* Nuevo estilo para el botón de regresar */
.back-btn {
    position: absolute;
    top: 0;
    left: 20px;
    padding: 10px 20px;
    background-color: var(--light-color);
    color: var(--dark-color);
    border: none;
    border-radius: 25px;
    cursor: pointer;
    font-weight: bold;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    text-decoration: none;
}

.back-btn:hover {
    background-color: var(--secondary-color);
    color: white;
    transform: translateY(-2px);
}

@media(max-width:768px){
    .cakes-grid{grid-template-columns:repeat(auto-fill,minmax(250px,1fr));}
    .back-btn {
        position: relative;
        left: auto;
        margin: 0 auto 20px;
        display: block;
        width: fit-content;
    }
}
</style>
</head>
<body>
<div class="container">
    <!-- Botón de regresar agregado -->
    <a href="dashboard_user.php" class="back-btn">
        <i class="fas fa-arrow-left"></i> Regresar
    </a>
    
    <div class="header">
        <h1><i class="fas fa-heart"></i> Mis Pasteles Favoritos</h1>
        <p>Guarda tus pasteles favoritos para comprarlos más tarde</p>
    </div>

    <div id="favorites-container" class="cakes-grid">
        <!-- Aquí se cargan los pasteles favoritos -->
    </div>

    <div id="empty-msg" class="empty-favorites" style="display:none;">
        <i class="fas fa-box-open"></i>
        <h3>Aún no tienes pasteles favoritos</h3>
        <p>Explora nuestra selección y guarda tus favoritos haciendo clic en el corazón ♥</p>
        <a href="pasteles_disponibles.php" class="btn btn-primary" style="width: fit-content; margin: 0 auto;"><i class="fas fa-birthday-cake"></i> Ver pasteles</a>
    </div>
</div>

<script>
// Obtener favoritos desde localStorage
let favoritos = JSON.parse(localStorage.getItem('favoritos')) || [];

const container = document.getElementById('favorites-container');
const emptyMsg = document.getElementById('empty-msg');

if(favoritos.length === 0){
    emptyMsg.style.display = 'block';
} else {
    emptyMsg.style.display = 'none';
    // Traer datos de cada pastel desde PHP usando AJAX (o pre-cargar)
    fetch('get_pasteles.php') // Creamos este PHP para devolver JSON con todos los pasteles
    .then(res=>res.json())
    .then(data=>{
        favoritos.forEach(id=>{
            const pastel = data.find(p=>p.id_pastel == id);
            if(pastel){
                const card = document.createElement('div');
                card.classList.add('cake-card');
                card.dataset.id = pastel.id_pastel;
                card.innerHTML = `
                    <img src="${pastel.imagen}" alt="${pastel.nombre}" class="cake-img">
                    <div class="cake-info">
                        <h3 class="cake-title">${pastel.nombre}</h3>
                        <p class="cake-description">${pastel.descripcion}</p>
                        <p class="cake-price">$${parseFloat(pastel.precio).toFixed(2)} USD</p>
                        <div class="cake-actions">
                            <a href="pago.php?id=${pastel.id_pastel}" class="btn btn-primary"><i class="fas fa-shopping-cart"></i> Pedir ahora</a>
                            <button class="btn btn-outline"><i class="fas fa-heart"></i> Quitar</button>
                        </div>
                    </div>
                `;
                container.appendChild(card);
            }
        });

        // Agregar funcionalidad de quitar favorito
        container.querySelectorAll('.btn-outline').forEach(btn=>{
            btn.addEventListener('click', e=>{
                e.preventDefault();
                const card = btn.closest('.cake-card');
                const id = card.dataset.id;
                favoritos = favoritos.filter(f=>f!=id);
                localStorage.setItem('favoritos', JSON.stringify(favoritos));
                card.remove();
                if(favoritos.length===0) emptyMsg.style.display='block';
            });
        });
    });
}
</script>
</body>
</html>
<?php $conn->close(); ?>

