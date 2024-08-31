<?php
session_start();

// Configuración de la conexión a la base de datos
$host = '10.241.0.55';
$db = 'flores';
$user = 'postgres';
$password = '1234';
$dsn = "pgsql:host=$host;port=55432;dbname=$db;user=$user;password=$password";

try {
    $pdo = new PDO($dsn);
    $error = '';

    // Procesar el formulario de inicio de sesión
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Consultar el usuario
        $stmt = $pdo->prepare("SELECT * FROM public.usuarios WHERE username = :username");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header('Location: inicio1.html');
            
            exit;
        } else {
            $error = 'Nombre de usuario o contraseña incorrectos.';
        }
    }
} catch (PDOException $e) {
    $error = "Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styl.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Login</title>
</head>
<body>
    <?php if (!empty($error)): ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '<?php echo $error; ?>'
            });
        </script>
    <?php endif; ?>

    <form method="post" action="">
        <h2>Iniciar sesión</h2>
        <br>
        <label for="username">Nombre de Usuario:</label>
        <input type="text" id="username" name="username" required>
        <br>
        <label for="password">Contraseña:</label>
        <input type="password" id="password" name="password" required>
        <br>
        <input type="submit" value="Iniciar sesión">
    </form>
</body>
</html>
