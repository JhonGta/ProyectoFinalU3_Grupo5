<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Cosechas</title>
    <link rel="stylesheet" href="css/esti.css">
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
                <li><a href="facturacion.php">Facturación</a></li>
                <li><a href="logout.php">Salir</a></li>
            </ul>
        </nav>
    </header>

    <main>

        <?php
        session_start();

        // Verifica si el usuario está autenticado
        if (!isset($_SESSION['username'])) {
            header('Location: login.php');
            exit;
        }

        // Verifica el rol del usuario
        $role = isset($_SESSION['role']) ? $_SESSION['role'] : null;
        if ($role != 'usuario_admin' && $role != 'usuario_produccion') {
            echo "<p class='alert alert-error'>Acceso denegado.</p>";
            exit;
        }

        // Configuración de la conexión a la base de datos
        $host = '10.241.0.55';
        $db = 'flores';
        $user = ($role == 'usuario_admin') ? 'usuario_admin' : 'usuario_produccion';
        $password = ($role == 'usuario_admin') ? 'admin_1234' : 'produccion_1234';
        $dsn = "pgsql:host=$host;port=55432;dbname=$db;user=$user;password=$password";

        try {
            $pdo = new PDO($dsn);

            if ($pdo) {
                // Manejo de inserción
                if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['empleado_id']) && !isset($_POST['update_id'])) {
                    $empleado_id = $_POST['empleado_id'];
                    $fecha = $_POST['fecha'];
                    $cantidad = $_POST['cantidad'];
                    $flor_id = $_POST['flor_id'];

                    // Validación del lado del servidor
                    if ($cantidad < 0) {
                        echo "<p class='alert alert-error'>Error: La cantidad no puede ser negativa.</p>";
                    } else {
                        $sql = "INSERT INTO cosechas (empleado_id, fecha, cantidad, flor_id) VALUES (:empleado_id, :fecha, :cantidad, :flor_id)";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([':empleado_id' => $empleado_id, ':fecha' => $fecha, ':cantidad' => $cantidad, ':flor_id' => $flor_id]);

                        echo "<p class='alert alert-success'>Datos insertados con éxito!</p>";
                    }
                }

                // Manejo de eliminación
                if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
                    $delete_id = $_POST['delete_id'];

                    try {
                        // Eliminar la fila de cosechas
                        $sql = "DELETE FROM cosechas WHERE id = :id";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([':id' => $delete_id]);

                        echo "<p class='alert alert-success'>Cosecha eliminada con éxito!</p>";

                     } catch (PDOException $e) {
                        if ($e->getCode() == '23503') {
                            echo "<p class='alert alert-error'>Error: Esta flor no se puede eliminar debido a que está siendo utilizada en un proceso de producción.</p>";
                        } else {
                            echo "<p class='alert alert-error'>Error: " . $e->getMessage() . "</p>";
                        }
                    }
                }

                // Manejo de edición
                if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_id'])) {
                    $edit_id = $_POST['edit_id'];
                    $sql = "SELECT * FROM cosechas WHERE id = :id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([':id' => $edit_id]);
                    $cosecha = $stmt->fetch(PDO::FETCH_ASSOC);
                }

                if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_id'])) {
                    $update_id = $_POST['update_id'];
                    $empleado_id = $_POST['empleado_id'];
                    $fecha = $_POST['fecha'];
                    $cantidad = $_POST['cantidad'];
                    $flor_id = $_POST['flor_id'];

                    $sql = "UPDATE cosechas SET empleado_id = :empleado_id, fecha = :fecha, cantidad = :cantidad, flor_id = :flor_id WHERE id = :id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([':empleado_id' => $empleado_id, ':fecha' => $fecha, ':cantidad' => $cantidad, ':flor_id' => $flor_id, ':id' => $update_id]);

                    echo "<p class='alert alert-success'>Cosecha actualizada con éxito!</p>";
                    header("Location: cosechas.php");
                    exit();
                }

                // Mostrar la tabla de cosechas
                $stmt = $pdo->query("SELECT cosechas.id, empleados.nombre AS empleado, cosechas.fecha, cosechas.cantidad, flores.nombre AS flor FROM cosechas JOIN empleados ON cosechas.empleado_id = empleados.id JOIN flores ON cosechas.flor_id = flores.id");

                echo "<table>";
                echo "<tr><th>ID</th><th>Empleado</th><th>Fecha</th><th>Cantidad</th><th>Flor</th><th>Acciones</th></tr>";

                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['empleado']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['fecha']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['cantidad']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['flor']) . "</td>";
                    echo "<td>
                        <div class='action-buttons'>
                            <form method='post' action='cosechas.php' style='display:inline-block;'>
                                <input type='hidden' name='edit_id' value='" . htmlspecialchars($row['id']) . "'>
                                <input type='submit' value='Editar'>
                            </form>
                            <form method='post' action='cosechas.php' style='display:inline-block;'>
                                <input type='hidden' name='delete_id' value='" . htmlspecialchars($row['id']) . "'>
                                <input type='submit' value='Eliminar'>
                            </form>
                        </div>
                    </td>";
                    echo "</tr>";
                }

                echo "</table>";
            }
        } catch (PDOException $e) {
            echo "<p class='alert alert-error'>Error: " . $e->getMessage() . "</p>";
        }
        ?>

        <!-- Formulario de Edición -->
        <?php if (isset($cosecha)): ?>
        
        <div class="form-container">
        <h2>Editar Cosecha</h2>
            <form method="post" action="">
                <input type="hidden" name="update_id" value="<?php echo htmlspecialchars($cosecha['id']); ?>">
                <label for="empleado_id">Empleado:</label>
                <select id="empleado_id" name="empleado_id" required>
                    <?php
                    try {
                        $stmt = $pdo->query("SELECT id, nombre FROM empleados");
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $selected = $row['id'] == $cosecha['empleado_id'] ? 'selected' : '';
                            echo "<option value=\"" . htmlspecialchars($row['id']) . "\" $selected>" . htmlspecialchars($row['nombre']) . "</option>";
                        }
                    } catch (PDOException $e) {
                        echo "<option>Error: " . $e->getMessage() . "</option>";
                    }
                    ?>
                </select>
                <br>
                <label for="fecha">Fecha:</label>
                <input type="date" id="fecha" name="fecha" value="<?php echo htmlspecialchars($cosecha['fecha']); ?>" required>
                <br>
                <label for="cantidad">Cantidad:</label>
                <input type="number" id="cantidad" name="cantidad" value="<?php echo htmlspecialchars($cosecha['cantidad']); ?>" required>
                <br>
                <label for="flor_id">Flor:</label>
                <select id="flor_id" name="flor_id" required>
                    <?php
                    try {
                        $stmt = $pdo->query("SELECT id, nombre FROM flores");
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $selected = $row['id'] == $cosecha['flor_id'] ? 'selected' : '';
                            echo "<option value=\"" . htmlspecialchars($row['id']) . "\" $selected>" . htmlspecialchars($row['nombre']) . "</option>";
                        }
                    } catch (PDOException $e) {
                        echo "<option>Error: " . $e->getMessage() . "</option>";
                    }
                    ?>
                </select>
                <br>
                <input type="submit" value="Actualizar">
            </form>
        </div>
        <?php endif; ?>

        <!-- Formulario de Inserción -->
       
        <div class="form-container">
        <h2>Agregar Nueva Cosecha</h2>
            <form method="post" action="">
                <label for="empleado_id">Empleado:</label>
                <select id="empleado_id" name="empleado_id" required>
                    <?php
                    try {
                        $stmt = $pdo->query("SELECT id, nombre FROM empleados");
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<option value=\"" . htmlspecialchars($row['id']) . "\">" . htmlspecialchars($row['nombre']) . "</option>";
                        }
                    } catch (PDOException $e) {
                        echo "<option>Error: " . $e->getMessage() . "</option>";
                    }
                    ?>
                </select>
                <br>
                <label for="fecha">Fecha:</label>
                <input type="date" id="fecha" name="fecha" required>
                <br>
                <label for="cantidad">Cantidad:</label>
                <input type="number" id="cantidad" name="cantidad" required>
                <br>
                <label for="flor_id">Flor:</label>
                <select id="flor_id" name="flor_id" required>
                    <?php
                    try {
                        $stmt = $pdo->query("SELECT id, nombre FROM flores");
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<option value=\"" . htmlspecialchars($row['id']) . "\">" . htmlspecialchars($row['nombre']) . "</option>";
                        }
                    } catch (PDOException $e) {
                        echo "<option>Error: " . $e->getMessage() . "</option>";
                    }
                    ?>
                </select>
                <br>
                <input type="submit" value="Agregar">
            </form>
        </div>
    </main>

</body>
</html>
