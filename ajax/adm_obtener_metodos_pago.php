<?php
//=====================================================
// CoDevPro Technology
// ajax/adm_obtener_metodos_pago.php
//=====================================================

session_start();

header("Content-Type: application/json; charset=utf-8");

require_once "../controladores/conexion.php";


/*=====================================================
=            VALIDAR SESIÓN
=====================================================*/

$idUser = $_SESSION["idUser"] ?? 0;

if (!$idUser) {

    echo json_encode([
        "estado" => "error",
        "mensaje" => "Sesión no válida."
    ]);

    exit;
}


/*=====================================================
=            OBTENER MÉTODOS DE PAGO
=====================================================*/

$sql = "SELECT

            id_metodo_pago,
            nombre

        FROM metodo_pago

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

$resultado = mysqli_stmt_get_result($stmt);


/*=====================================================
=            RECORRER RESULTADOS
=====================================================*/

$metodos = [];

while ($fila = mysqli_fetch_assoc($resultado)) {

    $metodos[] = [

        "id"     => $fila["id_metodo_pago"],
        "nombre" => $fila["nombre"]

    ];
}


/*=====================================================
=            RESPUESTA
=====================================================*/

echo json_encode([
    "estado" => "ok",
    "metodos" => $metodos
]);

exit;
