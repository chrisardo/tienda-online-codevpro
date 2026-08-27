<?php
//=====================================================
// CoDevPro Technology
// Archivo: modal/modal_adm_registrar_cuenta.php
// Módulo: Cuentas Bancarias
// Sistema: Inventa
//=====================================================
?>

<!--=====================================================
    MODAL REGISTRAR CUENTA BANCARIA
======================================================-->

<div
    class="modal fade"
    id="modalRegistrarCuenta"
    tabindex="-1"
    aria-labelledby="modalRegistrarCuentaLabel"
    aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content border-0 shadow">


            <!--=================================================
                CABECERA
            ==================================================-->

            <div class="modal-header">

                <div class="d-flex align-items-center">

                    <div class="me-3">

                        <div
                            class="d-flex align-items-center justify-content-center bg-primary text-white rounded-circle"
                            style="width: 45px; height: 45px;">

                            <i class="bi bi-bank2 fs-5"></i>

                        </div>

                    </div>


                    <div>

                        <h5
                            class="modal-title mb-1"
                            id="modalRegistrarCuentaLabel">

                            Registrar cuenta bancaria

                        </h5>

                        <p class="text-muted small mb-0">

                            Ingresa los datos de la nueva cuenta.

                        </p>

                    </div>

                </div>


                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Cerrar">
                </button>

            </div>


            <!--=================================================
                FORMULARIO
            ==================================================-->

            <form
                id="formRegistrarCuenta"
                method="POST"
                autocomplete="off">

                <!--=================================================
                    CUERPO
                ==================================================-->

                <div class="modal-body p-4">


                    <!--=================================================
                        INFORMACIÓN
                    ==================================================-->

                    <div
                        class="alert alert-light border mb-4"
                        role="alert">

                        <div class="d-flex align-items-start">

                            <i
                                class="bi bi-info-circle text-primary me-2 mt-1">
                            </i>

                            <div>

                                <strong class="d-block mb-1">
                                    Nueva cuenta bancaria
                                </strong>

                                <span class="small text-muted">
                                    Registra el nombre de la cuenta y el saldo
                                    inicial disponible.
                                </span>

                            </div>

                        </div>

                    </div>


                    <!--=================================================
                        NOMBRE DE LA CUENTA
                    ==================================================-->

                    <div class="mb-3">

                        <label
                            for="nombreCuenta"
                            class="form-label fw-semibold">

                            Nombre de la cuenta

                            <span class="text-danger">
                                *
                            </span>

                        </label>


                        <div class="input-group">

                            <span class="input-group-text">

                                <i class="bi bi-bank"></i>

                            </span>


                            <input
                                type="text"
                                class="form-control"
                                id="nombreCuenta"
                                name="nombre"
                                placeholder="Ej. Cuenta BCP"
                                maxlength="100"
                                required>

                        </div>


                        <div class="form-text">

                            Ingresa un nombre que permita identificar fácilmente
                            la cuenta.

                        </div>

                    </div>


                    <!--=================================================
                        BALANCE INICIAL
                    ==================================================-->

                    <div class="mb-3">

                        <label
                            for="balanceCuenta"
                            class="form-label fw-semibold">

                            Balance inicial

                            <span class="text-danger">
                                *
                            </span>

                        </label>


                        <div class="input-group">

                            <span class="input-group-text">

                                S/.

                            </span>


                            <input
                                type="number"
                                class="form-control"
                                id="balanceCuenta"
                                name="balance"
                                placeholder="0.00"
                                min="0"
                                step="0.01"
                                value="0.00"
                                required>

                        </div>


                        <div class="form-text">

                            Ingresa el saldo disponible al momento de registrar
                            la cuenta.

                        </div>

                    </div>


                    <!--=================================================
                        CONTENEDOR PARA MENSAJES AJAX
                    ==================================================-->

                    <div
                        id="mensajeRegistrarCuenta"
                        class="mt-3"
                        style="display: none;">
                    </div>

                </div>


                <!--=================================================
                    PIE DEL MODAL
                ==================================================-->

                <div class="modal-footer bg-light">

                    <button
                        type="button"
                        class="btn btn-outline-secondary"
                        data-bs-dismiss="modal">

                        <i class="bi bi-x-circle me-1"></i>

                        Cancelar

                    </button>


                    <button
                        type="submit"
                        class="btn btn-primary"
                        id="btnRegistrarCuenta">

                        <i class="bi bi-check-circle me-1"></i>

                        Registrar cuenta

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>