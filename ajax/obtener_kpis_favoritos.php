<?php
//=========================================================
// CoDevPro Technology
// ajax/obtener_kpis_favoritos.php
//=========================================================

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once "../controladores/conexion.php";

/*=========================================================
=            VALIDAR SESION
=========================================================*/

$idUser = $_SESSION["idUser"] ?? 0;

if (!$idUser) {

    echo json_encode([
        "ok" => false,
        "mensaje" => "Sesión no válida"
    ]);

    exit;
}

/*=========================================================
=            KPI 1 TOTAL FAVORITOS
=========================================================*/

$sqlTotal = "SELECT COUNT(*) total
            FROM favoritos f
            INNER JOIN producto p
                ON p.idProducto = f.idProducto
            WHERE p.id_user = ?";

$stmt = mysqli_prepare($conexion, $sqlTotal);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $idUser
);

mysqli_stmt_execute($stmt);

$totalFavoritos = mysqli_fetch_assoc(
    mysqli_stmt_get_result($stmt)
)["total"] ?? 0;

/*=========================================================
=            KPI 2 CLIENTES UNICOS
=========================================================*/

$sqlClientes = "SELECT COUNT(DISTINCT f.idCliente) total
                FROM favoritos f
                INNER JOIN producto p
                    ON p.idProducto = f.idProducto
                WHERE p.id_user = ?";

$stmt = mysqli_prepare(
    $conexion,
    $sqlClientes
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $idUser
);

mysqli_stmt_execute($stmt);

$totalClientes = mysqli_fetch_assoc(
    mysqli_stmt_get_result($stmt)
)["total"] ?? 0;

/*=========================================================
=            KPI 3 PRODUCTOS UNICOS
=========================================================*/

$sqlProductos = "SELECT COUNT(DISTINCT f.idProducto) total
                 FROM favoritos f
                 INNER JOIN producto p
                    ON p.idProducto = f.idProducto
                 WHERE p.id_user=?";

$stmt = mysqli_prepare(
    $conexion,
    $sqlProductos
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $idUser
);

mysqli_stmt_execute($stmt);

$totalProductos = mysqli_fetch_assoc(
    mysqli_stmt_get_result($stmt)
)["total"] ?? 0;

/*=========================================================
=            KPI 4 PRODUCTO MAS FAVORITO
=========================================================*/

$sqlTop = "SELECT

                p.nombre,
                COUNT(*) total

            FROM favoritos f

            INNER JOIN producto p
                ON p.idProducto = f.idProducto

            WHERE p.id_user = ?

            GROUP BY p.idProducto

            ORDER BY total DESC

            LIMIT 1";

$stmt = mysqli_prepare(
    $conexion,
    $sqlTop
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $idUser
);

mysqli_stmt_execute($stmt);

$topProducto = mysqli_fetch_assoc(
    mysqli_stmt_get_result($stmt)
);

$nombreTopProducto =
    $topProducto["nombre"] ?? "Sin datos";

//Clientes con mas favoritos
$sqlTopCliente = "

SELECT

    c.nombre,
    COUNT(*) total

FROM favoritos f

INNER JOIN clientes c
    ON c.idCliente = f.idCliente

INNER JOIN producto p
    ON p.idProducto = f.idProducto

WHERE p.id_user = ?

GROUP BY c.idCliente

ORDER BY total DESC

LIMIT 1

";
$stmt = mysqli_prepare($conexion, $sqlTopCliente);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $idUser
);

mysqli_stmt_execute($stmt);

$topCliente = mysqli_fetch_assoc(
    mysqli_stmt_get_result($stmt)
);

$nombreTopCliente =
    $topCliente["nombre"] ?? "--";
//Producto mas favorito del mes
$sqlMes = "

SELECT

    p.nombre,
    COUNT(*) total

FROM favoritos f

INNER JOIN producto p
    ON p.idProducto = f.idProducto

WHERE p.id_user = ?

AND MONTH(f.fecha)=MONTH(NOW())
AND YEAR(f.fecha)=YEAR(NOW())

GROUP BY p.idProducto

ORDER BY total DESC

LIMIT 1

";
$stmt = mysqli_prepare($conexion, $sqlMes);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $idUser
);

mysqli_stmt_execute($stmt);

$topMes = mysqli_fetch_assoc(
    mysqli_stmt_get_result($stmt)
);

$nombreTopMes =
    $topMes["nombre"] ?? "--";
//Coversion favorito -> venta
$sqlConversion = "

SELECT COUNT(DISTINCT f.idProducto) favoritos,

(

SELECT COUNT(DISTINCT d.idProducto)

FROM detalle_ticket_ventas d

INNER JOIN producto p2
    ON p2.idProducto=d.idProducto

WHERE p2.id_user=?

) vendidos

FROM favoritos f

INNER JOIN producto p
    ON p.idProducto=f.idProducto

WHERE p.id_user=?

";
$stmt = mysqli_prepare(
    $conexion,
    $sqlConversion
);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $idUser,
    $idUser
);

mysqli_stmt_execute($stmt);

$conversion = mysqli_fetch_assoc(
    mysqli_stmt_get_result($stmt)
);

$favoritos = intval(
    $conversion["favoritos"] ?? 0
);

$vendidos = intval(
    $conversion["vendidos"] ?? 0
);

$porcentajeConversion = 0;

if ($favoritos > 0) {

    $porcentajeConversion =
        round(
            ($vendidos / $favoritos) * 100,
            1
        );
}
$sqlValorPotencial = "

SELECT

    SUM(p.precio) total

FROM favoritos f

INNER JOIN producto p
    ON p.idProducto = f.idProducto

WHERE p.id_user = ?

";

$stmt = mysqli_prepare(
    $conexion,
    $sqlValorPotencial
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $idUser
);

mysqli_stmt_execute($stmt);

$valorPotencial = mysqli_fetch_assoc(
    mysqli_stmt_get_result($stmt)
);

$totalValorPotencial =
    number_format(
        $valorPotencial["total"] ?? 0,
        2
    );
/*=========================================================
=            GRAFICO PRODUCTOS FAVORITOS
=========================================================*/

$sqlGraficoProductos = "SELECT

                            p.nombre,
                            COUNT(*) total

                        FROM favoritos f

                        INNER JOIN producto p
                            ON p.idProducto = f.idProducto

                        WHERE p.id_user = ?

                        GROUP BY p.idProducto

                        ORDER BY total DESC

                        LIMIT 10";

$stmt = mysqli_prepare(
    $conexion,
    $sqlGraficoProductos
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $idUser
);

mysqli_stmt_execute($stmt);

$resProductos = mysqli_stmt_get_result($stmt);

$labelsProductos = [];
$datosProductos  = [];

while ($fila = mysqli_fetch_assoc($resProductos)) {

    $labelsProductos[] = $fila["nombre"];

    $datosProductos[] = (int)$fila["total"];
}

/*=========================================================
=            GRAFICO CATEGORIAS FAVORITAS
=========================================================*/

$sqlCategorias = "SELECT

                    c.nombre,
                    COUNT(*) total

                  FROM favoritos f

                  INNER JOIN producto p
                        ON p.idProducto = f.idProducto

                  LEFT JOIN categorias c
                        ON c.id_categorias = p.id_categorias

                  WHERE p.id_user = ?

                  GROUP BY p.id_categorias

                  ORDER BY total DESC

                  LIMIT 10";

$stmt = mysqli_prepare(
    $conexion,
    $sqlCategorias
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $idUser
);

mysqli_stmt_execute($stmt);

$resCategorias = mysqli_stmt_get_result($stmt);

$labelsCategorias = [];
$datosCategorias  = [];

while ($fila = mysqli_fetch_assoc($resCategorias)) {

    $labelsCategorias[] =
        $fila["nombre"] ?: "Sin categoría";

    $datosCategorias[] =
        (int)$fila["total"];
}

/*=========================================================
=            RESPUESTA
=========================================================*/

echo json_encode([

    "ok" => true,

    "kpis" => [

        "totalFavoritos" => $totalFavoritos,

        "clientes" => $totalClientes,

        "productos" => $totalProductos,

        "topProducto" => $nombreTopProducto,

        "topCliente" => $nombreTopCliente,

        "topMes" => $nombreTopMes,

        "conversion" => $porcentajeConversion,

        "valorPotencial" => $totalValorPotencial
    ],

    "graficoProductos" => [

        "labels" => $labelsProductos,

        "data" => $datosProductos
    ],

    "graficoCategorias" => [

        "labels" => $labelsCategorias,

        "data" => $datosCategorias
    ]
]);
