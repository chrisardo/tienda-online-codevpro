<?php
//=====================================================
// CoDevPro Technology
// Archivo: adm_detalles_empleado.php
// Módulo: Detalles del Empleado
// Sistema: Inventa
//=====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//-----------------------------------------------------
// Verificar sesión
//-----------------------------------------------------

if (!isset($_SESSION['idUser'])) {
    header("Location: login.php");
    exit;
}

$idUser = (int) $_SESSION['idUser'];

//-----------------------------------------------------
// Obtener ID del empleado
//-----------------------------------------------------

$idEmpleado = isset($_GET['id_empleado'])
    ? (int) $_GET['id_empleado']
    : 0;

if ($idEmpleado <= 0) {
    header("Location: adm_lista_empleados.php");
    exit;
}
?>

<?php include "includes/head.php"; ?>

<!--=====================================================
    CONTENEDOR GENERAL ADMINISTRACIÓN
======================================================-->

<div class="d-flex">

    <!--=================================================
        SIDEBAR
    ==================================================-->

    <?php include "includes/admin_sidebar.php"; ?>


    <!--=================================================
        CONTENIDO PRINCIPAL
    ==================================================-->

    <div class="flex-grow-1">

        <!--=================================================
            NAVBAR
        ==================================================-->

        <?php include "includes/admin_navbar.php"; ?>


        <!--=================================================
            CONTENIDO
        ==================================================-->

        <main class="container-fluid px-4 py-4">


            <!--=================================================
                HEADER DE LA PÁGINA
            ==================================================-->

            <div class="detalle-header mb-4">

                <div class="d-flex
                            flex-column
                            flex-lg-row
                            align-items-lg-center
                            justify-content-between
                            gap-3">


                    <!--=========================================
                        TÍTULO
                    ==========================================-->

                    <div>

                        <div class="d-flex
                                    align-items-center
                                    gap-2
                                    mb-2">

                            <div class="detalle-header-icon">

                                <i class="bi bi-person-vcard"></i>

                            </div>

                            <h4 class="mb-0">

                                Detalles del empleado

                            </h4>

                        </div>


                        <p class="mb-0 text-muted">

                            Consulta la información,
                            cargo, permisos y actividad
                            del empleado.

                        </p>

                    </div>
                    <!--=========================================
    ACCIONES
=========================================-->

                    <div class="d-flex
            align-items-center
            gap-2
            flex-wrap">

                        <!--=====================================
        EDITAR DATOS DEL EMPLEADO
    ======================================-->

                        <button type="button"
                            class="btn btn-primary btn-editar-empleado"
                            data-id-empleado="<?= $idEmpleado ?>">

                            <i class="bi bi-pencil-square me-1"></i>

                            Editar datos

                        </button>


                        <!--=====================================
                            EDITAR IMAGEN DEL EMPLEADO
                        ======================================-->

                        <button type="button"
                            class="btn btn-outline-primary btn-editar-imagen-empleado"
                            data-id-empleado="<?= $idEmpleado ?>">

                            <i class="bi bi-camera me-1"></i>

                            Editar imagen

                        </button>
                        <!--=====================================
                            WHATSAPP
                        ======================================-->

                        <button
                            type="button"
                            class="btn btn-outline-success btn-whatsapp-empleado"
                            data-id-empleado="<?= $idEmpleado ?>">

                            <i class="bi bi-whatsapp me-1"></i>

                            WhatsApp

                        </button>
                        <!--=====================================
    CORREO
======================================-->

                        <button
                            type="button"
                            class="btn btn-outline-secondary btn-correo-empleado"
                            data-id-empleado="<?= $idEmpleado ?>">

                            <i class="bi bi-envelope me-1"></i>

                            Correo

                        </button>
                        

                    </div>
                </div>

            </div>


            <!--=================================================
                CONTENEDOR PRINCIPAL DEL DETALLE
            ==================================================-->

            <div id="contenedorDetalleEmpleado">


                <!--=================================================
                    LOADING
                ==================================================-->

                <div class="detalle-card">

                    <div class="detalle-loading">

                        <div class="detalle-loading-content">


                            <div class="detalle-spinner">

                                <div class="spinner-border
                                            text-primary"
                                    role="status">

                                    <span class="visually-hidden">
                                        Cargando...
                                    </span>

                                </div>

                            </div>


                            <div class="mt-3">

                                <div class="fw-semibold">

                                    Cargando información del empleado...

                                </div>

                                <div class="text-muted small mt-1">

                                    Espere un momento mientras
                                    obtenemos los datos.

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


        </main>

    </div>

</div>


<!--=====================================================
    MODAL EDITAR IMAGEN EMPLEADO
======================================================-->

<?php include "modal/modal_editar_imagen_empleado.php"; ?>


<!--=====================================================
    MODAL EDITAR EMPLEADO
======================================================-->

<?php include "modal/modal_editar_empleado.php"; ?>


<!--=====================================================
    SCRIPTS
======================================================-->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="js/menu.js"></script>

<script src="js/adm_detalles_empleado.js"></script>

<script src="js/adm_lista_empleados.js"></script>


</body>

</html>