<?php
//=====================================================
// CoDevPro Technology
// admin_pedidos_clientes.php
//=====================================================

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once "controladores/conexion.php";

$idUser = $_SESSION["idUser"] ?? 0;

if (!$idUser) {

    header("Location: login.php");
    exit;
}

include "includes/head.php";
?>

<div class="d-flex">

    <!-- SIDEBAR -->

    <?php include "includes/admin_sidebar.php"; ?>



    <!-- CONTENIDO -->

    <div class="flex-grow-1">

        <!-- NAVBAR -->

        <?php include "includes/admin_navbar.php"; ?>


        <div class="container-fluid py-4 px-3">

            <!--=====================================
            =            TITULO
            =====================================-->

            <div class="d-flex justify-content-between align-items-center mb-4">

                <div>

                    <h2 class="fw-bold mb-1">

                        Gestión de Pedidos

                    </h2>

                    <p class="text-muted mb-0">

                        Administra todos los pedidos realizados
                        por los clientes de la tienda online.

                    </p>

                </div>

            </div>



            <!--=====================================
            =            KPI CARDS
            =====================================-->

            <div id="contenedorKPIPedidos">

                <?php include "includes/pedidos_clientes/kpis_pedidos.php"; ?>

            </div>



            <!--=====================================
            =            FILTROS
            =====================================-->

            <div class="card border-0 shadow-sm rounded-4 mb-3">

                <div class="card-body">

                    <div class="row g-3">


                        <!-- BUSCADOR -->

                        <div class="col-xl-4 col-lg-6">

                            <label class="form-label fw-semibold">

                                Buscar pedido

                            </label>

                            <input type="text"
                                class="form-control"
                                id="buscarPedido"
                                placeholder="Pedido, cliente, DNI o celular">

                        </div>



                        <!-- ESTADO -->

                        <div class="col-xl-2 col-lg-3">

                            <label class="form-label fw-semibold">

                                Estado

                            </label>

                            <select class="form-select"
                                id="filtroEstado">

                                <option value="">
                                    Todos
                                </option>

                                <option value="PENDIENTE">
                                    Pendiente
                                </option>

                                <option value="CONFIRMADO">
                                    Confirmado
                                </option>

                                <option value="PREPARANDO">
                                    Preparando
                                </option>

                                <option value="ENVIADO">
                                    Enviado
                                </option>

                                <option value="ENTREGADO">
                                    Entregado
                                </option>

                                <option value="CANCELADO">
                                    Cancelado
                                </option>

                            </select>

                        </div>



                        <!-- MÉTODO PAGO -->

                        <div class="col-xl-2 col-lg-3">

                            <label class="form-label fw-semibold">

                                Método Pago

                            </label>

                            <select class="form-select"
                                id="filtroMetodoPago">

                                <option value="">
                                    Todos
                                </option>

                            </select>

                        </div>



                        <!-- FECHA INICIO -->

                        <div class="col-xl-2 col-lg-3">

                            <label class="form-label fw-semibold">

                                Desde

                            </label>

                            <input type="date"
                                class="form-control"
                                id="fechaInicio">

                        </div>



                        <!-- FECHA FIN -->

                        <div class="col-xl-2 col-lg-3">

                            <label class="form-label fw-semibold">

                                Hasta

                            </label>

                            <input type="date"
                                class="form-control"
                                id="fechaFin">

                        </div>



                        <!-- ORDENAR -->

                        <div class="col-xl-3 col-lg-4">

                            <label class="form-label fw-semibold">

                                Ordenar

                            </label>

                            <select class="form-select"
                                id="ordenarPor">

                                <option value="recientes">
                                    Más recientes
                                </option>

                                <option value="antiguos">
                                    Más antiguos
                                </option>

                                <option value="mayor">
                                    Mayor monto
                                </option>

                                <option value="menor">
                                    Menor monto
                                </option>

                            </select>

                        </div>



                        <!-- BOTONES -->

                        <div class="col-xl-3 col-lg-4 d-flex align-items-end">

                            <button class="btn btn-primary me-2"
                                id="btnBuscarPedidos">

                                <i class="bi bi-search"></i>

                                Buscar

                            </button>


                            <button class="btn btn-outline-secondary"
                                id="btnLimpiarFiltros">

                                <i class="bi bi-arrow-clockwise"></i>

                            </button>

                        </div>


                    </div>

                </div>

            </div>



            <!--=====================================
            =            TABLA
            =====================================-->

            <div class="card border-0 shadow-sm rounded-3">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center mb-3">

                        <h5 class="fw-bold mb-0">

                            Lista de Pedidos

                        </h5>

                        <span class="badge bg-primary"
                            id="totalPedidos">

                            0 Pedidos

                        </span>

                    </div>



                    <div class="table-responsive">

                        <table class="table table-hover align-middle">

                            <thead class="table-light">

                                <tr>

                                    <th># Pedido</th>

                                    <th>Cliente</th>

                                    <th>Fecha</th>

                                    <th>Método Pago</th>

                                    <th>Total</th>

                                    <th>Estado</th>

                                    <th class="text-center">
                                        Acciones
                                    </th>

                                </tr>

                            </thead>

                            <tbody id="tablaPedidos">

                                <tr>

                                    <td colspan="7"
                                        class="text-center py-5">

                                        <div class="spinner-border text-primary"></div>

                                        <p class="mt-3 mb-0">

                                            Cargando pedidos...

                                        </p>

                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>



                    <!-- PAGINACIÓN -->

                    <div id="paginacionPedidos"
                        class="mt-4">

                    </div>

                </div>

            </div>

        </div>
        <!--=====================================
=            MODALES
======================================-->

        <?php
        include "includes/pedidos_clientes/modal_ver_pedido.php";
        include "includes/pedidos_clientes/modal_estado_pedido.php";
        ?>
    </div>

</div>







<!--=====================================
=            SCRIPTS
======================================-->
<script src="js/menu.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="js/adm_pedidos_clientes.js"></script>
</body>

</html>