<?php
//=========================================================
// CoDevPro Technology
// ajax/registrar_metodo_pago.php
//=========================================================

session_start();

header("Content-Type: application/json; charset=utf-8");

require_once "../controladores/conexion.php";

/*=========================================================
=            VALIDAR SESIÓN
=========================================================*/

if (!isset($_SESSION["idUser"])) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "La sesión ha expirado."
    ]);

    exit;
}

$idUser = (int)$_SESSION["idUser"];

/*=========================================================
=            LEER JSON
=========================================================*/

$input = json_decode(file_get_contents("php://input"), true);

if (!$input) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Datos inválidos."
    ]);

    exit;
}

$nombre = trim($input["nombre"] ?? "");

/*=========================================================
=            VALIDACIONES
=========================================================*/

if ($nombre == "") {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Ingrese el nombre del método de pago."
    ]);

    exit;
}

if (mb_strlen($nombre) > 100) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "El nombre es demasiado largo."
    ]);

    exit;
}

/* Eliminar espacios dobles */

$nombre = preg_replace('/\s+/', ' ', $nombre);

/*=========================================================
=            CONEXIÓN
=========================================================*/

mysqli_begin_transaction($conexion);

try {

    /*=====================================================
    =            VALIDAR DUPLICADOS
    =====================================================*/

    $sql = "SELECT id_metodo_pago
            FROM metodo_pago
            WHERE id_user = ?
            AND Eliminado = 0
            AND UPPER(TRIM(nombre)) = UPPER(TRIM(?))
            LIMIT 1";

    $stmt = mysqli_prepare($conexion, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "is",
        $idUser,
        $nombre
    );

    mysqli_stmt_execute($stmt);

    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {

        mysqli_rollback($conexion);

        echo json_encode([
            "estado" => false,
            "mensaje" => "Ya existe un método de pago con ese nombre."
        ]);

        exit;
    }

    mysqli_stmt_close($stmt);

    /*=====================================================
    =            REGISTRAR
    =====================================================*/

    $sql = "INSERT INTO metodo_pago
            (
                id_user,
                nombre,
                Eliminado
            )
            VALUES
            (
                ?, ?, 0
            )";

    $stmt = mysqli_prepare($conexion, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "is",
        $idUser,
        $nombre
    );

    if (!mysqli_stmt_execute($stmt)) {

        throw new Exception(mysqli_error($conexion));
    }

    $idMetodo = mysqli_insert_id($conexion);

    mysqli_commit($conexion);

    echo json_encode([
        "estado" => true,
        "mensaje" => "Método de pago registrado correctamente.",
        "id"      => $idMetodo
    ]);
} catch (Exception $e) {

    mysqli_rollback($conexion);

    echo json_encode([
        "estado" => false,
        "mensaje" => "No fue posible registrar el método de pago.",
        "error"   => $e->getMessage()
    ]);
} finally {

    if (isset($stmt) && $stmt) {
        mysqli_stmt_close($stmt);
    }

    mysqli_close($conexion);
}
