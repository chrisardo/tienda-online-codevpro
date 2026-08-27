<?php
//=========================================================
// CoDevPro Technology
// ajax/obtener_departamentos_clientes.php
//=========================================================

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once "../controladores/conexion.php";

$idUser = $_SESSION["idUser"] ?? 0;

if (!$idUser) {

    echo json_encode([
        "ok" => false,
        "mensaje" => "Sesión inválida"
    ]);

    exit;
}

try {

    $departamentos = [];

    $sql = "SELECT
                id_departamento,
                nombre
            FROM departamento
            WHERE id_user = ?
            AND Eliminado = 0
            ORDER BY nombre ASC";

    $stmt = mysqli_prepare($conexion, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $idUser
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {

        $departamentos[] = [

            "id_departamento" => (int)$row["id_departamento"],
            "nombre"          => $row["nombre"]

        ];
    }

    echo json_encode([

        "ok" => true,

        "departamentos" => $departamentos

    ]);
} catch (Exception $e) {

    echo json_encode([

        "ok" => false,

        "mensaje" => $e->getMessage()

    ]);
}
