<?php
//======================================================
// CoDevPro Technology
// ajax/obtener_marca.php
//======================================================

session_start();

header("Content-Type: application/json; charset=utf-8");

require_once "../controladores/conexion.php";


//======================================================
// VALIDAR SESION
//======================================================

if (!isset($_SESSION["idUser"])) {

    echo json_encode([

        "estado" => false,

        "mensaje" => "Sesión no válida"

    ]);

    exit;
}


$idUser = intval($_SESSION["idUser"]);


//======================================================
// RECIBIR ID MARCA
//======================================================

$idMarca = intval($_POST["idMarca"] ?? 0);


if ($idMarca <= 0) {


    echo json_encode([

        "estado" => false,

        "mensaje" => "Marca no válida"

    ]);


    exit;
}



//======================================================
// OBTENER MARCA
//======================================================

$sql = "

SELECT *

FROM marcas

WHERE id_marca = ?

AND id_user = ?

AND Eliminado = 0

LIMIT 1

";


$stmt = mysqli_prepare(
    $conexion,
    $sql
);


mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $idMarca,
    $idUser
);


mysqli_stmt_execute($stmt);


$resultado = mysqli_stmt_get_result($stmt);


$marca = mysqli_fetch_assoc($resultado);



if (!$marca) {


    echo json_encode([

        "estado" => false,

        "mensaje" => "La marca no existe"

    ]);


    exit;
}



//======================================================
// IMAGEN
//======================================================


$imagen = "assets/img/sin_imagen.png";


if (!empty($marca["imagen"])) {


    $imagen =

        "data:image/jpeg;base64," .

        base64_encode($marca["imagen"]);
}



//======================================================
// TOTAL PRODUCTOS ASOCIADOS
//======================================================

$sqlProductos = "

SELECT COUNT(*) total

FROM producto

WHERE id_marca = ?

AND id_user = ?

AND Eliminado = 0

";


$stmtProductos = mysqli_prepare(
    $conexion,
    $sqlProductos
);


mysqli_stmt_bind_param(
    $stmtProductos,
    "ii",
    $idMarca,
    $idUser
);


mysqli_stmt_execute($stmtProductos);


$totalProductos =

    mysqli_stmt_get_result($stmtProductos)

        ->fetch_assoc()["total"] ?? 0;




//======================================================
// TOTAL PRODUCTOS VENDIDOS
//======================================================

$sqlVendidos = "

SELECT 

COALESCE(
SUM(cpv.cantidad_total),
0
) total

FROM cantidad_producto_vendido cpv


INNER JOIN producto p

ON p.idProducto = cpv.idProducto


WHERE p.id_marca = ?

AND p.id_user = ?

AND p.Eliminado = 0

";


$stmtVendidos = mysqli_prepare(
    $conexion,
    $sqlVendidos
);


mysqli_stmt_bind_param(
    $stmtVendidos,
    "ii",
    $idMarca,
    $idUser
);


mysqli_stmt_execute($stmtVendidos);



$totalVendidos =

    mysqli_stmt_get_result($stmtVendidos)

        ->fetch_assoc()["total"] ?? 0;



//======================================================
// GENERAR HTML
//======================================================

ob_start();

?>

<div class="container-fluid p-3">


    <!--=====================================
    CABECERA MARCA
    =====================================-->

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body bg-light">


            <div class="row align-items-center">


                <div class="col-md-4 text-center">


                    <img

                        src="<?= $imagen ?>"

                        class="rounded shadow border"

                        style="
                    width:180px;
                    height:180px;
                    object-fit:cover;
                    ">


                </div>



                <div class="col-md-8">


                    <h2 class="fw-bold mb-2">

                        <?= htmlspecialchars($marca["nombre"]) ?>

                    </h2>


                    <p class="text-muted">

                        Información detallada de la marca registrada.

                    </p>



                    <?php if ($totalProductos > 0): ?>


                        <span class="badge bg-success fs-6">

                            <i class="bi bi-check-circle me-1"></i>

                            Marca en uso

                        </span>


                    <?php else: ?>


                        <span class="badge bg-secondary fs-6">

                            Sin productos asociados

                        </span>


                    <?php endif; ?>


                </div>


            </div>


        </div>

    </div>





    <!--=====================================
    KPIS
    =====================================-->

    <div class="row g-3 mb-4">



        <div class="col-md-4">


            <div class="card border-0 shadow-sm">


                <div class="card-body text-center">


                    <i class="bi bi-box-seam-fill text-primary fs-1"></i>


                    <h3 class="fw-bold mt-2">

                        <?= $totalProductos ?>

                    </h3>


                    <small class="text-muted">

                        Productos Asociados

                    </small>


                </div>


            </div>


        </div>




        <div class="col-md-4">


            <div class="card border-0 shadow-sm">


                <div class="card-body text-center">


                    <i class="bi bi-cart-check-fill text-success fs-1"></i>


                    <h3 class="fw-bold mt-2">

                        <?= $totalVendidos ?>

                    </h3>


                    <small class="text-muted">

                        Productos Vendidos

                    </small>


                </div>


            </div>


        </div>




        <div class="col-md-4">


            <div class="card border-0 shadow-sm">


                <div class="card-body text-center">


                    <i class="bi bi-hash text-warning fs-1"></i>


                    <h3 class="fw-bold mt-2">

                        #<?= $marca["id_marca"] ?>

                    </h3>


                    <small class="text-muted">

                        ID Marca

                    </small>


                </div>


            </div>


        </div>



    </div>





    <!--=====================================
    INFORMACION GENERAL
    =====================================-->


    <div class="card border-0 shadow-sm">


        <div class="card-header bg-white">


            <h5 class="mb-0">


                <i class="bi bi-info-circle-fill text-primary me-2"></i>


                Información General


            </h5>


        </div>



        <div class="card-body">


            <div class="row g-4">


                <div class="col-md-6">


                    <label class="text-muted small">

                        Nombre Marca

                    </label>


                    <h5 class="fw-bold">


                        <?= htmlspecialchars($marca["nombre"]) ?>


                    </h5>


                </div>




                <div class="col-md-6">


                    <label class="text-muted small">

                        Estado

                    </label>


                    <div>


                        <?php if ($totalProductos > 0): ?>


                            <span class="badge bg-success">

                                Activa

                            </span>


                        <?php else: ?>


                            <span class="badge bg-secondary">

                                Disponible

                            </span>


                        <?php endif; ?>


                    </div>


                </div>




                <div class="col-md-6">


                    <label class="text-muted small">

                        Productos asociados

                    </label>


                    <h6 class="fw-semibold">

                        <?= $totalProductos ?>

                    </h6>


                </div>



                <div class="col-md-6">


                    <label class="text-muted small">

                        Productos vendidos

                    </label>


                    <h6 class="fw-semibold">

                        <?= $totalVendidos ?>

                    </h6>


                </div>



            </div>


        </div>


    </div>


</div>


<?php


$html = ob_get_clean();



//======================================================
// RESPUESTA JSON
//======================================================

echo json_encode([

    "estado" => true,

    "html" => $html

]);
