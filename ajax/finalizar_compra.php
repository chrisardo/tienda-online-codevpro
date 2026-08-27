<?php
//======================================================
// CoDevPro Technology
// Archivo: ajax/finalizar_compra.php
// Módulo: Checkout / Finalizar Compra
// Sistema: Inventa
//
// FUNCIÓN:
// - Validar cliente
// - Obtener carrito pendiente
// - Bloquear carrito durante la compra
// - Validar stock
// - Obtener precios actuales
// - Calcular subtotal
// - Calcular impuesto UNA SOLA VEZ por producto/línea
// - Registrar ticket_ventas
// - Registrar detalle_ticket_ventas
// - Descontar stock
// - Actualizar cantidad_producto_vendido
// - Cerrar carrito
// - Actualizar dirección del cliente
// - Registrar notificación
// - Generar comprobante sin duplicados
// - Confirmar transacción
//
// IMPORTANTE:
// Al momento de realizar la compra:
//
//     id_empleado   = 0
//     id_repartidor = 0
//
// Posteriormente el administrador podrá asignarlos.
//======================================================

session_start();

header("Content-Type: application/json; charset=UTF-8");


//======================================================
// RESPUESTA JSON
//======================================================

function responder($estado, $mensaje, $datos = [])
{
    echo json_encode(
        array_merge(
            [
                "estado"  => $estado,
                "mensaje" => $mensaje
            ],
            $datos
        ),
        JSON_UNESCAPED_UNICODE
    );

    exit();
}


//======================================================
// VALIDAR MÉTODO
//======================================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    responder(
        false,
        "Método de solicitud no permitido."
    );
}


//======================================================
// VALIDAR SESIÓN
//======================================================

if (
    !isset($_SESSION["idCliente"]) ||
    intval($_SESSION["idCliente"]) <= 0
) {

    responder(
        false,
        "La sesión del cliente ha expirado. Inicie sesión nuevamente."
    );
}


//======================================================
// ID CLIENTE
//======================================================

$idCliente = intval(
    $_SESSION["idCliente"]
);


//======================================================
// CONEXIÓN
//======================================================

require_once "../controladores/conexion.php";


//======================================================
// VALIDAR CONEXIÓN
//======================================================

if (!$conexion) {

    responder(
        false,
        "No se pudo conectar con la base de datos."
    );
}


//======================================================
// CONFIGURAR UTF8MB4
//======================================================

mysqli_set_charset(
    $conexion,
    "utf8mb4"
);


//======================================================
// DATOS RECIBIDOS
//======================================================

$direccion = trim(
    $_POST["direccion"] ?? ""
);

$comentarios = trim(
    $_POST["comentarios"] ?? ""
);

$guardarDireccion = intval(
    $_POST["guardarDireccion"] ?? 0
);

$idMetodoPago = intval(
    $_POST["id_metodo_pago"] ?? 0
);


//======================================================
// VALIDAR DIRECCIÓN
//======================================================

if ($direccion === "") {

    responder(
        false,
        "Debe ingresar una dirección de entrega."
    );
}


//======================================================
// VALIDAR MÉTODO DE PAGO
//======================================================

if ($idMetodoPago <= 0) {

    responder(
        false,
        "Debe seleccionar un método de pago."
    );
}


//======================================================
// VALIDAR LONGITUD DIRECCIÓN
//======================================================

if (mb_strlen($direccion) > 500) {

    responder(
        false,
        "La dirección de entrega es demasiado larga."
    );
}


//======================================================
// VALIDAR LONGITUD COMENTARIOS
//======================================================

if (mb_strlen($comentarios) > 1000) {

    responder(
        false,
        "Las observaciones son demasiado largas."
    );
}


//======================================================
// OBTENER CLIENTE
//======================================================

$sqlCliente = "
    SELECT
        idCliente,
        nombre,
        dni_o_ruc,
        direccion,
        email,
        celular,
        id_user,
        estado,
        Eliminado
    FROM clientes
    WHERE idCliente = ?
    LIMIT 1
";

$stmtCliente = mysqli_prepare(
    $conexion,
    $sqlCliente
);

if (!$stmtCliente) {

    responder(
        false,
        "No se pudo preparar la consulta del cliente."
    );
}

mysqli_stmt_bind_param(
    $stmtCliente,
    "i",
    $idCliente
);

mysqli_stmt_execute(
    $stmtCliente
);

$resultadoCliente =
    mysqli_stmt_get_result(
        $stmtCliente
    );

$cliente = mysqli_fetch_assoc(
    $resultadoCliente
);

mysqli_stmt_close(
    $stmtCliente
);


//======================================================
// VALIDAR CLIENTE
//======================================================

if (!$cliente) {

    responder(
        false,
        "El cliente no existe."
    );
}


//======================================================
// VALIDAR ESTADO CLIENTE
//======================================================

if (
    isset($cliente["estado"]) &&
    strtoupper($cliente["estado"]) !== "ACTIVO"
) {

    responder(
        false,
        "El cliente se encuentra inactivo."
    );
}


//======================================================
// VALIDAR ELIMINADO
//======================================================

if (
    isset($cliente["Eliminado"]) &&
    intval($cliente["Eliminado"]) === 1
) {

    responder(
        false,
        "El cliente no se encuentra disponible."
    );
}


//======================================================
// INICIAR TRANSACCIÓN
//======================================================

mysqli_begin_transaction(
    $conexion
);


try {


    //==================================================
    // VALIDAR MÉTODO DE PAGO
    //==================================================

    $sqlMetodoPago = "
        SELECT
            id_metodo_pago,
            id_user,
            nombre
        FROM metodo_pago
        WHERE id_metodo_pago = ?
        AND Eliminado = 0
        LIMIT 1
    ";

    $stmtMetodoPago = mysqli_prepare(
        $conexion,
        $sqlMetodoPago
    );

    if (!$stmtMetodoPago) {

        throw new Exception(
            "No se pudo preparar la consulta del método de pago."
        );
    }

    mysqli_stmt_bind_param(
        $stmtMetodoPago,
        "i",
        $idMetodoPago
    );

    mysqli_stmt_execute(
        $stmtMetodoPago
    );

    $resultadoMetodoPago =
        mysqli_stmt_get_result(
            $stmtMetodoPago
        );

    $metodoPago = mysqli_fetch_assoc(
        $resultadoMetodoPago
    );

    mysqli_stmt_close(
        $stmtMetodoPago
    );


    //==================================================
    // VALIDAR MÉTODO DE PAGO
    //==================================================

    if (!$metodoPago) {

        throw new Exception(
            "El método de pago seleccionado no está disponible."
        );
    }


    //==================================================
    // OBTENER CARRITO
    //==================================================

    $sqlCarrito = "
        SELECT

            c.idCarrito,
            c.idCliente,
            c.idProducto,
            c.cantidad,

            p.nombre,
            p.codigo,
            p.precio,
            p.stock,
            p.aplica_impuesto,
            p.id_user,
            p.Eliminado

        FROM carrito_online c

        INNER JOIN producto p
            ON p.idProducto = c.idProducto

        WHERE c.idCliente = ?
        AND c.estado = 'pendiente'

        ORDER BY c.idCarrito ASC

        FOR UPDATE
    ";

    $stmtCarrito = mysqli_prepare(
        $conexion,
        $sqlCarrito
    );

    if (!$stmtCarrito) {

        throw new Exception(
            "No se pudo preparar la consulta del carrito."
        );
    }

    mysqli_stmt_bind_param(
        $stmtCarrito,
        "i",
        $idCliente
    );

    mysqli_stmt_execute(
        $stmtCarrito
    );

    $resultadoCarrito =
        mysqli_stmt_get_result(
            $stmtCarrito
        );


    //==================================================
    // VALIDAR CARRITO
    //==================================================

    if (
        !$resultadoCarrito ||
        mysqli_num_rows($resultadoCarrito) === 0
    ) {

        mysqli_stmt_close(
            $stmtCarrito
        );

        throw new Exception(
            "El carrito está vacío o el pedido ya fue procesado."
        );
    }


    //==================================================
    // VARIABLES
    //==================================================

    $items = [];

    $idUser = 0;

    $totalProductos = 0;


    //==================================================
    // CARGAR CARRITO
    //==================================================

    while (
        $item =
        mysqli_fetch_assoc(
            $resultadoCarrito
        )
    ) {

        //==============================================
        // CANTIDAD
        //==============================================

        $cantidad = intval(
            $item["cantidad"] ?? 0
        );

        if ($cantidad <= 0) {

            throw new Exception(
                "Existe una cantidad inválida en el carrito."
            );
        }


        //==============================================
        // PRODUCTO ELIMINADO
        //==============================================

        if (
            intval(
                $item["Eliminado"] ?? 0
            ) === 1
        ) {

            throw new Exception(
                "El producto " .
                    ($item["nombre"] ?? "Producto") .
                    " ya no está disponible."
            );
        }


        //==============================================
        // PROPIETARIO PRODUCTO
        //==============================================

        $idUserProducto = intval(
            $item["id_user"] ?? 0
        );

        if ($idUserProducto <= 0) {

            throw new Exception(
                "El producto " .
                    ($item["nombre"] ?? "Producto") .
                    " no tiene propietario configurado."
            );
        }


        //==============================================
        // DETERMINAR USER
        //==============================================

        if ($idUser <= 0) {

            $idUser =
                $idUserProducto;
        } elseif (
            $idUser !== $idUserProducto
        ) {

            throw new Exception(
                "El carrito contiene productos de diferentes tiendas."
            );
        }


        //==============================================
        // STOCK
        //==============================================

        $stock = intval(
            $item["stock"] ?? 0
        );

        if ($stock < $cantidad) {

            throw new Exception(
                "Stock insuficiente para el producto: " .
                    ($item["nombre"] ?? "Producto") .
                    ". Stock disponible: " .
                    $stock .
                    ". Cantidad solicitada: " .
                    $cantidad .
                    "."
            );
        }


        //==============================================
        // PRECIO ACTUAL
        //==============================================

        $precio = round(
            floatval(
                $item["precio"] ?? 0
            ),
            2
        );

        if ($precio < 0) {

            throw new Exception(
                "El precio del producto " .
                    ($item["nombre"] ?? "Producto") .
                    " no es válido."
            );
        }


        //==============================================
        // SUBTOTAL PRODUCTO
        //==============================================

        $subtotalProducto =
            round(
                $precio * $cantidad,
                2
            );


        //==============================================
        // TOTAL PRODUCTOS
        //==============================================

        $totalProductos +=
            $cantidad;


        //==============================================
        // GUARDAR ITEM
        //==============================================

        $item["cantidad"] =
            $cantidad;

        $item["precio_actual"] =
            $precio;

        $item["subtotal_producto"] =
            $subtotalProducto;

        $items[] =
            $item;
    }


    mysqli_stmt_close(
        $stmtCarrito
    );


    //==================================================
    // VALIDAR USER
    //==================================================

    if ($idUser <= 0) {

        throw new Exception(
            "No se pudo determinar el propietario de la tienda."
        );
    }


    //==================================================
    // VALIDAR MÉTODO DE PAGO DEL USER
    //==================================================

    if (
        intval(
            $metodoPago["id_user"] ?? 0
        ) !== $idUser
    ) {

        throw new Exception(
            "El método de pago seleccionado no pertenece a esta tienda."
        );
    }


    //==================================================
    // CONFIGURACIÓN TRIBUTARIA
    //==================================================

    $nombreImpuesto =
        "Impuesto";

    $porcentajeImpuesto =
        0;

    $impuestoActivo =
        0;

    $preciosIncluyenImpuesto =
        0;


    $sqlConfiguracion = "
        SELECT

            impuesto_activo,
            nombre_impuesto,
            porcentaje_impuesto,
            precios_incluyen_impuesto

        FROM configuracion_monedas_impuestos

        WHERE id_user = ?

        ORDER BY id_configuracion DESC

        LIMIT 1
    ";

    $stmtConfiguracion =
        mysqli_prepare(
            $conexion,
            $sqlConfiguracion
        );

    if (!$stmtConfiguracion) {

        throw new Exception(
            "No se pudo preparar la configuración tributaria."
        );
    }

    mysqli_stmt_bind_param(
        $stmtConfiguracion,
        "i",
        $idUser
    );

    mysqli_stmt_execute(
        $stmtConfiguracion
    );

    $resultadoConfiguracion =
        mysqli_stmt_get_result(
            $stmtConfiguracion
        );

    $configuracion =
        mysqli_fetch_assoc(
            $resultadoConfiguracion
        );

    mysqli_stmt_close(
        $stmtConfiguracion
    );


    //==================================================
    // CARGAR CONFIGURACIÓN
    //==================================================

    if ($configuracion) {

        $impuestoActivo =
            intval(
                $configuracion["impuesto_activo"] ?? 0
            );

        $nombreImpuesto =
            trim(
                $configuracion["nombre_impuesto"] ?? ""
            );

        if ($nombreImpuesto === "") {

            $nombreImpuesto =
                "Impuesto";
        }

        $porcentajeImpuesto =
            floatval(
                $configuracion["porcentaje_impuesto"] ?? 0
            );

        $preciosIncluyenImpuesto =
            intval(
                $configuracion["precios_incluyen_impuesto"] ?? 0
            );
    }


    //==================================================
    // CALCULAR TOTALES
    //==================================================

    $subtotal =
        0;

    $impuestoTotal =
        0;


    foreach ($items as &$item) {

        //==============================================
        // PRECIO UNITARIO
        //==============================================

        $precio =
            floatval(
                $item["precio_actual"]
            );


        //==============================================
        // CANTIDAD
        //==============================================

        $cantidad =
            intval(
                $item["cantidad"]
            );


        //==============================================
        // SUBTOTAL DE LA LÍNEA
        //==============================================

        $subtotalProducto =
            round(
                $precio * $cantidad,
                2
            );


        //==============================================
        // APLICA IMPUESTO
        //==============================================

        $aplicaImpuestoProducto =
            intval(
                $item["aplica_impuesto"] ?? 0
            );


        //==============================================
        // IMPUESTO DE LA LÍNEA
        //==============================================

        $impuestoProducto =
            0;


        if (
            $impuestoActivo === 1 &&
            $aplicaImpuestoProducto === 1 &&
            $porcentajeImpuesto > 0
        ) {

            //==========================================
            // PRECIO CON IMPUESTO
            //==========================================

            if (
                $preciosIncluyenImpuesto === 1
            ) {

                $baseUnitario =
                    $precio /
                    (
                        1 +
                        (
                            $porcentajeImpuesto / 100
                        )
                    );

                $impuestoProducto =
                    $precio -
                    $baseUnitario;
            } else {

                //======================================
                // PRECIO SIN IMPUESTO
                //======================================

                $impuestoProducto =
                    $precio *
                    (
                        $porcentajeImpuesto / 100
                    );
            }
        }


        //==============================================
        // REDONDEAR IMPUESTO
        //==============================================

        $impuestoProducto =
            round(
                $impuestoProducto,
                2
            );


        //==============================================
        // ACUMULAR SUBTOTAL
        //==============================================

        $subtotal +=
            $subtotalProducto;


        //==============================================
        // ACUMULAR IMPUESTO
        //==============================================

        $impuestoTotal +=
            $impuestoProducto;


        //==============================================
        // DATOS DEL DETALLE
        //==============================================

        $item["subtotal_producto"] =
            $subtotalProducto;

        $item["aplica_impuesto_detalle"] =
            $aplicaImpuestoProducto;

        $item["porcentaje_impuesto"] =
            (
                $aplicaImpuestoProducto === 1 &&
                $impuestoActivo === 1
            )
            ? $porcentajeImpuesto
            : 0;

        $item["monto_impuesto"] =
            $impuestoProducto;
    }

    unset($item);


    //==================================================
    // REDONDEAR TOTALES
    //==================================================

    $subtotal =
        round(
            $subtotal,
            2
        );

    $impuestoTotal =
        round(
            $impuestoTotal,
            2
        );


    //==================================================
    // TOTAL VENTA
    //==================================================

    if (
        $impuestoActivo === 1 &&
        $preciosIncluyenImpuesto === 0
    ) {

        $totalVenta =
            round(
                $subtotal +
                    $impuestoTotal,
                2
            );
    } else {

        $totalVenta =
            round(
                $subtotal,
                2
            );
    }


    //==================================================
    // VALIDAR TOTAL
    //==================================================

    if ($totalVenta <= 0) {

        throw new Exception(
            "El total de la compra no es válido."
        );
    }


    //==================================================
    // EMPLEADO
    //==================================================
    //
    // IMPORTANTE:
    //
    // El cliente NO selecciona empleado.
    //
    // El administrador asignará posteriormente
    // el empleado correspondiente.
    //
    // Por eso el pedido se registra con:
    //
    //     id_empleado = 0
    //
    // No se consulta la tabla empleados.
    // No se exige ningún empleado activo.
    //
    //==================================================

    $idEmpleado = 0;


    //==================================================
    // REPARTIDOR
    //==================================================
    //
    // El pedido todavía no tiene repartidor asignado.
    //
    // El administrador lo asignará posteriormente.
    //
    // Por eso se registra:
    //
    //     id_repartidor = 0
    //
    //==================================================

    $idRepartidor = 0;


    //==================================================
    // DATOS DEL COMPROBANTE
    //==================================================

    $tipoComprobante =
        "BOLETA";

    $serie =
        "B001";


    //==================================================
    // GENERAR NÚMERO DE COMPROBANTE
    //==================================================
    //
    // NO filtramos por tipo_comprobante ni serie para
    // calcular el siguiente número.
    //
    // Esto evita conflictos con unique_comprobante si
    // dicho índice utiliza:
    //
    // id_user + id_repartidor + numero
    //
    // o una combinación relacionada.
    //
    // FOR UPDATE permite serializar la lectura del
    // último número existente dentro de la transacción.
    //
    //==================================================

    $numero =
        1;


    $sqlNumero = "
        SELECT
            numero
        FROM ticket_ventas
        WHERE id_user = ?
        ORDER BY numero DESC
        LIMIT 1
        FOR UPDATE
    ";

    $stmtNumero =
        mysqli_prepare(
            $conexion,
            $sqlNumero
        );

    if (!$stmtNumero) {

        throw new Exception(
            "No se pudo preparar la numeración del comprobante."
        );
    }

    mysqli_stmt_bind_param(
        $stmtNumero,
        "i",
        $idUser
    );

    mysqli_stmt_execute(
        $stmtNumero
    );

    $resultadoNumero =
        mysqli_stmt_get_result(
            $stmtNumero
        );

    $filaNumero =
        mysqli_fetch_assoc(
            $resultadoNumero
        );

    mysqli_stmt_close(
        $stmtNumero
    );


    if ($filaNumero) {

        $ultimoNumero =
            intval(
                $filaNumero["numero"]
            );

        $numero =
            $ultimoNumero + 1;
    }


    //==================================================
    // FECHA Y HORA
    //==================================================

    $fechaVenta =
        date("Y-m-d");

    $horaVenta =
        date("H:i:s");

    $fechaActualizado =
        date("Y-m-d");


    //==================================================
    // ESTADOS
    //==================================================

    $estadoVenta =
        "PENDIENTE";

    $estadoEnvio =
        "PENDIENTE";


    //==================================================
    // PAGO
    //==================================================

    $pagoCliente =
        $totalVenta;

    $vueltoVenta =
        0;


    //==================================================
    // APLICA IGV
    //==================================================

    $aplicaIgv =
        (
            $impuestoActivo === 1 &&
            $impuestoTotal > 0
        )
        ? 1
        : 0;


    //==================================================
    // REGISTRAR TICKET
    //==================================================

    $sqlTicket = "
        INSERT INTO ticket_ventas
        (
            id_user,
            idCliente,
            direccion_envio,
            pago_cliente,
            total_venta,
            id_metodo_pago,
            estado_venta,
            fecha_venta,
            hora_venta,
            vuelto_venta,
            id_empleado,
            id_repartidor,
            tipo_comprobante,
            serie,
            numero,
            aplica_igv,
            estado_envio,
            observacion_envio
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?
        )
    ";


    $stmtTicket =
        mysqli_prepare(
            $conexion,
            $sqlTicket
        );

    if (!$stmtTicket) {

        throw new Exception(
            "No se pudo preparar el registro de la venta."
        );
    }


    //==================================================
    // 18 CAMPOS
    //
    // i  id_user
    // i  idCliente
    // s  direccion
    // d  pago_cliente
    // d  total_venta
    // i  id_metodo_pago
    // s  estado_venta
    // s  fecha_venta
    // s  hora_venta
    // d  vuelto_venta
    // i  id_empleado
    // i  id_repartidor
    // s  tipo_comprobante
    // s  serie
    // i  numero
    // i  aplica_igv
    // s  estado_envio
    // s  observacion_envio
    //
    //==================================================

    mysqli_stmt_bind_param(
        $stmtTicket,
        "iisddiissdiissiiss",
        $idUser,
        $idCliente,
        $direccion,
        $pagoCliente,
        $totalVenta,
        $idMetodoPago,
        $estadoVenta,
        $fechaVenta,
        $horaVenta,
        $vueltoVenta,
        $idEmpleado,
        $idRepartidor,
        $tipoComprobante,
        $serie,
        $numero,
        $aplicaIgv,
        $estadoEnvio,
        $comentarios
    );


    //==================================================
    // INTENTAR REGISTRAR TICKET
    //==================================================

    if (!mysqli_stmt_execute($stmtTicket)) {

        $codigoError =
            mysqli_stmt_errno(
                $stmtTicket
            );

        $mensajeError =
            mysqli_stmt_error(
                $stmtTicket
            );


        mysqli_stmt_close(
            $stmtTicket
        );


        //==============================================
        // DUPLICADO DEL COMPROBANTE
        //==============================================

        if ($codigoError === 1062) {

            throw new Exception(
                "El número de comprobante ya existe. Actualice la página e inténtelo nuevamente."
            );
        }


        throw new Exception(
            "No se pudo registrar el pedido."
        );
    }


    //==================================================
    // OBTENER ID TICKET
    //==================================================

    $idTicket =
        mysqli_insert_id(
            $conexion
        );


    mysqli_stmt_close(
        $stmtTicket
    );


    //==================================================
    // VALIDAR ID TICKET
    //==================================================

    if ($idTicket <= 0) {

        throw new Exception(
            "No se pudo obtener el número del pedido."
        );
    }


    //==================================================
    // REGISTRAR DETALLES
    //==================================================

    $sqlDetalle = "
        INSERT INTO detalle_ticket_ventas
        (
            id_user,
            idProducto,
            id_ticket_ventas,
            cantidad_pedido_producto,
            aplica_impuesto,
            porcentaje_impuesto,
            monto_impuesto,
            sub_total
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?
        )
    ";


    $stmtDetalle =
        mysqli_prepare(
            $conexion,
            $sqlDetalle
        );

    if (!$stmtDetalle) {

        throw new Exception(
            "No se pudo preparar el detalle de la venta."
        );
    }


    //==================================================
    // ACTUALIZAR STOCK
    //==================================================

    $sqlStock = "
        UPDATE producto
        SET
            stock = stock - ?,
            fecha_actualizado = ?
        WHERE idProducto = ?
        AND id_user = ?
        AND Eliminado = 0
        AND stock >= ?
    ";


    $stmtStock =
        mysqli_prepare(
            $conexion,
            $sqlStock
        );

    if (!$stmtStock) {

        throw new Exception(
            "No se pudo preparar la actualización del stock."
        );
    }


    //==================================================
    // BUSCAR CANTIDAD VENDIDA
    //==================================================

    $sqlCantidadVendida = "
        SELECT
            id_cantidad
        FROM cantidad_producto_vendido
        WHERE id_user = ?
        AND idProducto = ?
        LIMIT 1
        FOR UPDATE
    ";


    $stmtCantidadBuscar =
        mysqli_prepare(
            $conexion,
            $sqlCantidadVendida
        );

    if (!$stmtCantidadBuscar) {

        throw new Exception(
            "No se pudo preparar el control de cantidad vendida."
        );
    }


    //==================================================
    // INSERTAR CANTIDAD VENDIDA
    //==================================================

    $sqlCantidadInsertar = "
        INSERT INTO cantidad_producto_vendido
        (
            id_user,
            idProducto,
            cantidad_total
        )
        VALUES
        (
            ?,
            ?,
            ?
        )
    ";


    $stmtCantidadInsertar =
        mysqli_prepare(
            $conexion,
            $sqlCantidadInsertar
        );

    if (!$stmtCantidadInsertar) {

        throw new Exception(
            "No se pudo preparar el registro de cantidad vendida."
        );
    }


    //==================================================
    // ACTUALIZAR CANTIDAD VENDIDA
    //==================================================

    $sqlCantidadActualizar = "
        UPDATE cantidad_producto_vendido
        SET
            cantidad_total =
                cantidad_total + ?
        WHERE id_cantidad = ?
    ";


    $stmtCantidadActualizar =
        mysqli_prepare(
            $conexion,
            $sqlCantidadActualizar
        );

    if (!$stmtCantidadActualizar) {

        throw new Exception(
            "No se pudo preparar la actualización de cantidad vendida."
        );
    }


    //==================================================
    // PROCESAR PRODUCTOS
    //==================================================

    foreach ($items as $item) {

        $idProducto =
            intval(
                $item["idProducto"]
            );

        $cantidad =
            intval(
                $item["cantidad"]
            );

        $aplicaImpuesto =
            intval(
                $item["aplica_impuesto_detalle"]
            );

        $porcentajeDetalle =
            floatval(
                $item["porcentaje_impuesto"]
            );

        $montoImpuesto =
            round(
                floatval(
                    $item["monto_impuesto"]
                ),
                2
            );

        $subTotalDetalle =
            round(
                floatval(
                    $item["subtotal_producto"]
                ),
                2
            );


        //==============================================
        // INSERTAR DETALLE
        //==============================================

        mysqli_stmt_bind_param(
            $stmtDetalle,
            "iiiiiddd",
            $idUser,
            $idProducto,
            $idTicket,
            $cantidad,
            $aplicaImpuesto,
            $porcentajeDetalle,
            $montoImpuesto,
            $subTotalDetalle
        );


        if (
            !mysqli_stmt_execute(
                $stmtDetalle
            )
        ) {

            throw new Exception(
                "No se pudo registrar el detalle del producto: " .
                    ($item["nombre"] ?? "Producto")
            );
        }


        //==============================================
        // DESCONTAR STOCK
        //==============================================

        mysqli_stmt_bind_param(
            $stmtStock,
            "isiii",
            $cantidad,
            $fechaActualizado,
            $idProducto,
            $idUser,
            $cantidad
        );


        if (
            !mysqli_stmt_execute(
                $stmtStock
            )
        ) {

            throw new Exception(
                "No se pudo actualizar el stock del producto: " .
                    ($item["nombre"] ?? "Producto")
            );
        }


        //==============================================
        // VALIDAR STOCK ACTUALIZADO
        //==============================================

        if (
            mysqli_stmt_affected_rows(
                $stmtStock
            ) !== 1
        ) {

            throw new Exception(
                "El stock cambió mientras se procesaba el pedido para el producto: " .
                    ($item["nombre"] ?? "Producto") .
                    ". Actualice el carrito e inténtelo nuevamente."
            );
        }


        //==============================================
        // BUSCAR CANTIDAD VENDIDA
        //==============================================

        mysqli_stmt_bind_param(
            $stmtCantidadBuscar,
            "ii",
            $idUser,
            $idProducto
        );

        mysqli_stmt_execute(
            $stmtCantidadBuscar
        );

        $resultadoCantidad =
            mysqli_stmt_get_result(
                $stmtCantidadBuscar
            );

        $cantidadVendida =
            mysqli_fetch_assoc(
                $resultadoCantidad
            );


        //==============================================
        // ACTUALIZAR O INSERTAR
        //==============================================

        if ($cantidadVendida) {

            $idCantidad =
                intval(
                    $cantidadVendida["id_cantidad"]
                );


            mysqli_stmt_bind_param(
                $stmtCantidadActualizar,
                "ii",
                $cantidad,
                $idCantidad
            );


            if (
                !mysqli_stmt_execute(
                    $stmtCantidadActualizar
                )
            ) {

                throw new Exception(
                    "No se pudo actualizar la cantidad vendida del producto."
                );
            }
        } else {

            mysqli_stmt_bind_param(
                $stmtCantidadInsertar,
                "iii",
                $idUser,
                $idProducto,
                $cantidad
            );


            if (
                !mysqli_stmt_execute(
                    $stmtCantidadInsertar
                )
            ) {

                throw new Exception(
                    "No se pudo registrar la cantidad vendida del producto."
                );
            }
        }
    }


    //==================================================
    // CERRAR STATEMENTS
    //==================================================

    mysqli_stmt_close(
        $stmtDetalle
    );

    mysqli_stmt_close(
        $stmtStock
    );

    mysqli_stmt_close(
        $stmtCantidadBuscar
    );

    mysqli_stmt_close(
        $stmtCantidadInsertar
    );

    mysqli_stmt_close(
        $stmtCantidadActualizar
    );


    //==================================================
    // CERRAR CARRITO
    //==================================================

    $sqlCerrarCarrito = "
        UPDATE carrito_online
        SET
            estado = 'comprado',
            fecha_actualizado = NOW()
        WHERE idCliente = ?
        AND estado = 'pendiente'
    ";


    $stmtCerrarCarrito =
        mysqli_prepare(
            $conexion,
            $sqlCerrarCarrito
        );

    if (!$stmtCerrarCarrito) {

        throw new Exception(
            "No se pudo preparar el cierre del carrito."
        );
    }


    mysqli_stmt_bind_param(
        $stmtCerrarCarrito,
        "i",
        $idCliente
    );


    if (
        !mysqli_stmt_execute(
            $stmtCerrarCarrito
        )
    ) {

        throw new Exception(
            "No se pudo cerrar el carrito."
        );
    }


    mysqli_stmt_close(
        $stmtCerrarCarrito
    );


    //==================================================
    // ACTUALIZAR DIRECCIÓN
    //==================================================

    if ($guardarDireccion === 1) {

        $sqlActualizarDireccion = "
            UPDATE clientes
            SET
                direccion = ?,
                fecha_actualizado = ?
            WHERE idCliente = ?
        ";


        $stmtActualizarDireccion =
            mysqli_prepare(
                $conexion,
                $sqlActualizarDireccion
            );

        if (!$stmtActualizarDireccion) {

            throw new Exception(
                "No se pudo preparar la actualización de dirección."
            );
        }


        mysqli_stmt_bind_param(
            $stmtActualizarDireccion,
            "ssi",
            $direccion,
            $fechaActualizado,
            $idCliente
        );


        if (
            !mysqli_stmt_execute(
                $stmtActualizarDireccion
            )
        ) {

            throw new Exception(
                "No se pudo actualizar la dirección del cliente."
            );
        }


        mysqli_stmt_close(
            $stmtActualizarDireccion
        );
    }


    //==================================================
    // NOTIFICACIÓN CLIENTE
    //==================================================

    $tituloNotificacion =
        "Pedido realizado correctamente";


    $mensajeNotificacion =
        "Tu pedido #" .
        $idTicket .
        " fue registrado correctamente por un total de S/ " .
        number_format(
            $totalVenta,
            2,
            ".",
            ""
        ) .
        ".";


    $iconoNotificacion =
        "bi-bag-check-fill";


    $colorNotificacion =
        "success";


    $urlNotificacion =
        "pedido_confirmado.php?id=" .
        $idTicket;


    $tipoNotificacion =
        "pedido";


    $sqlNotificacion = "
        INSERT INTO notificaciones_cliente
        (
            idCliente,
            titulo,
            mensaje,
            icono,
            color,
            url,
            leido,
            fecha,
            Eliminado,
            tipo
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            0,
            NOW(),
            0,
            ?
        )
    ";


    $stmtNotificacion =
        mysqli_prepare(
            $conexion,
            $sqlNotificacion
        );


    if ($stmtNotificacion) {

        mysqli_stmt_bind_param(
            $stmtNotificacion,
            "issssss",
            $idCliente,
            $tituloNotificacion,
            $mensajeNotificacion,
            $iconoNotificacion,
            $colorNotificacion,
            $urlNotificacion,
            $tipoNotificacion
        );


        //==============================================
        // LA NOTIFICACIÓN NO DEBE CANCELAR LA COMPRA
        //==============================================

        mysqli_stmt_execute(
            $stmtNotificacion
        );


        mysqli_stmt_close(
            $stmtNotificacion
        );
    }


    //==================================================
    // CONFIRMAR TRANSACCIÓN
    //==================================================

    mysqli_commit(
        $conexion
    );


    //==================================================
    // RESPUESTA EXITOSA
    //==================================================

    responder(
        true,
        "Tu pedido fue registrado correctamente.",
        [
            "id_ticket" =>
            $idTicket,

            "numero_comprobante" =>
            $numero,

            "serie" =>
            $serie,

            "tipo_comprobante" =>
            $tipoComprobante,

            "total" =>
            number_format(
                $totalVenta,
                2,
                ".",
                ""
            ),

            "subtotal" =>
            number_format(
                $subtotal,
                2,
                ".",
                ""
            ),

            "impuesto" =>
            number_format(
                $impuestoTotal,
                2,
                ".",
                ""
            ),

            "total_productos" =>
            $totalProductos,

            "nombre_impuesto" =>
            $nombreImpuesto,

            "porcentaje_impuesto" =>
            $porcentajeImpuesto,

            //==========================================
            // ASIGNACIÓN PENDIENTE
            //==========================================

            "id_empleado" =>
            0,

            "id_repartidor" =>
            0
        ]
    );
} catch (Throwable $e) {


    //==================================================
    // REVERTIR TRANSACCIÓN
    //==================================================

    mysqli_rollback(
        $conexion
    );


    //==================================================
    // LOG
    //==================================================

    error_log(
        "ERROR FINALIZAR COMPRA | " .
            "Cliente: " .
            $idCliente .
            " | " .
            "User: " .
            ($idUser ?? 0) .
            " | " .
            "Error: " .
            $e->getMessage()
    );


    //==================================================
    // RESPUESTA
    //==================================================

    responder(
        false,
        $e->getMessage()
    );
}
?>