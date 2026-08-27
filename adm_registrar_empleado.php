<?php
//=====================================================
// CoDevPro Technology
// Archivo: adm_registrar_empleado.php
// Módulo: Registrar Empleado
//=====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "controladores/conexion.php";

/*=====================================================
=            VALIDAR USUARIO LOGUEADO
=====================================================*/

$idUser = isset($_SESSION["idUser"]) ? (int) $_SESSION["idUser"] : 0;

if ($idUser <= 0) {

    echo '
    <div class="container py-5">
        <div class="alert alert-danger shadow-sm">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            No se pudo identificar al usuario.
        </div>
    </div>';

    exit;
}


/*=====================================================
=            CARGAR ROLES DEL USUARIO
=====================================================*/

$roles = [];

$sqlRoles = "
    SELECT
        id_rol,
        nombre
    FROM rol
    WHERE id_user = ?
    ORDER BY nombre ASC
";

$stmtRoles = mysqli_prepare($conexion, $sqlRoles);

if ($stmtRoles) {

    mysqli_stmt_bind_param(
        $stmtRoles,
        "i",
        $idUser
    );

    mysqli_stmt_execute($stmtRoles);

    $resultadoRoles = mysqli_stmt_get_result($stmtRoles);

    if ($resultadoRoles) {

        while ($filaRol = mysqli_fetch_assoc($resultadoRoles)) {

            $roles[] = $filaRol;
        }
    }

    mysqli_stmt_close($stmtRoles);
}


/*=====================================================
=            CARGAR PAÍSES
=====================================================*/

$paises = [];

$sqlPaises = "
    SELECT
        id_pais,
        nombre
    FROM pais
    WHERE id_user = ?
      AND Eliminado = 0
    ORDER BY nombre ASC
";

$stmtPaises = mysqli_prepare($conexion, $sqlPaises);

if ($stmtPaises) {

    mysqli_stmt_bind_param(
        $stmtPaises,
        "i",
        $idUser
    );

    mysqli_stmt_execute($stmtPaises);

    $resultadoPaises = mysqli_stmt_get_result($stmtPaises);

    if ($resultadoPaises) {

        while ($filaPais = mysqli_fetch_assoc($resultadoPaises)) {

            $paises[] = $filaPais;
        }
    }

    mysqli_stmt_close($stmtPaises);
}


/*=====================================================
=            CARGAR MÓDULOS
=====================================================*/

$modulos = [];

$sqlModulos = "
    SELECT
        id_modulo,
        nombre,
        codigo,
        icono,
        orden
    FROM modulos
    WHERE id_user = ?
      AND estado = 1
    ORDER BY orden ASC, nombre ASC
";

$stmtModulos = mysqli_prepare($conexion, $sqlModulos);

if ($stmtModulos) {

    mysqli_stmt_bind_param(
        $stmtModulos,
        "i",
        $idUser
    );

    mysqli_stmt_execute($stmtModulos);

    $resultadoModulos = mysqli_stmt_get_result($stmtModulos);

    if ($resultadoModulos) {

        while ($filaModulo = mysqli_fetch_assoc($resultadoModulos)) {

            $modulos[] = $filaModulo;
        }
    }

    mysqli_stmt_close($stmtModulos);
}


include "includes/head.php";

?>

<!-- =====================================================
     CONTENEDOR PRINCIPAL
====================================================== -->

<div class="d-flex">

    <!-- =================================================
         SIDEBAR
    ================================================== -->

    <?php include "includes/admin_sidebar.php"; ?>


    <!-- =================================================
         CONTENIDO PRINCIPAL
    ================================================== -->

    <div class="flex-grow-1">

        <!-- =================================================
             NAVBAR
        ================================================== -->

        <?php include "includes/admin_navbar.php"; ?>


        <!-- =================================================
             CONTENIDO
        ================================================== -->

        <main class="container-fluid py-4 px-4">

            <!-- =================================================
                 ENCABEZADO
            ================================================== -->

            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">

                <div>

                    <div class="d-flex align-items-center gap-2 mb-1">

                        <i class="bi bi-person-plus-fill text-primary fs-3"></i>

                        <h2 class="fw-bold mb-0">
                            Registrar Empleado
                        </h2>

                    </div>

                    <p class="text-muted mb-0">
                        Registra los datos personales, laborales y permisos del empleado.
                    </p>

                </div>
            </div>
            <!-- =================================================
                 ALERTA GENERAL
            ================================================== -->

            <div id="mensajeEmpleado"
                class="mb-3"
                style="display:none;">
            </div>


            <!-- =================================================
                 FORMULARIO PRINCIPAL
            ================================================== -->

            <form id="formRegistrarEmpleado"
                enctype="multipart/form-data"
                autocomplete="off">

                <!-- =================================================
                     DATOS PERSONALES
                ================================================== -->

                <div class="card border-0 shadow-sm mb-4">

                    <div class="card-header bg-white border-bottom py-3">

                        <div class="d-flex align-items-center">

                            <div class="rounded-circle bg-primary bg-opacity-10
                                        text-primary d-flex align-items-center
                                        justify-content-center me-3"
                                style="width:42px;height:42px;">

                                <i class="bi bi-person-vcard fs-5"></i>

                            </div>

                            <div>

                                <h5 class="fw-bold mb-0">
                                    Información personal
                                </h5>

                                <small class="text-muted">
                                    Datos básicos de identificación del empleado.
                                </small>

                            </div>

                        </div>

                    </div>


                    <div class="card-body">

                        <div class="row g-4">

                            <!-- =================================================
                                 FOTO
                            ================================================== -->

                            <div class="col-12 col-lg-3">

                                <label class="form-label fw-semibold">
                                    Foto del empleado
                                </label>

                                <div class="empleado-imagen-container">

                                    <div id="contenedorVistaPreviaEmpleado"
                                        class="empleado-imagen-preview">

                                        <div class="empleado-imagen-placeholder">
                                            <i class="bi bi-person-bounding-box"></i>
                                            <span>Sin imagen</span>
                                        </div>

                                    </div>

                                    <input
                                        type="file"
                                        id="imagenEmpleado"
                                        name="imagenEmpleado"
                                        accept="image/jpeg,image/png,image/webp"
                                        class="d-none">

                                    <div class="d-flex justify-content-start mt-2">
                                        <label
                                            for="imagenEmpleado"
                                            class="btn btn-primary">
                                            <i class="bi bi-image me-1"></i>
                                            Seleccionar imagen
                                        </label>
                                    </div>

                                </div>

                            </div>


                            <!-- =================================================
                                 DATOS
                            ================================================== -->

                            <div class="col-12 col-lg-9">

                                <div class="row g-3">

                                    <!-- DNI -->

                                    <div class="col-12 col-md-6">

                                        <label for="dni"
                                            class="form-label fw-semibold">

                                            DNI
                                            <span class="text-danger">*</span>

                                        </label>

                                        <div class="input-group">

                                            <span class="input-group-text">
                                                <i class="bi bi-person-vcard"></i>
                                            </span>

                                            <input type="text"
                                                class="form-control"
                                                id="dni"
                                                name="dni"
                                                maxlength="20"
                                                placeholder="Ingrese el DNI"
                                                required>

                                        </div>

                                        <div class="invalid-feedback">
                                            Ingrese un DNI válido.
                                        </div>

                                    </div>


                                    <!-- CELULAR -->

                                    <div class="col-12 col-md-6">

                                        <label for="celular"
                                            class="form-label fw-semibold">

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
                                                placeholder="Ingrese el celular"
                                                required>

                                        </div>

                                        <div class="invalid-feedback">
                                            Ingrese un número de celular válido.
                                        </div>

                                    </div>


                                    <!-- NOMBRE -->

                                    <div class="col-12 col-md-6">

                                        <label for="nombre"
                                            class="form-label fw-semibold">

                                            Nombres
                                            <span class="text-danger">*</span>

                                        </label>

                                        <div class="input-group">

                                            <span class="input-group-text">
                                                <i class="bi bi-person"></i>
                                            </span>

                                            <input type="text"
                                                class="form-control"
                                                id="nombre"
                                                name="nombre"
                                                maxlength="150"
                                                placeholder="Ingrese los nombres"
                                                required>

                                        </div>

                                        <div class="invalid-feedback">
                                            Ingrese los nombres del empleado.
                                        </div>

                                    </div>


                                    <!-- APELLIDO -->

                                    <div class="col-12 col-md-6">

                                        <label for="apellido"
                                            class="form-label fw-semibold">

                                            Apellidos
                                            <span class="text-danger">*</span>

                                        </label>

                                        <div class="input-group">

                                            <span class="input-group-text">
                                                <i class="bi bi-person-lines-fill"></i>
                                            </span>

                                            <input type="text"
                                                class="form-control"
                                                id="apellido"
                                                name="apellido"
                                                maxlength="150"
                                                placeholder="Ingrese los apellidos"
                                                required>

                                        </div>

                                        <div class="invalid-feedback">
                                            Ingrese los apellidos del empleado.
                                        </div>

                                    </div>


                                    <!-- EMAIL -->

                                    <div class="col-12">

                                        <label for="email"
                                            class="form-label fw-semibold">

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
                                                placeholder="ejemplo@correo.com"
                                                required>

                                        </div>

                                        <div class="invalid-feedback">
                                            Ingrese un correo electrónico válido.
                                        </div>

                                    </div>
                                    <!-- =================================================
     DIRECCIÓN
================================================== -->

                                    <div class="col-12 col-md-6">

                                        <label for="direccion"
                                            class="form-label fw-semibold">

                                            Dirección
                                            <span class="text-danger">*</span>

                                        </label>

                                        <div class="input-group">

                                            <span class="input-group-text">
                                                <i class="bi bi-geo-alt"></i>
                                            </span>

                                            <input type="text"
                                                class="form-control"
                                                id="direccion"
                                                name="direccion"
                                                maxlength="255"
                                                placeholder="Ingrese la dirección"
                                                required>

                                        </div>

                                        <div class="invalid-feedback">
                                            Ingrese la dirección del empleado.
                                        </div>

                                    </div>


                                    <!-- =================================================
     CONTRASEÑA
================================================== -->

                                    <div class="col-12 col-md-6">

                                        <label for="contrasena"
                                            class="form-label fw-semibold">

                                            Contraseña

                                            <span class="text-muted fw-normal">
                                                (opcional)
                                            </span>

                                        </label>

                                        <div class="input-group">

                                            <span class="input-group-text">
                                                <i class="bi bi-lock"></i>
                                            </span>

                                            <input type="password"
                                                class="form-control"
                                                id="contrasena"
                                                name="contrasena"
                                                maxlength="255"
                                                placeholder="Ingrese una contraseña"
                                                autocomplete="new-password">

                                            <button
                                                type="button"
                                                class="btn btn-outline-secondary"
                                                id="btnMostrarContrasena"
                                                title="Mostrar contraseña"
                                                tabindex="-1">

                                                <i class="bi bi-eye"
                                                    id="iconoMostrarContrasena">
                                                </i>

                                            </button>

                                        </div>

                                        <div class="form-text">
                                            Si la deja vacía, el empleado no tendrá contraseña de acceso.
                                        </div>

                                        <div class="invalid-feedback">
                                            La contraseña ingresada no es válida.
                                        </div>

                                    </div>
                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     UBICACIÓN
                ================================================== -->

                <div class="card border-0 shadow-sm mb-4">

                    <div class="card-header bg-white border-bottom py-3">

                        <div class="d-flex align-items-center">

                            <div class="rounded-circle bg-success bg-opacity-10
                                        text-success d-flex align-items-center
                                        justify-content-center me-3"
                                style="width:42px;height:42px;">

                                <i class="bi bi-geo-alt-fill fs-5"></i>

                            </div>

                            <div>

                                <h5 class="fw-bold mb-0">
                                    Ubicación
                                </h5>

                                <small class="text-muted">
                                    Selecciona la ubicación correspondiente al empleado.
                                </small>

                            </div>

                        </div>

                    </div>


                    <div class="card-body">

                        <div class="row g-3">

                            <!-- PAÍS -->

                            <div class="col-12 col-md-6 col-lg-3">

                                <label for="id_pais"
                                    class="form-label fw-semibold">

                                    País
                                    <span class="text-danger">*</span>

                                </label>

                                <select class="form-select"
                                    id="id_pais"
                                    name="id_pais"
                                    required>

                                    <option value="">
                                        Seleccionar país
                                    </option>

                                    <?php foreach ($paises as $pais): ?>

                                        <option value="<?= (int) $pais["id_pais"]; ?>">

                                            <?= htmlspecialchars(
                                                $pais["nombre"],
                                                ENT_QUOTES,
                                                "UTF-8"
                                            ); ?>

                                        </option>

                                    <?php endforeach; ?>

                                </select>

                                <div class="invalid-feedback">
                                    Seleccione un país.
                                </div>

                            </div>


                            <!-- DEPARTAMENTO -->

                            <div class="col-12 col-md-6 col-lg-3">

                                <label for="id_departamento"
                                    class="form-label fw-semibold">

                                    Departamento
                                    <span class="text-danger">*</span>

                                </label>

                                <select class="form-select"
                                    id="id_departamento"
                                    name="id_departamento"
                                    required
                                    disabled>

                                    <option value="">
                                        Seleccionar departamento
                                    </option>

                                </select>

                                <div class="invalid-feedback">
                                    Seleccione un departamento.
                                </div>

                            </div>


                            <!-- PROVINCIA -->

                            <div class="col-12 col-md-6 col-lg-3">

                                <label for="id_provincia"
                                    class="form-label fw-semibold">

                                    Provincia
                                    <span class="text-danger">*</span>

                                </label>

                                <select class="form-select"
                                    id="id_provincia"
                                    name="id_provincia"
                                    required
                                    disabled>

                                    <option value="">
                                        Seleccionar provincia
                                    </option>

                                </select>

                                <div class="invalid-feedback">
                                    Seleccione una provincia.
                                </div>

                            </div>


                            <!-- DISTRITO -->

                            <div class="col-12 col-md-6 col-lg-3">

                                <label for="id_distrito"
                                    class="form-label fw-semibold">

                                    Distrito
                                    <span class="text-danger">*</span>

                                </label>

                                <select class="form-select"
                                    id="id_distrito"
                                    name="id_distrito"
                                    required
                                    disabled>

                                    <option value="">
                                        Seleccionar distrito
                                    </option>

                                </select>

                                <div class="invalid-feedback">
                                    Seleccione un distrito.
                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     INFORMACIÓN LABORAL
                ================================================== -->

                <div class="card border-0 shadow-sm mb-4">

                    <div class="card-header bg-white border-bottom py-3">

                        <div class="d-flex align-items-center">

                            <div class="rounded-circle bg-warning bg-opacity-10
                                        text-warning d-flex align-items-center
                                        justify-content-center me-3"
                                style="width:42px;height:42px;">

                                <i class="bi bi-briefcase-fill fs-5"></i>

                            </div>

                            <div>

                                <h5 class="fw-bold mb-0">
                                    Información laboral
                                </h5>

                                <small class="text-muted">
                                    Configura el cargo y estado del empleado.
                                </small>

                            </div>

                        </div>

                    </div>


                    <div class="card-body">

                        <div class="row g-3">

                            <!-- ROL -->

                            <div class="col-12 col-md-6">

                                <label for="id_rol"
                                    class="form-label fw-semibold">

                                    Cargo / Rol
                                    <span class="text-danger">*</span>

                                </label>

                                <select class="form-select"
                                    id="id_rol"
                                    name="id_rol"
                                    required>

                                    <option value="">
                                        Seleccionar cargo / rol
                                    </option>

                                    <?php foreach ($roles as $rol): ?>

                                        <option value="<?= (int) $rol["id_rol"]; ?>">

                                            <?= htmlspecialchars(
                                                $rol["nombre"],
                                                ENT_QUOTES,
                                                "UTF-8"
                                            ); ?>

                                        </option>

                                    <?php endforeach; ?>

                                </select>

                                <?php if (empty($roles)): ?>

                                    <div class="form-text text-danger">

                                        <i class="bi bi-exclamation-circle me-1"></i>

                                        No existen roles registrados para esta empresa.

                                        <a href="roles.php"
                                            class="text-decoration-none">

                                            Crear rol

                                        </a>

                                    </div>

                                <?php else: ?>

                                    <div class="form-text">

                                        Los permisos se cargarán según el rol seleccionado.

                                    </div>

                                <?php endif; ?>

                                <div class="invalid-feedback">
                                    Seleccione un cargo o rol.
                                </div>

                            </div>


                            <!-- ESTADO -->

                            <div class="col-12 col-md-6">

                                <label for="estado"
                                    class="form-label fw-semibold">

                                    Estado
                                    <span class="text-danger">*</span>

                                </label>

                                <select class="form-select"
                                    id="estado"
                                    name="estado"
                                    required>

                                    <option value="ACTIVO" selected>
                                        ACTIVO
                                    </option>

                                    <option value="INACTIVO">
                                        INACTIVO
                                    </option>

                                </select>

                                <div class="form-text">
                                    Un empleado inactivo no debería tener acceso a las operaciones del sistema.
                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     PERMISOS
                ================================================== -->

                <div class="card border-0 shadow-sm mb-4">

                    <div class="card-header bg-white border-bottom py-3">

                        <div class="d-flex flex-wrap justify-content-between
                                    align-items-center gap-3">

                            <div class="d-flex align-items-center">

                                <div class="rounded-circle bg-danger bg-opacity-10
                                            text-danger d-flex align-items-center
                                            justify-content-center me-3"
                                    style="width:42px;height:42px;">

                                    <i class="bi bi-shield-lock-fill fs-5"></i>

                                </div>

                                <div>

                                    <h5 class="fw-bold mb-0">
                                        Permisos del empleado
                                    </h5>

                                    <small class="text-muted">
                                        Los permisos corresponden al rol seleccionado.
                                    </small>

                                </div>

                            </div>


                            <!-- ESTADO DE CARGA -->

                            <div id="estadoCargaPermisos"
                                class="text-muted small">

                                <i class="bi bi-info-circle me-1"></i>

                                Seleccione un rol para consultar sus permisos.

                            </div>

                        </div>

                    </div>


                    <div class="card-body p-0">

                        <!-- =================================================
                             MENSAJE SIN ROL
                        ================================================== -->

                        <div id="mensajeSinRol"
                            class="p-4 text-center">

                            <div class="mb-3">

                                <i class="bi bi-shield-exclamation
                                          text-muted"
                                    style="font-size:3rem;">
                                </i>

                            </div>

                            <h6 class="fw-semibold">
                                Seleccione un cargo o rol
                            </h6>

                            <p class="text-muted mb-0">
                                Los permisos asociados al rol aparecerán aquí.
                            </p>

                        </div>


                        <!-- =================================================
                             TABLA DE PERMISOS
                        ================================================== -->

                        <div id="contenedorPermisos"
                            class="table-responsive"
                            style="display:none;">

                            <table class="table table-hover align-middle mb-0">

                                <thead class="table-light">

                                    <tr>

                                        <th class="ps-4">
                                            Módulo
                                        </th>

                                        <th class="text-center">
                                            <div>
                                                <i class="bi bi-eye me-1"></i>
                                                Ver
                                            </div>
                                        </th>

                                        <th class="text-center">
                                            <div>
                                                <i class="bi bi-plus-circle me-1"></i>
                                                Crear
                                            </div>
                                        </th>

                                        <th class="text-center">
                                            <div>
                                                <i class="bi bi-pencil me-1"></i>
                                                Editar
                                            </div>
                                        </th>

                                        <th class="text-center">
                                            <div>
                                                <i class="bi bi-trash me-1"></i>
                                                Eliminar
                                            </div>
                                        </th>

                                    </tr>

                                </thead>


                                <tbody id="tablaPermisos">

                                    <!--
                                        Los permisos serán cargados mediante AJAX
                                        cuando el administrador seleccione un rol.
                                    -->

                                </tbody>

                            </table>

                        </div>


                        <!-- =================================================
                             SIN PERMISOS
                        ================================================== -->

                        <div id="mensajeSinPermisos"
                            class="p-4 text-center"
                            style="display:none;">

                            <div class="mb-3">

                                <i class="bi bi-shield-x text-warning"
                                    style="font-size:3rem;">
                                </i>

                            </div>

                            <h6 class="fw-semibold">
                                Este rol no tiene permisos configurados
                            </h6>

                            <p class="text-muted mb-0">
                                Configure los permisos desde la sección
                                <a href="roles.php">
                                    Cargos y Roles
                                </a>.
                            </p>

                        </div>

                    </div>

                </div>
                <!-- =================================================
                     BOTONES
                ================================================== -->

                <div class="card border-0 shadow-sm mb-4">

                    <div class="card-body">

                        <div class="d-flex flex-wrap justify-content-end gap-2">

                            <a href="adm_lista_empleados.php"
                                class="btn btn-outline-secondary">

                                <i class="bi bi-x-circle me-2"></i>

                                Cancelar

                            </a>


                            <button type="reset"
                                class="btn btn-outline-warning"
                                id="btnLimpiarEmpleado">

                                <i class="bi bi-arrow-counterclockwise me-2"></i>

                                Limpiar

                            </button>


                            <button type="submit"
                                class="btn btn-primary px-4"
                                id="btnRegistrarEmpleado">

                                <i class="bi bi-person-plus-fill me-2"></i>

                                Registrar empleado

                            </button>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     CAMPOS OCULTOS
                ================================================== -->

                <input type="hidden"
                    name="id_user"
                    id="id_user"
                    value="<?= $idUser; ?>">

            </form>

        </main>

    </div>

</div>


<!-- =====================================================
     SCRIPTS
====================================================== -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>


<!-- =====================================================
     JAVASCRIPT DEL MÓDULO
====================================================== -->

<script src="js/adm_registrar_empleado.js"></script>


<!-- =====================================================
     JAVASCRIPT DEL MENÚ
====================================================== -->

<script src="js/menu.js"></script>


</body>

</html>