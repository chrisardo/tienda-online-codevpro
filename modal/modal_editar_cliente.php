<?php
//=====================================================
// CoDevPro Technology
// modal/modal_editar_cliente.php
//=====================================================
?>

<div class="modal fade"
    id="modalEditarCliente"
    tabindex="-1"
    aria-labelledby="modalEditarClienteLabel"
    aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">

        <div class="modal-content border-0 shadow-lg">

            <!--=====================================
            HEADER
            ======================================-->

            <div class="modal-header bg-warning">

                <div>

                    <h4
                        class="modal-title fw-bold mb-0"
                        id="modalEditarClienteLabel">

                        <i class="bi bi-pencil-square me-2"></i>

                        Editar Cliente

                    </h4>

                    <small class="text-dark">

                        Actualiza la información del cliente.

                    </small>

                </div>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Cerrar">

                </button>

            </div>

            <!--=====================================
            FORMULARIO
            ======================================-->

            <form
                id="formEditarCliente"
                enctype="multipart/form-data"
                autocomplete="off">

                <!-- ID CLIENTE -->

                <input
                    type="hidden"
                    id="editarIdCliente"
                    name="idCliente">

                <!--=====================================
                BODY
                ======================================-->

                <div class="modal-body bg-light">

                    <div id="contenidoEditarCliente">

                        <div class="text-center py-5">

                            <div
                                class="spinner-border text-warning"
                                role="status">

                            </div>

                            <div class="mt-3 text-muted">

                                Cargando información del cliente...

                            </div>

                        </div>

                    </div>

                </div>

                <!--=====================================
                FOOTER
                ======================================-->

                <div class="modal-footer bg-white">

                    <button
                        type="button"
                        class="btn btn-outline-secondary"
                        data-bs-dismiss="modal">

                        <i class="bi bi-x-circle me-2"></i>

                        Cancelar

                    </button>

                    <button
                        type="submit"
                        class="btn btn-warning fw-semibold">

                        <i class="bi bi-save-fill me-2"></i>

                        Guardar Cambios

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>