<?php
//=====================================================
// CoDevPro Technology
// modal/modal_ver_cliente.php
//=====================================================
?>

<div class="modal fade"
    id="modalVerCliente"
    tabindex="-1"
    aria-labelledby="modalVerClienteLabel"
    aria-hidden="true">

    <div class="modal-dialog modal-fullscreen-lg-down modal-lg modal-dialog-centered">

        <div class="modal-content border-0 shadow-lg">

            <!--=====================================
                HEADER
            =====================================-->

            <div class="modal-header bg-primary text-white border-0">

                <h5 class="modal-title fw-bold"
                    id="modalVerClienteLabel">

                    <i class="bi bi-person-vcard-fill me-2"></i>

                    Perfil Completo del Cliente

                </h5>

                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal"
                    aria-label="Cerrar">

                </button>

            </div>

            <!--=====================================
                BODY DINÁMICO
            =====================================-->

            <div
                class="modal-body p-0"
                id="contenidoVerCliente">

                <div class="text-center py-1">

                    <div class="spinner-border text-primary"
                        role="status">

                    </div>

                    <div class="mt-1">

                        Cargando información del cliente...

                    </div>

                </div>

            </div>

            <!--=====================================
                FOOTER / ACCIONES
            =====================================-->

            <div class="modal-footer bg-light border-top">

                <div class="d-flex flex-wrap gap-2 w-100 justify-content-end">

                    <!-- WHATSAPP -->

                    <button
                        type="button"
                        class="btn btn-success btn-contactar-whatsapp"
                        id="btnWhatsAppCliente"
                        disabled>

                        <i class="bi bi-whatsapp me-1"></i>

                        Enviar WhatsApp

                    </button>

                    <!-- EMAIL -->

                    <button
                        type="button"
                        class="btn btn-primary btn-contactar-email"
                        id="btnEmailCliente"
                        disabled>

                        <i class="bi bi-envelope-fill me-1"></i>

                        Enviar Email

                    </button>

                    <!-- CERRAR -->

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">

                        <i class="bi bi-x-lg me-1"></i>

                        Cerrar

                    </button>

                </div>

            </div>

        </div>

    </div>

</div>