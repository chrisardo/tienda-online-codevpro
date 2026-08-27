<!--======================================================
CoDevPro Technology
PREFERENCIAS Y CONFIGURACIÓN
includes/perfil/preferencias.php
=======================================================-->
<div class="card border-0 shadow-sm">

    <div class="card-header bg-white">

        <h4 class="fw-bold mb-1">

            <i class="bi bi-sliders text-primary"></i>
            Preferencias y configuración

        </h4>

        <small class="text-muted">

            Personaliza tu experiencia dentro de la tienda.

        </small>

    </div>


    <div class="card-body">

        <div class="row g-4">

            <!--=====================================
            NOTIFICACIONES
            ======================================-->

            <div class="col-lg-6">

                <div class="card shadow-sm h-100">

                    <div class="card-header">

                        <h5 class="mb-0">

                            <i class="bi bi-bell-fill text-warning"></i>
                            Notificaciones

                        </h5>

                    </div>

                    <div class="card-body">


                        <div class="form-check form-switch mb-4">

                            <input
                                class="form-check-input"
                                type="checkbox"
                                id="correoPromociones">

                            <label class="form-check-label">

                                Recibir promociones por correo

                            </label>

                        </div>



                        <div class="form-check form-switch mb-4">

                            <input
                                class="form-check-input"
                                type="checkbox"
                                id="estadoPedido">

                            <label class="form-check-label">

                                Notificar cambios del pedido

                            </label>

                        </div>



                        <div class="form-check form-switch mb-4">

                            <input
                                class="form-check-input"
                                type="checkbox"
                                id="nuevosProductos">

                            <label class="form-check-label">

                                Avisarme sobre nuevos productos

                            </label>

                        </div>



                        <div class="form-check form-switch">

                            <input
                                class="form-check-input"
                                type="checkbox"
                                id="ofertasFlash">

                            <label class="form-check-label">

                                Recibir ofertas Flash

                            </label>

                        </div>

                    </div>

                </div>

            </div>



            <!--=====================================
            PREFERENCIAS
            ======================================-->

            <div class="col-lg-6">

                <div class="card shadow-sm h-100">

                    <div class="card-header">

                        <h5 class="mb-0">

                            <i class="bi bi-gear-fill text-primary"></i>
                            Preferencias

                        </h5>

                    </div>

                    <div class="card-body">


                        <!-- IDIOMA -->

                        <div class="mb-4">

                            <label class="form-label fw-semibold">

                                Idioma

                            </label>

                            <select
                                class="form-select"
                                id="idioma">

                                <option value="Español">

                                    Español

                                </option>

                                <option value="English">

                                    English

                                </option>

                                <option value="Português">

                                    Português

                                </option>

                            </select>

                        </div>



                        <!-- MONEDA -->

                        <div class="mb-4">

                            <label class="form-label fw-semibold">

                                Moneda

                            </label>

                            <select
                                class="form-select"
                                id="moneda">

                                <option value="Soles">

                                    Soles (S/)

                                </option>

                                <option value="Dólares">

                                    Dólares (USD)

                                </option>

                            </select>

                        </div>



                        <!-- MÉTODO DE PAGO FAVORITO -->

                        <div class="mb-4">

                            <label class="form-label fw-semibold">

                                Método de pago favorito

                            </label>

                            <select
                                class="form-select"
                                id="metodoPago">

                                <option value="">

                                    Cargando métodos de pago...

                                </option>

                            </select>

                        </div>


                    </div>

                </div>

            </div>


        </div>

    </div>



    <!--=====================================
    FOOTER
    ======================================-->

    <div class="card-footer bg-white">

        <div class="d-flex justify-content-end gap-2">


            <!--<button
                class="btn btn-outline-secondary"
                id="btnRestablecerPreferencias">

                <i class="bi bi-arrow-counterclockwise"></i>
                Restablecer

            </button>-->



            <button
                class="btn btn-primary"
                id="btnGuardarPreferencias">

                <i class="bi bi-check-circle-fill"></i>
                Guardar preferencias

            </button>


        </div>

    </div>

</div>