<!--=====================================================
    MODAL EXPORTAR
======================================================-->

<div
    class="modal fade"
    id="modalExportarContabilidad"
    tabindex="-1"
    aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content border-0 shadow">

            <div class="modal-header">

                <h5 class="modal-title fw-bold">

                    <i class="bi bi-download me-2 text-primary"></i>

                    Exportar información contable

                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Cerrar">
                </button>

            </div>


            <div class="modal-body">

                <p class="text-muted">

                    Selecciona el formato en el que deseas exportar
                    el resumen contable.

                </p>


                <div class="row g-3">


                    <!-- EXCEL -->

                    <div class="col-12 col-md-6">

                        <button
                            type="button"
                            class="btn btn-outline-success w-100 py-3"
                            id="btnExportarExcel">

                            <i class="bi bi-file-earmark-excel fs-3 d-block mb-2"></i>

                            <strong class="d-block">
                                Excel
                            </strong>

                            <small class="text-muted">
                                Archivo XLSX
                            </small>

                        </button>

                    </div>


                    <!-- PDF -->

                    <div class="col-12 col-md-6">

                        <button
                            type="button"
                            class="btn btn-outline-danger w-100 py-3"
                            id="btnExportarPDF">

                            <i class="bi bi-file-earmark-pdf fs-3 d-block mb-2"></i>

                            <strong class="d-block">
                                PDF
                            </strong>

                            <small class="text-muted">
                                Documento PDF
                            </small>

                        </button>

                    </div>


                </div>

            </div>

        </div>

    </div>

</div>