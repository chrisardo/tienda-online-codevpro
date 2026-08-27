<?php
//=====================================================
// CoDevPro Technology
// Archivo: adm_mi_empresa.php
// Módulo: Mi Empresa
// Sistema: Inventa
//=====================================================


if (session_status() === PHP_SESSION_NONE) {

    session_start();
}


//=====================================================
// VALIDAR SESIÓN
//=====================================================

$idUser = isset($_SESSION["idUser"])
    ? (int) $_SESSION["idUser"]
    : 0;


if ($idUser <= 0) {

    header("Location: login.php");

    exit;
}


//=====================================================
// CONEXIÓN
//=====================================================

require_once "controladores/conexion.php";

?>

<?php include "includes/head.php"; ?>


<!--=====================================================
    CONTENEDOR GENERAL
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

        <main class="container-fluid px-4 py-4 mi-empresa-page">


            <!--=================================================
                ENCABEZADO
            ==================================================-->

            <div class="mi-empresa-header mb-4">

                <div>

                    <div class="d-flex align-items-center gap-2 mb-2">

                        <span class="mi-empresa-header-icon">

                            <i class="bi bi-buildings-fill"></i>

                        </span>

                        <h1 class="mi-empresa-title mb-0">

                            Mi Empresa

                        </h1>

                    </div>


                    <p class="mi-empresa-subtitle mb-0">

                        Administra la información y configuración
                        principal de tu empresa.

                    </p>

                </div>


                <!-- ESTADO -->

                <div class="mi-empresa-status">

                    <span class="status-dot"></span>

                    <span id="empresaEstadoTexto">

                        Activo

                    </span>

                </div>

            </div>



            <!--=================================================
                CONTENEDOR PRINCIPAL
            ==================================================-->

            <div class="row g-4">


                <!--=================================================
                    COLUMNA IZQUIERDA
                ==================================================-->

                <div class="col-xl-4 col-lg-5">


                    <!--=================================================
                        TARJETA LOGO
                    ==================================================-->

                    <div class="card mi-empresa-card logo-card">


                        <div class="card-body">


                            <div class="section-card-title">

                                <div class="section-icon">

                                    <i class="bi bi-image"></i>

                                </div>

                                <div>

                                    <h5 class="mb-1">

                                        Logo de la empresa

                                    </h5>

                                    <p class="mb-0">

                                        Imagen que identifica a tu empresa.

                                    </p>

                                </div>

                            </div>


                            <!--=================================================
                                PREVISUALIZACIÓN
                            ==================================================-->

                            <div class="empresa-logo-wrapper mt-4">

                                <div
                                    class="empresa-logo-preview"
                                    id="empresaLogoPreview">

                                    <div class="logo-placeholder">

                                        <i class="bi bi-buildings"></i>

                                        <span>

                                            Sin logo

                                        </span>

                                    </div>

                                </div>

                            </div>


                            <!--=================================================
                                INFORMACIÓN ARCHIVO
                            ==================================================-->

                            <div class="logo-info text-center mt-3">

                                <strong id="empresaNombreLogo">

                                    Mi Empresa

                                </strong>

                                <small>

                                    Formatos permitidos: JPG, JPEG, PNG o WEBP

                                </small>

                                <small>

                                    Tamaño máximo recomendado: 2 MB

                                </small>

                            </div>


                            <!--=================================================
                                BOTONES LOGO
                            ==================================================-->

                            <div class="logo-actions mt-4">

                                <input
                                    type="file"
                                    id="inputLogoEmpresa"
                                    accept="image/jpeg,image/png,image/webp"
                                    class="d-none">


                                <button
                                    type="button"
                                    class="btn btn-primary w-100"
                                    id="btnSeleccionarLogo">

                                    <i class="bi bi-camera me-2"></i>

                                    Cambiar logo

                                </button>


                                <button
                                    type="button"
                                    class="btn btn-outline-danger w-100 mt-2"
                                    id="btnEliminarLogo">

                                    <i class="bi bi-trash3 me-2"></i>

                                    Eliminar logo

                                </button>

                            </div>


                        </div>

                    </div>



                    <!--=================================================
                        TARJETA ESTADO
                    ==================================================-->

                    <div class="card mi-empresa-card mt-4">


                        <div class="card-body">


                            <div class="section-card-title">

                                <div class="section-icon section-icon-success">

                                    <i class="bi bi-shield-check"></i>

                                </div>

                                <div>

                                    <h5 class="mb-1">

                                        Estado de la empresa

                                    </h5>

                                    <p class="mb-0">

                                        Estado actual de la cuenta.

                                    </p>

                                </div>

                            </div>


                            <div class="empresa-account-status mt-4">


                                <div class="account-status-icon">

                                    <i class="bi bi-check-lg"></i>

                                </div>


                                <div>

                                    <strong id="empresaEstado">

                                        Activo

                                    </strong>

                                    <span>

                                        La empresa se encuentra activa.

                                    </span>

                                </div>

                            </div>


                        </div>

                    </div>


                </div>



                <!--=================================================
                    COLUMNA DERECHA
                ==================================================-->

                <div class="col-xl-8 col-lg-7">


                    <!--=================================================
                        INFORMACIÓN GENERAL
                    ==================================================-->

                    <div class="card mi-empresa-card">


                        <div class="card-body">


                            <div class="section-card-title">

                                <div class="section-icon">

                                    <i class="bi bi-buildings-fill"></i>

                                </div>

                                <div>

                                    <h5 class="mb-1">

                                        Información de la empresa

                                    </h5>

                                    <p class="mb-0">

                                        Datos principales de tu negocio.

                                    </p>

                                </div>

                            </div>


                            <form
                                id="formMiEmpresa"
                                autocomplete="off">


                                <div class="row g-3 mt-2">


                                    <!--=========================================
                                        NOMBRE EMPRESA
                                    ==========================================-->

                                    <div class="col-md-8">

                                        <label
                                            for="nombreEmpresa"
                                            class="form-label">

                                            Nombre de la empresa

                                            <span class="text-danger">

                                                *

                                            </span>

                                        </label>


                                        <div class="input-group">

                                            <span class="input-group-text">

                                                <i class="bi bi-building"></i>

                                            </span>


                                            <input
                                                type="text"
                                                class="form-control"
                                                id="nombreEmpresa"
                                                name="nombreEmpresa"
                                                maxlength="150"
                                                placeholder="Nombre de tu empresa">

                                        </div>

                                    </div>


                                    <!--=========================================
                                        RUC
                                    ==========================================-->

                                    <div class="col-md-4">

                                        <label
                                            for="ruc"
                                            class="form-label">

                                            RUC

                                        </label>


                                        <div class="input-group">

                                            <span class="input-group-text">

                                                <i class="bi bi-card-text"></i>

                                            </span>


                                            <input
                                                type="text"
                                                class="form-control"
                                                id="ruc"
                                                name="ruc"
                                                maxlength="20"
                                                placeholder="RUC">

                                        </div>

                                    </div>


                                    <!--=========================================
                                        CORREO
                                    ==========================================-->

                                    <div class="col-md-6">

                                        <label
                                            for="emailEmpresa"
                                            class="form-label">

                                            Correo electrónico

                                            <span class="text-danger">

                                                *

                                            </span>

                                        </label>


                                        <div class="input-group">

                                            <span class="input-group-text">

                                                <i class="bi bi-envelope"></i>

                                            </span>


                                            <input
                                                type="email"
                                                class="form-control"
                                                id="emailEmpresa"
                                                name="email"
                                                maxlength="150"
                                                placeholder="correo@empresa.com">

                                        </div>

                                    </div>


                                    <!--=========================================
                                        CELULAR
                                    ==========================================-->

                                    <div class="col-md-6">

                                        <label
                                            for="celularEmpresa"
                                            class="form-label">

                                            Teléfono / Celular

                                        </label>


                                        <div class="input-group">

                                            <span class="input-group-text">

                                                <i class="bi bi-telephone"></i>

                                            </span>


                                            <input
                                                type="text"
                                                class="form-control"
                                                id="celularEmpresa"
                                                name="celular"
                                                maxlength="20"
                                                placeholder="Número de contacto">

                                        </div>

                                    </div>


                                    <!--=========================================
                                        DIRECCIÓN
                                    ==========================================-->

                                    <div class="col-12">

                                        <label
                                            for="direccionEmpresa"
                                            class="form-label">

                                            Dirección

                                        </label>


                                        <div class="input-group">

                                            <span class="input-group-text">

                                                <i class="bi bi-geo-alt"></i>

                                            </span>


                                            <input
                                                type="text"
                                                class="form-control"
                                                id="direccionEmpresa"
                                                name="direccion"
                                                maxlength="255"
                                                placeholder="Dirección de la empresa">

                                        </div>

                                    </div>


                                </div>


                                <!--=================================================
                                    BOTONES
                                ==================================================-->

                                <div class="form-actions mt-4 pt-3">

                                    <button
                                        type="reset"
                                        class="btn btn-light border"
                                        id="btnCancelarCambios">

                                        <i class="bi bi-arrow-counterclockwise me-2"></i>

                                        Restablecer

                                    </button>


                                    <button
                                        type="submit"
                                        class="btn btn-primary"
                                        id="btnGuardarEmpresa">

                                        <i class="bi bi-check2-circle me-2"></i>

                                        Guardar cambios

                                    </button>

                                </div>


                            </form>


                        </div>

                    </div>



                    <!--=================================================
                        INFORMACIÓN DE ACCESO
                    ==================================================-->

                    <div class="card mi-empresa-card mt-4">


                        <div class="card-body">


                            <div class="section-card-title">

                                <div class="section-icon section-icon-warning">

                                    <i class="bi bi-person-lock"></i>

                                </div>

                                <div>

                                    <h5 class="mb-1">

                                        Información de acceso

                                    </h5>

                                    <p class="mb-0">

                                        Datos utilizados para acceder al
                                        panel administrativo.

                                    </p>

                                </div>

                            </div>


                            <div class="row g-3 mt-2">


                                <!--=========================================
                                    USUARIO
                                ==========================================-->

                                <div class="col-md-6">

                                    <label class="form-label">

                                        Nombre de usuario

                                    </label>


                                    <div class="account-info-box">

                                        <div class="account-info-icon">

                                            <i class="bi bi-person"></i>

                                        </div>


                                        <div>

                                            <span>

                                                Usuario

                                            </span>

                                            <strong id="empresaUsername">

                                                Administrador

                                            </strong>

                                        </div>

                                    </div>

                                </div>


                                <!--=========================================
                                    ROL
                                ==========================================-->

                                <div class="col-md-6">

                                    <label class="form-label">

                                        Rol de acceso

                                    </label>


                                    <div class="account-info-box">

                                        <div class="account-info-icon">

                                            <i class="bi bi-person-badge"></i>

                                        </div>


                                        <div>

                                            <span>

                                                Rol

                                            </span>

                                            <strong id="empresaRol">

                                                Administrador

                                            </strong>

                                        </div>

                                    </div>

                                </div>


                                <!--=========================================
                                    FECHA REGISTRO
                                ==========================================-->

                                <div class="col-md-6">

                                    <label class="form-label">

                                        Fecha de registro

                                    </label>


                                    <div class="account-info-box">

                                        <div class="account-info-icon">

                                            <i class="bi bi-calendar3"></i>

                                        </div>


                                        <div>

                                            <span>

                                                Registrado el

                                            </span>

                                            <strong id="empresaFechaRegistro">

                                                --

                                            </strong>

                                        </div>

                                    </div>

                                </div>


                                <!--=========================================
                                    CAMBIO CONTRASEÑA
                                ==========================================-->

                                <div class="col-md-6">

                                    <label class="form-label">

                                        Seguridad

                                    </label>


                                    <div class="account-info-box">

                                        <div class="account-info-icon">

                                            <i class="bi bi-key"></i>

                                        </div>


                                        <div class="flex-grow-1">

                                            <span>

                                                Contraseña

                                            </span>

                                            <strong>

                                                ••••••••••••

                                            </strong>

                                        </div>


                                        <button
                                            type="button"
                                            class="btn btn-outline-primary btn-sm"
                                            id="btnCambiarPassword">

                                            Cambiar

                                        </button>

                                    </div>

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
    MODAL CAMBIAR CONTRASEÑA
======================================================-->

<div
    class="modal fade"
    id="modalCambiarPassword"
    tabindex="-1"
    aria-labelledby="modalCambiarPasswordLabel"
    aria-hidden="true">


    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">


            <!--=================================================
                HEADER
            ==================================================-->

            <div class="modal-header">

                <div>

                    <h5
                        class="modal-title"
                        id="modalCambiarPasswordLabel">

                        Cambiar contraseña

                    </h5>

                    <small class="text-muted">

                        Actualiza la contraseña de acceso
                        al panel administrativo.

                    </small>

                </div>


                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Cerrar">
                </button>

            </div>


            <!--=================================================
                BODY
            ==================================================-->

            <div class="modal-body">


                <form
                    id="formCambiarPassword"
                    autocomplete="off">


                    <!--=========================================
                        CONTRASEÑA ACTUAL
                    ==========================================-->

                    <div class="mb-3">

                        <label
                            for="passwordActual"
                            class="form-label">

                            Contraseña actual

                        </label>


                        <div class="input-group">

                            <span class="input-group-text">

                                <i class="bi bi-lock"></i>

                            </span>


                            <input
                                type="password"
                                class="form-control"
                                id="passwordActual"
                                name="passwordActual"
                                autocomplete="current-password"
                                required>


                            <button
                                type="button"
                                class="btn btn-outline-secondary btn-toggle-password"
                                data-target="passwordActual">

                                <i class="bi bi-eye"></i>

                            </button>

                        </div>

                    </div>


                    <!--=========================================
                        NUEVA CONTRASEÑA
                    ==========================================-->

                    <div class="mb-3">

                        <label
                            for="passwordNueva"
                            class="form-label">

                            Nueva contraseña

                        </label>


                        <div class="input-group">

                            <span class="input-group-text">

                                <i class="bi bi-key"></i>

                            </span>


                            <input
                                type="password"
                                class="form-control"
                                id="passwordNueva"
                                name="passwordNueva"
                                autocomplete="new-password"
                                minlength="8"
                                required>


                            <button
                                type="button"
                                class="btn btn-outline-secondary btn-toggle-password"
                                data-target="passwordNueva">

                                <i class="bi bi-eye"></i>

                            </button>

                        </div>


                        <div class="password-help">

                            La contraseña debe tener al menos
                            8 caracteres.

                        </div>

                    </div>


                    <!--=========================================
                        CONFIRMAR CONTRASEÑA
                    ==========================================-->

                    <div class="mb-3">

                        <label
                            for="passwordConfirmar"
                            class="form-label">

                            Confirmar nueva contraseña

                        </label>


                        <div class="input-group">

                            <span class="input-group-text">

                                <i class="bi bi-shield-lock"></i>

                            </span>


                            <input
                                type="password"
                                class="form-control"
                                id="passwordConfirmar"
                                name="passwordConfirmar"
                                autocomplete="new-password"
                                minlength="8"
                                required>


                            <button
                                type="button"
                                class="btn btn-outline-secondary btn-toggle-password"
                                data-target="passwordConfirmar">

                                <i class="bi bi-eye"></i>

                            </button>

                        </div>

                    </div>


                    <!--=========================================
                        ALERTA
                    ==========================================-->

                    <div class="password-security-alert">

                        <i class="bi bi-shield-check"></i>

                        <span>

                            Después de cambiar la contraseña,
                            deberás utilizar la nueva contraseña
                            para iniciar sesión.

                        </span>

                    </div>


                </form>


            </div>


            <!--=================================================
                FOOTER
            ==================================================-->

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-light border"
                    data-bs-dismiss="modal">

                    Cancelar

                </button>


                <button
                    type="submit"
                    form="formCambiarPassword"
                    class="btn btn-primary"
                    id="btnGuardarPassword">

                    <i class="bi bi-check2-circle me-2"></i>

                    Actualizar contraseña

                </button>

            </div>


        </div>

    </div>

</div>



<!--=====================================================
    MODAL CONFIRMAR ELIMINACIÓN LOGO
======================================================-->

<div
    class="modal fade"
    id="modalEliminarLogo"
    tabindex="-1"
    aria-labelledby="modalEliminarLogoLabel"
    aria-hidden="true">


    <div class="modal-dialog modal-dialog-centered modal-sm">

        <div class="modal-content">


            <div class="modal-header">

                <h5
                    class="modal-title"
                    id="modalEliminarLogoLabel">

                    Eliminar logo

                </h5>


                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Cerrar">
                </button>

            </div>


            <div class="modal-body text-center">

                <div class="delete-logo-icon">

                    <i class="bi bi-trash3"></i>

                </div>


                <p class="mt-3 mb-0">

                    ¿Estás seguro de que deseas eliminar
                    el logo de la empresa?

                </p>

            </div>


            <div class="modal-footer justify-content-center">

                <button
                    type="button"
                    class="btn btn-light border"
                    data-bs-dismiss="modal">

                    Cancelar

                </button>


                <button
                    type="button"
                    class="btn btn-danger"
                    id="btnConfirmarEliminarLogo">

                    <i class="bi bi-trash3 me-2"></i>

                    Eliminar

                </button>

            </div>


        </div>

    </div>

</div>



<!--=====================================================
    INPUT OCULTO
======================================================-->

<input
    type="hidden"
    id="idUserEmpresa"
    value="<?php echo $idUser; ?>">



<!--=====================================================
    BOOTSTRAP
======================================================-->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js">
</script>



<!--=====================================================
    SWEET ALERT
======================================================-->

<script
    src="https://cdn.jsdelivr.net/npm/sweetalert2@11">
</script>



<!--=====================================================
    JS DEL MÓDULO
======================================================-->

<script
    src="js/adm_mi_empresa.js">
</script>



<!--=====================================================
    MENÚ ADMINISTRATIVO
======================================================-->

<script
    src="js/menu.js">
</script>


</body>

</html>