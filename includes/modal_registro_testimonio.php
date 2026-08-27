<?php
//======================================================
// CoDevPro Technology
// Toda esta parte es de includes/modal_registro_testimonio.php
//======================================================
?>

<div class="modal fade"
    id="modalTestimonio"
    tabindex="-1"
    aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content border-0 shadow">

            <!--=====================================
            CABECERA
            ======================================-->

            <div class="modal-header bg-primary text-white">

                <h5 class="modal-title">

                    <i class="bi bi-star-fill me-2"></i>

                    Calificar producto

                </h5>

                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal">

                </button>

            </div>

            <!--=====================================
            CUERPO
            ======================================-->

            <div class="modal-body">

                <!-- Campos ocultos -->

                <input
                    type="hidden"
                    id="idTicketTestimonio">

                <input
                    type="hidden"
                    id="idProductoTestimonio">

                <input
                    type="hidden"
                    id="calificacionTestimonio"
                    value="0">

                <!--=====================================
MENSAJES
======================================-->

                <div
                    id="alertaTestimonio"
                    class="alert alert-info border d-flex align-items-center mb-4">

                    <i
                        id="iconoAlertaTestimonio"
                        class="bi bi-info-circle-fill me-2"></i>

                    <span id="textoAlertaTestimonio">

                        Tu opinión ayudará a otros clientes a conocer mejor este producto.

                    </span>

                </div>
                <!--=====================================
PRODUCTO + CALIFICACIÓN
======================================-->

                <div class="card mb-2">

                    <div class="card-body">

                        <div class="row align-items-center">

                            <!-- Imagen -->

                            <div class="col-md-3 text-center">

                                <img
                                    id="imagenProductoModal"
                                    src=""
                                    class="img-fluid rounded border"
                                    style="max-height:120px;object-fit:cover;">

                            </div>

                            <!-- Información -->

                            <div class="col-md-5">

                                <h5
                                    id="nombreProductoModal"
                                    class="fw-bold mb-3">
                                </h5>

                                <p class="mb-2">
                                    <strong>Precio:</strong>
                                    <span id="precioProductoModal"></span>
                                </p>

                                <p class="mb-0">
                                    <strong>Cantidad comprada:</strong>
                                    <span id="cantidadProductoModal"></span>
                                </p>

                            </div>

                            <!-- Calificación -->

                            <div class="col-md-4 text-center border-start">

                                <label class="form-label fw-bold d-block mb-3">

                                    Tu calificación

                                </label>

                                <div
                                    id="contenedorEstrellas"
                                    class="fs-2 mb-2">

                                    <i
                                        class="bi bi-star estrellaCalificacion"
                                        data-valor="1"
                                        style="cursor:pointer;"></i>

                                    <i
                                        class="bi bi-star estrellaCalificacion"
                                        data-valor="2"
                                        style="cursor:pointer;"></i>

                                    <i
                                        class="bi bi-star estrellaCalificacion"
                                        data-valor="3"
                                        style="cursor:pointer;"></i>

                                    <i
                                        class="bi bi-star estrellaCalificacion"
                                        data-valor="4"
                                        style="cursor:pointer;"></i>

                                    <i
                                        class="bi bi-star estrellaCalificacion"
                                        data-valor="5"
                                        style="cursor:pointer;"></i>

                                </div>

                                <small
                                    id="textoCalificacion"
                                    class="text-muted">

                                    Selecciona una calificación

                                </small>

                            </div>

                        </div>

                    </div>

                </div>

                <!--=====================================
                COMENTARIO
                ======================================-->

                <div class="mb-1">

                    <label
                        class="form-label fw-bold">

                        Cuéntanos tu experiencia

                    </label>

                    <textarea
                        id="comentarioTestimonio"
                        class="form-control"
                        rows="4"
                        maxlength="500"
                        placeholder="Escribe aquí tu opinión sobre el producto..."></textarea>

                    <div
                        class="text-end mt-2">

                        <small
                            id="contadorComentario"
                            class="text-muted">

                            0 / 500 caracteres

                        </small>

                    </div>

                </div>

            </div>

            <!--=====================================
            FOOTER
            ======================================-->

            <div class="modal-footer">

                <button
                    class="btn btn-secondary"
                    data-bs-dismiss="modal">

                    <i class="bi bi-x-circle"></i>

                    Cancelar

                </button>

                <button
                    id="guardarTestimonio"
                    class="btn btn-primary">

                    <i class="bi bi-send-fill"></i>

                    Guardar opinión

                </button>

            </div>

        </div>

    </div>

</div>