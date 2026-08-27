<!--=====================================
=            MODAL TESTIMONIO
======================================-->

<div class="modal fade"
    id="modalTestimonio"
    tabindex="-1">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content border-0 shadow">

            <div class="modal-header">

                <h5 class="modal-title">

                    <i class="bi bi-chat-left-text-fill text-primary"></i>

                    Detalle del Testimonio

                </h5>

                <button type="button"
                    class="btn-close"
                    data-bs-dismiss="modal">

                </button>

            </div>

            <div class="modal-body">

                <input type="hidden"
                    id="idTestimonio">
                <div class="row mb-3">

                    <div class="col-md-4">

                        <label class="fw-bold">
                            Estado
                        </label>

                        <div id="detalleEstado"></div>

                    </div>

                    <div class="col-md-4">

                        <label class="fw-bold">
                            Fecha
                        </label>

                        <div id="detalleFecha"></div>

                    </div>

                    <div class="col-md-4">

                        <label class="fw-bold">
                            Ticket
                        </label>

                        <div id="detalleTicket"></div>

                    </div>

                </div>
                <div class="row g-4">

                    <div class="col-md-6">

                        <label class="fw-bold">

                            Cliente

                        </label>

                        <div id="detalleCliente">

                        </div>

                    </div>

                    <div class="col-md-6">

                        <label class="fw-bold">

                            Producto

                        </label>

                        <div id="detalleProducto">

                        </div>

                    </div>

                </div>

                <hr>

                <div class="mb-3">

                    <label class="fw-bold">

                        Calificación

                    </label>

                    <div id="detalleCalificacion">

                    </div>

                </div>

                <div class="mb-3">

                    <label class="fw-bold">

                        Comentario

                    </label>

                    <div class="border rounded p-3 bg-light"
                        id="detalleComentario">

                    </div>

                </div>

                <div class="mb-3">

                    <label class="fw-bold">

                        Respuesta del Administrador

                    </label>

                    <textarea
                        id="respuestaTestimonio"
                        class="form-control"
                        rows="4"
                        placeholder="Escriba una respuesta profesional..."></textarea>
                    <div class="mt-2 small text-muted"
                        id="detalleFechaRespuesta">
                    </div>
                </div>

            </div>

            <div class="modal-footer">

                <button class="btn btn-danger"
                    id="btnRechazarTestimonio">

                    <i class="bi bi-x-circle-fill"></i>

                    Rechazar

                </button>

                <button class="btn btn-success"
                    id="btnAprobarTestimonio">

                    <i class="bi bi-check-circle-fill"></i>

                    Aprobar

                </button>

                <button class="btn btn-primary"
                    id="btnGuardarRespuesta">

                    <i class="bi bi-reply-fill"></i>

                    Guardar Respuesta

                </button>

            </div>

        </div>

    </div>

</div>