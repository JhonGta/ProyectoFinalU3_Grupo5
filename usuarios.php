<?php
session_start();

// Verifica si el usuario está autenticado
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

// Verifica el rol del usuario
$role = $_SESSION['role'];
if ($role != 'usuario_admin') {
    echo "<div class='alert alert-error'>Acceso denegado.</div>";
    exit;
}

// Configuración de la conexión a la base de datos
$host = '10.241.0.55';
$db = 'flores';
$user = 'postgres';
$password = '1234';
$dsn = "pgsql:host=$host;port=55432;dbname=$db;user=$user;password=$password";

try {
    $pdo = new PDO($dsn);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Procesar la creación y eliminación de usuarios
        if (isset($_POST['action'])) {
            if ($_POST['action'] == 'add') {
                // Agregar nuevo usuario
                $username = $_POST['username'];
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $role = $_POST['role'];

                $stmt = $pdo->prepare("INSERT INTO public.usuarios (username, password, role) VALUES (:username, :password, :role)");
                $stmt->execute([':username' => $username, ':password' => $password, ':role' => $role]);
            } elseif ($_POST['action'] == 'delete') {
                // Eliminar usuario
                $username = $_POST['username'];

                $stmt = $pdo->prepare("DELETE FROM public.usuarios WHERE username = :username");
                $stmt->execute([':username' => $username]);
            }
        }
    }

    // Obtener todos los usuarios
    $stmt = $pdo->query("SELECT * FROM public.usuarios");
} catch (PDOException $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Usuarios</title>
    <link rel="stylesheet" href="css/styleUser.css">
</head>
<body>
    <header class="header">
        <div class="logo">
            <img src="img/logo.png" alt="Florería Elegante">
        </div>
        <nav class="nav">
            <ul>
                <li><a href="inicio1.html">Inicio</a></li>
                <li><a href="flores.php">Flores</a></li>
                <li><a href="cosechas.php">Cosechas</a></li>
                <li><a href="produccion.php">Producción</a></li>
                <li><a href="exportaciones.php">Exportaciones</a></li>
                <li><a href="empleados.php">Empleados</a></li>
                <?php if ($role == 'usuario_admin') : ?>
                    <li><a href="usuarios.php">Usuarios</a></li>
                <?php endif; ?>
                <li><a href="logout.php">Salir</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <h2>Administrar Usuarios</h2>
        <form method="post" action="">
            <label for="username">Nombre de Usuario:</label>
            <input type="text" id="username" name="username" required>
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>
            <label for="role">Rol:</label>
            <select id="role" name="role">
                <option value="usuario_admin">Administrador</option>
                <option value="usuario_ventas">Ventas</option>
                <option value="usuario_produccion">Producción</option>
            </select>
            <input type="submit" name="action" value="add" />
        </form>

        <h3>Usuarios Existentes</h3>
        <table>
            <thead>
                <tr>
                    <th>Nombre de Usuario</th>
                    <th>Rol</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) : ?>
                    <tr>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td><?= htmlspecialchars($row['role']) ?></td>
                        <td>
                            <form method="post" action="" style="display:inline;">
                                <input type="hidden" name="username" value="<?= htmlspecialchars($row['username']) ?>">
                                <input type="submit" name="action" value="delete">
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
