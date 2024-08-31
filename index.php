
<?php
session_start();

// Verifica si el usuario está autenticado
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

// Verifica el rol del usuario
$role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio </title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/estilos.css">
   
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom fixed-top">
        <a class="navbar-brand" href="#">Gestión de Flores</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="flores.php">Flores</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="cosechas.php">Cosechas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="produccion.php">Producción</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="exportaciones.php">Exportaciones</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="empleados.php">Empleados</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="facturacion.php">Facturación</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Top content -->
    <div class="top-content">
        <!-- Carousel -->
        <div id="carousel-example" class="carousel slide" data-ride="carousel" data-interval="3000">
            <ol class="carousel-indicators">
                <li data-target="#carousel-example" data-slide-to="0" class="active"></li>
                <li data-target="#carousel-example" data-slide-to="1"></li>
                <li data-target="#carousel-example" data-slide-to="2"></li>
                <li data-target="#carousel-example" data-slide-to="3"></li>
            </ol>
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="f1.jpeg" class="d-block w-100" alt="slide-img-1">
                    <div class="carousel-caption">
                        <h1>FloraExport</h1>
                        <div class="carousel-caption-description">
                            <p>Flores de calidad, cosechadas con pasión.</p>
                        </div>
                    </div>
                </div>
                <div class="carousel-item">
                    <img src="f2.jpeg" class="d-block w-100" alt="slide-img-2">
                    <div class="carousel-caption">
                        <h3>FloraExport</h3>
                        <div class="carousel-caption-description">
                            <p>Naturaleza en cada pétalo, exportación de excelencia.</p>
                        </div>
                    </div>
                </div>
                <div class="carousel-item">
                    <img src="f3.jpeg" class="d-block w-100" alt="slide-img-3">
                    <div class="carousel-caption">
                        <h3>FloraExport</h3>
                        <div class="carousel-caption-description">
                            <p>Conectando el mundo a través de la belleza floral.</p>
                        </div>
                    </div>
                </div>
                <div class="carousel-item">
                    <img src="f4.jpeg" class="d-block w-100" alt="slide-img-4">
                    <div class="carousel-caption">
                        <h3>FloraExport</h3>
                        <div class="carousel-caption-description">
                            <p>Desde nuestros campos, flores que inspiran al mundo.</p>
                        </div>
                    </div>
                </div>
            </div>
            <a class="carousel-control-prev" href="#carousel-example" role="button" data-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="sr-only">Previous</span>
            </a>
            <a class="carousel-control-next" href="#carousel-example" role="button" data-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="sr-only">Next</span>
            </a>
        </div>
        <!-- End carousel -->
    </div>

    <!-- Include jQuery and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.6.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/noty@3.1.4/lib/noty.js"></script>

<script>
Swal.fire({
  title: '¡Éxito!',
  text: 'La operación se realizó correctamente.',
  icon: 'success',
  confirmButtonText: 'Aceptar'
});
toastr.success('La operación se realizó correctamente', 'Éxito');
new Noty({
  text: 'La operación se realizó correctamente',
  type: 'success',
  layout: 'topRight',
  timeout: 3000
}).show();

</script>
</body>
</html>