<!--=====================================================
    MODAL VER NOTIFICACIÓN
======================================================-->
<div
    class="modal fade"
    id="modalVerNotificacion"
    tabindex="-1"
    aria-labelledby="modalVerNotificacionLabel"
    aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content border-0 shadow">


            <!-- HEADER -->

            <div class="modal-header">

                <h5
                    class="modal-title fw-bold"
                    id="modalVerNotificacionLabel">

                    <i class="bi bi-bell-fill text-primary me-2"></i>

                    Detalle de notificación

                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Cerrar">
                </button>

            </div>



            <!-- BODY -->

            <div class="modal-body">


                <!-- CLIENTE -->

                <div class="mb-3">

                    <small class="text-muted d-block">

                        Cliente

                    </small>

                    <strong id="detalleClienteNotificacion">

                        -

                    </strong>

                </div>



                <!-- TITULO -->

                <div class="mb-3">

                    <small class="text-muted d-block">

                        Título

                    </small>

                    <h5
                        class="fw-bold mb-0"
                        id="detalleTituloNotificacion">

                        -

                    </h5>

                </div>



                <!-- MENSAJE -->

                <div class="mb-3">

                    <small class="text-muted d-block mb-1">

                        Mensaje

                    </small>

                    <div
                        class="bg-light rounded p-3"
                        id="detalleMensajeNotificacion">

                        -

                    </div>

                </div>



                <!-- INFORMACION -->

                <div class="row g-3">


                    <div class="col-6">

                        <small class="text-muted d-block">

                            Tipo

                        </small>

                        <span
                            class="badge bg-primary"
                            id="detalleTipoNotificacion">

                            -

                        </span>

                    </div>


                    <div class="col-6">

                        <small class="text-muted d-block">

                            Estado

                        </small>

                        <span
                            class="badge bg-secondary"
                            id="detalleEstadoNotificacion">

                            -

                        </span>

                    </div>


                    <div class="col-12">

                        <small class="text-muted d-block">

                            Fecha

                        </small>

                        <span id="detalleFechaNotificacion">

                            -

                        </span>

                    </div>

                </div>

            </div>



            <!-- FOOTER -->

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal">

                    Cerrar

                </button>

            </div>

        </div>

    </div>

</div>