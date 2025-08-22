<?php
// login.php
session_start();
require_once 'db.php'; // Asegúrate de que getPDO() está definido

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = trim($_POST['password'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !$password) {
        $errors[] = 'Por favor complete todos los campos.';
    } else {
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT id_users, password, name FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Login correcto
            session_regenerate_id(true);
            $_SESSION['usuario'] = [
                'id_users' => $user['id_users'],
                'name' => $user['name'] ?? '',
                'email' => $email
            ];

            // Redirección según el correo
            if ($email === 'adminchispitas@gmail.com') {
                header('Location: dashboard.php');
            } else {
                header('Location: dashboard_user.php'); // Redirigir usuario normal a cliente.php
            }
            exit;
        } else {
            $errors[] = 'Correo o contraseña incorrectos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dulce Acceso | Pastelería Elegante</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600&family=Montserrat:wght@300;400;500&display=swap');
        body { font-family: 'Montserrat', sans-serif; background-color: #faf6f2; }
        .title-font { font-family: 'Playfair Display', serif; }
        .login-container { box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08); }
        .input-field:focus { outline: none; box-shadow: 0 0 0 2px #f5c6d9; }
        .cake-decoration { animation: float 6s ease-in-out infinite; }
        @keyframes float { 0%,100%{transform:translateY(0);} 50%{transform:translateY(-15px);} }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-rose-50 to-amber-50">
    <div class="login-container relative flex flex-col md:flex-row w-full max-w-5xl bg-white rounded-3xl overflow-hidden">
        <!-- Lado Izquierdo -->
        <div class="w-full md:w-1/2 h-64 md:h-auto bg-gradient-to-br from-rose-200 to-amber-200 flex flex-col items-center justify-center p-8 relative overflow-hidden">
            <div class="absolute inset-0 opacity-20">
                <div class="absolute top-10 left-10 w-16 h-16 rounded-full bg-rose-300"></div>
                <div class="absolute bottom-20 right-20 w-24 h-24 rounded-full bg-amber-300"></div>
                <div class="absolute top-1/2 left-1/4 w-12 h-12 rounded-full bg-pink-100"></div>
            </div>
            <h1 class="title-font text-4xl md:text-5xl font-bold text-rose-600 mb-2 z-10">Chispitas De Amor</h1>
            <p class="text-amber-900/80 mb-6 text-center z-10">Donde cada bocado es una experiencia</p>
            <div class="cake-decoration z-10">
                <img src="../../Pastel/imagen/logo.png"
                     alt="Pastel" class="w-48 h-auto">
            </div>
        </div>

        <!-- Lado Derecho -->
        <div class="w-full md:w-1/2 p-10 flex flex-col justify-center">
            <h2 class="title-font text-3xl font-semibold text-gray-800 mb-2">Bienvenido de vuelta</h2>
            <p class="text-gray-600 mb-8">Inicia sesión para acceder a tu cuenta</p>

            <?php if ($errors): ?>
                <div class="mb-4">
                    <?php foreach ($errors as $err): ?>
                        <div class="text-red-600 text-sm">• <?= htmlspecialchars($err) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico</label>
                    <input type="email" id="email" name="email"
                        class="input-field w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-pink-300 transition duration-200"
                        placeholder="tu@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                    <input type="password" id="password" name="password"
                        class="input-field w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-pink-300 transition duration-200"
                        placeholder="••••••••">
                </div>

                <button type="submit"
                    class="w-full py-3 px-4 bg-gradient-to-r from-rose-400 to-amber-400 hover:from-rose-500 hover:to-amber-500 text-white font-medium rounded-lg shadow-md transition duration-200 transform hover:scale-105">
                    Iniciar sesión
                </button>

                <div class="text-center text-sm text-gray-500 mt-2">
                    ¿No tienes una cuenta?
                    <a href="registro.php" class="font-medium text-rose-500 hover:text-rose-600">Regístrate aquí</a>
                </div>
            </form>

            <div class="mt-8 pt-6 border-t border-gray-100 text-center">
                <p class="text-xs text-gray-500">© <?= date('Y') ?> Chispitas De Amor. Todos los derechos reservados.</p>
            </div>
        </div>
    </div>
</body>
</html>
