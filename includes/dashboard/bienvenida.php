<?php
//=====================================================
// CoDevPro Technology
// includes/dashboard/bienvenida.php
//=====================================================

/*=============================================
=            TOTAL PRODUCTOS
=============================================*/

$sqlProductos = "SELECT COUNT(*) AS total
                FROM producto
                WHERE id_user = ?
                AND Eliminado = 0";

$stmt = mysqli_prepare($conexion, $sqlProductos);

mysqli_stmt_bind_param($stmt, "i", $idUser);

mysqli_stmt_execute($stmt);

$totalProductos = mysqli_fetch_assoc(
    mysqli_stmt_get_result($stmt)
)["total"];

/*=============================================
=            TOTAL CLIENTES
=============================================*/

$sqlClientes = "SELECT COUNT(*) AS total
                FROM clientes
                WHERE id_user = ?
                AND Eliminado = 0";

$stmt = mysqli_prepare($conexion, $sqlClientes);

mysqli_stmt_bind_param($stmt, "i", $idUser);

mysqli_stmt_execute($stmt);

$totalClientes = mysqli_fetch_assoc(
    mysqli_stmt_get_result($stmt)
)["total"];
/*=============================================
=            TOTAL VENTAS
=============================================*/

$sqlVentas = "SELECT COUNT(*) AS total
            FROM ticket_ventas
            WHERE id_user = ?";

$stmt = mysqli_prepare($conexion, $sqlVentas);

mysqli_stmt_bind_param($stmt, "i", $idUser);

mysqli_stmt_execute($stmt);

$totalVentas = mysqli_fetch_assoc(
    mysqli_stmt_get_result($stmt)
)["total"];
/*=============================================
=            TOTAL EMPLEADOS
=============================================*/

$sqlEmpleados = "SELECT COUNT(*) AS total
                FROM empleados
                WHERE id_user = ?";

$stmt = mysqli_prepare($conexion, $sqlEmpleados);

mysqli_stmt_bind_param($stmt, "i", $idUser);

mysqli_stmt_execute($stmt);

$totalEmpleados = mysqli_fetch_assoc(
    mysqli_stmt_get_result($stmt)
)["total"];
?>

<div class="card shadow-sm border-0 rounded-4 mb-4">

    <div class="card-body p-4">

        <div class="row align-items-center">


            <!--=====================================
            =            INFORMACIÓN
            =====================================-->

            <div class="col-lg-8">


                <span class="badge bg-primary px-3 py-2 mb-3">

                    Panel Administrativo

                </span>


                <h2 class="fw-bold">

                    <?= $saludo; ?>,

                    <?= htmlspecialchars($empresa["nombreEmpresa"]); ?>

                </h2>


                <p class="text-muted">

                    Bienvenido nuevamente al Dashboard Administrativo.
                    Desde aquí podrás administrar tu tienda online y
                    visualizar las estadísticas de tu negocio en tiempo real.

                </p>
            </div>



            <!--=====================================
            =            INFORMACIÓN DE LA EMPRESA
            =====================================-->

            <div class="col-lg-4 mt-4 mt-lg-0">


                <div class="text-center">


                    <div class="logo-empresa mb-4">

                        <img src="<?= $logoEmpresa; ?>"
                            alt="Logo de la empresa">

                    </div>


                    <h4>

                        <?= htmlspecialchars($empresa["nombreEmpresa"]); ?>

                    </h4>


                    <p class="text-muted mb-1">

                        RUC:
                        <?= htmlspecialchars($empresa["ruc"]); ?>

                    </p>


                    <p class="text-muted mb-3">

                        Miembro desde:
                        <?= date("d/m/Y", strtotime($empresa["fecha_registro"])); ?>

                    </p>
                    <div class="mt-3">

                        <small class="text-muted">

                            Fecha actual:
                            <?= $fechaActual; ?>

                        </small>

                    </div>


                </div>


            </div>


        </div>

    </div>

</div>