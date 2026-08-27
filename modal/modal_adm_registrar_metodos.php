<?php
//==========================================================
// CoDevPro Technology
// modal/modal_adm_registrar_metodos.php
//==========================================================
?>

<!--==========================================================
=            MODAL NUEVO MÉTODO DE PAGO
===========================================================-->

<div class="modal fade"
    id="modalNuevoMetodo"
    tabindex="-1"
    aria-labelledby="modalNuevoMetodoLabel"
    aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered modal-lg">

        <div class="modal-content border-0 shadow-lg">

            <!--=====================================
            =            HEADER
            ======================================-->

            <div class="modal-header bg-primary text-white">

                <div>

                    <h4 class="modal-title fw-bold"
                        id="modalNuevoMetodoLabel">

                        <i class="bi bi-credit-card-2-front-fill me-2"></i>

                        Registrar Método de Pago

                    </h4>

                    <small class="opacity-75">

                        Agregue un nuevo método de pago disponible para las ventas.

                    </small>

                </div>

                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal"
                    aria-label="Cerrar">
                </button>

            </div>

            <!--=====================================
            =            FORMULARIO
            ======================================-->

            <form
                id="formRegistrarMetodo"
                autocomplete="off">

                <div class="modal-body">

                    <div class="row g-4">

                        <!--=====================================
                        =            NOMBRE
                        ======================================-->

                        <div class="col-md-12">

                            <label
                                class="form-label fw-semibold">

                                Nombre del método
                                <span class="text-danger">*</span>

                            </label>

                            <div class="input-group">

                                <span class="input-group-text">

                                    <i class="bi bi-credit-card"></i>

                                </span>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="nombreMetodo"
                                    name="nombre"
                                    maxlength="80"
                                    placeholder="Ejemplo: Efectivo, Yape, Plin, Visa..."
                                    required>

                            </div>

                            <small class="text-muted">

                                No se permiten nombres duplicados.

                            </small>

                        </div>

                        <!--=====================================
                        =            VISTA PREVIA
                        ======================================-->

                        <div class="col-md-12">

                            <div class="card bg-light border-0">

                                <div class="card-body">

                                    <div class="d-flex align-items-center">

                                        <div
                                            class="rounded-circle bg-primary text-white d-flex justify-content-center align-items-center me-3"
                                            style="width:60px;height:60px;">

                                            <i class="bi bi-credit-card-2-front-fill fs-3"></i>

                                        </div>

                                        <div>

                                            <small class="text-muted">

                                                Vista previa

                                            </small>

                                            <h5
                                                class="fw-bold mb-0"
                                                id="previewMetodo">

                                                Nuevo Método

                                            </h5>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                        <!--=====================================
                        =            INFORMACIÓN
                        ======================================-->

                        <div class="col-md-12">

                            <div class="alert alert-info border-0 mb-0">

                                <i class="bi bi-info-circle-fill me-2"></i>

                                Este método estará disponible inmediatamente
                                para registrar ventas dentro del sistema.

                            </div>

                        </div>

                    </div>

                </div>

                <!--=====================================
                =            FOOTER
                ======================================-->

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-light"
                        data-bs-dismiss="modal">

                        <i class="bi bi-x-circle me-2"></i>

                        Cancelar

                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary"
                        id="btnGuardarMetodo">

                        <span
                            class="spinner-border spinner-border-sm me-2 d-none"
                            id="spinnerMetodo">
                        </span>

                        <i
                            class="bi bi-check-circle-fill me-2"
                            id="iconGuardarMetodo"></i>

                        Guardar Método

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>