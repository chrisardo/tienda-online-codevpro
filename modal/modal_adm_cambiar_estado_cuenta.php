<?php
//=====================================================
// CoDevPro Technology
// Archivo: modal/modal_adm_cambiar_estado_cuenta.php
// Módulo: Cuentas Bancarias
// Sistema: Inventa
//=====================================================
?>

<!--=====================================================
    MODAL CAMBIAR ESTADO CUENTA
======================================================-->

<div
    class="modal fade"
    id="modalCambiarEstadoCuenta"
    tabindex="-1"
    aria-labelledby="tituloModalCambiarEstadoCuenta"
    aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content border-0 shadow">

            <!--=================================================
                CABECERA
            ==================================================-->

            <div class="modal-header">

                <h5
                    class="modal-title"
                    id="tituloModalCambiarEstadoCuenta">

                    <i
                        class="bi bi-exclamation-circle me-2"
                        id="iconoModalCambiarEstadoCuenta">
                    </i>

                    <span id="textoTituloCambiarEstadoCuenta">
                        Cambiar estado de cuenta
                    </span>

                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Cerrar">
                </button>

            </div>


            <!--=================================================
                CUERPO
            ==================================================-->

            <div class="modal-body">

                <!--=============================================
                    ALERTA
                ==============================================-->

                <div
                    id="alertaCambiarEstadoCuenta"
                    class="alert alert-warning d-none"
                    role="alert">

                    <i
                        class="bi bi-exclamation-triangle-fill me-2">
                    </i>

                    <span id="mensajeCambiarEstadoCuenta">
                        ¿Deseas cambiar el estado de esta cuenta?
                    </span>

                </div>


                <!--=============================================
                    INFORMACIÓN DE LA CUENTA
                ==============================================-->

                <div class="text-center py-2">

                    <div
                        class="mb-3">

                        <div
                            class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light"
                            style="width: 70px; height: 70px;">

                            <i
                                class="bi bi-bank fs-2 text-primary"
                                id="iconoCuentaCambiarEstado">
                            </i>

                        </div>

                    </div>


                    <h5
                        class="mb-1"
                        id="nombreCuentaCambiarEstado">

                        Cuenta bancaria

                    </h5>


                    <p
                        class="text-muted mb-0">

                        ID:
                        <span id="idCuentaCambiarEstado">
                            -
                        </span>

                    </p>

                </div>


                <!--=============================================
                    MENSAJE DE PROCESO / RESULTADO
                ==============================================-->

                <div
                    id="mensajeProcesoCambiarEstadoCuenta"
                    class="alert mt-3 d-none"
                    role="alert">
                </div>

            </div>


            <!--=================================================
                FOOTER
            ==================================================-->

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    id="btnCancelarCambiarEstadoCuenta"
                    data-bs-dismiss="modal">

                    <i class="bi bi-x-circle me-1"></i>

                    Cancelar

                </button>


                <button
                    type="button"
                    class="btn btn-warning"
                    id="btnConfirmarCambiarEstadoCuenta">

                    <i
                        class="bi bi-check-circle me-1"
                        id="iconoConfirmarCambiarEstadoCuenta">
                    </i>

                    <span id="textoConfirmarCambiarEstadoCuenta">
                        Confirmar

                    </span>

                </button>

            </div>

        </div>

    </div>

</div>