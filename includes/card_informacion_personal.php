<?php
//======================================================
// CoDevPro Technology
// includes/card_informacion_personal.php
//======================================================

//------------------------------------------------------
// PAÍSES
//------------------------------------------------------

$sqlPaises = "SELECT *
FROM pais
WHERE Eliminado=0
ORDER BY nombre ASC";

$paises = mysqli_query(
    $conexion,
    $sqlPaises
);

//------------------------------------------------------
// DEPARTAMENTOS
//------------------------------------------------------

$departamentos = [];

if (!empty($cliente["id_pais"])) {

    $sql = "SELECT *
    FROM departamento
    WHERE Eliminado=0
    AND id_pais=?
    ORDER BY nombre ASC";

    $stmt = mysqli_prepare(
        $conexion,
        $sql
    );

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $cliente["id_pais"]
    );

    mysqli_stmt_execute($stmt);

    $departamentos = mysqli_stmt_get_result($stmt);
}

//------------------------------------------------------
// PROVINCIAS
//------------------------------------------------------

$provincias = [];

if (!empty($cliente["id_departamento"])) {

    $sql = "SELECT *
    FROM provincia
    WHERE Eliminado=0
    AND id_departamento=?
    ORDER BY nombre ASC";

    $stmt = mysqli_prepare(
        $conexion,
        $sql
    );

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $cliente["id_departamento"]
    );

    mysqli_stmt_execute($stmt);

    $provincias = mysqli_stmt_get_result($stmt);
}

//------------------------------------------------------
// DISTRITOS
//------------------------------------------------------

$distritos = [];

if (!empty($cliente["id_provincia"])) {

    $sql = "SELECT *
    FROM distrito
    WHERE Eliminado=0
    AND id_provincia=?
    ORDER BY nombre ASC";

    $stmt = mysqli_prepare(
        $conexion,
        $sql
    );

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $cliente["id_provincia"]
    );

    mysqli_stmt_execute($stmt);

    $distritos = mysqli_stmt_get_result($stmt);
}

?>

<!--======================================================
=            INFORMACIÓN PERSONAL
=======================================================-->

<div class="card border-0 shadow-sm mb-4">

    <div class="card-header bg-white py-3">

        <div class="d-flex align-items-center">

            <div
                class="rounded-circle bg-primary text-white d-flex justify-content-center align-items-center me-3"
                style="width:55px;height:55px;">

                <i class="bi bi-person-vcard fs-3"></i>

            </div>

            <div>

                <h4 class="fw-bold mb-0">

                    Información Personal

                </h4>

                <small class="text-muted">

                    Actualiza tus datos personales.

                </small>

            </div>

        </div>

    </div>

    <div class="card-body">

        <form
            id="formPerfil"
            autocomplete="off">

            <div class="row">

                <!--=========================================
                NOMBRE
                ==========================================-->

                <div class="col-md-6 mb-3">

                    <label class="form-label fw-semibold">

                        Nombre Completo

                    </label>

                    <input
                        type="text"
                        class="form-control"
                        id="nombre"
                        name="nombre"
                        maxlength="150"
                        value="<?= htmlspecialchars($cliente["nombre"]) ?>">

                </div>

                <!--=========================================
                DNI
                ==========================================-->

                <div class="col-md-6 mb-3">

                    <label class="form-label fw-semibold">

                        DNI / RUC

                    </label>

                    <input
                        type="text"
                        class="form-control"
                        id="dni"
                        name="dni"
                        maxlength="11"
                        value="<?= htmlspecialchars($cliente["dni_o_ruc"]) ?>">

                    <small
                        id="mensajeDni"
                        class="text-danger"></small>

                </div>

                <!--=========================================
                CELULAR
                ==========================================-->

                <div class="col-md-6 mb-3">

                    <label class="form-label fw-semibold">

                        Celular

                    </label>

                    <input
                        type="text"
                        class="form-control"
                        id="celular"
                        name="celular"
                        maxlength="9"
                        value="<?= htmlspecialchars($cliente["celular"]) ?>">

                </div>

                <!--=========================================
                EMAIL
                ==========================================-->

                <div class="col-md-6 mb-3">

                    <label class="form-label fw-semibold">

                        Correo Electrónico

                    </label>

                    <input
                        type="email"
                        class="form-control"
                        id="email"
                        name="email"
                        maxlength="150"
                        value="<?= htmlspecialchars($cliente["email"]) ?>">

                </div>

                <!--=========================================
                PAÍS
                ==========================================-->

                <div class="col-md-6 mb-3">

                    <label class="form-label fw-semibold">

                        País

                    </label>

                    <select
                        class="form-select"
                        id="id_pais"
                        name="id_pais">

                        <option value="">

                            Seleccione

                        </option>

                        <?php while ($pais = mysqli_fetch_assoc($paises)): ?>

                            <option
                                value="<?= $pais["id_pais"] ?>"
                                <?= ($cliente["id_pais"] == $pais["id_pais"]) ? "selected" : "" ?>>

                                <?= htmlspecialchars($pais["nombre"]) ?>

                            </option>

                        <?php endwhile; ?>

                    </select>

                </div>

                <!--=========================================
                DEPARTAMENTO
                ==========================================-->

                <div class="col-md-6 mb-3">

                    <label class="form-label fw-semibold">

                        Departamento

                    </label>

                    <select
                        class="form-select"
                        id="id_departamento"
                        name="id_departamento">

                        <option value="">

                            Seleccione

                        </option>

                        <?php

                        if ($departamentos) :

                            while ($dep = mysqli_fetch_assoc($departamentos)) :

                        ?>

                                <option
                                    value="<?= $dep["id_departamento"] ?>"
                                    <?= ($cliente["id_departamento"] == $dep["id_departamento"]) ? "selected" : "" ?>>

                                    <?= htmlspecialchars($dep["nombre"]) ?>

                                </option>

                        <?php

                            endwhile;

                        endif;

                        ?>

                    </select>

                </div>

                <!--=========================================
                PROVINCIA
                ==========================================-->

                <div class="col-md-6 mb-3">

                    <label class="form-label fw-semibold">

                        Provincia

                    </label>

                    <select
                        class="form-select"
                        id="id_provincia"
                        name="id_provincia">

                        <option value="">

                            Seleccione

                        </option>

                        <?php

                        if ($provincias) :

                            while ($prov = mysqli_fetch_assoc($provincias)) :

                        ?>

                                <option
                                    value="<?= $prov["id_provincia"] ?>"
                                    <?= ($cliente["id_provincia"] == $prov["id_provincia"]) ? "selected" : "" ?>>

                                    <?= htmlspecialchars($prov["nombre"]) ?>

                                </option>

                        <?php

                            endwhile;

                        endif;

                        ?>

                    </select>

                </div>

                <!--=========================================
                DISTRITO
                ==========================================-->

                <div class="col-md-6 mb-3">

                    <label class="form-label fw-semibold">

                        Distrito

                    </label>

                    <select
                        class="form-select"
                        id="id_distrito"
                        name="id_distrito">

                        <option value="">

                            Seleccione

                        </option>

                        <?php

                        if ($distritos) :

                            while ($dist = mysqli_fetch_assoc($distritos)) :

                        ?>

                                <option
                                    value="<?= $dist["id_distrito"] ?>"
                                    <?= ($cliente["id_distrito"] == $dist["id_distrito"]) ? "selected" : "" ?>>

                                    <?= htmlspecialchars($dist["nombre"]) ?>

                                </option>

                        <?php

                            endwhile;

                        endif;

                        ?>

                    </select>

                </div>

                <!--=========================================
                DIRECCIÓN
                ==========================================-->

                <div class="col-12 mb-4">

                    <label class="form-label fw-semibold">

                        Dirección Principal

                    </label>

                    <textarea
                        class="form-control"
                        rows="4"
                        id="direccion"
                        name="direccion"
                        maxlength="255"><?= htmlspecialchars($cliente["direccion"]) ?></textarea>

                </div>

            </div>

            <hr>

            <div class="d-flex justify-content-end">

                <button
                    type="button"
                    class="btn btn-primary px-4"
                    id="btnGuardarPerfil">

                    <i class="bi bi-check-circle-fill me-2"></i>

                    Guardar Cambios

                </button>

            </div>

        </form>

    </div>

</div>