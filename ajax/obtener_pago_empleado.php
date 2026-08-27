<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/obtener_pago_empleado.php
// Módulo: Pagos a Empleados
// Sistema: Inventa
//=====================================================

declare(strict_types=1);

header("Content-Type: application/json; charset=UTF-8");

//=====================================================
// RESPUESTA JSON
//=====================================================

function respuestaJSON(
    bool $success,
    string $mensaje = "",
    array $datos = []
): void {

    echo json_encode(
        [
            "success" => $success,
            "mensaje" => $mensaje,
            "datos"   => $datos
        ],
        JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
    );

    exit;
}

//=====================================================
// INICIAR SESIÓN
//=====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//=====================================================
// CONEXIÓN
//=====================================================

require_once "../controladores/conexion.php";

//=====================================================
// VALIDAR CONEXIÓN
//=====================================================

if (!isset($conexion) || !($conexion instanceof mysqli)) {

    respuestaJSON(
        false,
        "No fue posible establecer la conexión con la base de datos."
    );
}

//=====================================================
// CONFIGURAR MYSQLI
//=====================================================

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

//=====================================================
// OBTENER USUARIO ACTUAL
//=====================================================

$idUser = 0;

/*
|--------------------------------------------------------------------------
| IMPORTANTE
|--------------------------------------------------------------------------
| Ajusta estas variables si en tu sistema la sesión utiliza
| otro nombre para almacenar el ID del usuario.
|
| Se contemplan varias posibilidades para hacerlo compatible
| con el sistema actual.
|--------------------------------------------------------------------------
*/

if (isset($_SESSION["id_user"])) {

    $idUser = (int) $_SESSION["id_user"];
} elseif (isset($_SESSION["idUser"])) {

    $idUser = (int) $_SESSION["idUser"];
} elseif (isset($_SESSION["usuario"]["id_user"])) {

    $idUser = (int) $_SESSION["usuario"]["id_user"];
} elseif (isset($_SESSION["usuario"]["idUser"])) {

    $idUser = (int) $_SESSION["usuario"]["idUser"];
}

//=====================================================
// VALIDAR USUARIO
//=====================================================

if ($idUser <= 0) {

    respuestaJSON(
        false,
        "La sesión del usuario no es válida o ha expirado."
    );
}

//=====================================================
// OBTENER ID DEL PAGO
//=====================================================

$idPago = filter_input(
    INPUT_GET,
    "id_pago",
    FILTER_VALIDATE_INT
);

//=====================================================
// VALIDAR ID
//=====================================================

if (!$idPago || $idPago <= 0) {

    respuestaJSON(
        false,
        "El identificador del pago no es válido."
    );
}

//=====================================================
// CONSULTAR PAGO
//=====================================================

try {

    /*
    |--------------------------------------------------------------------------
    | CONSULTA
    |--------------------------------------------------------------------------
    |
    | pago_empleado
    |      |
    |      +---- empleados
    |      |
    |      +---- sueldo_empleado
    |      |
    |      +---- cuenta_banco
    |      |
    |      +---- metodo_pago
    |
    |--------------------------------------------------------------------------
    */

    $sql = "
        SELECT

            /*-----------------------------------------
              PAGO
            -----------------------------------------*/

            p.id_pago,

            p.id_empleado,

            p.id_sueldo,

            p.periodo_inicio,

            p.periodo_fin,

            p.monto_base,

            p.bonificaciones,

            p.descuentos,

            p.monto_total,

            p.fecha_pago,

            p.id_cuenta_bancaria,

            p.id_metodo_pago,

            p.estado,

            p.observacion,

            p.id_user,

            p.fecha_registro,

            p.fecha_actualizado,


            /*-----------------------------------------
              EMPLEADO
            -----------------------------------------*/

            e.nombre AS nombre_empleado,

            e.apellido AS apellido_empleado,

            e.dni AS dni_empleado,

            e.celular AS celular_empleado,

            e.email AS email_empleado,

            e.estado AS estado_empleado,


            /*-----------------------------------------
              SUELDO
            -----------------------------------------*/

            s.sueldo_base,

            s.tipo_pago,

            s.fecha_inicio AS sueldo_fecha_inicio,

            s.fecha_fin AS sueldo_fecha_fin,

            s.estado AS estado_sueldo,


            /*-----------------------------------------
              CUENTA BANCARIA
            -----------------------------------------*/

            cb.nombre AS cuenta_bancaria,

            cb.balance AS balance_cuenta,


            /*-----------------------------------------
              MÉTODO DE PAGO
            -----------------------------------------*/

            mp.nombre AS metodo_pago


        FROM pago_empleado p


        /*---------------------------------------------
          EMPLEADO
        ---------------------------------------------*/

        INNER JOIN empleados e
            ON e.id_empleado = p.id_empleado
            AND e.id_user = p.id_user


        /*---------------------------------------------
          SUELDO
        ---------------------------------------------*/

        LEFT JOIN sueldo_empleado s
            ON s.id_sueldo = p.id_sueldo
            AND s.id_empleado = p.id_empleado
            AND s.id_user = p.id_user


        /*---------------------------------------------
          CUENTA BANCARIA
        ---------------------------------------------*/

        LEFT JOIN cuenta_banco cb
            ON cb.id_cuenta_bancaria = p.id_cuenta_bancaria
            AND cb.id_user = p.id_user


        /*---------------------------------------------
          MÉTODO DE PAGO
        ---------------------------------------------*/

        LEFT JOIN metodo_pago mp
            ON mp.id_metodo_pago = p.id_metodo_pago
            AND mp.id_user = p.id_user


        /*---------------------------------------------
          SEGURIDAD
        ---------------------------------------------*/

        WHERE p.id_pago = ?

          AND p.id_user = ?


        LIMIT 1
    ";

    //=================================================
    // PREPARAR
    //=================================================

    $stmt = $conexion->prepare($sql);

    //=================================================
    // PARÁMETROS
    //=================================================

    $stmt->bind_param(
        "ii",
        $idPago,
        $idUser
    );

    //=================================================
    // EJECUTAR
    //=================================================

    $stmt->execute();

    //=================================================
    // RESULTADO
    //=================================================

    $resultado = $stmt->get_result();

    //=================================================
    // VERIFICAR EXISTENCIA
    //=================================================

    if ($resultado->num_rows === 0) {

        $stmt->close();

        respuestaJSON(
            false,
            "No se encontró el pago solicitado."
        );
    }

    //=================================================
    // OBTENER REGISTRO
    //=================================================

    $fila = $resultado->fetch_assoc();

    //=================================================
    // CERRAR
    //=================================================

    $stmt->close();

    //=================================================
    // FORMAR NOMBRE COMPLETO
    //=================================================

    $nombreEmpleado = trim(
        ($fila["nombre_empleado"] ?? "") .
            " " .
            ($fila["apellido_empleado"] ?? "")
    );

    //=================================================
    // PREPARAR RESPUESTA
    //=================================================

    $pago = [

        //=================================================
        // IDENTIFICADORES
        //=================================================

        "id_pago" => (int) $fila["id_pago"],

        "id_empleado" => (int) $fila["id_empleado"],

        "id_sueldo" => (int) $fila["id_sueldo"],

        "id_cuenta_bancaria" =>
        !empty($fila["id_cuenta_bancaria"])
            ? (int) $fila["id_cuenta_bancaria"]
            : null,

        "id_metodo_pago" =>
        !empty($fila["id_metodo_pago"])
            ? (int) $fila["id_metodo_pago"]
            : null,


        //=================================================
        // EMPLEADO
        //=================================================

        "empleado" => $nombreEmpleado,

        "nombre" => $fila["nombre_empleado"] ?? "",

        "apellido" => $fila["apellido_empleado"] ?? "",

        "dni" => $fila["dni_empleado"] ?? "",

        "celular" => $fila["celular_empleado"] ?? "",

        "email" => $fila["email_empleado"] ?? "",

        "estado_empleado" => $fila["estado_empleado"] ?? "",


        //=================================================
        // SUELDO
        //=================================================

        "sueldo_base" =>
        number_format(
            (float) ($fila["sueldo_base"] ?? 0),
            2,
            ".",
            ""
        ),

        "tipo_pago" => $fila["tipo_pago"] ?? "",

        "sueldo_fecha_inicio" =>
        $fila["sueldo_fecha_inicio"] ?? null,

        "sueldo_fecha_fin" =>
        $fila["sueldo_fecha_fin"] ?? null,

        "estado_sueldo" =>
        $fila["estado_sueldo"] ?? "",


        //=================================================
        // PERÍODO
        //=================================================

        "periodo_inicio" =>
        $fila["periodo_inicio"] ?? null,

        "periodo_fin" =>
        $fila["periodo_fin"] ?? null,


        //=================================================
        // MONTOS
        //=================================================

        "monto_base" =>
        number_format(
            (float) ($fila["monto_base"] ?? 0),
            2,
            ".",
            ""
        ),

        "bonificaciones" =>
        number_format(
            (float) ($fila["bonificaciones"] ?? 0),
            2,
            ".",
            ""
        ),

        "descuentos" =>
        number_format(
            (float) ($fila["descuentos"] ?? 0),
            2,
            ".",
            ""
        ),

        "monto_total" =>
        number_format(
            (float) ($fila["monto_total"] ?? 0),
            2,
            ".",
            ""
        ),


        //=================================================
        // PAGO
        //=================================================

        "fecha_pago" =>
        $fila["fecha_pago"] ?? null,

        "estado" =>
        strtoupper(
            trim(
                $fila["estado"] ?? ""
            )
        ),

        "observacion" =>
        $fila["observacion"] ?? "",


        //=================================================
        // CUENTA BANCARIA
        //=================================================

        "cuenta_bancaria" =>
        $fila["cuenta_bancaria"] ?? "",

        "balance_cuenta" =>
        number_format(
            (float) ($fila["balance_cuenta"] ?? 0),
            2,
            ".",
            ""
        ),


        //=================================================
        // MÉTODO DE PAGO
        //=================================================

        "metodo_pago" =>
        $fila["metodo_pago"] ?? "",


        //=================================================
        // AUDITORÍA
        //=================================================

        "fecha_registro" =>
        $fila["fecha_registro"] ?? null,

        "fecha_actualizado" =>
        $fila["fecha_actualizado"] ?? null

    ];

    //=====================================================
    // RESPUESTA
    //=====================================================

    respuestaJSON(
        true,
        "Pago obtenido correctamente.",
        [
            "pago" => $pago
        ]
    );
} catch (mysqli_sql_exception $e) {

    //=================================================
    // ERROR MYSQL
    //=================================================

    error_log(
        "Error MySQL en obtener_pago_empleado.php: " .
            $e->getMessage()
    );

    respuestaJSON(
        false,
        "Ocurrió un error al consultar la información del pago."
    );
} catch (Throwable $e) {

    //=================================================
    // ERROR GENERAL
    //=================================================

    error_log(
        "Error en obtener_pago_empleado.php: " .
            $e->getMessage()
    );

    respuestaJSON(
        false,
        "Ocurrió un error inesperado al consultar el pago."
    );
}
