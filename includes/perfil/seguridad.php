<!--======================================================
CoDevPro Technology
SEGURIDAD DE LA CUENTA
includes/perfil/seguridad.php
=======================================================-->

<div class="card border-0 shadow-sm">

    <!--=========================================
    HEADER
    ==========================================-->

    <div class="card-header bg-white">

        <div class="d-flex justify-content-between align-items-center">

            <div>

                <h4 class="fw-bold mb-1">

                    <i class="bi bi-shield-lock-fill text-primary"></i>

                    Seguridad de la cuenta

                </h4>

                <small class="text-muted">

                    Protege tu cuenta y mantén tu información segura.

                </small>

            </div>

            <span
                class="badge bg-success fs-6"
                id="estadoCuenta">

                <i class="bi bi-shield-check"></i>

                Cuenta protegida

            </span>

        </div>

    </div>


    <!--=========================================
    BODY
    ==========================================-->

    <div class="card-body">

        <div class="row g-4">


            <!--=========================================
            CAMBIAR CONTRASEÑA
            ==========================================-->

            <div class="col-lg-8">

                <div class="card border-0 bg-light">

                    <div class="card-body">

                        <h5 class="fw-bold mb-4">

                            Cambiar contraseña

                        </h5>


                        <!-- CONTRASEÑA ACTUAL -->

                        <div class="mb-4">

                            <label class="form-label fw-semibold">

                                Contraseña actual

                            </label>

                            <div class="input-group">

                                <span class="input-group-text">

                                    <i class="bi bi-lock-fill"></i>

                                </span>

                                <input
                                    type="password"
                                    class="form-control"
                                    id="passwordActual"
                                    placeholder="Ingrese su contraseña actual">

                                <button
                                    type="button"
                                    class="btn btn-outline-secondary btnPassword"
                                    data-target="passwordActual">

                                    <i class="bi bi-eye"></i>

                                </button>

                            </div>

                        </div>


                        <!-- NUEVA CONTRASEÑA -->

                        <div class="mb-4">

                            <label class="form-label fw-semibold">

                                Nueva contraseña

                            </label>

                            <div class="input-group">

                                <span class="input-group-text">

                                    <i class="bi bi-key-fill"></i>

                                </span>

                                <input
                                    type="password"
                                    class="form-control"
                                    id="passwordNueva"
                                    placeholder="Ingrese una nueva contraseña">

                                <button
                                    type="button"
                                    class="btn btn-outline-secondary btnPassword"
                                    data-target="passwordNueva">

                                    <i class="bi bi-eye"></i>

                                </button>

                            </div>

                        </div>


                        <!-- CONFIRMAR CONTRASEÑA -->

                        <div class="mb-4">

                            <label class="form-label fw-semibold">

                                Confirmar contraseña

                            </label>

                            <div class="input-group">

                                <span class="input-group-text">

                                    <i class="bi bi-check-circle-fill"></i>

                                </span>

                                <input
                                    type="password"
                                    class="form-control"
                                    id="passwordConfirmar"
                                    placeholder="Repita la nueva contraseña">

                                <button
                                    type="button"
                                    class="btn btn-outline-secondary btnPassword"
                                    data-target="passwordConfirmar">

                                    <i class="bi bi-eye"></i>

                                </button>

                            </div>

                        </div>



                        <!--=========================================
                        FORTALEZA DE CONTRASEÑA
                        ==========================================-->

                        <div class="mb-3">

                            <label class="form-label fw-semibold">

                                Seguridad de la contraseña

                            </label>

                            <div
                                class="progress"
                                style="height:12px;">

                                <div
                                    id="barraPassword"
                                    class="progress-bar"
                                    style="width:0%;">

                                </div>

                            </div>

                            <small
                                id="textoFortaleza"
                                class="text-muted">

                                Ingrese una contraseña segura.

                            </small>

                        </div>

                    </div>

                </div>

            </div>



            <!--=========================================
            PANEL DERECHO
            ==========================================-->

            <div class="col-lg-4">


                <!-- INFORMACIÓN -->

                <div class="card border-primary mb-4">

                    <div class="card-header bg-primary text-white">

                        <h6 class="mb-0">

                            <i class="bi bi-person-lock"></i>

                            Información de seguridad

                        </h6>

                    </div>

                    <div class="card-body">

                        <p class="mb-3">

                            <strong>Correo:</strong>

                            <br>

                            <span id="correoSeguridad">

                                <?= htmlspecialchars($cliente["email"]); ?>

                            </span>

                        </p>
                        <p class="mb-0">

                            <strong>Estado:</strong>

                            <br>

                            <span class="text-success">

                                Cuenta protegida

                            </span>

                        </p>

                    </div>

                </div>



                <!-- RECOMENDACIONES -->

                <div class="card border-warning">

                    <div class="card-header bg-warning">

                        <h6 class="mb-0">

                            <i class="bi bi-lightbulb-fill"></i>

                            Recomendaciones

                        </h6>

                    </div>

                    <div class="card-body">

                        <ul class="list-group list-group-flush">

                            <li class="list-group-item">

                                Mínimo 8 caracteres.

                            </li>

                            <li class="list-group-item">

                                Una letra mayúscula.

                            </li>

                            <li class="list-group-item">

                                Una letra minúscula.

                            </li>

                            <li class="list-group-item">

                                Al menos un número.

                            </li>

                            <li class="list-group-item">

                                Un carácter especial.

                            </li>

                        </ul>

                    </div>

                </div>

            </div>

        </div>

    </div>



    <!--=========================================
    FOOTER
    ==========================================-->

    <div class="card-footer bg-white">

        <div class="d-flex justify-content-end gap-2">

            <button
                type="button"
                id="btnCancelarPassword"
                class="btn btn-outline-secondary">

                <i class="bi bi-arrow-counterclockwise"></i>

                Limpiar

            </button>


            <button
                type="button"
                id="btnActualizarPassword"
                class="btn btn-primary">

                <i class="bi bi-shield-lock-fill"></i>

                Actualizar contraseña

            </button>

        </div>

    </div>

</div>