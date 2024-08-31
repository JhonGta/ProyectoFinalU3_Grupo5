<?php
session_start();

// Configuración de la conexión a la base de datos
$host = '10.241.0.55';
$db = 'flores';
$user = 'Postgres1';
$password = '1234';
$dsn = "pgsql:host=$host;port=5432;dbname=$db;user=$user;password=$password";

try {
    $pdo = new PDO($dsn);

    // Obtener el nombre de usuario y la contraseña del formulario
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Consulta para verificar el usuario
    $stmt = $pdo->prepare('SELECT username, password, role FROM public.usuarios WHERE username = :username');
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verificar si el usuario existe y la contraseña es correcta
    if ($user && crypt($password, $user['password']) === $user['password']) {
        // Guardar los detalles del usuario en la sesión
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // Redirigir al usuario a la página principal o al dashboard
        header('Location: dashboard.php');
        exit;
    } else {
        echo '<p>Nombre de usuario o contraseña incorrectos.</p>';
    }
} catch (PDOException $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>
