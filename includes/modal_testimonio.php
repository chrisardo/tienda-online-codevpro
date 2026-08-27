<!-- ================================
     MODAL TESTIMONIO
================================ -->

<div class="modal fade" id="modalTestimonio" tabindex="-1">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">

                    <i class="bi bi-star-fill text-warning"></i>

                    Dejar opinión del producto

                </h5>

                <button class="btn-close" data-bs-dismiss="modal"></button>

            </div>

            <div class="modal-body">

                <input type="hidden" id="idTicketTestimonio">
                <input type="hidden" id="idProductoTestimonio">

                <!-- ESTRELLAS -->
                <div class="mb-3 text-center">

                    <label class="form-label fw-bold">

                        Calificación

                    </label>

                    <div id="estrellas" class="fs-3 text-warning">

                        <i class="bi bi-star" data-value="1"></i>
                        <i class="bi bi-star" data-value="2"></i>
                        <i class="bi bi-star" data-value="3"></i>
                        <i class="bi bi-star" data-value="4"></i>
                        <i class="bi bi-star" data-value="5"></i>

                    </div>

                    <input type="hidden" id="calificacion" value="0">

                </div>

                <!-- COMENTARIO -->
                <div class="mb-3">

                    <label class="form-label fw-bold">

                        Comentario

                    </label>

                    <textarea
                        id="comentarioTestimonio"
                        class="form-control"
                        rows="4"
                        placeholder="Escribe tu opinión sobre el producto..."></textarea>

                </div>

            </div>

            <div class="modal-footer">

                <button class="btn btn-secondary" data-bs-dismiss="modal">

                    Cancelar

                </button>

                <button class="btn btn-warning" id="btnEnviarTestimonio">

                    Enviar opinión

                </button>

            </div>

        </div>

    </div>

</div>