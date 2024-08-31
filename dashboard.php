<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

echo "<h2>Bienvenido, " . htmlspecialchars($_SESSION['username']) . "!</h2>";
echo "<p>Rol: " . htmlspecialchars($_SESSION['role']) . "</p>";

// Mostrar contenido basado en el rol del usuario
if ($_SESSION['role'] == 'usuario_admin') {
    echo "<p>Acceso completo a todas las áreas.</p>";
    // Puedes incluir enlaces a todas las páginas aquí
} elseif ($_SESSION['role'] == 'usuario_ventas') {
    echo "<p>Acceso a ventas y clientes.</p>";
    // Incluye enlaces o contenido relevante para ventas
} elseif ($_SESSION['role'] == 'usuario_produccion') {
    echo "<p>Acceso a producción y flores.</p>";
    // Incluye enlaces o contenido relevante para producción
} else {
    echo "<p>Acceso denegado.</p>";
}
?>/* crontap */
