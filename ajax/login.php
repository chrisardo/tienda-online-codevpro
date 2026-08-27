<?php
//Esta parte pertenece a ajax/login.php
session_start();

header("Content-Type: application/json; charset=UTF-8");

require_once "../controladores/conexion.php";
require_once "../controladores/token_carrito.php";
require_once "../controladores/notificaciones_cliente.php";
/*=========================================================
=            VALIDAR DATOS
=========================================================*/

if (
    empty($_POST["email"]) ||
    empty($_POST["contrasena"])
) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Complete todos los campos."
    ]);

    exit();
}

$email = trim($_POST["email"]);
$contrasena = $_POST["contrasena"];
/*=========================================================
=            BUSCAR ADMINISTRADOR (EMPRESA)
=========================================================*/

$sqlAdmin = "SELECT *

FROM usuario_acceso

WHERE email = ?
OR username = ?

LIMIT 1";

$stmtAdmin = mysqli_prepare(
    $conexion,
    $sqlAdmin
);

mysqli_stmt_bind_param(
    $stmtAdmin,
    "ss",
    $email,
    $email
);

mysqli_stmt_execute(
    $stmtAdmin
);

$resultAdmin = mysqli_stmt_get_result(
    $stmtAdmin
);


/*=========================================================
=            VALIDAR ADMINISTRADOR
=========================================================*/

if (mysqli_num_rows($resultAdmin) > 0) {

    $admin = mysqli_fetch_assoc(
        $resultAdmin
    );

    /*
    Verificar la contraseña.
    */

    if (!password_verify(
        $contrasena,
        $admin["contrasena"]
    )) {

        echo json_encode([

            "estado" => false,
            "mensaje" => "Correo o contraseña incorrectos."

        ]);

        exit();
    }


    /*=========================================
    CREAR SESIONES DEL ADMINISTRADOR
    =========================================*/

    $_SESSION["idUser"] = $admin["id_user"];

    $_SESSION["nombreEmpresa"] = $admin["nombreEmpresa"];

    $_SESSION["emailEmpresa"] = $admin["email"];

    $_SESSION["rolEmpresa"] = $admin["rol"];


    /*=========================================
    RESPUESTA
    =========================================*/

    echo json_encode([

        "estado" => true,

        "mensaje" => "Bienvenido " . $admin["nombreEmpresa"],

        "redireccion" => "admin_index.php"

    ]);

    exit();
}

mysqli_stmt_close(
    $stmtAdmin
);
/*=========================================================
=            BUSCAR CLIENTE
=========================================================*/

$sql = "SELECT *

FROM clientes

WHERE email=?

AND Eliminado=0

LIMIT 1";

$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "s",
    $email
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($resultado) == 0) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Correo o contraseña incorrectos."
    ]);

    exit();
}

$cliente = mysqli_fetch_assoc($resultado);

/*=========================================================
=            VERIFICAR CONTRASEÑA
=========================================================*/

if (!password_verify($contrasena, $cliente["contrasena"])) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Correo o contraseña incorrectos."
    ]);

    exit();
}

/*=========================================================
=            CREAR SESIÓN
=========================================================*/

$_SESSION["idCliente"] = $cliente["idCliente"];
$_SESSION["nombreCliente"] = $cliente["nombre"];
$_SESSION["emailCliente"] = $cliente["email"];
/*======================================================
=            NOTIFICACIÓN DE BIENVENIDA
======================================================*/

$idCliente = (int) $cliente["idCliente"];


/*======================================================
=            VERIFICAR SI YA EXISTE
======================================================*/

$sql = "

SELECT id_notificacion

FROM notificaciones_cliente

WHERE idCliente = ?
AND titulo = '¡Bienvenido a CoDevPro Technology!'

LIMIT 1

";


$stmt = mysqli_prepare(
    $conexion,
    $sql
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $idCliente
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);


/*======================================================
=            CREAR NOTIFICACIÓN
======================================================*/

if (mysqli_num_rows($resultado) === 0) {

    crearNotificacionCliente(

        $conexion,

        $idCliente,

        "¡Bienvenido a CoDevPro Technology!",

        "Gracias por confiar en nosotros. Ya puedes comenzar a comprar nuestros productos y servicios.",

        "bi-person-check-fill",

        "success",

        "perfil.php"

    );
}


mysqli_stmt_close($stmt);
/*=========================================================
=            MIGRAR CARRITO DEL TOKEN AL CLIENTE
=========================================================*/

$token = obtenerTokenCarrito();

/*
Si el cliente ya tenía productos,
solo aumentará cantidades.
*/

$sqlToken = "SELECT *

FROM carrito_online

WHERE token=?

AND estado='pendiente'";

$stmtToken = mysqli_prepare($conexion, $sqlToken);

mysqli_stmt_bind_param(
    $stmtToken,
    "s",
    $token
);

mysqli_stmt_execute($stmtToken);

$resToken = mysqli_stmt_get_result($stmtToken);

while ($item = mysqli_fetch_assoc($resToken)) {

    $sqlExiste = "SELECT idCarrito,cantidad

    FROM carrito_online

    WHERE idCliente=?

    AND idProducto=?

    AND estado='pendiente'

    LIMIT 1";

    $stmtExiste = mysqli_prepare($conexion, $sqlExiste);

    mysqli_stmt_bind_param(
        $stmtExiste,
        "ii",
        $cliente["idCliente"],
        $item["idProducto"]
    );

    mysqli_stmt_execute($stmtExiste);

    $resExiste = mysqli_stmt_get_result($stmtExiste);

    if (mysqli_num_rows($resExiste) > 0) {

        $carrito = mysqli_fetch_assoc($resExiste);

        $nuevaCantidad =
            $carrito["cantidad"] +
            $item["cantidad"];

        $sqlActualizar = "UPDATE carrito_online

        SET cantidad=?,
            fecha_actualizado=NOW()

        WHERE idCarrito=?";

        $stmtActualizar = mysqli_prepare(
            $conexion,
            $sqlActualizar
        );

        mysqli_stmt_bind_param(
            $stmtActualizar,
            "ii",
            $nuevaCantidad,
            $carrito["idCarrito"]
        );

        mysqli_stmt_execute($stmtActualizar);

        /*
        Eliminar el carrito temporal
        */

        $sqlEliminar = "DELETE

        FROM carrito_online

        WHERE idCarrito=?";

        $stmtEliminar = mysqli_prepare(
            $conexion,
            $sqlEliminar
        );

        mysqli_stmt_bind_param(
            $stmtEliminar,
            "i",
            $item["idCarrito"]
        );

        mysqli_stmt_execute($stmtEliminar);
    } else {

        /*
        Pasar el carrito al cliente
        */

        $sqlActualizar = "UPDATE carrito_online

        SET idCliente=?,
            token=NULL,
            fecha_actualizado=NOW()

        WHERE idCarrito=?";

        $stmtActualizar = mysqli_prepare(
            $conexion,
            $sqlActualizar
        );

        mysqli_stmt_bind_param(
            $stmtActualizar,
            "ii",
            $cliente["idCliente"],
            $item["idCarrito"]
        );

        mysqli_stmt_execute($stmtActualizar);
    }
}

/*=========================================================
=            RESPUESTA CLIENTE
=========================================================*/

echo json_encode([

    "estado" => true,

    "mensaje" => "Bienvenido " . $cliente["nombre"],

    "redireccion" => "index.php"

]);
