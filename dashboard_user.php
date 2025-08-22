<?php
session_start();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel - Pastelería Chispitas De Amor</title>
    <style>
        :root {
            --primary-color: #e83f6f;
            --secondary-color: #ff8fab;
            --accent-color: #ffb3c6;
            --light-color: #ffdde4;
            --dark-color: #6d213c;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #fff9fb;
            color: #333;
        }
        
        .dashboard {
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: 100vh;
        }
        
        .sidebar {
            background: linear-gradient(to bottom, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 30px 20px;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .logo {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .logo img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid white;
        }
        
        .logo h2 {
            margin: 10px 0 0;
            font-size: 1.3rem;
        }
        
        .nav-menu {
            margin-top: 30px;
        }
        
        .nav-item {
            margin-bottom: 15px;
        }
        
        .nav-item a {
            display: flex;
            align-items: center;
            color: white;
            text-decoration: none;
            padding: 12px 15px;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .nav-item a:hover, .nav-item a.active {
            background-color: rgba(255, 255, 255, 0.2);
        }
        
        .nav-item i {
            margin-right: 10px;
            font-size: 1.2rem;
        }
        
        .main-content {
            padding: 30px;
        }
        
        .welcome-banner {
            background: linear-gradient(to right, var(--light-color), white);
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        
        .welcome-text h1 {
            color: var(--dark-color);
            margin: 0;
            font-size: 1.8rem;
        }
        
        .welcome-text p {
            color: var(--dark-color);
            margin: 5px 0 0;
            opacity: 0.8;
        }
        
        .user-profile {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .profile-info {
            text-align: right;
        }
        
        .user-name {
            font-weight: 600;
            margin: 0;
            color: var(--dark-color);
            font-size: 1rem;
        }
        
        .user-email {
            display: block;
            font-size: 0.8rem;
            opacity: 0.8;
            color: var(--dark-color);
        }
        
        .profile-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary-color);
        }
        
        /* Estilos para el carrusel */
        .featured-section {
            margin-top: 40px;
            position: relative;
            overflow: hidden;
        }
        
        .featured-section h2 {
            color: var(--dark-color);
            border-bottom: 2px solid var(--accent-color);
            padding-bottom: 10px;
            margin-bottom: 20px;
            font-size: 1.4em;
        }
        
        .carousel-container {
            position: relative;
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            overflow: hidden;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .carousel {
            display: flex;
            transition: transform 0.8s cubic-bezier(0.25, 0.1, 0.25, 1);
            height: 400px;
        }
        
        .slide {
            min-width: 100%;
            box-sizing: border-box;
            position: relative;
        }
        
        .slide-content {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.6);
            padding: 20px;
            color: white;
            transition: all 0.5s ease;
        }
        
        .slide img {
            width: 100%;
            height: 400px;
            object-fit: cover;
            display: block;
        }
        
        .cake-name {
            font-weight: bold;
            font-size: 1.8em;
            margin-bottom: 5px;
            color: var(--light-color);
        }
        
        .cake-description {
            font-size: 1em;
            max-width: 600px;
            margin: 0 auto;
            opacity: 0.9;
        }
        
        .indicators {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 10px;
        }
        
        .indicator {
            width: 15px;
            height: 15px;
            border-radius: 50%;
            background-color: #ccc;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .indicator.active {
            background-color: var(--primary-color);
            transform: scale(1.2);
        }
        
        .carousel-nav {
            position: absolute;
            top: 50%;
            width: 100%;
            display: flex;
            justify-content: space-between;
            transform: translateY(-50%);
            padding: 0 20px;
            box-sizing: border-box;
        }
        
        .carousel-btn {
            background: rgba(255, 255, 255, 0.7);
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 1.5em;
            color: var(--dark-color);
            transition: all 0.3s;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        
        .carousel-btn:hover {
            background: rgba(255, 255, 255, 0.9);
            transform: scale(1.1);
        }
        
        @media (max-width: 768px) {
            .dashboard {
                grid-template-columns: 1fr;
            }
            .sidebar {
                display: none;
            }
            .carousel {
                height: 300px;
            }
            .slide img {
                height: 300px;
            }
            .carousel-btn {
                width: 40px;
                height: 40px;
                font-size: 1.2em;
            }
            .cake-name {
                font-size: 1.4em;
            }
            .welcome-banner {
                flex-direction: column;
                text-align: center;
            }
            .user-profile {
                margin-top: 15px;
                justify-content: center;
            }
            .profile-info {
                text-align: center;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

    <div class="dashboard">
        <div class="sidebar">
            <div class="logo">
                <img src="../../Pastel/imagen/logo.png" alt="logo.png">
                <h2>Chispitas De Amor</h2>
            </div>
            <div class="nav-menu">
                <div class="nav-item"><a href="#" class="active"><i class="fas fa-home"></i><span>Inicio</span></a></div>
                <div class="nav-item"><a href="personalizados.php"><i class="btn-personalizados fas fa-magic"></i><span>Pasteles Personalizados</span></a></div>
                <div class="nav-item"><a href="pasteles_disponibles.php"><i class="btn-pasteles_disponibles fas fa-birthday-cake"></i><span>Pasteles Disponibles</span></a></div>
                <div class="nav-item"><a href="pedidos.php"><i class="btn-pedidos fas fa-clipboard-list"></i><span>Mis Pedidos</span></a></div>
                <div class="nav-item"><a href="favoritos.php"><i class="btn-favoritos fas fa-heart"></i><span>Favoritos</span></a></div>
                <div class="nav-item"><a href="Index.php"><i class="fas fa-sign-out-alt"></i><span>Cerrar Sesión</span></a></div>
            </div>
        </div>
        <div class="main-content">
            <div class="welcome-banner">
                <div class="welcome-text">
                    <h1>¡Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario']['name'] ?? 'Usuario'); ?>!</h1>
                    <p>Descubre nuestros deliciosos pasteles y crea tus propios diseños</p>
                </div>
                <div class="user-profile">
                    <div class="profile-info">
                        <p class="user-name"><?php echo htmlspecialchars($_SESSION['usuario']['name'] ?? 'Usuario'); ?></p>
                        <span class="user-email"><?php echo htmlspecialchars($_SESSION['usuario']['email']); ?></span>
                    </div>
                 <!--   <img src="assets/default-avatar.jpg" alt="Foto de perfil" class="profile-avatar"> -->
                </div>
            </div>

            <!-- Sección de pasteles destacados con carrusel -->
            <div class="featured-section">
                <h2>Pasteles Destacados</h2>
                <div class="carousel-container">
                    <div class="carousel" id="carousel">
                        <!-- Slide 1 -->
                        <div class="slide">
                            <img src="https://images.unsplash.com/photo-1552689486-f6773047d19f?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Pastel de Chocolate">
                            <div class="slide-content">
                                <div class="cake-name">Chocolate Supreme</div>
                                <div class="cake-description">Delicioso pastel de chocolate con capas de ganache y relleno de crema de avellanas</div>
                            </div>
                        </div>
                        <!-- Slide 2 -->
                        <div class="slide">
                            <img src="https://images.unsplash.com/photo-1565958011703-44f9829ba187?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Pastel de Frutas">
                            <div class="slide-content">
                                <div class="cake-name">Frutas Frescas</div>
                                <div class="cake-description">Bizcocho de vainilla con crema chantilly y decorado con frutas de temporada</div>
                            </div>
                        </div>
                        <!-- Slide 3 -->
                        <div class="slide">
                            <img src="https://images.unsplash.com/photo-1578985545062-69928b1d9587?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Pastel de Vainilla">
                            <div class="slide-content">
                                <div class="cake-name">Vainilla Clásica</div>
                                <div class="cake-description">Nuestro clásico pastel de vainilla con relleno de mermelada de fresa y buttercream</div>
                            </div>
                        </div>
                        <!-- Slide 4 -->
                        <div class="slide">
                            <img src="https://images.unsplash.com/photo-1588195538326-c5b1e9f80a1b?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Pastel de Red Velvet">
                            <div class="slide-content">
                                <div class="cake-name">Red Velvet</div>
                                <div class="cake-description">Bizcocho rojo aterciopelado con frosting de queso crema y decorado con frutos rojos</div>
                            </div>
                        </div>
                    </div>
                    <div class="carousel-nav">
                        <button class="carousel-btn" id="prevBtn"><i class="fas fa-chevron-left"></i></button>
                        <button class="carousel-btn" id="nextBtn"><i class="fas fa-chevron-right"></i></button>
                    </div>
                    <div class="indicators" id="indicators">
                        <!-- Los indicadores se generarán con JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const carousel = document.getElementById('carousel');
            const slides = document.querySelectorAll('.slide');
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            const indicatorsContainer = document.getElementById('indicators');
            let currentIndex = 0;
            let autoSlideInterval;
            const totalSlides = slides.length;
            
            // Crear indicadores
            for(let i = 0; i < totalSlides; i++) {
                const indicator = document.createElement('div');
                indicator.classList.add('indicator');
                if(i === 0) indicator.classList.add('active');
                indicator.addEventListener('click', () => moveToSlide(i));
                indicatorsContainer.appendChild(indicator);
            }
            
            const indicators = document.querySelectorAll('.indicator');
            
            // Función para mover el carrusel
            function moveToSlide(index) {
                currentIndex = index;
                carousel.style.transform = `translateX(-${currentIndex * 100}%)`;
                
                // Actualizar indicadores
                indicators.forEach((indicator, i) => {
                    if(i === currentIndex) {
                        indicator.classList.add('active');
                    } else {
                        indicator.classList.remove('active');
                    }
                });
            }
            
            // Función para avanzar al siguiente slide
            function nextSlide() {
                currentIndex = (currentIndex + 1) % totalSlides;
                moveToSlide(currentIndex);
            }
            
            // Función para retroceder al slide anterior
            function prevSlide() {
                currentIndex = (currentIndex - 1 + totalSlides) % totalSlides;
                moveToSlide(currentIndex);
            }
            
            // Event Listeners para los botones
            prevBtn.addEventListener('click', function() {
                prevSlide();
                resetAutoSlide();
            });
            
            nextBtn.addEventListener('click', function() {
                nextSlide();
                resetAutoSlide();
            });
            
            // Auto avanzar cada 5 segundos
            function startAutoSlide() {
                autoSlideInterval = setInterval(nextSlide, 5000);
            }
            
            function resetAutoSlide() {
                clearInterval(autoSlideInterval);
                startAutoSlide();
            }
            
            // Iniciar el carrusel automático
            startAutoSlide();
            
            // Iniciar con el primer slide
            moveToSlide(0);
        });
    </script>
</body>
</html>