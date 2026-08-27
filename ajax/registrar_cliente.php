<?php
//=========================================================
// CoDevPro Technology
// ajax/registrar_cliente.php
//=========================================================

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once "../controladores/conexion.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

/*=========================================================
VALIDAR SESIÓN
=========================================================*/

if (!isset($_SESSION["idUser"])) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Sesión expirada."
    ]);

    exit();
}

$idUser = (int)$_SESSION["idUser"];

/*=========================================================
SOLO POST
=========================================================*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Método no permitido."
    ]);

    exit();
}

/*=========================================================
RECIBIR DATOS
=========================================================*/

$nombre          = trim($_POST["nombre"] ?? "");
$dni             = trim($_POST["dni_ruc"] ?? "");
$celular         = trim($_POST["celular"] ?? "");
$email           = strtolower(trim($_POST["email"] ?? ""));
$password        = $_POST["password"] ?? "";
$password2       = $_POST["confirmar_password"] ?? "";

$idRubro         = (int)($_POST["id_rubro"] ?? 0);
$direccion       = trim($_POST["direccion"] ?? "");

$idPais          = (int)($_POST["id_pais"] ?? 0);
$idDepartamento  = (int)($_POST["id_departamento"] ?? 0);
$idProvincia     = (int)($_POST["id_provincia"] ?? 0);
$idDistrito      = (int)($_POST["id_distrito"] ?? 0);

/*=========================================================
VALIDACIONES
=========================================================*/

if (strlen($nombre) < 3) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Ingrese un nombre válido."
    ]);

    exit();
}

if ($direccion == "") {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Ingrese la dirección."
    ]);

    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Correo electrónico inválido."
    ]);

    exit();
}

if (!ctype_digit($dni)) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "DNI/RUC inválido."
    ]);

    exit();
}

if (strlen($dni) != 8 && strlen($dni) != 11) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "El DNI debe tener 8 dígitos o el RUC 11."
    ]);

    exit();
}

if (!ctype_digit($celular)) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Celular inválido."
    ]);

    exit();
}

if (strlen($celular) != 9) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "El celular debe tener 9 dígitos."
    ]);

    exit();
}

if (strlen($password) < 6) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "La contraseña debe tener mínimo 6 caracteres."
    ]);

    exit();
}

if ($password !== $password2) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Las contraseñas no coinciden."
    ]);

    exit();
}

/*=========================================================
VALIDAR UBICACIÓN
=========================================================*/

$stmt = mysqli_prepare(
    $conexion,
    "SELECT id_pais
     FROM pais
     WHERE id_pais=? AND Eliminado=0"
);

mysqli_stmt_bind_param($stmt, "i", $idPais);
mysqli_stmt_execute($stmt);

if (!mysqli_stmt_get_result($stmt)->num_rows) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "País inválido."
    ]);

    exit();
}

mysqli_stmt_close($stmt);

/*=========================================================
DEPARTAMENTO
=========================================================*/

$stmt = mysqli_prepare(
    $conexion,
    "SELECT id_departamento
     FROM departamento
     WHERE id_departamento=?
     AND id_pais=?
     AND Eliminado=0"
);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $idDepartamento,
    $idPais
);

mysqli_stmt_execute($stmt);

if (!mysqli_stmt_get_result($stmt)->num_rows) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Departamento inválido."
    ]);

    exit();
}

mysqli_stmt_close($stmt);

/*=========================================================
PROVINCIA
=========================================================*/

$stmt = mysqli_prepare(
    $conexion,
    "SELECT id_provincia
     FROM provincia
     WHERE id_provincia=?
     AND id_departamento=?
     AND Eliminado=0"
);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $idProvincia,
    $idDepartamento
);

mysqli_stmt_execute($stmt);

if (!mysqli_stmt_get_result($stmt)->num_rows) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Provincia inválida."
    ]);

    exit();
}

mysqli_stmt_close($stmt);

/*=========================================================
DISTRITO
=========================================================*/

$stmt = mysqli_prepare(
    $conexion,
    "SELECT id_distrito
     FROM distrito
     WHERE id_distrito=?
     AND id_provincia=?
     AND Eliminado=0"
);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $idDistrito,
    $idProvincia
);

mysqli_stmt_execute($stmt);

if (!mysqli_stmt_get_result($stmt)->num_rows) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Distrito inválido."
    ]);

    exit();
}

mysqli_stmt_close($stmt);

/*=========================================================
VALIDAR RUBRO
=========================================================*/

if ($idRubro > 0) {

    $stmt = mysqli_prepare(
        $conexion,
        "SELECT id_rubro
         FROM rubros
         WHERE id_rubro=?
         AND Eliminado=0"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $idRubro
    );

    mysqli_stmt_execute($stmt);

    if (!mysqli_stmt_get_result($stmt)->num_rows) {

        echo json_encode([
            "estado" => false,
            "mensaje" => "Rubro inválido."
        ]);

        exit();
    }

    mysqli_stmt_close($stmt);
}

/*=========================================================
EMAIL REPETIDO
=========================================================*/

$stmt = mysqli_prepare(
    $conexion,
    "SELECT idCliente
     FROM clientes
     WHERE email=?
     AND Eliminado=0"
);

mysqli_stmt_bind_param(
    $stmt,
    "s",
    $email
);

mysqli_stmt_execute($stmt);

if (mysqli_stmt_get_result($stmt)->num_rows > 0) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Este correo ya existe."
    ]);

    exit();
}

mysqli_stmt_close($stmt);

/*=========================================================
DNI REPETIDO
=========================================================*/

$stmt = mysqli_prepare(
    $conexion,
    "SELECT idCliente
     FROM clientes
     WHERE dni_o_ruc=?
     AND Eliminado=0"
);

mysqli_stmt_bind_param(
    $stmt,
    "s",
    $dni
);

mysqli_stmt_execute($stmt);

if (mysqli_stmt_get_result($stmt)->num_rows > 0) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "El DNI/RUC ya existe."
    ]);

    exit();
}

mysqli_stmt_close($stmt);

/*=========================================================
IMAGEN
=========================================================*/

$imagen = null;

if (
    isset($_FILES["imagenCliente"]) &&
    $_FILES["imagenCliente"]["error"] === 0
) {

    $mime = mime_content_type(
        $_FILES["imagenCliente"]["tmp_name"]
    );

    $permitidos = [
        "image/jpeg",
        "image/png",
        "image/webp"
    ];

    if (!in_array($mime, $permitidos)) {

        echo json_encode([
            "estado" => false,
            "mensaje" => "Formato de imagen inválido."
        ]);

        exit();
    }

    if ($_FILES["imagenCliente"]["size"] > 2831155) {

        echo json_encode([
            "estado" => false,
            "mensaje" => "Máximo 2.7 MB."
        ]);

        exit();
    }

    $imagen = file_get_contents(
        $_FILES["imagenCliente"]["tmp_name"]
    );
}

/*=========================================================
REGISTRAR
=========================================================*/

$passwordHash = password_hash(
    $password,
    PASSWORD_DEFAULT
);

mysqli_begin_transaction($conexion);

try {

    $sql = "
    INSERT INTO clientes
    (
        nombre,
        dni_o_ruc,
        imagen,
        id_user,
        id_departamento,
        fecha_registro,
        id_distrito,
        id_provincia,
        direccion,
        email,
        contrasena,
        estado,
        celular,
        id_rubro,
        Eliminado,
        fecha_actualizado,
        id_pais
    )
    VALUES
    (
        ?,
        ?,
        ?,
        ?,
        ?,
        CURDATE(),
        ?,
        ?,
        ?,
        ?,
        ?,
        'ACTIVO',
        ?,
        ?,
        0,
        CURDATE(),
        ?
    )
    ";

    $stmt = mysqli_prepare($conexion, $sql);

    $blob = null;

    mysqli_stmt_bind_param(
        $stmt,
        "ssbiiiissssii",
        $nombre,
        $dni,
        $blob,
        $idUser,
        $idDepartamento,
        $idDistrito,
        $idProvincia,
        $direccion,
        $email,
        $passwordHash,
        $celular,
        $idRubro,
        $idPais
    );

    if ($imagen !== null) {
        mysqli_stmt_send_long_data(
            $stmt,
            2,
            $imagen
        );
    }

    mysqli_stmt_execute($stmt);

    $idCliente = mysqli_insert_id($conexion);

    mysqli_stmt_close($stmt);

    /*=========================================
IDIOMA POR DEFECTO
=========================================*/

    $idIdioma = 1;

    $sql = "SELECT id_idiomas
        FROM idiomas
        WHERE Eliminado = 0
        ORDER BY id_idiomas ASC
        LIMIT 1";

    $resultado = mysqli_query($conexion, $sql);

    if ($fila = mysqli_fetch_assoc($resultado)) {

        $idIdioma = (int)$fila["id_idiomas"];
    }

    /*=========================================
MONEDA POR DEFECTO
=========================================*/

    $idMoneda = 1;

    $sql = "SELECT id_moneda
        FROM monedas
        WHERE Eliminado = 0
        ORDER BY id_moneda ASC
        LIMIT 1";

    $resultado = mysqli_query($conexion, $sql);

    if ($fila = mysqli_fetch_assoc($resultado)) {

        $idMoneda = (int)$fila["id_moneda"];
    }
    /*=========================================
MÉTODO DE PAGO POR DEFECTO
=========================================*/

    $idMetodoPago = 0;

    $sql = "SELECT id_metodo_pago
        FROM metodo_pago
        WHERE Eliminado = 0
        ORDER BY id_metodo_pago ASC
        LIMIT 1";

    $resultado = mysqli_query($conexion, $sql);

    if ($fila = mysqli_fetch_assoc($resultado)) {

        $idMetodoPago = (int)$fila["id_metodo_pago"];
    }
    /*=========================================
PREFERENCIAS
=========================================*/

    $stmt = mysqli_prepare(
        $conexion,
        "INSERT INTO preferencias_cliente
    (
        idCliente,
        correo_promociones,
        estado_pedido,
        nuevos_productos,
        ofertas_flash,
        id_idiomas,
        id_moneda,
        id_metodo_pago,
        fecha_actualizado
    )
    VALUES
    (
        ?,
        1,
        1,
        1,
        1,
        ?,
        ?,
        ?,
        NOW()
    )"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "iiii",
        $idCliente,
        $idIdioma,
        $idMoneda,
        $idMetodoPago
    );

    mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);
    /* notificación */

    mysqli_query(
        $conexion,
        "INSERT INTO notificaciones_cliente
        (
            idCliente,
            titulo,
            mensaje,
            icono,
            color,
            leido,
            fecha,
            Eliminado,
            tipo
        )
        VALUES
        (
            {$idCliente},
            'Bienvenido',
            'Tu cuenta fue creada correctamente.',
            'bi-person-check-fill',
            'success',
            0,
            NOW(),
            0,
            'CUENTA'
        )"
    );

    mysqli_commit($conexion);

    echo json_encode([
        "estado" => true,
        "mensaje" => "Cliente registrado correctamente."
    ]);
} catch (Exception $e) {

    mysqli_rollback($conexion);

    echo json_encode([
        "estado" => false,
        "mensaje" => "Error: " . $e->getMessage()
    ]);
}

mysqli_close($conexion);
exit();
