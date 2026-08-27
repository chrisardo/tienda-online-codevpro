<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/adm_actualizar_estado_pedido.php
// Módulo: Gestión de Pedidos de Clientes
// Sistema: Inventa
//=====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../controladores/conexion.php";

header("Content-Type: application/json; charset=UTF-8");


//=====================================================
// FUNCIÓN RESPUESTA JSON
//=====================================================

function responderJSON(
    string $estado,
    string $mensaje,
    array $datos = [],
    int $codigoHTTP = 200
) {

    http_response_code($codigoHTTP);

    echo json_encode(
        array_merge(
            [
                "estado" => $estado,
                "mensaje" => $mensaje
            ],
            $datos
        ),
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


//=====================================================
// SOLO POST
//=====================================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    responderJSON(
        "error",
        "Método de solicitud no permitido.",
        [],
        405
    );
}


//=====================================================
// OBTENER USUARIO DE LA SESIÓN
//=====================================================

$idUser =
    intval(
        $_SESSION["idUser"] ?? 0
    );


if ($idUser <= 0) {

    responderJSON(
        "error",
        "Sesión no válida. Inicie sesión nuevamente.",
        [],
        401
    );
}


//=====================================================
// OBTENER DATOS RECIBIDOS
//=====================================================

$idPedido =
    intval(
        $_POST["idPedido"] ?? 0
    );


$estadoPedido =
    strtoupper(
        trim(
            (string)(
                $_POST["estadoPedido"] ?? ""
            )
        )
    );


$idEmpleadoRecibido =
    intval(
        $_POST["idEmpleado"] ?? 0
    );


$observacionPedido =
    trim(
        (string)(
            $_POST["observacionPedido"] ?? ""
        )
    );


//=====================================================
// NORMALIZAR OBSERVACIÓN
//=====================================================

if (
    $observacionPedido === ""
) {

    $observacionPedido =
        null;
}


//=====================================================
// VALIDAR ID PEDIDO
//=====================================================

if ($idPedido <= 0) {

    responderJSON(
        "error",
        "El ID del pedido no es válido.",
        [],
        400
    );
}


//=====================================================
// ESTADOS PERMITIDOS
//=====================================================
//
// Estos son los estados utilizados por el módulo
// de gestión de pedidos.
//
//=====================================================

$estadosPermitidos = [

    "PENDIENTE",
    "PREPARANDO",
    "ASIGNADO",
    "OBTENIDO",
    "ENTREGADO",
    "NO_ENTREGADO",
    "CANCELADO"

];


if (
    !in_array(
        $estadoPedido,
        $estadosPermitidos,
        true
    )
) {

    responderJSON(
        "error",
        "El estado seleccionado no es válido.",
        [
            "estado_recibido" =>
            $estadoPedido
        ],
        400
    );
}


//=====================================================
// VALIDAR CONEXIÓN
//=====================================================

if (
    !isset($conexion) ||
    !$conexion
) {

    responderJSON(
        "error",
        "No existe una conexión válida con la base de datos.",
        [],
        500
    );
}


//=====================================================
// INICIAR TRANSACCIÓN
//=====================================================

mysqli_begin_transaction(
    $conexion
);


try {


    //=================================================
    // OBTENER PEDIDO Y BLOQUEAR FILA
    //=================================================
    //
    // FOR UPDATE evita que dos solicitudes simultáneas
    // modifiquen el mismo pedido al mismo tiempo.
    //
    //=================================================

    $sqlPedido = "

        SELECT

            tv.id_ticket_ventas,
            tv.id_user,
            tv.estado_envio,
            tv.id_empleado,
            tv.observacion_envio

        FROM ticket_ventas tv

        WHERE tv.id_ticket_ventas = ?
        AND tv.id_user = ?

        LIMIT 1

        FOR UPDATE
    ";


    $stmtPedido =
        mysqli_prepare(
            $conexion,
            $sqlPedido
        );


    if (!$stmtPedido) {

        throw new Exception(
            "No se pudo preparar la consulta del pedido."
        );
    }


    mysqli_stmt_bind_param(
        $stmtPedido,
        "ii",
        $idPedido,
        $idUser
    );


    if (
        !mysqli_stmt_execute(
            $stmtPedido
        )
    ) {

        mysqli_stmt_close(
            $stmtPedido
        );

        throw new Exception(
            "No se pudo consultar el pedido."
        );
    }


    $resultadoPedido =
        mysqli_stmt_get_result(
            $stmtPedido
        );


    if (!$resultadoPedido) {

        mysqli_stmt_close(
            $stmtPedido
        );

        throw new Exception(
            "No se pudo obtener la información del pedido."
        );
    }


    $pedido =
        mysqli_fetch_assoc(
            $resultadoPedido
        );


    mysqli_stmt_close(
        $stmtPedido
    );


    //=================================================
    // VERIFICAR EXISTENCIA
    //=================================================

    if (!$pedido) {

        throw new Exception(
            "El pedido no existe o no pertenece al usuario actual."
        );
    }


    //=================================================
    // DATOS ACTUALES DE LA BD
    //=================================================

    $estadoActualBD =
        strtoupper(
            trim(
                (string)(
                    $pedido["estado_envio"] ?? ""
                )
            )
        );


    $idEmpleadoActualBD =
        intval(
            $pedido["id_empleado"] ?? 0
        );


    $observacionActualBD =
        $pedido["observacion_envio"] ?? "";


    //=================================================
    // SEGURIDAD:
    //
    // SI EL PEDIDO YA ESTÁ ENTREGADO EN LA BD,
    // NO SE PERMITE MODIFICARLO.
    //
    //=================================================

    if (
        $estadoActualBD === "ENTREGADO"
    ) {

        mysqli_rollback(
            $conexion
        );


        responderJSON(
            "error",
            "Este pedido ya fue entregado y no puede modificarse.",
            [
                "estado_actual" =>
                $estadoActualBD,

                "id_pedido" =>
                $idPedido
            ],
            409
        );
    }


    //=================================================
    // DETERMINAR REPARTIDOR FINAL
    //=================================================
    //
    // Si el navegador envía un repartidor válido,
    // se utilizará ese.
    //
    // Si no envía uno, conservamos el repartidor
    // que ya existe en BD.
    //
    // Esto evita reemplazar accidentalmente el
    // id_empleado existente por 0.
    //
    //=================================================

    $idEmpleadoFinal =
        $idEmpleadoActualBD;


    if (
        $idEmpleadoRecibido > 0
    ) {

        $idEmpleadoFinal =
            $idEmpleadoRecibido;
    }


    //=================================================
    // PREPARANDO REQUIERE REPARTIDOR
    //=================================================

    if (
        $estadoPedido === "PREPARANDO" &&
        $idEmpleadoFinal <= 0
    ) {

        mysqli_rollback(
            $conexion
        );


        responderJSON(
            "error",
            "Debe seleccionar un repartidor para preparar el pedido.",
            [],
            400
        );
    }


    //=================================================
    // SI HAY REPARTIDOR, VALIDARLO
    //=================================================
    //
    // El empleado debe:
    //
    // - Existir
    // - Pertenecer al mismo usuario
    // - Estar ACTIVO
    // - Tener rol REPARTIDOR
    //
    //=================================================

    if (
        $idEmpleadoFinal > 0
    ) {


        $sqlEmpleado = "

            SELECT

                e.id_empleado,
                e.nombre,
                e.apellido,
                e.celular,
                e.email,
                e.estado,

                r.id_rol,
                r.nombre AS rol

            FROM empleados e

            INNER JOIN rol r
                ON r.id_rol = e.id_rol
                AND r.id_user = e.id_user

            WHERE e.id_empleado = ?
            AND e.id_user = ?
            AND e.estado = 'ACTIVO'
            AND UPPER(TRIM(r.nombre)) = 'REPARTIDOR'

            LIMIT 1
        ";


        $stmtEmpleado =
            mysqli_prepare(
                $conexion,
                $sqlEmpleado
            );


        if (!$stmtEmpleado) {

            throw new Exception(
                "No se pudo validar el repartidor."
            );
        }


        mysqli_stmt_bind_param(
            $stmtEmpleado,
            "ii",
            $idEmpleadoFinal,
            $idUser
        );


        if (
            !mysqli_stmt_execute(
                $stmtEmpleado
            )
        ) {

            mysqli_stmt_close(
                $stmtEmpleado
            );

            throw new Exception(
                "No se pudo comprobar el repartidor."
            );
        }


        $resultadoEmpleado =
            mysqli_stmt_get_result(
                $stmtEmpleado
            );


        if (!$resultadoEmpleado) {

            mysqli_stmt_close(
                $stmtEmpleado
            );

            throw new Exception(
                "No se pudo obtener la información del repartidor."
            );
        }


        $empleado =
            mysqli_fetch_assoc(
                $resultadoEmpleado
            );


        mysqli_stmt_close(
            $stmtEmpleado
        );


        //=============================================
        // REPARTIDOR NO VÁLIDO
        //=============================================

        if (!$empleado) {

            mysqli_rollback(
                $conexion
            );


            responderJSON(
                "error",
                "El repartidor seleccionado no es válido, no pertenece al usuario actual, está inactivo o no tiene el rol REPARTIDOR.",
                [],
                400
            );
        }
    }


    //=================================================
    // PREPARAR VALOR DE ID_EMPLEADO
    //=================================================
    //
    // Si existe repartidor:
    //     se guarda su ID.
    //
    // Si no existe:
    //     se conserva el valor actual.
    //
    //=================================================

    $idEmpleadoGuardar =
        $idEmpleadoFinal;


    //=================================================
    // ACTUALIZAR PEDIDO
    //=================================================

    $sqlActualizar = "

        UPDATE ticket_ventas

        SET

            estado_envio = ?,
            id_empleado = ?,
            observacion_envio = ?

        WHERE id_ticket_ventas = ?
        AND id_user = ?

        LIMIT 1
    ";


    $stmtActualizar =
        mysqli_prepare(
            $conexion,
            $sqlActualizar
        );


    if (!$stmtActualizar) {

        throw new Exception(
            "No se pudo preparar la actualización del pedido."
        );
    }


    mysqli_stmt_bind_param(
        $stmtActualizar,
        "sisii",
        $estadoPedido,
        $idEmpleadoGuardar,
        $observacionPedido,
        $idPedido,
        $idUser
    );


    if (
        !mysqli_stmt_execute(
            $stmtActualizar
        )
    ) {

        $errorSQL =
            mysqli_stmt_error(
                $stmtActualizar
            );


        mysqli_stmt_close(
            $stmtActualizar
        );


        throw new Exception(
            "No se pudo actualizar el pedido: " .
                $errorSQL
        );
    }


    $filasAfectadas =
        mysqli_stmt_affected_rows(
            $stmtActualizar
        );


    mysqli_stmt_close(
        $stmtActualizar
    );


    //=================================================
    // CONFIRMAR TRANSACCIÓN
    //=================================================

    if (
        !mysqli_commit(
            $conexion
        )
    ) {

        throw new Exception(
            "No se pudo confirmar la actualización del pedido."
        );
    }


    //=================================================
    // MENSAJE SEGÚN ESTADO
    //=================================================

    $mensaje =
        "Pedido actualizado correctamente.";


    switch ($estadoPedido) {

        case "PENDIENTE":

            $mensaje =
                "El pedido volvió al estado PENDIENTE.";

            break;


        case "PREPARANDO":

            $mensaje =
                "El pedido está siendo preparado y el repartidor fue asignado correctamente.";

            break;


        case "ASIGNADO":

            $mensaje =
                "El pedido fue asignado correctamente.";

            break;


        case "OBTENIDO":

            $mensaje =
                "El pedido fue marcado como OBTENIDO.";

            break;


        case "ENTREGADO":

            $mensaje =
                "El pedido fue marcado como ENTREGADO correctamente.";

            break;


        case "NO_ENTREGADO":

            $mensaje =
                "El pedido fue marcado como NO ENTREGADO.";

            break;


        case "CANCELADO":

            $mensaje =
                "El pedido fue cancelado correctamente.";

            break;
    }


    //=================================================
    // RESPUESTA FINAL
    //=================================================

    responderJSON(
        "ok",
        $mensaje,
        [

            "id_pedido" =>
            $idPedido,

            "estado_anterior" =>
            $estadoActualBD,

            "estado_nuevo" =>
            $estadoPedido,

            "id_empleado" =>
            $idEmpleadoGuardar,

            "observacion" =>
            $observacionPedido ?? "",

            "filas_afectadas" =>
            $filasAfectadas

        ]
    );
} catch (Throwable $e) {


    //=================================================
    // ROLLBACK
    //=================================================

    mysqli_rollback(
        $conexion
    );


    //=================================================
    // LOG DEL ERROR
    //=================================================

    error_log(
        "Error en adm_actualizar_estado_pedido.php: " .
            $e->getMessage()
    );


    //=================================================
    // RESPUESTA
    //=================================================

    responderJSON(
        "error",
        $e->getMessage(),
        [],
        500
    );
}
