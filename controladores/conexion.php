<?php
//======================================================
// CoDevPro Technology
// controladores/conexion.php
//======================================================

mysqli_report(
    MYSQLI_REPORT_ERROR |
        MYSQLI_REPORT_STRICT
);

try {

    $conexion = new mysqli(
        "localhost",
        "root",
        "",
        "tienda_codevpro"
    );

    $conexion->set_charset(
        "utf8mb4"
    );
} catch (Exception $e) {

    die("Error de conexión: " .
        $e->getMessage());
}
