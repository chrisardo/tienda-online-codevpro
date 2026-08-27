<?php
//=====================================================
// CoDevPro Technology
// Archivo: adm_monedas_impuestos.php
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

        <main class="container-fluid px-4 py-4 adm-monedas-impuestos-page">


            <!--=================================================
                ENCABEZADO
            ==================================================-->

            <div class="page-header mb-4">

                <div>

                    <h1 class="page-title">

                        <i class="bi bi-currency-exchange me-2"></i>

                        Monedas e Impuestos

                    </h1>

                    <p class="page-description">

                        Configura la moneda principal de la tienda,
                        el formato de precios y los impuestos aplicables
                        a las ventas.

                    </p>

                </div>

            </div>



            <!--=================================================
                CONTENEDOR DE CONFIGURACIÓN
            ==================================================-->

            <div class="row g-4">


                <!--=================================================
                    COLUMNA IZQUIERDA
                ==================================================-->

                <div class="col-12 col-xl-8">


                    <!--=================================================
                        TARJETA MONEDA
                    ==================================================-->

                    <div class="card config-card border-0 shadow-sm mb-4">


                        <!-- HEADER -->

                        <div class="card-header bg-white border-0">

                            <div class="d-flex align-items-center">


                                <div class="config-icon moneda-icon">

                                    <i class="bi bi-currency-dollar"></i>

                                </div>


                                <div class="ms-3">

                                    <h5 class="mb-1">

                                        Configuración de moneda

                                    </h5>

                                    <p class="text-muted mb-0">

                                        Define cómo se mostrarán los
                                        precios en la tienda.

                                    </p>

                                </div>

                            </div>

                        </div>



                        <!-- BODY -->

                        <div class="card-body">


                            <div class="row g-3">


                                <!-- NOMBRE -->

                                <div class="col-md-6">

                                    <label
                                        for="nombre_moneda"
                                        class="form-label">

                                        Nombre de la moneda

                                    </label>

                                    <input
                                        type="text"
                                        class="form-control"
                                        id="nombre_moneda"
                                        name="nombre_moneda"
                                        placeholder="Ej. Sol peruano"
                                        maxlength="100">

                                </div>



                                <!-- CÓDIGO -->

                                <div class="col-md-6">

                                    <label
                                        for="codigo_moneda"
                                        class="form-label">

                                        Código de moneda

                                    </label>

                                    <input
                                        type="text"
                                        class="form-control text-uppercase"
                                        id="codigo_moneda"
                                        name="codigo_moneda"
                                        placeholder="Ej. PEN"
                                        maxlength="10">

                                    <div class="form-text">

                                        Código internacional de la moneda.

                                    </div>

                                </div>



                                <!-- SÍMBOLO -->

                                <div class="col-md-4">

                                    <label
                                        for="simbolo_moneda"
                                        class="form-label">

                                        Símbolo

                                    </label>

                                    <input
                                        type="text"
                                        class="form-control"
                                        id="simbolo_moneda"
                                        name="simbolo_moneda"
                                        placeholder="Ej. S/"
                                        maxlength="10">

                                </div>



                                <!-- DECIMALES -->

                                <div class="col-md-4">

                                    <label
                                        for="decimales"
                                        class="form-label">

                                        Decimales

                                    </label>

                                    <select
                                        class="form-select"
                                        id="decimales"
                                        name="decimales">

                                        <option value="0">
                                            0
                                        </option>

                                        <option value="1">
                                            1
                                        </option>

                                        <option value="2" selected>
                                            2
                                        </option>

                                        <option value="3">
                                            3
                                        </option>

                                        <option value="4">
                                            4
                                        </option>

                                    </select>

                                </div>



                                <!-- POSICIÓN -->

                                <div class="col-md-4">

                                    <label
                                        for="posicion_simbolo"
                                        class="form-label">

                                        Posición del símbolo

                                    </label>

                                    <select
                                        class="form-select"
                                        id="posicion_simbolo"
                                        name="posicion_simbolo">

                                        <option value="ANTES" selected>

                                            Antes del precio

                                        </option>

                                        <option value="DESPUES">

                                            Después del precio

                                        </option>

                                    </select>

                                </div>



                                <!-- SEPARADOR DECIMAL -->

                                <div class="col-md-6">

                                    <label
                                        for="separador_decimal"
                                        class="form-label">

                                        Separador decimal

                                    </label>

                                    <select
                                        class="form-select"
                                        id="separador_decimal"
                                        name="separador_decimal">

                                        <option value=".">

                                            Punto (.)

                                        </option>

                                        <option value=",">

                                            Coma (,)

                                        </option>

                                    </select>

                                </div>



                                <!-- SEPARADOR MILES -->

                                <div class="col-md-6">

                                    <label
                                        for="separador_miles"
                                        class="form-label">

                                        Separador de miles

                                    </label>

                                    <select
                                        class="form-select"
                                        id="separador_miles"
                                        name="separador_miles">

                                        <option value=",">

                                            Coma (,)

                                        </option>

                                        <option value=".">

                                            Punto (.)

                                        </option>

                                        <option value=" ">

                                            Espacio

                                        </option>

                                    </select>

                                </div>


                            </div>

                        </div>

                    </div>



                    <!--=================================================
                        TARJETA IMPUESTOS
                    ==================================================-->

                    <div class="card config-card border-0 shadow-sm mb-4">


                        <!-- HEADER -->

                        <div class="card-header bg-white border-0">

                            <div class="d-flex align-items-center">


                                <div class="config-icon impuesto-icon">

                                    <i class="bi bi-receipt-cutoff"></i>

                                </div>


                                <div class="ms-3">

                                    <h5 class="mb-1">

                                        Configuración de impuestos

                                    </h5>

                                    <p class="text-muted mb-0">

                                        Define el impuesto aplicado
                                        a las ventas.

                                    </p>

                                </div>

                            </div>

                        </div>



                        <!-- BODY -->

                        <div class="card-body">


                            <!-- ACTIVAR IMPUESTO -->

                            <div
                                class="tax-switch-box
                                       d-flex
                                       align-items-center
                                       justify-content-between
                                       mb-4">


                                <div>

                                    <strong>

                                        Activar impuesto

                                    </strong>

                                    <p class="text-muted mb-0">

                                        El impuesto se aplicará
                                        automáticamente a las ventas.

                                    </p>

                                </div>


                                <div class="form-check form-switch">

                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        role="switch"
                                        id="impuesto_activo"
                                        name="impuesto_activo">

                                </div>

                            </div>



                            <div class="row g-3">


                                <!-- NOMBRE IMPUESTO -->

                                <div class="col-md-6">

                                    <label
                                        for="nombre_impuesto"
                                        class="form-label">

                                        Nombre del impuesto

                                    </label>

                                    <input
                                        type="text"
                                        class="form-control"
                                        id="nombre_impuesto"
                                        name="nombre_impuesto"
                                        placeholder="Ej. IGV"
                                        maxlength="100">

                                </div>



                                <!-- PORCENTAJE -->

                                <div class="col-md-6">

                                    <label
                                        for="porcentaje_impuesto"
                                        class="form-label">

                                        Porcentaje

                                    </label>

                                    <div class="input-group">

                                        <input
                                            type="number"
                                            class="form-control"
                                            id="porcentaje_impuesto"
                                            name="porcentaje_impuesto"
                                            min="0"
                                            max="100"
                                            step="0.01"
                                            placeholder="18.00">

                                        <span class="input-group-text">

                                            %

                                        </span>

                                    </div>

                                </div>



                                <!-- PRECIOS INCLUYEN IMPUESTO -->

                                <div class="col-12">

                                    <div class="form-check
                                                impuesto-incluido-box">

                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            id="precios_incluyen_impuesto"
                                            name="precios_incluyen_impuesto">

                                        <label
                                            class="form-check-label"
                                            for="precios_incluyen_impuesto">

                                            <strong>

                                                Los precios incluyen impuesto

                                            </strong>

                                            <span class="d-block text-muted">

                                                Indica si los precios mostrados
                                                al cliente ya incluyen el impuesto.

                                            </span>

                                        </label>

                                    </div>

                                </div>


                            </div>

                        </div>

                    </div>



                    <!--=================================================
                        BOTONES
                    ==================================================-->

                    <div class="d-flex
                                justify-content-end
                                gap-2
                                mb-4">


                        <button
                            type="button"
                            class="btn btn-outline-secondary"
                            id="btnRestablecer">

                            <i class="bi bi-arrow-counterclockwise me-1"></i>

                            Restablecer

                        </button>


                        <button
                            type="button"
                            class="btn btn-primary"
                            id="btnGuardarConfiguracion">

                            <i class="bi bi-check-lg me-1"></i>

                            Guardar configuración

                        </button>


                    </div>


                </div>



                <!--=================================================
                    COLUMNA DERECHA
                ==================================================-->

                <div class="col-12 col-xl-4">


                    <!--=================================================
                        VISTA PREVIA
                    ==================================================-->

                    <div class="card preview-card border-0 shadow-sm mb-4">


                        <div class="card-header bg-white border-0">

                            <div class="d-flex align-items-center">

                                <div class="config-icon preview-icon">

                                    <i class="bi bi-eye"></i>

                                </div>

                                <div class="ms-3">

                                    <h5 class="mb-1">

                                        Vista previa

                                    </h5>

                                    <p class="text-muted mb-0">

                                        Así se mostrarán los precios.

                                    </p>

                                </div>

                            </div>

                        </div>



                        <div class="card-body">


                            <!-- PRECIO GRANDE -->

                            <div class="price-preview">

                                <span
                                    id="previewPrecio">

                                    S/ 1,250.50

                                </span>

                            </div>



                            <!-- DATOS -->

                            <div class="preview-details">


                                <div class="preview-row">

                                    <span>

                                        Moneda

                                    </span>

                                    <strong id="previewMoneda">

                                        Sol peruano

                                    </strong>

                                </div>


                                <div class="preview-row">

                                    <span>

                                        Código

                                    </span>

                                    <strong id="previewCodigo">

                                        PEN

                                    </strong>

                                </div>


                                <div class="preview-row">

                                    <span>

                                        Impuesto

                                    </span>

                                    <strong id="previewImpuesto">

                                        IGV 18%

                                    </strong>

                                </div>


                                <div class="preview-row">

                                    <span>

                                        Precios incluyen impuesto

                                    </span>

                                    <strong id="previewIncluye">

                                        No

                                    </strong>

                                </div>


                            </div>


                        </div>

                    </div>



                    <!--=================================================
                        INFORMACIÓN
                    ==================================================-->

                    <div class="card info-card border-0 shadow-sm">


                        <div class="card-body">


                            <div class="d-flex">

                                <div class="info-icon">

                                    <i class="bi bi-info-circle-fill"></i>

                                </div>


                                <div class="ms-3">

                                    <h6>

                                        Importante

                                    </h6>

                                    <p class="text-muted mb-0">

                                        Esta configuración será utilizada
                                        posteriormente por el sistema de
                                        ventas y productos para mostrar y
                                        calcular correctamente los precios.

                                    </p>

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
    src="js/adm_monedas_impuestos.js">
</script>



<!--=====================================================
    MENÚ ADMINISTRATIVO
======================================================-->

<script
    src="js/menu.js">
</script>


</body>

</html>