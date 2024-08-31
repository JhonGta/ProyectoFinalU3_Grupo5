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

    // Procesar el formulario
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $pdo->beginTransaction(); // Iniciar la transacción

        try {
            $exportacion_id = $_POST['exportacion_id'];
            $fecha = $_POST['fecha'];
            $cliente_nombre = $_POST['cliente_nombre'];
            $cliente_cedula_ruc = $_POST['cliente_cedula_ruc'];
            $cliente_direccion = $_POST['cliente_direccion'];
            $cliente_correo = $_POST['cliente_correo'];
            $seguro = $_POST['seguro'];
            $costo_exportacion = $_POST['costo_exportacion'];
            $metodo_pago_id = $_POST['metodo_pago'];
            $iva = $_POST['iva'];
            $total = $_POST['total'];
            $subtotal = $_POST['subtotal'];

            // Insertar cliente
            $sql_cliente = "INSERT INTO clientes (nombre, cedula_ruc, direccion, correo_electronico) VALUES (:nombre, :cedula_ruc, :direccion, :correo_electronico) RETURNING id";
            $stmt_cliente = $pdo->prepare($sql_cliente);
            $stmt_cliente->execute([
                ':nombre' => $cliente_nombre,
                ':cedula_ruc' => $cliente_cedula_ruc,
                ':direccion' => $cliente_direccion,
                ':correo_electronico' => $cliente_correo
            ]);
            $cliente_id = $stmt_cliente->fetchColumn();

            // Insertar en la tabla facturacion
            $sql_facturacion = "INSERT INTO facturacion (exportacion_id, fecha, cliente_id, seguro, costo_exportacion, iva, total, monto, metodo_pago_id)
                                VALUES (:exportacion_id, :fecha, :cliente_id, :seguro, :costo_exportacion, :iva, :total, :monto, :metodo_pago_id)";
            $stmt_facturacion = $pdo->prepare($sql_facturacion);
            $stmt_facturacion->execute([
                ':exportacion_id' => $exportacion_id,
                ':fecha' => $fecha,
                ':cliente_id' => $cliente_id,
                ':seguro' => $seguro,
                ':costo_exportacion' => $costo_exportacion,
                ':iva' => $iva,
                ':total' => $total,
                ':monto' => $subtotal,
                ':metodo_pago_id' => $metodo_pago_id
            ]);

            $pdo->commit(); // Confirmar la transacción
            echo "<div class='alert alert-success'>Datos insertados con éxito!</div>";
        } catch (Exception $e) {
            $pdo->rollback(); // Revertir la transacción
            echo "<div class='alert alert-error'>Permisos insuficientes </div>";
        }
    }

    // Consultar datos de la tabla facturacion
    $facturacion = [];
    $sql = "SELECT f.id, f.exportacion_id, f.fecha, c.nombre AS cliente_nombre, f.seguro, f.costo_exportacion, f.iva, f.total, f.monto, mp.nombre AS metodo_pago
            FROM facturacion f
            JOIN clientes c ON f.cliente_id = c.id
            JOIN metodo_pago mp ON f.metodo_pago_id = mp.id";
    $stmt = $pdo->query($sql);
    $facturacion = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error en la conexión: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facturación</title>
    <link rel="stylesheet" href="css/esti.css">
</head>
<body>
    <header class="header">
        <div class="logo">
            <img src="img/logo.png" alt="Florería Elegante">
        </div>
        <nav class="nav">
            <ul>
                <li><a href="logout.php">Salir</a></li>
                <li><a href="inicio1.html">Inicio</a></li>
                <li><a href="flores.php">Flores</a></li>
                <li><a href="cosechas.php">Cosechas</a></li>
                <li><a href="produccion.php">Producción</a></li>
                <li><a href="exportaciones.php">Exportaciones</a></li>
                <li><a href="empleados.php">Empleados</a></li>
                <li><a href="facturacion.php">Facturación</a></li>
            </ul>
        </nav>
    </header>

    <div class="form-container">
        <h2>Insertar Nueva Facturación</h2>
        <form method="post" action="">
            <div class="form-group">
                <label for="exportacion_id">Exportación:</label>
                <select id="exportacion_id" name="exportacion_id" required class="form-control">
                    <?php
                    try {
                        $stmt = $pdo->query("SELECT exportaciones.id, produccion.cantidad, flores.precio_unitario, flores.nombre AS flor
                                            FROM exportaciones
                                            JOIN produccion ON exportaciones.produccion_id = produccion.id
                                            JOIN cosechas ON produccion.cosecha_id = cosechas.id
                                            JOIN flores ON cosechas.flor_id = flores.id");
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<option value=\"" . htmlspecialchars($row['id']) . "\" data-cantidad=\"" . htmlspecialchars($row['cantidad']) . "\" data-precio-unitario=\"" . htmlspecialchars($row['precio_unitario']) . "\">" . htmlspecialchars($row['flor']) . " - Cantidad: " . htmlspecialchars($row['cantidad']) . "</option>";
                        }
                    } catch (PDOException $e) {
                        echo "<option>Error: " . $e->getMessage() . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="fecha">Fecha:</label>
                <input type="date" id="fecha" name="fecha" required class="form-control">
            </div>
            <div class="form-group">
                <label for="cliente_nombre">Cliente Nombre:</label>
                <input type="text" id="cliente_nombre" name="cliente_nombre" required class="form-control">
            </div>
            <div class="form-group">
                <label for="cliente_cedula_ruc">Cliente Cédula/RUC:</label>
                <input type="text" id="cliente_cedula_ruc" name="cliente_cedula_ruc" required class="form-control">
            </div>
            <div class="form-group">
                <label for="cliente_direccion">Cliente Dirección:</label>
                <input type="text" id="cliente_direccion" name="cliente_direccion" class="form-control">
            </div>
            <div class="form-group">
                <label for="cliente_correo">Cliente Correo Electrónico:</label>
                <input type="email" id="cliente_correo" name="cliente_correo" class="form-control">
            </div>
            <div class="form-group">
                <label for="seguro">Seguro:</label>
                <input type="number" id="seguro" name="seguro" value="100" readonly class="form-control">
            </div>
            <div class="form-group">
                <label for="costo_exportacion">Costo de Envío de Exportación:</label>
                <input type="number" step="0.01" id="costo_exportacion" name="costo_exportacion" min="0.01" required oninput="calcularTotales()" class="form-control">
            </div>
            <div class="form-group">
                <label for="metodo_pago">Método de Pago:</label>
                <select id="metodo_pago" name="metodo_pago" required class="form-control">
                    <?php
                    try {
                        $stmt = $pdo->query("SELECT id, nombre FROM metodo_pago");
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<option value=\"" . htmlspecialchars($row['id']) . "\">" . htmlspecialchars($row['nombre']) . "</option>";
                        }
                    } catch (PDOException $e) {
                        echo "<option>Error: " . $e->getMessage() . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="iva">IVA:</label>
                <input type="number" step="0.01" id="iva" name="iva" readonly class="form-control">
            </div>
            <div class="form-group">
                <label for="total">Total:</label>
                <input type="number" step="0.01" id="total" name="total" readonly class="form-control">
            </div>
            <div class="form-group">
                <label for="subtotal">Subtotal:</label>
                <input type="number" step="0.01" id="subtotal" name="subtotal" readonly class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Insertar</button>
        </form>
    </div>

    <div class="table-container">
        <h2>Datos de Facturación</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Exportación</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Seguro</th>
                    <th>Costo Exportación</th>
                    <th>IVA</th>
                    <th>Total</th>
                    <th>Monto</th>
                    <th>Método de Pago</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($facturacion as $row) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td><?php echo htmlspecialchars($row['exportacion_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['fecha']); ?></td>
                        <td><?php echo htmlspecialchars($row['cliente_nombre']); ?></td>
                        <td><?php echo htmlspecialchars($row['seguro']); ?></td>
                        <td><?php echo htmlspecialchars($row['costo_exportacion']); ?></td>
                        <td><?php echo htmlspecialchars($row['iva']); ?></td>
                        <td><?php echo htmlspecialchars($row['total']); ?></td>
                        <td><?php echo htmlspecialchars($row['monto']); ?></td>
                        <td><?php echo htmlspecialchars($row['metodo_pago']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        function calcularTotales() {
            const exportacionSelect = document.getElementById('exportacion_id');
            const costoExportacion = parseFloat(document.getElementById('costo_exportacion').value) || 0;
            const seguro = parseFloat(document.getElementById('seguro').value) || 0;

            const selectedOption = exportacionSelect.options[exportacionSelect.selectedIndex];
            const cantidad = parseFloat(selectedOption.getAttribute('data-cantidad')) || 0;
            const precioUnitario = parseFloat(selectedOption.getAttribute('data-precio-unitario')) || 0;

            const monto = cantidad * precioUnitario;
            const subtotal = monto + seguro + costoExportacion;
            const iva = subtotal * 0.15;
            const total = subtotal + iva;

            document.getElementById('subtotal').value = subtotal.toFixed(2);
            document.getElementById('iva').value = iva.toFixed(2);
            document.getElementById('total').value = total.toFixed(2);
        }
    </script>
</body>
</html>
