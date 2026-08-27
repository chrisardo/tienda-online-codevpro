<!-- =====================================================
     MODAL EDITAR MÉTODO DE PAGO
====================================================== -->

<div
    class="modal fade"
    id="modalEditarMetodo"
    tabindex="-1"
    aria-labelledby="modalEditarMetodoLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content border-0 shadow-lg">

            <!-- =================================================
             HEADER
        ================================================== -->

            <div class="modal-header bg-primary text-white">

                <h5
                    class="modal-title fw-bold"
                    id="modalEditarMetodoLabel">

                    <i class="bi bi-pencil-square me-2"></i>

                    Editar Método de Pago

                </h5>

                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal"
                    aria-label="Cerrar">
                </button>

            </div>


            <!-- =================================================
             FORMULARIO
        ================================================== -->

            <form
                id="formEditarMetodo"
                autocomplete="off">

                <div class="modal-body">

                    <!-- ID OCULTO -->

                    <input
                        type="hidden"
                        id="editarIdMetodo"
                        name="id_metodo_pago">


                    <!-- =================================================
                     NOMBRE
                ================================================== -->

                    <div class="mb-3">

                        <label
                            for="editarNombreMetodo"
                            class="form-label fw-semibold">

                            <i class="bi bi-credit-card me-1 text-primary"></i>

                            Nombre del método de pago

                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="editarNombreMetodo"
                            name="nombre"
                            maxlength="100"
                            placeholder="Ejemplo: Yape"
                            required>

                        <div class="form-text">

                            Ingresa el nombre que deseas mostrar
                            para este método de pago.

                        </div>

                    </div>
                    <!-- =================================================
                     CARGANDO
                ================================================== -->

                    <div
                        id="cargandoEditarMetodo"
                        class="text-center py-3 d-none">

                        <div
                            class="spinner-border text-primary"
                            role="status">
                        </div>

                        <div class="small text-muted mt-2">

                            Cargando información...

                        </div>

                    </div>


                    <!-- =================================================
                     MENSAJE
                ================================================== -->

                    <div
                        id="mensajeEditarMetodo"
                        class="alert d-none mb-0">

                    </div>

                </div>


                <!-- =================================================
                 FOOTER
            ================================================== -->

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">

                        <i class="bi bi-x-circle me-1"></i>

                        Cancelar

                    </button>


                    <button
                        type="submit"
                        class="btn btn-primary"
                        id="btnActualizarMetodo">

                        <i class="bi bi-check-circle me-1"></i>

                        Guardar Cambios

                    </button>

                </div>

            </form>

        </div>

    </div>
</div>
<!--=====================================================
=            MODAL CONFIRMAR ELIMINACIÓN
======================================================-->

<div
    class="modal fade"
    id="modalEliminarMetodo"
    tabindex="-1"
    aria-labelledby="modalEliminarMetodoLabel"
    aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content border-0 shadow-lg">

            <!-- HEADER -->

            <div class="modal-header border-0">

                <h5
                    class="modal-title fw-bold"
                    id="modalEliminarMetodoLabel">

                    <i class="bi bi-trash-fill text-danger me-2"></i>

                    Eliminar método de pago

                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Cerrar">
                </button>

            </div>


            <!-- BODY -->

            <div class="modal-body text-center px-4 pb-4">

                <div class="mb-3">

                    <div
                        class="rounded-circle bg-danger bg-opacity-10
                               d-inline-flex align-items-center
                               justify-content-center"
                        style="width: 75px; height: 75px;">

                        <i
                            class="bi bi-trash3-fill text-danger"
                            style="font-size: 2rem;">
                        </i>

                    </div>

                </div>


                <h5 class="fw-bold mb-2">

                    ¿Eliminar este método de pago?

                </h5>


                <p class="text-muted mb-3">

                    Estás a punto de eliminar:

                </p>


                <div
                    class="alert alert-light border
                           d-flex align-items-center
                           justify-content-center mb-3">

                    <i class="bi bi-credit-card-2-front-fill
                              text-primary me-2">
                    </i>

                    <strong id="nombreMetodoEliminar">

                        --

                    </strong>

                </div>


                <div
                    class="alert alert-warning text-start small mb-0">

                    <i class="bi bi-info-circle-fill me-2"></i>

                    El método será desactivado y dejará de aparecer
                    en la lista de métodos disponibles. Su historial
                    de ventas se conservará.

                </div>


                <!-- ID OCULTO -->

                <input
                    type="hidden"
                    id="idMetodoEliminar"
                    value="">

            </div>


            <!-- FOOTER -->

            <div class="modal-footer border-0 px-4 pb-4">

                <button
                    type="button"
                    class="btn btn-light border"
                    data-bs-dismiss="modal">

                    <i class="bi bi-x-circle me-1"></i>

                    Cancelar

                </button>


                <button
                    type="button"
                    class="btn btn-danger"
                    id="btnConfirmarEliminarMetodo">

                    <i class="bi bi-trash-fill me-1"></i>

                    Sí, eliminar

                </button>

            </div>

        </div>

    </div>

</div>