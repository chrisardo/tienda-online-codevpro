<?php
session_start();

require_once "controladores/obtener_detalle_producto.php";
?>
<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title><?= htmlspecialchars($producto['nombre']); ?> | CoDevPro Technology</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css" rel="stylesheet">

</head>

<body class="bg-light">

<?php include "includes/topbar.php"; ?>

<?php include "includes/navbar.php"; ?>

<div class="container py-5">

<?php include "includes/detalle_producto.php"; ?>

</div>
<?php include "includes/carrito_offcanvas.php"; ?>

<?php include "includes/footer.php"; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

<script src="js/producto.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="js/carrito.js"></script>
</body>

</html>