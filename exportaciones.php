<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Exportaciones</title>
    <link rel="stylesheet" href="css/esti.css">
    <script>
    function actualizarPrecioTotal() {
        var produccionSelect = document.getElementById("produccion_id");
        var produccion_id = produccionSelect.value;
        if (produccion_id !== "") {
            var precio_flor = parseFloat(produccionSelect.options[produccionSelect.selectedIndex].getAttribute('data-precio'));
            var cantidad_produccion = parseFloat(produccionSelect.options[produccionSelect.selectedIndex].getAttribute('data-cantidad'));
            var precio_total = precio_flor * cantidad_produccion;
            document.getElementById("precio_total").value = precio_total.toFixed(2);
        } else {
            document.getElementById("precio_total").value = "";
        }
    }
    
    </script>
</head>
<body>
    <br>
    <h2>Exportaciones</h2>
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
            <li><a href="facturacion.php">Facturación</a></li>
            <li><a href="logout.php">Salir</a></li>
        </ul>
    </nav>

    </header>
    <?php
session_start();

// Verifica si el usuario está autenticado
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

// Verifica el rol del usuario
$role = isset($_SESSION['role']) ? $_SESSION['role'] : null;
if ($role != 'usuario_admin' && $role != 'usuario_ventas') {
    echo "<div class='alert alert-error'>Acceso denegado.</div>";
    exit;
}

// Configuración de la conexión a la base de datos
$host = '10.241.0.55';
$db = 'flores';
$user = ($role == 'usuario_admin') ? 'usuario_admin' : 'usuario_ventas';
$password = ($role == 'usuario_admin') ? 'admin_1234' : 'ventas_1234';
$dsn = "pgsql:host=$host;port=55432;dbname=$db;user=$user;password=$password";

try {
    $pdo = new PDO($dsn);

    if ($pdo) {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (isset($_POST['update_id'])) {
                // Manejar la actualización
                $update_id = $_POST['update_id'];
                $produccion_id = isset($_POST['produccion_id']) ? $_POST['produccion_id'] : null;
                $fecha = isset($_POST['fecha']) ? $_POST['fecha'] : null;
                $destino_id = isset($_POST['destino_id']) ? $_POST['destino_id'] : null;
                $precio_total = isset($_POST['precio_total']) ? $_POST['precio_total'] : null;

                if ($produccion_id && $fecha && $destino_id && $precio_total) {
                    $sql_update = "UPDATE exportaciones SET produccion_id = :produccion_id, fecha = :fecha, destino_id = :destino_id, precio_total = :precio_total WHERE id = :id";
                    $stmt_update = $pdo->prepare($sql_update);
                    $stmt_update->execute([':produccion_id' => $produccion_id, ':fecha' => $fecha, ':destino_id' => $destino_id, ':precio_total' => $precio_total, ':id' => $update_id]);

                    echo "<div class='alert alert-success'>Exportación actualizada con éxito!</div>";
                } else {
                    echo "<div class='alert alert-error'>Error: Por favor, completa todos los campos del formulario.</div>";
                }
            } else {
                // Manejar la inserción
                $produccion_id = isset($_POST['produccion_id']) ? $_POST['produccion_id'] : null;
                $fecha = isset($_POST['fecha']) ? $_POST['fecha'] : null;
                $destino_id = isset($_POST['destino_id']) ? $_POST['destino_id'] : null;
                $precio_total = isset($_POST['precio_total']) ? $_POST['precio_total'] : null;

                if ($produccion_id && $fecha && $destino_id && !empty($precio_total) && is_numeric($precio_total)) {
                    // Insertar en la tabla exportaciones
                    $sql_insert = "INSERT INTO exportaciones (produccion_id, fecha, destino_id, precio_total) VALUES (:produccion_id, :fecha, :destino_id, :precio_total)";
                    $stmt_insert = $pdo->prepare($sql_insert);
                    $stmt_insert->execute([':produccion_id' => $produccion_id, ':fecha' => $fecha, ':destino_id' => $destino_id, ':precio_total' => $precio_total]);

                    // Actualizar el estado de la producción a "Exportado"
                    $sql_update = "UPDATE produccion SET estado = 'Exportado' WHERE id = :produccion_id";
                    $stmt_update = $pdo->prepare($sql_update);
                    $stmt_update->execute([':produccion_id' => $produccion_id]);

                    echo "<div class='alert alert-success'>Datos insertados con éxito!</div>";
                } else {
                    echo "<div class='alert alert-error'>Error: Precio total no válido.</div>";
                }
            }
        }

        // Manejo de edición
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_id'])) {
            $edit_id = $_POST['edit_id'];
            $sql = "SELECT * FROM exportaciones WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $edit_id]);
            $exportacion = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Gestión de Exportaciones</title>
            <link rel="stylesheet" href="esti.css">
        </head>
        <body>
            <div class="form-container">
                <h2><?php echo isset($exportacion) ? "Editar Exportación" : "Insertar Nueva Exportación"; ?></h2>
                <form method="post" action="">
                    <?php if (isset($exportacion)): ?>
                        <input type="hidden" name="update_id" value="<?php echo htmlspecialchars($exportacion['id']); ?>">
                    <?php endif; ?>
                    <label for="produccion_id">Producción:</label>
                    <select id="produccion_id" name="produccion_id" onchange="actualizarPrecioTotal()" required>
                        <?php
                        try {
                            $stmt = $pdo->query("SELECT produccion.id, flores.nombre AS flor, produccion.cantidad, flores.precio_unitario
                                                 FROM produccion
                                                 JOIN cosechas ON produccion.cosecha_id = cosechas.id
                                                 JOIN flores ON cosechas.flor_id = flores.id
                                                 WHERE produccion.estado = 'Para exportación'");
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                $selected = isset($exportacion) && $row['id'] == $exportacion['produccion_id'] ? 'selected' : '';
                                echo "<option value=\"" . htmlspecialchars($row['id']) . "\" data-precio=\"" . htmlspecialchars($row['precio_unitario']) . "\" data-cantidad=\"" . htmlspecialchars($row['cantidad']) . "\" $selected>" . htmlspecialchars($row['flor']) . " - " . htmlspecialchars($row['cantidad']) . " unidades</option>";
                            }
                        } catch (PDOException $e) {
                            echo "<option>Error: " . $e->getMessage() . "</option>";
                        }
                        ?>
                    </select>
                    <br>
                    <label for="fecha">Fecha:</label>
                    <input type="date" id="fecha" name="fecha" value="<?php echo isset($exportacion) ? htmlspecialchars($exportacion['fecha']) : ''; ?>" required>
                    <br>
                    <label for="destino_id">Destino:</label>
                    <select id="destino_id" name="destino_id" required>
                        <?php
                        try {
                            $stmt = $pdo->query("SELECT p.id, pi.nombre FROM pais p JOIN pais_info pi ON p.id = pi.pais_id");
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                $selected = isset($exportacion) && $row['id'] == $exportacion['destino_id'] ? 'selected' : '';
                                echo "<option value=\"" . htmlspecialchars($row['id']) . "\" $selected>" . htmlspecialchars($row['nombre']) . "</option>";
                            }
                        } catch (PDOException $e) {
                            echo "<option>Error: " . $e->getMessage() . "</option>";
                        }
                        ?>
                    </select>
                    <br>
                    <label for="precio_total">Precio Total:</label>
                    <input type="text" id="precio_total" name="precio_total" value="<?php echo isset($exportacion) ? htmlspecialchars($exportacion['precio_total']) : ''; ?>" readonly>
                    <br>
                    <input type="submit" value="<?php echo isset($exportacion) ? "Actualizar" : "Insertar"; ?>">
                </form>
            </div>

            <h2>Exportaciones Registradas</h2>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Destino</th>
                    <th>Cantidad</th>
                    <th>Flor</th>
                    <th>Precio Total</th>
                    <th>Acciones</th>
                </tr>
                <?php
                $stmt = $pdo->query("SELECT ex.id, ex.fecha, pi.nombre AS pais_destino, pr.cantidad, fl.nombre AS flor, ex.precio_total
                                    FROM exportaciones ex
                                    JOIN produccion pr ON ex.produccion_id = pr.id
                                    JOIN cosechas co ON pr.cosecha_id = co.id
                                    JOIN flores fl ON co.flor_id = fl.id
                                    JOIN pais p ON ex.destino_id = p.id
                                    JOIN pais_info pi ON p.id = pi.pais_id");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['fecha']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['pais_destino']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['cantidad']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['flor']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['precio_total']) . "</td>";
                    echo "<td>";
                    echo "<div class='action-buttons'>";
                    echo "<form method=\"post\" action=\"\" style=\"display:inline-block;\">";
                    echo "<input type=\"hidden\" name=\"edit_id\" value=\"" . htmlspecialchars($row['id']) . "\">";
                    echo "<input type=\"submit\" value=\"Editar\">";
                    echo "</form>";
                    echo "</div>";
                    echo "</td>";
                    echo "</tr>";
                }
                ?>
            </table>

            <script>
                function actualizarPrecioTotal() {
                    var select = document.getElementById('produccion_id');
                    var option = select.options[select.selectedIndex];
                    var precio = option.getAttribute('data-precio');
                    var cantidad = option.getAttribute('data-cantidad');
                    var precioTotal = precio * cantidad;
                    document.getElementById('precio_total').value = precioTotal.toFixed(2);
                }
                document.addEventListener('DOMContentLoaded', function () {
                    if (document.getElementById('produccion_id')) {
                        actualizarPrecioTotal();
                    }
                });
            </script>
        </body>
        </html>
        <?php
    }
} catch (PDOException $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>


</body>
</html>
