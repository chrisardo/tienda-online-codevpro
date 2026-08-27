<?php
//=====================================================
// CoDevPro Technology
// Archivo: adm_registrar_proveedor.php
// Módulo: Registrar Proveedor
//=====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


//=====================================================
// VALIDAR SESIÓN
//=====================================================

$idUser = isset($_SESSION['idUser'])
    ? (int) $_SESSION['idUser']
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

        <main class="container-fluid px-4 py-4">


            <!--=================================================
                ENCABEZADO
            ==================================================-->

            <div class="proveedor-header mb-4">

                <div>

                    <div class="d-flex align-items-center gap-2 mb-2">

                        <a href="proveedores.php"
                            class="btn btn-light btn-sm proveedor-back-btn">

                            <i class="bi bi-arrow-left"></i>

                        </a>

                        <span class="text-muted small">

                            Proveedores

                        </span>

                        <i class="bi bi-chevron-right text-muted small"></i>

                        <span class="text-muted small">

                            Registrar proveedor

                        </span>

                    </div>


                    <h1 class="proveedor-title">

                        Registrar proveedor

                    </h1>


                    <p class="proveedor-subtitle">

                        Registra la información del proveedor que abastecerá
                        los productos de tu tienda.

                    </p>

                </div>


                <div>

                    <a href="adm_lista_proveedores.php"
                        class="btn btn-outline-secondary">

                        <i class="bi bi-people me-1"></i>

                        Lista de proveedores

                    </a>

                </div>

            </div>


            <!--=================================================
                ALERTA
            ==================================================-->

            <div id="alertaProveedor"
                class="alert d-none proveedor-alert"
                role="alert">

            </div>


            <!--=================================================
                FORMULARIO
            ==================================================-->

            <form id="formRegistrarProveedor"
                enctype="multipart/form-data"
                autocomplete="off">


                <div class="row g-4">


                    <!--=================================================
                        COLUMNA IZQUIERDA
                    ==================================================-->

                    <div class="col-12 col-xl-8">


                        <!--=================================================
                            INFORMACIÓN GENERAL
                        ==================================================-->

                        <div class="card proveedor-card">


                            <div class="card-header proveedor-card-header">

                                <div class="d-flex align-items-center">

                                    <div class="proveedor-section-icon">

                                        <i class="bi bi-building"></i>

                                    </div>

                                    <div>

                                        <h5 class="mb-0">

                                            Información del proveedor

                                        </h5>

                                        <small class="text-muted">

                                            Datos principales del proveedor

                                        </small>

                                    </div>

                                </div>

                            </div>


                            <div class="card-body">


                                <div class="row g-3">


                                    <!-- NOMBRE -->

                                    <div class="col-12">

                                        <label for="nombre"
                                            class="form-label">

                                            Nombre / Razón social

                                            <span class="text-danger">*</span>

                                        </label>


                                        <div class="input-group">

                                            <span class="input-group-text">

                                                <i class="bi bi-building"></i>

                                            </span>


                                            <input type="text"
                                                class="form-control"
                                                id="nombre"
                                                name="nombre"
                                                maxlength="150"
                                                placeholder="Ingrese el nombre o razón social"
                                                required>

                                        </div>


                                        <div class="form-text">

                                            Nombre comercial o razón social del proveedor.

                                        </div>

                                    </div>


                                    <!-- RUC -->

                                    <div class="col-md-6">

                                        <label for="ruc"
                                            class="form-label">

                                            RUC

                                            <span class="text-danger">*</span>

                                        </label>


                                        <div class="input-group">

                                            <span class="input-group-text">

                                                <i class="bi bi-card-text"></i>

                                            </span>


                                            <input type="text"
                                                class="form-control"
                                                id="ruc"
                                                name="ruc"
                                                maxlength="11"
                                                inputmode="numeric"
                                                placeholder="Ej. 20123456789"
                                                required>

                                        </div>


                                        <div class="invalid-feedback"
                                            id="errorRuc">

                                            Ingrese un RUC válido.

                                        </div>

                                    </div>


                                    <!-- CELULAR -->

                                    <div class="col-md-6">

                                        <label for="celular"
                                            class="form-label">

                                            Celular

                                            <span class="text-danger">*</span>

                                        </label>


                                        <div class="input-group">

                                            <span class="input-group-text">

                                                <i class="bi bi-phone"></i>

                                            </span>


                                            <input type="text"
                                                class="form-control"
                                                id="celular"
                                                name="celular"
                                                maxlength="15"
                                                inputmode="numeric"
                                                placeholder="Ingrese el número de celular"
                                                required>

                                        </div>

                                    </div>


                                    <!-- EMAIL -->

                                    <div class="col-12">

                                        <label for="email"
                                            class="form-label">

                                            Correo electrónico

                                            <span class="text-danger">*</span>

                                        </label>


                                        <div class="input-group">

                                            <span class="input-group-text">

                                                <i class="bi bi-envelope"></i>

                                            </span>


                                            <input type="email"
                                                class="form-control"
                                                id="email"
                                                name="email"
                                                maxlength="150"
                                                placeholder="proveedor@empresa.com"
                                                required>

                                        </div>

                                    </div>


                                    <!-- DIRECCIÓN -->

                                    <div class="col-12">

                                        <label for="direccion"
                                            class="form-label">

                                            Dirección

                                            <span class="text-danger">(Opcional)</span>

                                        </label>


                                        <div class="input-group">

                                            <span class="input-group-text align-items-start pt-2">

                                                <i class="bi bi-geo-alt"></i>

                                            </span>


                                            <textarea
                                                class="form-control"
                                                id="direccion"
                                                name="direccion"
                                                rows="3"
                                                maxlength="250"
                                                placeholder="Ingrese la dirección del proveedor"></textarea>

                                        </div>

                                    </div>


                                </div>

                            </div>

                        </div>


                        <!--=================================================
                            UBICACIÓN
                        ==================================================-->

                        <div class="card proveedor-card mt-4">


                            <div class="card-header proveedor-card-header">

                                <div class="d-flex align-items-center">

                                    <div class="proveedor-section-icon">

                                        <i class="bi bi-geo-alt"></i>

                                    </div>

                                    <div>

                                        <h5 class="mb-0">

                                            Ubicación

                                        </h5>

                                        <small class="text-muted">

                                            Información geográfica del proveedor

                                        </small>

                                    </div>

                                </div>

                            </div>


                            <div class="card-body">


                                <div class="row g-3">


                                    <!-- PAÍS -->

                                    <div class="col-md-6">

                                        <label for="id_pais"
                                            class="form-label">

                                            País

                                        </label>


                                        <select
                                            class="form-select"
                                            id="id_pais"
                                            name="id_pais">

                                            <option value="">

                                                Seleccione un país

                                            </option>

                                        </select>


                                        <div class="form-text">

                                            El país se muestra como referencia.
                                            Actualmente no existe una columna
                                            `id_pais` en la tabla `provedores`.

                                        </div>

                                    </div>


                                    <!-- DEPARTAMENTO -->

                                    <div class="col-md-6">

                                        <label for="id_departamneto"
                                            class="form-label">

                                            Departamento

                                            <span class="text-danger">*</span>

                                        </label>


                                        <select
                                            class="form-select"
                                            id="id_departamento"
                                            name="id_departamento"
                                            required>
                                        </select>
                                    </div>


                                    <!-- PROVINCIA -->

                                    <div class="col-md-6">

                                        <label for="provincia"
                                            class="form-label">

                                            Provincia

                                            <span class="text-danger">*</span>

                                        </label>


                                        <select
                                            class="form-select"
                                            id="provincia"
                                            name="provincia"
                                            required
                                            disabled>
                                        </select>
                                    </div>


                                    <!-- DISTRITO -->

                                    <div class="col-md-6">

                                        <label for="dsitrito"
                                            class="form-label">

                                            Distrito

                                            <span class="text-danger">*</span>

                                        </label>


                                        <select
                                            class="form-select"
                                            id="id_distrito"
                                            name="id_distrito"
                                            required
                                            disabled>

                                            <option value="">

                                                Seleccione un distrito

                                            </option>

                                        </select>

                                    </div>


                                </div>

                            </div>

                        </div>


                    </div>


                    <!--=================================================
                        COLUMNA DERECHA
                    ==================================================-->

                    <div class="col-12 col-xl-4">


                        <!--=================================================
                            IMAGEN
                        ==================================================-->

                        <div class="card proveedor-card">


                            <div class="card-header proveedor-card-header">

                                <div class="d-flex align-items-center">

                                    <div class="proveedor-section-icon">

                                        <i class="bi bi-image"></i>

                                    </div>

                                    <div>

                                        <h5 class="mb-0">

                                            Imagen

                                        </h5>

                                        <small class="text-muted">

                                            Logo o imagen del proveedor

                                        </small>

                                    </div>

                                </div>

                            </div>


                            <div class="card-body">


                                <div class="proveedor-image-wrapper">


                                    <div id="contenedorImagen"
                                        class="proveedor-image-preview">

                                        <div class="proveedor-image-placeholder">

                                            <i class="bi bi-building"></i>

                                            <span>

                                                Sin imagen

                                            </span>

                                        </div>

                                    </div>


                                    <button type="button"
                                        class="btn btn-light proveedor-image-remove d-none"
                                        id="btnEliminarImagen"
                                        title="Eliminar imagen">

                                        <i class="bi bi-x-lg"></i>

                                    </button>

                                </div>


                                <div class="mt-3">

                                    <label for="imagen"
                                        class="form-label">

                                        Seleccionar imagen

                                    </label>


                                    <input
                                        type="file"
                                        class="form-control"
                                        id="imagen"
                                        name="imagen"
                                        accept="image/jpeg,image/png,image/webp">


                                    <div class="form-text">

                                        JPG, PNG o WEBP.
                                        Tamaño máximo recomendado: 2 MB.

                                    </div>

                                </div>

                            </div>

                        </div>


                        <!--=================================================
                            RESUMEN
                        ==================================================-->

                        <div class="card proveedor-card mt-4">


                            <div class="card-header proveedor-card-header">

                                <div class="d-flex align-items-center">

                                    <div class="proveedor-section-icon">

                                        <i class="bi bi-info-circle"></i>

                                    </div>

                                    <div>

                                        <h5 class="mb-0">

                                            Información

                                        </h5>

                                    </div>

                                </div>

                            </div>


                            <div class="card-body">


                                <div class="proveedor-info-item">

                                    <i class="bi bi-check-circle"></i>

                                    <span>

                                        El proveedor quedará asociado
                                        a tu cuenta administrativa.

                                    </span>

                                </div>


                                <div class="proveedor-info-item">

                                    <i class="bi bi-shield-check"></i>

                                    <span>

                                        Los datos estarán disponibles
                                        únicamente para tu tienda.

                                    </span>

                                </div>


                                <div class="proveedor-info-item">

                                    <i class="bi bi-calendar-check"></i>

                                    <span>

                                        La fecha de registro será generada
                                        automáticamente.

                                    </span>

                                </div>


                            </div>

                        </div>


                    </div>


                </div>


                <!--=================================================
                    BOTONES
                ==================================================-->

                <div class="card proveedor-card mt-4">

                    <div class="card-body proveedor-actions">


                        <a href="proveedores.php"
                            class="btn btn-outline-secondary">

                            <i class="bi bi-x-lg me-1"></i>

                            Cancelar

                        </a>


                        <button
                            type="submit"
                            class="btn btn-primary"
                            id="btnRegistrarProveedor">

                            <span
                                class="spinner-border spinner-border-sm d-none"
                                id="spinnerProveedor"
                                aria-hidden="true"></span>

                            <i class="bi bi-person-plus-fill me-1"
                                id="iconRegistrarProveedor"></i>

                            <span id="textoRegistrarProveedor">

                                Registrar proveedor

                            </span>

                        </button>


                    </div>

                </div>


            </form>


        </main>

    </div>

</div>


<!--=====================================================
    LIBRERÍAS JAVASCRIPT
======================================================-->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js">
</script>


<!--=====================================================
    JAVASCRIPT DEL MÓDULO
======================================================-->

<script
    src="js/adm_registrar_proveedor.js">
</script>


<!--=====================================================
    MENÚ ADMINISTRATIVO
======================================================-->

<script
    src="js/menu.js">
</script>


</body>

</html>