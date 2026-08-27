<?php
session_start();

/*=========================================
CLIENTE LOGUEADO
=========================================*/

if (isset($_SESSION["idCliente"])) {

    header("Location: index.php");
    exit();
}


/*=========================================
ADMINISTRADOR LOGUEADO
=========================================*/

if (isset($_SESSION["idUser"])) {

    header("Location: admin_index.php");
    exit();
}


require_once "controladores/conexion.php";

/*=========================================
DATOS DE LA EMPRESA
=========================================*/

$sqlEmpresa = "SELECT *
FROM usuario_acceso
ORDER BY id_user ASC
LIMIT 1";

$resultEmpresa = mysqli_query($conexion, $sqlEmpresa);

$empresa = mysqli_fetch_assoc($resultEmpresa);
?>
<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1">

    <title>

        Iniciar Sesión |
        <?= htmlspecialchars($empresa["nombreEmpresa"]); ?>

    </title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet">
    <link rel="stylesheet" href="css/login.css">

</head>

<body>

    <?php include "includes/navbar.php"; ?>

    <div class="container py-5">

        <div class="row justify-content-center">

            <div class="col-lg-10">

                <div class="card login-card">

                    <div class="row g-0">

                        <!--=====================================
                    IZQUIERDA
                    ======================================-->

                        <div class="col-lg-5 login-left d-flex flex-column justify-content-center">

                            <div class="text-center">

                                <img
                                    src="assets/logos/logo.png"
                                    class="logo mb-4">

                                <h2>

                                    Bienvenido

                                </h2>

                                <p class="mt-3">

                                    Inicia sesión para acceder a tu cuenta,
                                    revisar tus pedidos y finalizar tus compras.

                                </p>

                                <hr>

                                <h5>

                                    <?= htmlspecialchars($empresa["nombreEmpresa"]); ?>

                                </h5>

                            </div>

                        </div>

                        <!--=====================================
                    DERECHA
                    ======================================-->

                        <div class="col-lg-7 login-right">

                            <h3 class="fw-bold mb-4">

                                Iniciar Sesión

                            </h3>

                            <form id="formLogin">

                                <div class="mb-3">

                                    <label class="form-label">

                                        Correo electrónico

                                    </label>

                                    <input
                                        type="email"
                                        class="form-control"
                                        name="email"
                                        id="email"
                                        autocomplete="email"
                                        required>

                                </div>

                                <div class="mb-3">

                                    <label class="form-label">

                                        Contraseña

                                    </label>

                                    <div class="input-group">

                                        <input
                                            type="password"
                                            class="form-control"
                                            name="contrasena"
                                            id="contrasena"
                                            required>

                                        <button
                                            class="btn btn-outline-secondary"
                                            type="button"
                                            id="verPassword">

                                            <i class="bi bi-eye"></i>

                                        </button>

                                    </div>

                                </div>

                                <div class="d-flex justify-content-between mb-4">

                                    <div class="form-check">

                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            id="recordarme">

                                        <label
                                            class="form-check-label"
                                            for="recordarme">

                                            Recordarme

                                        </label>

                                    </div>

                                    <a href="recuperar_password.php">

                                        ¿Olvidaste tu contraseña?

                                    </a>

                                </div>

                                <div class="d-grid mb-3">

                                    <button
                                        type="submit"
                                        class="btn btn-primary btn-login">

                                        <i class="bi bi-box-arrow-in-right"></i>

                                        Iniciar Sesión

                                    </button>

                                </div>

                                <div class="text-center">

                                    ¿No tienes cuenta?

                                    <a href="registro_cuenta.php">

                                        Regístrate aquí

                                    </a>

                                </div>

                            </form>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/login.js"></script>

    <script>
        document.getElementById("verPassword").onclick = function() {

            let pass = document.getElementById("contrasena");

            if (pass.type == "password") {

                pass.type = "text";

                this.innerHTML = '<i class="bi bi-eye-slash"></i>';

            } else {

                pass.type = "password";

                this.innerHTML = '<i class="bi bi-eye"></i>';

            }

        };
    </script>

</body>

</html>