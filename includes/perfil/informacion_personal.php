<!--======================================================
CoDevPro Technology
INFORMACIÓN PERSONAL DEL CLIENTE
=======================================================-->

<div class="card border-0 shadow-sm">

    <!--=========================================
    HEADER
    ==========================================-->

    <div class="card-header bg-white">

        <div class="d-flex justify-content-between align-items-center">

            <div>

                <h4 class="fw-bold mb-1">

                    <i class="bi bi-person-vcard-fill text-primary"></i>

                    Información personal

                </h4>

                <small class="text-muted">

                    Administra la información de tu cuenta.

                </small>

            </div>

            <button
                class="btn btn-primary"
                id="btnEditarPerfil">

                <i class="bi bi-pencil-square"></i>

                Editar información

            </button>

        </div>

    </div>

    <!--=========================================
    BODY
    ==========================================-->

    <div class="card-body">

        <form id="formEditarPerfil">
            <div class="row g-4">

                <!--======================================================
                    =            FOTOGRAFÍA DEL CLIENTE
                    ======================================================-->

                <div class="col-lg-3 text-center">

                    <?php

                    if (!empty($cliente["imagen"])) {

                        $fotoPerfil = "data:image/jpeg;base64," .
                            base64_encode($cliente["imagen"]);
                    } else {

                        $fotoPerfil = "assets/img/user.png";
                    }

                    ?>

                    <img

                        id="previewFotoPerfil"

                        src="<?= $fotoPerfil ?>"

                        class="img-fluid rounded-circle border shadow"

                        style="width:180px;
                        height:180px;
                        object-fit:cover;
                        cursor:pointer;">

                    <!--=========================================
                    INPUT OCULTO
                    ==========================================-->

                    <input

                        type="file"

                        id="fotoPerfil"

                        name="foto"

                        accept=".jpg,.jpeg,.png,.webp"

                        class="d-none">

                    <!--=========================================
                    BOTÓN CAMBIAR FOTO
                    ==========================================-->

                    <div class="mt-3 d-grid gap-2">

                        <button

                            type="button"

                            class="btn btn-outline-primary"

                            id="btnCambiarFoto">

                            <i class="bi bi-camera-fill me-2"></i>

                            Cambiar fotografía

                        </button>

                        <small class="text-muted">

                            JPG, PNG o WEBP

                            <br>

                            Máximo 3 MB

                        </small>

                    </div>

                </div>

                <!-- DATOS -->

                <div class="col-lg-9">

                    <div class="row g-4">

                        <div class="col-md-6">

                            <label class="form-label fw-semibold">

                                Nombre completo

                            </label>

                            <input
                                type="text"
                                name="nombre"
                                class="form-control"
                                value="<?= !empty($cliente["nombre"])
                                            ? htmlspecialchars($cliente["nombre"])
                                            : "-" ?>"
                                readonly>

                        </div>

                        <div class="col-md-6">

                            <label class="form-label fw-semibold">

                                DNI / RUC

                            </label>

                            <input
                                type="text"
                                name="dni_o_ruc"
                                class="form-control"
                                value="<?= !empty($cliente["dni_o_ruc"])
                                            ? htmlspecialchars($cliente["dni_o_ruc"])
                                            : "-" ?>"
                                readonly>

                        </div>

                        <div class="col-md-6">

                            <label class="form-label fw-semibold">

                                Correo electrónico

                            </label>

                            <input
                                type="email"
                                name="email"
                                class="form-control"
                                value="<?= !empty($cliente["email"])
                                            ? htmlspecialchars($cliente["email"])
                                            : "-" ?>"
                                readonly>

                        </div>

                        <div class="col-md-6">

                            <label class="form-label fw-semibold">

                                Celular

                            </label>

                            <input
                                type="text"
                                name="celular"
                                class="form-control"
                                value="<?= !empty($cliente["celular"])
                                            ? htmlspecialchars($cliente["celular"])
                                            : "-" ?>"
                                readonly>

                        </div>

                        <div class="col-md-3">

                            <label class="form-label fw-semibold">

                                País

                            </label>

                            <?php

                            $sqlPais = "

                                SELECT *

                                FROM pais

                                WHERE Eliminado=0

                                ORDER BY nombre

                                ";

                            $paises = mysqli_query($conexion, $sqlPais);

                            ?>

                            <select
                                class="form-select"
                                id="pais"
                                name="id_pais"
                                disabled>

                                <?php while ($p = mysqli_fetch_assoc($paises)) { ?>

                                    <option
                                        value="<?= $p["id_pais"] ?>"
                                        <?= $cliente["id_pais"] == $p["id_pais"] ? "selected" : "" ?>>

                                        <?= htmlspecialchars($p["nombre"]) ?>

                                    </option>

                                <?php } ?>

                            </select>

                        </div>

                        <div class="col-md-3">

                            <label class="form-label fw-semibold">

                                Departamento

                            </label>

                            <?php

                            $sql = "

                            SELECT *

                            FROM departamento

                            WHERE id_pais='" . $cliente["id_pais"] . "'

                            ORDER BY nombre

                            ";

                            $departamentos = mysqli_query($conexion, $sql);

                            ?>

                            <select
                                class="form-select"
                                id="departamento"
                                name="id_departamento"
                                disabled>

                                <?php while ($d = mysqli_fetch_assoc($departamentos)) { ?>

                                    <option
                                        value="<?= $d["id_departamento"] ?>"
                                        <?= $cliente["id_departamento"] == $d["id_departamento"] ? "selected" : "" ?>>

                                        <?= htmlspecialchars($d["nombre"]) ?>

                                    </option>

                                <?php } ?>

                            </select>

                        </div>

                        <div class="col-md-3">

                            <label class="form-label fw-semibold">

                                Provincia

                            </label>

                            <?php

                            $sql = "

                                SELECT *

                                FROM provincia

                                WHERE id_departamento='" . $cliente["id_departamento"] . "'

                                ORDER BY nombre

                                ";

                            $provincias = mysqli_query($conexion, $sql);

                            ?>

                            <select
                                class="form-select"
                                id="provincia"
                                name="id_provincia"
                                disabled>

                                <?php while ($p = mysqli_fetch_assoc($provincias)) { ?>

                                    <option
                                        value="<?= $p["id_provincia"] ?>"
                                        <?= $cliente["id_provincia"] == $p["id_provincia"] ? "selected" : "" ?>>

                                        <?= htmlspecialchars($p["nombre"]) ?>

                                    </option>

                                <?php } ?>

                            </select>

                        </div>

                        <div class="col-md-3">

                            <label class="form-label fw-semibold">

                                Distrito

                            </label>

                            <?php

                            $sql = "

                            SELECT *

                            FROM distrito

                            WHERE id_provincia='" . $cliente["id_provincia"] . "'

                            ORDER BY nombre

                            ";

                            $distritos = mysqli_query($conexion, $sql);

                            ?>

                            <select
                                class="form-select"
                                id="distrito"
                                name="id_distrito"
                                disabled>

                                <?php while ($d = mysqli_fetch_assoc($distritos)) { ?>

                                    <option
                                        value="<?= $d["id_distrito"] ?>"
                                        <?= $cliente["id_distrito"] == $d["id_distrito"] ? "selected" : "" ?>>

                                        <?= htmlspecialchars($d["nombre"]) ?>

                                    </option>

                                <?php } ?>

                            </select>

                        </div>

                        <div class="col-md-6">

                            <label class="form-label fw-semibold">

                                Fecha de registro

                            </label>

                            <input
                                type="text"
                                class="form-control"
                                value="<?= date("d/m/Y", strtotime($cliente["fecha_registro"])) ?>"
                                readonly>

                        </div>

                        <div class="col-12">

                            <label class="form-label fw-semibold">

                                Dirección

                            </label>

                            <textarea
                                class="form-control"
                                rows="3"
                                name="direccion"
                                readonly><?= !empty($cliente["direccion"])
                                                ? htmlspecialchars($cliente["direccion"])
                                                : "-" ?></textarea>

                        </div>

                    </div>

                </div>
                <!--=========================================
                FOOTER
                ==========================================-->

                <div class="card-footer bg-white">

                    <div class="row">

                        <div class="col-md-6">

                            <small class="text-muted">

                                Última actualización:

                                <strong>

                                    <?=

                                    !empty($cliente["fecha_actualizado"])

                                        ? date("d/m/Y", strtotime($cliente["fecha_actualizado"]))

                                        : "Sin actualizaciones";

                                    ?>

                                </strong>

                            </small>

                        </div>

                        <div class="col-md-6 text-md-end">

                            <button
                                type="submit"
                                class="btn btn-success"
                                id="btnGuardarPerfil"
                                disabled>

                                <i class="bi bi-check-circle-fill"></i>

                                Guardar cambios

                            </button>

                        </div>

                    </div>

                </div>

            </div>
        </form>
    </div>



</div>