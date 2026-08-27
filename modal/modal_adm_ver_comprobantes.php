<!-- ==========================================================
     CoDevPro Technology
     modal_adm_ver_comprobantes.php
========================================================== -->

<div class="modal fade"
    id="modalVerComprobante"
    tabindex="-1">


    <div class="modal-dialog modal-lg modal-dialog-centered">


        <div class="modal-content border-0 shadow-lg rounded-4">


            <!-- HEADER -->

            <div class="modal-header bg-primary text-white">


                <div>

                    <h5 class="modal-title fw-bold">

                        <i class="bi bi-receipt-cutoff me-2"></i>

                        Detalle del Comprobante

                    </h5>


                    <small>
                        Vista previa tipo SUNAT
                    </small>


                </div>


                <button
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal">
                </button>


            </div>




            <div class="modal-body p-4">


                <!-- ACCIONES -->

                <div class="d-flex justify-content-end gap-2 mb-4">


                    <button
                        class="btn btn-danger btn-sm"
                        id="btnAnularComprobante">

                        <i class="bi bi-x-circle me-1"></i>

                        Anular

                    </button>


                    <button
                        class="btn btn-dark btn-sm"
                        id="btnImprimirComprobante">

                        <i class="bi bi-printer me-1"></i>

                        Imprimir PDF

                    </button>



                </div>




                <!-- DOCUMENTO -->

                <div class="card border rounded-4">


                    <div class="card-body p-4">


                        <div class="row">


                            <!-- EMPRESA -->

                            <div class="col-md-6">


                                <h4 class="fw-bold mb-1">

                                    CoDevPro Technology

                                </h4>


                                <p class="mb-1">

                                    RUC: 20XXXXXXXXX

                                </p>


                                <p class="mb-1">

                                    Monitor Huáscar #811

                                </p>


                                <p class="mb-0">

                                    Iquitos - Perú

                                </p>



                            </div>




                            <!-- COMPROBANTE -->


                            <div class="col-md-6 text-md-end">


                                <h3
                                    class="fw-bold text-primary"
                                    id="modalSerieNumero">

                                </h3>



                                <div id="modalEstado">


                                </div>


                                <p class="mt-2 mb-0">

                                    Fecha:

                                    <b id="modalFecha"></b>


                                </p>



                            </div>


                        </div>



                        <hr>



                        <!-- CLIENTE -->


                        <div class="row g-3">


                            <div class="col-md-4">


                                <label class="text-muted small">

                                    Cliente

                                </label>


                                <h6 id="modalCliente">

                                </h6>


                            </div>



                            <div class="col-md-4">


                                <label class="text-muted small">

                                    Documento

                                </label>


                                <h6 id="modalDocumento">

                                </h6>


                            </div>




                            <div class="col-md-4">


                                <label class="text-muted small">

                                    Método Pago

                                </label>


                                <h6 id="modalMetodoPago">

                                </h6>


                            </div>


                        </div>



                        <div class="row mt-3">


                            <div class="col-md-6">


                                <label class="text-muted small">

                                    Empleado

                                </label>


                                <h6 id="modalEmpleado">

                                </h6>


                            </div>


                        </div>




                        <hr>




                        <!-- DETALLE PRODUCTOS -->


                        <div class="table-responsive">


                            <table class="table align-middle">


                                <thead class="table-light">


                                    <tr>


                                        <th>
                                            Producto
                                        </th>


                                        <th class="text-center">
                                            Cantidad
                                        </th>


                                        <th class="text-end">
                                            Precio
                                        </th>


                                        <th class="text-end">
                                            Subtotal
                                        </th>


                                    </tr>


                                </thead>



                                <tbody id="tbodyDetalleComprobante">


                                </tbody>



                            </table>


                        </div>





                        <hr>




                        <!-- TOTALES -->


                        <div class="row justify-content-end">


                            <div class="col-md-4">


                                <div class="d-flex justify-content-between">


                                    <span>
                                        Subtotal
                                    </span>


                                    <strong id="modalSubtotal">
                                        S/0.00
                                    </strong>


                                </div>



                                <div class="d-flex justify-content-between">


                                    <span>
                                        IGV
                                    </span>


                                    <strong id="modalIGV">
                                        S/0.00
                                    </strong>


                                </div>



                                <hr>



                                <div class="d-flex justify-content-between fs-4">


                                    <span>
                                        TOTAL
                                    </span>


                                    <strong
                                        class="text-success"
                                        id="modalTotal">

                                        S/0.00

                                    </strong>


                                </div>



                            </div>


                        </div>





                    </div>

                </div>



            </div>



        </div>


    </div>