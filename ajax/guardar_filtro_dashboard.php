<?php
//=====================================================
// CoDevPro Technology
// ajax/guardar_filtro_dashboard.php
//=====================================================

session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $_SESSION["dashboard_fecha_inicio"] = $_POST["fecha_inicio"] ?? "";
    $_SESSION["dashboard_fecha_fin"]    = $_POST["fecha_fin"] ?? "";

    echo json_encode([
        "estado" => true
    ]);

    exit;
}
