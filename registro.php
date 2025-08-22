<?php
require_once 'db.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $password = trim($_POST['password'] ?? '');
    $name = trim($_POST['name'] ?? '');

    if (!$email) $errors[] = 'Correo inválido.';
    if (strlen($password) < 6) $errors[] = 'La contraseña debe tener al menos 6 caracteres.';

    if (empty($errors)) {
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT id_users FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'El correo ya está registrado.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $insert = $pdo->prepare("INSERT INTO users (email, password, name) VALUES (?, ?, ?)");
            $insert->execute([$email, $hash, $name]);
            $success = true;
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Registro - Sweet Éclairs</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center bg-rose-50">
  <div class="w-full max-w-md bg-white p-8 rounded-xl shadow">
    <h1 class="text-2xl font-semibold mb-4">Crear cuenta</h1>

    <?php if ($success): ?>
      <div class="p-3 mb-4 bg-green-100 text-green-800 rounded">Registro exitoso. Puedes <a class="underline" href="Index.php">iniciar sesión</a>.</div>
    <?php endif; ?>

    <?php if ($errors): ?>
      <div class="mb-4">
        <?php foreach ($errors as $err): ?>
          <div class="text-red-600 text-sm">• <?php echo htmlspecialchars($err); ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="post" class="space-y-4">
      <div>
        <label class="block text-sm">Nombre (opcional)</label>
        <input name="name" class="w-full px-3 py-2 border rounded" value="<?php echo htmlspecialchars($name ?? ''); ?>">
      </div>
      <div>
        <label class="block text-sm">Correo</label>
        <input name="email" type="email" required class="w-full px-3 py-2 border rounded" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
      </div>
      <div>
        <label class="block text-sm">Contraseña</label>
        <input name="password" type="password" required class="w-full px-3 py-2 border rounded">
      </div>

      <button class="w-full py-2 bg-rose-500 text-white rounded">Registrarme</button>
    </form>

    <p class="mt-4 text-sm text-gray-600">¿Ya tienes cuenta? <a href="Index.php" class="text-rose-600 underline">Inicia sesión</a></p>
  </div>
</body>
</html>
