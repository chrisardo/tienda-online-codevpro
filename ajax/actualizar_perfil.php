<?php
//======================================================
// CoDevPro Technology
// ajax/actualizar_perfil.php
//======================================================

session_start();

header("Content-Type: application/json; charset=utf-8");

/*======================================================
=            VALIDAR SESIÓN
======================================================*/

if (!isset($_SESSION["idCliente"])) {

    echo json_encode([

        "estado" => "error",

        "mensaje" => "Debe iniciar sesión."

    ]);

    exit();
}

/*======================================================
=            CONEXIÓN
======================================================*/

require_once "../controladores/conexion.php";

mysqli_report(
    MYSQLI_REPORT_ERROR |
        MYSQLI_REPORT_STRICT
);

/*======================================================
=            RESPUESTA JSON
======================================================*/

function responder(
    $estado,
    $mensaje,
    $extra = []
) {

    echo json_encode(

        array_merge(

            [

                "estado" => $estado,

                "mensaje" => $mensaje

            ],

            $extra

        )

    );

    exit();
}

/*======================================================
=            OBTENER DATOS DEL FORMULARIO
======================================================*/

function obtenerDatosFormulario()
{

    return [

        "nombre" => trim($_POST["nombre"] ?? ""),

        "dni_o_ruc" => trim($_POST["dni_o_ruc"] ?? ""),

        "celular" => trim($_POST["celular"] ?? ""),

        "email" => trim($_POST["email"] ?? ""),

        "direccion" => trim($_POST["direccion"] ?? ""),

        "id_pais" => intval($_POST["id_pais"] ?? 0),

        "id_departamento" => intval($_POST["id_departamento"] ?? 0),

        "id_provincia" => intval($_POST["id_provincia"] ?? 0),

        "id_distrito" => intval($_POST["id_distrito"] ?? 0)

    ];
}

/*======================================================
=            VALIDAR EMAIL
======================================================*/

function validarEmail($email)
{

    return filter_var(

        $email,

        FILTER_VALIDATE_EMAIL

    );
}

/*======================================================
=            VALIDAR FORMULARIO
======================================================*/

function validarFormulario($datos)
{

    //------------------------------------------
    // NOMBRE
    //------------------------------------------

    if ($datos["nombre"] == "") {

        throw new Exception(

            "Ingrese el nombre."

        );
    }

    //------------------------------------------
    // DNI / RUC
    //------------------------------------------

    if ($datos["dni_o_ruc"] != "") {

        if (

            strlen($datos["dni_o_ruc"]) != 8 &&

            strlen($datos["dni_o_ruc"]) != 11

        ) {

            throw new Exception(

                "El DNI debe tener 8 dígitos o el RUC 11."

            );
        }
    }

    //------------------------------------------
    // CELULAR
    //------------------------------------------

    if ($datos["celular"] != "") {

        if (

            !ctype_digit($datos["celular"]) ||

            strlen($datos["celular"]) != 9

        ) {

            throw new Exception(

                "El celular debe tener 9 dígitos."

            );
        }
    }

    //------------------------------------------
    // EMAIL
    //------------------------------------------

    if ($datos["email"] == "") {

        throw new Exception(

            "Ingrese el correo electrónico."

        );
    }

    if (!validarEmail($datos["email"])) {

        throw new Exception(

            "Correo electrónico inválido."

        );
    }

    //------------------------------------------
    // DIRECCIÓN
    //------------------------------------------

    if ($datos["direccion"] == "") {

        throw new Exception(

            "Ingrese la dirección."

        );
    }

    //------------------------------------------
    // PAÍS
    //------------------------------------------

    if ($datos["id_pais"] <= 0) {

        throw new Exception(

            "Seleccione el país."

        );
    }

    //------------------------------------------
    // DEPARTAMENTO
    //------------------------------------------

    if ($datos["id_departamento"] <= 0) {

        throw new Exception(

            "Seleccione el departamento."

        );
    }

    //------------------------------------------
    // PROVINCIA
    //------------------------------------------

    if ($datos["id_provincia"] <= 0) {

        throw new Exception(

            "Seleccione la provincia."

        );
    }

    //------------------------------------------
    // DISTRITO
    //------------------------------------------

    if ($datos["id_distrito"] <= 0) {

        throw new Exception(

            "Seleccione el distrito."

        );
    }
}

/*======================================================
=            VARIABLES PRINCIPALES
======================================================*/

$idCliente = (int) $_SESSION["idCliente"];

$datos = obtenerDatosFormulario();
/*======================================================
=            PROCESO PRINCIPAL
======================================================*/

try {

    validarFormulario($datos);

    mysqli_begin_transaction($conexion);

    /*======================================================
    =            VALIDAR DNI / RUC DUPLICADO
    ======================================================*/

    if ($datos["dni_o_ruc"] != "") {

        $sql = "SELECT idCliente

                FROM clientes

                WHERE dni_o_ruc = ?

                AND idCliente <> ?

                LIMIT 1";

        $stmt = mysqli_prepare(
            $conexion,
            $sql
        );

        mysqli_stmt_bind_param(
            $stmt,
            "si",
            $datos["dni_o_ruc"],
            $idCliente
        );

        mysqli_stmt_execute($stmt);

        $resultado = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($resultado) > 0) {

            throw new Exception(
                "El DNI/RUC ya se encuentra registrado por otro cliente."
            );
        }

        mysqli_stmt_close($stmt);
    }

    /*======================================================
    =            VALIDAR CORREO DUPLICADO
    ======================================================*/

    $sql = "SELECT idCliente

            FROM clientes

            WHERE email = ?

            AND idCliente <> ?

            LIMIT 1";

    $stmt = mysqli_prepare(
        $conexion,
        $sql
    );

    mysqli_stmt_bind_param(
        $stmt,
        "si",
        $datos["email"],
        $idCliente
    );

    mysqli_stmt_execute($stmt);

    $resultado = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($resultado) > 0) {

        throw new Exception(
            "El correo electrónico ya pertenece a otro cliente."
        );
    }

    mysqli_stmt_close($stmt);

    /*======================================================
    =            ACTUALIZAR CLIENTE
    ======================================================*/

    $sql = "UPDATE clientes

            SET

                nombre = ?,
                dni_o_ruc = ?,
                celular = ?,
                email = ?,
                direccion = ?,
                id_pais = ?,
                id_departamento = ?,
                id_provincia = ?,
                id_distrito = ?,
                fecha_actualizado = CURDATE()

            WHERE idCliente = ?";

    $stmt = mysqli_prepare(
        $conexion,
        $sql
    );

    mysqli_stmt_bind_param(

        $stmt,

        "sssssiiiii",

        $datos["nombre"],
        $datos["dni_o_ruc"],
        $datos["celular"],
        $datos["email"],
        $datos["direccion"],
        $datos["id_pais"],
        $datos["id_departamento"],
        $datos["id_provincia"],
        $datos["id_distrito"],
        $idCliente

    );

    mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);
    /*======================================================
    =            OBTENER NOMBRES DE UBICACIÓN
    ======================================================*/

    $pais = "";
    $departamento = "";
    $provincia = "";
    $distrito = "";

    // País
    $stmt = mysqli_prepare(
        $conexion,
        "SELECT nombre
         FROM pais
         WHERE id_pais=?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $datos["id_pais"]
    );

    mysqli_stmt_execute($stmt);

    mysqli_stmt_bind_result(
        $stmt,
        $pais
    );

    mysqli_stmt_fetch($stmt);

    mysqli_stmt_close($stmt);

    // Departamento
    $stmt = mysqli_prepare(
        $conexion,
        "SELECT nombre
         FROM departamento
         WHERE id_departamento=?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $datos["id_departamento"]
    );

    mysqli_stmt_execute($stmt);

    mysqli_stmt_bind_result(
        $stmt,
        $departamento
    );

    mysqli_stmt_fetch($stmt);

    mysqli_stmt_close($stmt);

    // Provincia
    $stmt = mysqli_prepare(
        $conexion,
        "SELECT nombre
         FROM provincia
         WHERE id_provincia=?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $datos["id_provincia"]
    );

    mysqli_stmt_execute($stmt);

    mysqli_stmt_bind_result(
        $stmt,
        $provincia
    );

    mysqli_stmt_fetch($stmt);

    mysqli_stmt_close($stmt);

    // Distrito
    $stmt = mysqli_prepare(
        $conexion,
        "SELECT nombre
         FROM distrito
         WHERE id_distrito=?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $datos["id_distrito"]
    );

    mysqli_stmt_execute($stmt);

    mysqli_stmt_bind_result(
        $stmt,
        $distrito
    );

    mysqli_stmt_fetch($stmt);

    mysqli_stmt_close($stmt);

    /*======================================================
    =            DIRECCIÓN COMPLETA
    ======================================================*/

    $direccionCompleta = trim(

        $datos["direccion"] .

            ", " .

            $distrito .

            ", " .

            $provincia .

            ", " .

            $departamento .

            ", " .

            $pais

    );

    /*======================================================
    =            CONFIRMAR TRANSACCIÓN
    ======================================================*/

    mysqli_commit($conexion);

    responder(

        "ok",

        "Los datos de tu perfil fueron actualizados correctamente.",

        [

            "nombre" => $datos["nombre"],

            "email" => $datos["email"],

            "celular" => $datos["celular"],

            "direccionCompleta" => $direccionCompleta

        ]

    );
} catch (Exception $e) {

    mysqli_rollback($conexion);

    responder(

        "error",

        $e->getMessage()

    );
}

mysqli_close($conexion);
