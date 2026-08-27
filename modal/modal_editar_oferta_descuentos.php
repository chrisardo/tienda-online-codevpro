<?php
//=====================================================
// CoDevPro Technology
// Archivo: modal/modal_editar_oferta_descuento.php
// Módulo: Ofertas y Descuentos
// Sistema: Inventa
//=====================================================
?>

<!--=====================================================
    MODAL EDITAR OFERTA / DESCUENTO
======================================================-->

<div
    class="modal fade"
    id="modalEditarOfertaDescuento"
    tabindex="-1"
    aria-labelledby="tituloModalEditarOfertaDescuento"
    aria-hidden="true">

    <div
        class="modal-dialog modal-lg modal-dialog-centered modal-lg">

        <div class="modal-content border-0 shadow-lg rounded-4">

            <!--=================================================
                HEADER
            ==================================================-->

            <div class="modal-header border-0 px-4 pt-4 pb-3">

                <div>

                    <h5
                        class="modal-title fw-bold"
                        id="tituloModalEditarOfertaDescuento">

                        <i
                            class="bi bi-pencil-square text-warning me-2">
                        </i>

                        Editar oferta y descuento

                    </h5>

                    <p
                        class="text-muted small mb-0 mt-1">

                        Modifica la configuración comercial del producto.

                    </p>

                </div>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Cerrar">
                </button>

            </div>

            <!--=================================================
                BODY
            ==================================================-->

            <div class="modal-body px-4 pb-4">

                <!--=================================================
                    FORMULARIO
                ==================================================-->

                <form
                    id="formEditarOfertaDescuento"
                    novalidate>

                    <!--=================================================
                        ID PRODUCTO
                    ==================================================-->

                    <input
                        type="hidden"
                        id="editarOfertaIdProducto"
                        name="idProducto"
                        value="">

                    <!--=================================================
                        INFORMACIÓN DEL PRODUCTO
                    ==================================================-->

                    <div
                        class="card border-0 bg-light rounded-4 mb-4">

                        <div class="card-body p-3">

                            <div
                                class="d-flex align-items-center">

                                <!-- ICONO -->

                                <div
                                    class="bg-white rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                                    style="width:64px;height:64px;">

                                    <i
                                        class="bi bi-box-seam fs-3 text-primary">
                                    </i>

                                </div>

                                <!-- INFORMACIÓN -->

                                <div
                                    class="ms-3 flex-grow-1">

                                    <div
                                        class="fw-bold fs-6"
                                        id="editarOfertaNombreProducto">

                                        Cargando producto...

                                    </div>

                                    <div
                                        class="small text-muted mt-1">

                                        Código:

                                        <span
                                            id="editarOfertaCodigoProducto">

                                            —

                                        </span>

                                    </div>

                                </div>

                                <!-- STOCK -->

                                <div
                                    class="text-end">

                                    <div
                                        class="small text-muted">

                                        Stock

                                    </div>

                                    <span
                                        class="badge bg-secondary-subtle text-secondary"
                                        id="editarOfertaStock">

                                        0

                                    </span>

                                </div>

                            </div>

                        </div>

                    </div>

                    <!--=================================================
                        PRECIOS
                    ==================================================-->

                    <div class="row g-3 mb-4">

                        <!-- PRECIO ACTUAL -->

                        <div class="col-md-6">

                            <div
                                class="form-floating">

                                <input
                                    type="number"
                                    class="form-control"
                                    id="editarOfertaPrecio"
                                    name="precio"
                                    placeholder="Precio"
                                    step="0.01"
                                    min="0"
                                    readonly>

                                <label
                                    for="editarOfertaPrecio">

                                    <i
                                        class="bi bi-currency-dollar me-1">
                                    </i>

                                    Precio normal

                                </label>

                            </div>

                            <div
                                class="form-text">

                                Precio base del producto.

                            </div>

                        </div>

                        <!-- PRECIO ANTERIOR -->

                        <div class="col-md-6">

                            <div
                                class="form-floating">

                                <input
                                    type="number"
                                    class="form-control"
                                    id="editarOfertaPrecioAnterior"
                                    name="precio_anterior"
                                    placeholder="Precio anterior"
                                    step="0.01"
                                    min="0">

                                <label
                                    for="editarOfertaPrecioAnterior">

                                    <i
                                        class="bi bi-tag me-1">
                                    </i>

                                    Precio anterior

                                </label>

                            </div>

                            <div
                                class="form-text">

                                Precio mostrado como referencia.

                            </div>

                        </div>

                    </div>

                    <!--=================================================
                        ACTIVAR OFERTA
                    ==================================================-->

                    <div
                        class="card border rounded-4 mb-4">

                        <div
                            class="card-body p-3">

                            <div
                                class="d-flex align-items-center justify-content-between">

                                <div>

                                    <div
                                        class="fw-semibold">

                                        <i
                                            class="bi bi-megaphone text-success me-2">
                                        </i>

                                        Oferta activa

                                    </div>

                                    <div
                                        class="small text-muted mt-1">

                                        Activa o desactiva la oferta del producto.

                                    </div>

                                </div>

                                <div
                                    class="form-check form-switch mb-0">

                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        role="switch"
                                        id="editarOfertaActiva"
                                        name="oferta"
                                        value="1"
                                        style="width:3rem;height:1.5rem;">

                                </div>

                            </div>

                        </div>

                    </div>

                    <!--=================================================
                        DESCUENTO
                    ==================================================-->

                    <div class="card border rounded-4 mb-4">

                        <div
                            class="card-body p-3">

                            <div
                                class="row g-3 align-items-center">

                                <!-- DESCUENTO -->

                                <div class="col-md-6">

                                    <label
                                        for="editarOfertaDescuento"
                                        class="form-label fw-semibold">

                                        <i
                                            class="bi bi-percent text-danger me-1">
                                        </i>

                                        Descuento

                                    </label>

                                    <div
                                        class="input-group">

                                        <input
                                            type="number"
                                            class="form-control"
                                            id="editarOfertaDescuento"
                                            name="descuento"
                                            placeholder="0"
                                            min="0"
                                            max="100"
                                            step="0.01"
                                            value="0">

                                        <span
                                            class="input-group-text">

                                            %

                                        </span>

                                    </div>

                                    <div
                                        class="form-text">

                                        Ingresa un valor entre 0 y 100%.

                                    </div>

                                </div>

                                <!-- PRECIO FINAL -->

                                <div class="col-md-6">

                                    <div
                                        class="bg-success-subtle rounded-3 p-3">

                                        <div
                                            class="small text-success fw-semibold mb-1">

                                            Precio con descuento

                                        </div>

                                        <div
                                            class="fs-4 fw-bold text-success"
                                            id="editarOfertaPrecioFinal">

                                            S/ 0.00

                                        </div>

                                        <div
                                            class="small text-success"
                                            id="editarOfertaAhorro">

                                            Ahorro: S/ 0.00

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                    <!--=================================================
                        INFORMACIÓN ADICIONAL
                    ==================================================-->

                    <div
                        class="row g-3 mb-2">

                        <!-- CATEGORÍA -->

                        <div class="col-md-4">

                            <label
                                class="form-label small text-muted mb-1">

                                Categoría

                            </label>

                            <div
                                class="fw-semibold"
                                id="editarOfertaCategoria">

                                —

                            </div>

                        </div>

                        <!-- MARCA -->

                        <div class="col-md-4">

                            <label
                                class="form-label small text-muted mb-1">

                                Marca

                            </label>

                            <div
                                class="fw-semibold"
                                id="editarOfertaMarca">

                                —

                            </div>

                        </div>

                        <!-- SUCURSAL -->

                        <div class="col-md-4">

                            <label
                                class="form-label small text-muted mb-1">

                                Sucursal

                            </label>

                            <div
                                class="fw-semibold"
                                id="editarOfertaSucursal">

                                —

                            </div>

                        </div>

                    </div>

                    <!--=================================================
                        ALERTA
                    ==================================================-->

                    <div
                        id="alertaEditarOfertaDescuento"
                        class="alert d-none rounded-3 mt-4 mb-0"
                        role="alert">

                        <div
                            class="d-flex align-items-start">

                            <i
                                id="iconoAlertaEditarOferta"
                                class="bi me-2 fs-5">
                            </i>

                            <div
                                id="textoAlertaEditarOferta">

                            </div>

                        </div>

                    </div>

                </form>

            </div>

            <!--=================================================
                FOOTER
            ==================================================-->

            <div
                class="modal-footer border-0 px-4 pb-4 pt-0">

                <button
                    type="button"
                    class="btn btn-light border rounded-3 px-4"
                    data-bs-dismiss="modal">

                    <i
                        class="bi bi-x-lg me-1">
                    </i>

                    Cancelar

                </button>

                <button
                    type="button"
                    class="btn btn-warning rounded-3 px-4 fw-semibold"
                    id="btnGuardarCambiosOferta">

                    <span
                        id="contenidoBtnGuardarOferta">

                        <i
                            class="bi bi-check-lg me-1">
                        </i>

                        Guardar cambios

                    </span>

                    <span
                        id="spinnerBtnGuardarOferta"
                        class="d-none">

                        <span
                            class="spinner-border spinner-border-sm me-2"
                            role="status"
                            aria-hidden="true">
                        </span>

                        Guardando...

                    </span>

                </button>

            </div>

        </div>

    </div>

</div>