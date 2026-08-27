<?php
session_start();

require_once "controladores/conexion.php";

/*
=========================================
EMPRESA
=========================================
*/

$sqlEmpresa = "SELECT nombreEmpresa,imagen
FROM usuario_acceso
LIMIT 1";

$resEmpresa = mysqli_query($conexion, $sqlEmpresa);

$empresa = mysqli_fetch_assoc($resEmpresa);

/*
=========================================
PAÍSES
=========================================
*/

$sqlPais = "

SELECT *

FROM pais

WHERE Eliminado=0

ORDER BY nombre ASC

";

$paises = mysqli_query(
    $conexion,
    $sqlPais
);

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>

        Crear Cuenta |

        <?= htmlspecialchars($empresa["nombreEmpresa"]); ?>

    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet">
    <link rel="stylesheet" href="css/registro_cuenta.css">

</head>

<body>

    <?php include "includes/navbar.php"; ?>

    <div class="container py-5">

        <div class="row justify-content-center">

            <div class="col-lg-10">

                <div class="card shadow">

                    <div class="card-body p-5">

                        <div class="text-center mb-5">

                            <?php if (!empty($empresa["imagen"])): ?>

                                <img
                                    src="assets/logos/logo.png"
                                    class="logo mb-3">

                            <?php endif; ?>

                            <h2 class="fw-bold">

                                Crear Cuenta

                            </h2>

                            <p class="text-muted">

                                Complete sus datos para registrarse.

                            </p>

                        </div>
                        <div class="alert alert-light border mb-4">

                            <small>

                                <i class="bi bi-asterisk text-danger"></i>
                                Campo obligatorio

                                &nbsp;&nbsp;&nbsp;

                                <i class="bi bi-patch-question text-secondary"></i>
                                Campo opcional

                            </small>

                        </div>
                        <form
                            id="formRegistroCliente"
                            autocomplete="off"
                            enctype="multipart/form-data">

                            <div class="row">

                                <!-- FOTO -->

                                <div class="col-md-12 text-center mb-4">

                                    <label class="form-label fw-bold">

                                        Foto de Perfil (Opcional)
                                        <i class="bi bi-patch-question text-secondary"
                                            title="Campo opcional">
                                        </i>
                                    </label>

                                    <br>

                                    <img

                                        id="preview"

                                        src="assets/img/usuario.png"

                                        class="rounded-circle border"

                                        style="width:120px;height:120px;object-fit:cover;">

                                    <input

                                        type="file"

                                        class="form-control mt-3"

                                        id="imagen"

                                        name="imagen"

                                        accept="image/*">

                                </div>

                                <!-- NOMBRE -->

                                <div class="col-md-6 mb-3">

                                    <label class="form-label">

                                        Nombre Completo
                                        <i class="bi bi-asterisk text-danger"
                                            title="Campo obligatorio">
                                        </i>
                                    </label>

                                    <input

                                        type="text"

                                        class="form-control"

                                        name="nombre"

                                        required>

                                </div>

                                <!-- DNI -->

                                <div class="col-md-6 mb-3">

                                    <label class="form-label">

                                        DNI / RUC
                                        <i class="bi bi-asterisk text-danger"
                                            title="Campo obligatorio">
                                        </i>
                                    </label>

                                    <input

                                        type="text"

                                        class="form-control"

                                        maxlength="11"

                                        name="dni">

                                </div>

                                <!-- CELULAR -->

                                <div class="col-md-6 mb-3">

                                    <label class="form-label">

                                        Celular
                                        <i class="bi bi-asterisk text-danger"
                                            title="Campo obligatorio">
                                        </i>
                                    </label>

                                    <input

                                        type="text"

                                        class="form-control"

                                        maxlength="9"

                                        name="celular">

                                </div>

                                <!-- EMAIL -->

                                <div class="col-md-6 mb-3">

                                    <label class="form-label">

                                        Correo Electrónico
                                        <i class="bi bi-asterisk text-danger"
                                            title="Campo obligatorio">
                                        </i>
                                    </label>

                                    <input

                                        type="email"

                                        class="form-control"

                                        name="email"

                                        required>

                                </div>

                                <!-- PASSWORD -->

                                <div class="col-md-6 mb-3">

                                    <label class="form-label">

                                        Contraseña
                                        <i class="bi bi-asterisk text-danger"
                                            title="Campo obligatorio">
                                        </i>
                                    </label>

                                    <div class="input-group">

                                        <input
                                            type="password"
                                            class="form-control"
                                            id="password"
                                            name="password"
                                            autocomplete="new-password"
                                            required>

                                        <button
                                            class="btn btn-outline-secondary"
                                            type="button"
                                            id="verPassword">

                                            <i class="bi bi-eye"></i>

                                        </button>

                                    </div>

                                    <div class="progress mt-2" style="height:6px;">

                                        <div
                                            id="seguridadPassword"
                                            class="progress-bar"
                                            style="width:0%"></div>

                                    </div>

                                    <small id="textoPassword" class="text-muted"></small>

                                </div>

                                <!-- REPETIR PASSWORD -->

                                <div class="col-md-6 mb-3">

                                    <label class="form-label">

                                        Confirmar Contraseña
                                        <i class="bi bi-asterisk text-danger"
                                            title="Campo obligatorio">
                                        </i>
                                    </label>

                                    <div class="input-group">

                                        <input
                                            type="password"
                                            class="form-control"
                                            id="password2"
                                            name="password2"
                                            autocomplete="new-password"
                                            required>

                                        <button
                                            class="btn btn-outline-secondary"
                                            type="button"
                                            id="verPassword2">

                                            <i class="bi bi-eye"></i>

                                        </button>

                                    </div>

                                </div>

                                <!-- DIRECCION -->

                                <div class="col-md-12 mb-3">

                                    <label class="form-label">

                                        Dirección
                                        <i class="bi bi-asterisk text-danger"
                                            title="Campo obligatorio">
                                        </i>
                                    </label>

                                    <input

                                        type="text"

                                        class="form-control"

                                        name="direccion">

                                </div>

                                <!--=====================================
                                PAÍS
                                =====================================-->

                                <div class="col-md-3 mb-3">

                                    <label class="form-label">

                                        País
                                        <i class="bi bi-asterisk text-danger"
                                            title="Campo obligatorio">
                                        </i>
                                    </label>

                                    <select
                                        class="form-select"
                                        name="pais"
                                        id="pais">

                                        <option value="">

                                            Seleccione

                                        </option>

                                        <?php while ($pais = mysqli_fetch_assoc($paises)): ?>

                                            <option
                                                value="<?= $pais["id_pais"]; ?>">

                                                <?= htmlspecialchars($pais["nombre"]); ?>

                                            </option>

                                        <?php endwhile; ?>

                                    </select>

                                </div>


                                <!--=====================================
                                DEPARTAMENTO
                                =====================================-->

                                <div class="col-md-3 mb-3">

                                    <label class="form-label">

                                        Departamento
                                        <i class="bi bi-asterisk text-danger"
                                            title="Campo obligatorio">
                                        </i>
                                    </label>

                                    <select
                                        class="form-select"
                                        name="departamento"
                                        id="departamento">

                                        <option value="">

                                            Seleccione un país

                                        </option>

                                    </select>

                                </div>


                                <!--=====================================
                                PROVINCIA
                                =====================================-->

                                <div class="col-md-3 mb-3">

                                    <label class="form-label">

                                        Provincia
                                        <i class="bi bi-asterisk text-danger"
                                            title="Campo obligatorio">
                                        </i>
                                    </label>

                                    <select
                                        class="form-select"
                                        name="provincia"
                                        id="provincia">

                                        <option value="">

                                            Seleccione un departamento

                                        </option>

                                    </select>

                                </div>


                                <!--=====================================
                                DISTRITO
                                =====================================-->

                                <div class="col-md-3 mb-3">

                                    <label class="form-label">

                                        Distrito
                                        <i class="bi bi-asterisk text-danger"
                                            title="Campo obligatorio">
                                        </i>
                                    </label>

                                    <select
                                        class="form-select"
                                        name="distrito"
                                        id="distrito">

                                        <option value="">

                                            Seleccione una provincia

                                        </option>

                                    </select>

                                </div>

                                <div class="col-md-12">

                                    <div class="form-check">

                                        <input

                                            class="form-check-input"

                                            type="checkbox"

                                            required>

                                        <label class="form-check-label">

                                            Acepto los términos y condiciones.

                                        </label>

                                    </div>

                                </div>

                                <div class="col-md-12 mt-4">

                                    <button
                                        id="btnRegistrar"
                                        class="btn btn-primary btn-lg w-100"
                                        type="submit">

                                        <span id="textoBoton">

                                            <i class="bi bi-person-plus-fill"></i>

                                            Crear Cuenta

                                        </span>

                                    </button>

                                </div>

                                <div class="col-md-12 text-center mt-4">

                                    ¿Ya tienes una cuenta?

                                    <a href="login.php">

                                        Iniciar Sesión

                                    </a>

                                </div>

                            </div>

                        </form>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <script>
        document.getElementById("imagen").addEventListener("change", function(e) {

            const file = e.target.files[0];

            if (!file) return;

            document.getElementById("preview").src = URL.createObjectURL(file);

        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="js/registro_cliente.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>