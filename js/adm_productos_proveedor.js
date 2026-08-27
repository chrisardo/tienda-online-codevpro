//=====================================================
// CoDevPro Technology
// Archivo: js/adm_productos_proveedor.js
// Módulo: Productos del Proveedor
// Sistema: Inventa
//=====================================================

"use strict";

//=====================================================
// VARIABLES GLOBALES
//=====================================================

let paginaActualProductosProveedor = 1;

const registrosPorPaginaProductosProveedor = 10;

let temporizadorBusquedaProductoProveedor = null;

let solicitudProductosProveedorActual = null;

let solicitudKPIProductosProveedorActual = null;

let modalVerProductoProveedor = null;

let productoProveedorSeleccionado = null;

let flatpickrFechaProductoProveedor = null;

//=====================================================
// CONFIGURACIÓN AJAX
//=====================================================

const URL_AJAX_PRODUCTOS_PROVEEDOR = "ajax/obtener_productos_proveedor.php";

const URL_AJAX_KPI_PRODUCTOS_PROVEEDOR =
  "ajax/obtener_kpi_productos_proveedor.php";

const URL_AJAX_PROVEEDORES = "ajax/obtener_proveedores_filtro.php";
const URL_AJAX_PROVEEDOR_PRODUCTO = "ajax/obtener_proveedor_producto.php";

const URL_AJAX_ACTUALIZAR_PROVEEDOR_PRODUCTO =
  "ajax/actualizar_proveedor_producto.php";
const URL_AJAX_CATEGORIAS = "ajax/obtener_categorias_filtro.php";

const URL_AJAX_MARCAS = "ajax/obtener_marcas_filtro.php";

const URL_AJAX_DETALLE_PRODUCTO = "ajax/obtener_detalle_producto_proveedor.php";

//=====================================================
// INICIALIZACIÓN
//=====================================================

document.addEventListener("DOMContentLoaded", function () {
  inicializarProductosProveedor();
});

//=====================================================
// FUNCIÓN PRINCIPAL
//=====================================================

function inicializarProductosProveedor() {
  inicializarModalProductoProveedor();

  inicializarFechaProductoProveedor();

  inicializarEventosFiltros();
  inicializarEventosEditarProveedor();
  cargarProveedoresFiltro();

  cargarCategoriasFiltro();

  cargarMarcasFiltro();

  cargarKPIProductosProveedor();

  cargarProductosProveedor();
}

//=====================================================
// INICIALIZAR MODAL
//=====================================================

function inicializarModalProductoProveedor() {
  const elementoModal = document.getElementById("modalVerProductoProveedor");

  if (!elementoModal) {
    console.warn("No se encontró el modal #modalVerProductoProveedor");

    return;
  }

  modalVerProductoProveedor = new bootstrap.Modal(elementoModal);
}

//=====================================================
// INICIALIZAR FLATPICKR
//=====================================================

function inicializarFechaProductoProveedor() {
  const inputFecha = document.getElementById("filtroFechaProductoProveedor");

  if (!inputFecha) {
    return;
  }

  flatpickrFechaProductoProveedor = flatpickr(inputFecha, {
    locale: "es",

    dateFormat: "Y-m-d",

    altInput: true,

    altFormat: "d/m/Y",

    allowInput: true,

    disableMobile: true,

    onChange: function () {
      paginaActualProductosProveedor = 1;

      cargarProductosProveedor();
    },
  });
}

//=====================================================
// INICIALIZAR EVENTOS
//=====================================================

function inicializarEventosFiltros() {
  //=================================================
  // BUSCADOR
  //=================================================

  const inputBusqueda = document.getElementById("buscarProductoProveedor");

  if (inputBusqueda) {
    inputBusqueda.addEventListener("input", function () {
      clearTimeout(temporizadorBusquedaProductoProveedor);

      temporizadorBusquedaProductoProveedor = setTimeout(function () {
        paginaActualProductosProveedor = 1;

        cargarProductosProveedor();
      }, 300);
    });
  }

  //=================================================
  // PROVEEDOR
  //=================================================

  const filtroProveedor = document.getElementById("filtroProveedorProducto");

  if (filtroProveedor) {
    filtroProveedor.addEventListener("change", function () {
      paginaActualProductosProveedor = 1;

      cargarProductosProveedor();
    });
  }

  //=================================================
  // CATEGORÍA
  //=================================================

  const filtroCategoria = document.getElementById(
    "filtroCategoriaProductoProveedor",
  );

  if (filtroCategoria) {
    filtroCategoria.addEventListener("change", function () {
      paginaActualProductosProveedor = 1;

      cargarProductosProveedor();
    });
  }

  //=================================================
  // MARCA
  //=================================================

  const filtroMarca = document.getElementById("filtroMarcaProductoProveedor");

  if (filtroMarca) {
    filtroMarca.addEventListener("change", function () {
      paginaActualProductosProveedor = 1;

      cargarProductosProveedor();
    });
  }

  //=================================================
  // STOCK
  //=================================================

  const filtroStock = document.getElementById("filtroStockProductoProveedor");

  if (filtroStock) {
    filtroStock.addEventListener("change", function () {
      paginaActualProductosProveedor = 1;

      cargarProductosProveedor();
    });
  }

  //=================================================
  // ESTADO
  //=================================================

  const filtroEstado = document.getElementById("filtroEstadoProductoProveedor");

  if (filtroEstado) {
    filtroEstado.addEventListener("change", function () {
      paginaActualProductosProveedor = 1;

      cargarProductosProveedor();
    });
  }

  //=================================================
  // BOTÓN LIMPIAR
  //=================================================

  const btnLimpiar = document.getElementById(
    "btnLimpiarFiltrosProductoProveedor",
  );

  if (btnLimpiar) {
    btnLimpiar.addEventListener("click", limpiarFiltrosProductosProveedor);
  }
}

//=====================================================
// OBTENER FILTROS ACTUALES
//=====================================================

function obtenerFiltrosProductosProveedor() {
  const inputBusqueda = document.getElementById("buscarProductoProveedor");

  const filtroProveedor = document.getElementById("filtroProveedorProducto");

  const filtroCategoria = document.getElementById(
    "filtroCategoriaProductoProveedor",
  );

  const filtroMarca = document.getElementById("filtroMarcaProductoProveedor");

  const filtroStock = document.getElementById("filtroStockProductoProveedor");

  const filtroFecha = document.getElementById("filtroFechaProductoProveedor");

  const filtroEstado = document.getElementById("filtroEstadoProductoProveedor");

  return {
    buscar: inputBusqueda ? inputBusqueda.value.trim() : "",

    proveedor: filtroProveedor ? filtroProveedor.value : "",

    categoria: filtroCategoria ? filtroCategoria.value : "",

    marca: filtroMarca ? filtroMarca.value : "",

    stock: filtroStock ? filtroStock.value : "todos",

    fecha: filtroFecha ? filtroFecha.value : "",

    estado: filtroEstado ? filtroEstado.value : "todos",
  };
}

//=====================================================
// CARGAR PRODUCTOS
//=====================================================

async function cargarProductosProveedor() {
  //=================================================
  // CANCELAR PETICIÓN ANTERIOR
  //=================================================

  if (solicitudProductosProveedorActual) {
    solicitudProductosProveedorActual.abort();
  }

  //=================================================
  // MOSTRAR LOADING
  //=================================================

  mostrarCargaProductosProveedor();

  //=================================================
  // OBTENER FILTROS
  //=================================================

  const filtros = obtenerFiltrosProductosProveedor();

  //=================================================
  // FORM DATA
  //=================================================

  const formData = new URLSearchParams();

  formData.append("pagina", paginaActualProductosProveedor);

  formData.append("limite", registrosPorPaginaProductosProveedor);

  formData.append("buscar", filtros.buscar);

  formData.append("proveedor", filtros.proveedor);

  formData.append("categoria", filtros.categoria);

  formData.append("marca", filtros.marca);

  formData.append("stock", filtros.stock);

  formData.append("fecha", filtros.fecha);

  formData.append("estado", filtros.estado);

  //=================================================
  // ABORT CONTROLLER
  //=================================================

  const controller = new AbortController();

  solicitudProductosProveedorActual = controller;

  try {
    const respuesta = await fetch(URL_AJAX_PRODUCTOS_PROVEEDOR, {
      method: "POST",

      headers: {
        "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
      },

      body: formData.toString(),

      signal: controller.signal,
    });

    if (!respuesta.ok) {
      throw new Error("Error HTTP: " + respuesta.status);
    }

    const datos = await respuesta.json();

    if (!datos.success) {
      throw new Error(datos.mensaje || "No se pudieron obtener los productos.");
    }

    //=================================================
    // RENDERIZAR
    //=================================================

    renderizarProductosProveedor(datos);

    actualizarPaginacionProductosProveedor(datos);

    actualizarTextoResultadosProductosProveedor(datos);
  } catch (error) {
    if (error.name === "AbortError") {
      return;
    }

    console.error("Error al cargar productos del proveedor:", error);

    mostrarErrorProductosProveedor(error.message);
  } finally {
    solicitudProductosProveedorActual = null;
  }
}

//=====================================================
// MOSTRAR LOADING
//=====================================================

function mostrarCargaProductosProveedor() {
  const tabla = document.getElementById("tablaProductosProveedor");

  if (!tabla) {
    return;
  }

  tabla.innerHTML = `

        <tr>

            <td
                colspan="10"
                class="text-center py-5">

                <div
                    class="spinner-border text-primary mb-3"
                    role="status">

                    <span class="visually-hidden">
                        Cargando...
                    </span>

                </div>

                <div class="text-muted">

                    Cargando productos...

                </div>

            </td>

        </tr>

    `;
}

//=====================================================
// MOSTRAR ERROR
//=====================================================

function mostrarErrorProductosProveedor(mensaje) {
  const tabla = document.getElementById("tablaProductosProveedor");

  if (!tabla) {
    return;
  }

  tabla.innerHTML = `

        <tr>

            <td
                colspan="10"
                class="text-center py-5">

                <div class="text-danger mb-2">

                    <i
                        class="bi bi-exclamation-triangle-fill fs-2">
                    </i>

                </div>

                <div class="fw-semibold text-danger">

                    No se pudieron cargar los productos.

                </div>

                <div class="text-muted small mt-1">

                    ${escaparHTML(mensaje)}

                </div>

                <button
                    type="button"
                    class="btn btn-outline-primary btn-sm mt-3"
                    onclick="cargarProductosProveedor()">

                    <i class="bi bi-arrow-clockwise me-1"></i>

                    Reintentar

                </button>

            </td>

        </tr>

    `;
}

//=====================================================
// RENDERIZAR TABLA
//=====================================================

function renderizarProductosProveedor(datos) {
  const tabla = document.getElementById("tablaProductosProveedor");

  if (!tabla) {
    return;
  }

  const productos = Array.isArray(datos.productos) ? datos.productos : [];

  if (productos.length === 0) {
    tabla.innerHTML = `

            <tr>

                <td
                    colspan="10"
                    class="text-center py-5">

                    <div class="text-muted mb-2">

                        <i
                            class="bi bi-box-seam fs-1">
                        </i>

                    </div>

                    <div class="fw-semibold">

                        No se encontraron productos

                    </div>

                    <div class="text-muted small">

                        No existen productos que coincidan
                        con los filtros seleccionados.

                    </div>

                </td>

            </tr>

        `;

    return;
  }

  let html = "";

  productos.forEach(function (producto) {
    html += crearFilaProductoProveedor(producto);
  });

  tabla.innerHTML = html;

  inicializarBotonesVerProductoProveedor();
}

//=====================================================
// CREAR FILA PRODUCTO
//=====================================================

function crearFilaProductoProveedor(producto) {
  const idProducto = Number(producto.idProducto || 0);

  const nombre = producto.nombre || "Sin nombre";

  const codigo = producto.codigo || "Sin código";

  const categoria = producto.categoria || "Sin categoría";

  const marca = producto.marca || "Sin marca";

  const costo = parseFloat(producto.costo_compra || 0);

  const precio = parseFloat(producto.precio || 0);

  const stock = parseInt(producto.stock || 0);

  const estado = String(producto.estado || "").toUpperCase();

  const fecha = producto.fecha_registro || "-";

  const imagen = producto.imagen
    ? producto.imagen
    : "assets/img/producto_default.png";

  //=================================================
  // STOCK
  //=================================================

  let stockHTML = "";

  if (stock <= 0) {
    stockHTML = `

            <span class="badge bg-danger-subtle text-danger">

                Agotado

            </span>

        `;
  } else if (stock <= 5) {
    stockHTML = `

            <span class="badge bg-warning-subtle text-warning-emphasis">

                ${stock}

            </span>

        `;
  } else {
    stockHTML = `

            <span class="badge bg-success-subtle text-success">

                ${stock}

            </span>

        `;
  }

  //=================================================
  // ESTADO
  //=================================================

  let estadoHTML = "";

  if (estado === "ACTIVO" || estado === "1") {
    estadoHTML = `

            <span
                class="badge bg-success-subtle text-success">

                <i class="bi bi-check-circle-fill me-1"></i>

                Activo

            </span>

        `;
  } else {
    estadoHTML = `

            <span
                class="badge bg-secondary-subtle text-secondary">

                <i class="bi bi-pause-circle-fill me-1"></i>

                Inactivo

            </span>

        `;
  }

  return `

        <tr data-id-producto="${idProducto}">


            <!-- PRODUCTO -->

            <td class="ps-4">


                <div class="d-flex align-items-center gap-3">


                    <div
                        class="adm-producto-proveedor-imagen">


                        <img
                            src="${escaparAtributo(imagen)}"
                            alt="${escaparAtributo(nombre)}"
                            loading="lazy"
                            onerror="this.onerror=null;this.src='assets/img/producto_default.png';">


                    </div>


                    <div>

                        <div
                            class="fw-semibold text-dark">

                            ${escaparHTML(nombre)}

                        </div>


                        ${
                          producto.tipo
                            ? `
                                    <small class="text-muted">

                                        ${escaparHTML(producto.tipo)}

                                    </small>
                                  `
                            : ""
                        }

                    </div>

                </div>


            </td>



            <!-- CÓDIGO -->

            <td>

                <span
                    class="badge bg-light text-dark border">

                    ${escaparHTML(codigo)}

                </span>

            </td>



            <!-- CATEGORÍA -->

            <td>

                <span class="text-muted">

                    ${escaparHTML(categoria)}

                </span>

            </td>



            <!-- MARCA -->

            <td>

                <span class="text-muted">

                    ${escaparHTML(marca)}

                </span>

            </td>



            <!-- COSTO -->

            <td class="text-end">

                <span class="fw-semibold">

                    S/ ${formatearMoneda(costo)}

                </span>

            </td>



            <!-- PRECIO -->

            <td class="text-end">

                <span
                    class="fw-semibold text-primary">

                    S/ ${formatearMoneda(precio)}

                </span>

            </td>



            <!-- STOCK -->

            <td class="text-center">

                ${stockHTML}

            </td>



            <!-- ESTADO -->

            <td>

                ${estadoHTML}

            </td>



            <!-- FECHA -->

            <td>

                <span class="text-muted small">

                    ${formatearFecha(fecha)}

                </span>

            </td>



            <!-- ACCIONES -->

            <td class="text-end pe-4">


                <div
                    class="btn-group"
                    role="group">


                    <button
                        type="button"
                        class="btn btn-sm btn-outline-primary btn-ver-producto-proveedor"
                        data-id-producto="${idProducto}"
                        title="Ver producto">


                        <i class="bi bi-eye-fill"></i>


                    </button>
<!-- EDITAR PROVEEDOR -->

        <button
            type="button"
            class="btn btn-sm btn-outline-warning btn-editar-proveedor-producto"
            data-id-producto="${idProducto}"
            data-id-proveedor="${producto.id_provedor || ""}"
            title="Editar proveedor">

            <i class="bi bi-pencil-square"></i>

        </button>

                </div>


            </td>


        </tr>

    `;
}

//=====================================================
// EVENTOS BOTONES PRODUCTO
//=====================================================

function inicializarBotonesVerProductoProveedor() {
  //=================================================
  // BOTÓN VER PRODUCTO
  //=================================================

  const botonesVer = document.querySelectorAll(".btn-ver-producto-proveedor");

  botonesVer.forEach(function (boton) {
    boton.addEventListener("click", function () {
      const idProducto = parseInt(this.dataset.idProducto, 10);

      if (!idProducto) {
        console.error("ID de producto inválido.");

        return;
      }

      abrirDetalleProductoProveedor(idProducto);
    });
  });

  //=================================================
  // BOTÓN EDITAR PROVEEDOR
  //=================================================

  const botonesEditarProveedor = document.querySelectorAll(
    ".btn-editar-proveedor-producto",
  );

  botonesEditarProveedor.forEach(function (boton) {
    boton.addEventListener("click", function () {
      const idProducto = parseInt(this.dataset.idProducto, 10);

      if (!idProducto) {
        console.error("ID de producto inválido.");

        return;
      }

      abrirEditarProveedorProducto(idProducto);
    });
  });
}
//=====================================================
// INICIALIZAR EVENTOS EDITAR PROVEEDOR
//=====================================================

function inicializarEventosEditarProveedor() {
  const btnGuardar = document.getElementById("btnGuardarProveedorProducto");

  if (!btnGuardar) {
    console.warn("No existe #btnGuardarProveedorProducto");

    return;
  }

  btnGuardar.addEventListener("click", guardarCambiosProveedorProducto);
}
//=====================================================
// ABRIR EDITAR PROVEEDOR DEL PRODUCTO
//=====================================================

async function abrirEditarProveedorProducto(idProducto) {
  //=================================================
  // VALIDAR ID PRODUCTO
  //=================================================

  idProducto = parseInt(idProducto, 10);

  if (!idProducto || idProducto <= 0) {
    console.error("ID de producto inválido.");

    return;
  }

  //=================================================
  // MODAL
  //=================================================

  const elementoModal = document.getElementById("modalEditarProveedorProducto");

  if (!elementoModal) {
    console.warn("No existe el modal #modalEditarProveedorProducto");

    return;
  }

  //=================================================
  // ELEMENTOS
  //=================================================

  const inputIdProducto = document.getElementById("editarProveedorIdProducto");

  const nombreProducto = document.getElementById(
    "editarProveedorNombreProducto",
  );

  const proveedorActual = document.getElementById("editarProveedorActual");

  const selectProveedor = document.getElementById("editarProveedorIdProveedor");

  const mensaje = document.getElementById("mensajeEditarProveedorProducto");

  const botonGuardar = document.getElementById("btnGuardarProveedorProducto");

  //=================================================
  // GUARDAR ID PRODUCTO
  //=================================================

  if (inputIdProducto) {
    inputIdProducto.value = idProducto;
  }

  //=================================================
  // ESTADO INICIAL
  //=================================================

  if (nombreProducto) {
    nombreProducto.textContent = "Cargando información del producto...";
  }

  if (proveedorActual) {
    proveedorActual.innerHTML = `
      <span class="text-muted">

        <span
          class="spinner-border spinner-border-sm me-2"
          role="status">
        </span>

        Cargando...

      </span>
    `;
  }

  if (selectProveedor) {
    selectProveedor.disabled = true;

    selectProveedor.innerHTML = `
      <option value="">
        Cargando proveedores...
      </option>
    `;
  }

  if (mensaje) {
    mensaje.classList.add("d-none");

    mensaje.innerHTML = "";
  }

  if (botonGuardar) {
    botonGuardar.disabled = true;
  }

  //=================================================
  // MOSTRAR MODAL
  //=================================================

  const modal = bootstrap.Modal.getOrCreateInstance(elementoModal);

  modal.show();

  //=================================================
  // OBTENER INFORMACIÓN DEL PRODUCTO
  //=================================================

  try {
    const formData = new URLSearchParams();

    formData.append("idProducto", String(idProducto));

    const respuesta = await fetch(URL_AJAX_PROVEEDOR_PRODUCTO, {
      method: "POST",

      headers: {
        "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",

        Accept: "application/json",
      },

      body: formData.toString(),
    });

    //=================================================
    // VALIDAR HTTP
    //=================================================

    if (!respuesta.ok) {
      throw new Error("Error HTTP: " + respuesta.status);
    }

    //=================================================
    // JSON
    //=================================================

    const datos = await respuesta.json();

    //=================================================
    // VALIDAR RESPUESTA
    //=================================================

    if (!datos.success) {
      throw new Error(
        datos.mensaje || "No se pudo obtener la información del producto.",
      );
    }

    //=================================================
    // PRODUCTO
    //=================================================

    const producto = datos.producto || {};

    const idProveedorActual = parseInt(producto.id_provedor || 0, 10);

    //=================================================
    // NOMBRE PRODUCTO
    //=================================================

    if (nombreProducto) {
      nombreProducto.textContent = producto.nombre || "Producto sin nombre";
    }

    //=================================================
    // PROVEEDOR ACTUAL
    //=================================================

    if (proveedorActual) {
      if (idProveedorActual > 0) {
        proveedorActual.innerHTML = `
          <div
            class="d-flex align-items-center gap-2">

            <i
              class="bi bi-building-fill
                     text-warning">
            </i>

            <span class="fw-semibold">

              ${escaparHTML(producto.proveedor || "Proveedor sin nombre")}

            </span>

          </div>
        `;
      } else {
        proveedorActual.innerHTML = `
          <span class="text-muted">

            <i
              class="bi bi-building-x me-1">
            </i>

            Sin proveedor asignado

          </span>
        `;
      }
    }

    //=================================================
    // CARGAR PROVEEDORES
    //=================================================

    await cargarProveedoresEditarProducto(idProveedorActual);

    //=================================================
    // HABILITAR GUARDAR
    //=================================================

    if (botonGuardar) {
      botonGuardar.disabled = false;
    }
  } catch (error) {
    console.error("Error al obtener proveedor del producto:", error);

    //=================================================
    // ERROR PROVEEDOR ACTUAL
    //=================================================

    if (proveedorActual) {
      proveedorActual.innerHTML = `
        <span class="text-danger">

          <i
            class="bi bi-exclamation-triangle-fill
                   me-1">
          </i>

          No se pudo cargar el proveedor actual.

        </span>
      `;
    }

    //=================================================
    // ERROR SELECT
    //=================================================

    if (selectProveedor) {
      selectProveedor.disabled = true;

      selectProveedor.innerHTML = `
        <option value="">
          No se pudieron cargar los proveedores
        </option>
      `;
    }

    //=================================================
    // MENSAJE
    //=================================================

    mostrarMensajeEditarProveedor("danger", error.message);

    //=================================================
    // BOTÓN
    //=================================================

    if (botonGuardar) {
      botonGuardar.disabled = true;
    }
  }
}
//=====================================================
// CARGAR PROVEEDORES PARA EDITAR PRODUCTO
//=====================================================

async function cargarProveedoresEditarProducto(
  idProveedorActual = 0,
  nombreProveedorActual = "",
) {
  //=================================================
  // SELECT
  //=================================================

  const selectProveedor = document.getElementById("editarProveedorIdProveedor");

  if (!selectProveedor) {
    console.warn("No existe #editarProveedorIdProveedor");

    return;
  }

  //=================================================
  // NORMALIZAR ID
  //=================================================

  idProveedorActual = parseInt(idProveedorActual, 10) || 0;

  //=================================================
  // DESHABILITAR
  //=================================================

  selectProveedor.disabled = true;

  selectProveedor.innerHTML = `
    <option value="">
      Cargando proveedores...
    </option>
  `;

  try {
    //=================================================
    // OBTENER PROVEEDORES
    //=================================================

    const respuesta = await fetch(URL_AJAX_PROVEEDORES, {
      method: "GET",

      headers: {
        Accept: "application/json",
      },
    });

    //=================================================
    // VALIDAR HTTP
    //=================================================

    if (!respuesta.ok) {
      throw new Error("Error HTTP: " + respuesta.status);
    }

    //=================================================
    // JSON
    //=================================================

    const datos = await respuesta.json();

    //=================================================
    // VALIDAR RESPUESTA
    //=================================================

    if (!datos.success) {
      throw new Error(
        datos.mensaje || "No se pudieron cargar los proveedores.",
      );
    }

    //=================================================
    // LISTA
    //=================================================

    const proveedores = Array.isArray(datos.proveedores)
      ? datos.proveedores
      : [];

    //=================================================
    // LIMPIAR
    //=================================================

    selectProveedor.innerHTML = "";

    //=================================================
    // OPCIÓN SIN PROVEEDOR
    //=================================================

    const opcionSinProveedor = document.createElement("option");

    opcionSinProveedor.value = "";

    opcionSinProveedor.textContent = "Sin proveedor";

    selectProveedor.appendChild(opcionSinProveedor);

    //=================================================
    // VERIFICAR SI EXISTE EL PROVEEDOR ACTUAL
    //=================================================

    let proveedorActualEncontrado = false;

    //=================================================
    // AGREGAR PROVEEDORES
    //=================================================

    proveedores.forEach(function (proveedor) {
      const idProveedor = parseInt(
        proveedor.id_provedor || proveedor.idProveedor || proveedor.id || 0,
        10,
      );

      const nombre =
        proveedor.nombre ||
        proveedor.razon_social ||
        proveedor.razonSocial ||
        "Proveedor sin nombre";

      if (idProveedor <= 0) {
        return;
      }

      const option = document.createElement("option");

      option.value = String(idProveedor);

      option.textContent = nombre;

      //=================================================
      // PROVEEDOR ACTUAL
      //=================================================

      if (idProveedor === idProveedorActual) {
        proveedorActualEncontrado = true;
      }

      selectProveedor.appendChild(option);
    });

    //=================================================
    // SI EL PROVEEDOR ACTUAL NO VIENE EN LA LISTA
    // LO AGREGAMOS
    //=================================================

    if (idProveedorActual > 0 && !proveedorActualEncontrado) {
      const optionActual = document.createElement("option");

      optionActual.value = String(idProveedorActual);

      optionActual.textContent = nombreProveedorActual || "Proveedor actual";

      selectProveedor.appendChild(optionActual);
    }

    //=================================================
    // SELECCIONAR PROVEEDOR ACTUAL
    //=================================================

    if (idProveedorActual > 0) {
      selectProveedor.value = String(idProveedorActual);
    } else {
      selectProveedor.value = "";
    }

    //=================================================
    // HABILITAR
    //=================================================

    selectProveedor.disabled = false;
  } catch (error) {
    console.error("Error al cargar proveedores para editar:", error);

    selectProveedor.innerHTML = `
      <option value="">
        Error al cargar proveedores
      </option>
    `;

    selectProveedor.disabled = true;

    throw error;
  }
}
//=====================================================
// MOSTRAR MENSAJE EDITAR PROVEEDOR
//=====================================================

function mostrarMensajeEditarProveedor(tipo, mensaje) {
  const contenedor = document.getElementById("mensajeEditarProveedorProducto");

  if (!contenedor) {
    return;
  }

  let icono = "bi-info-circle-fill";

  if (tipo === "success") {
    icono = "bi-check-circle-fill";
  } else if (tipo === "danger") {
    icono = "bi-exclamation-triangle-fill";
  } else if (tipo === "warning") {
    icono = "bi-exclamation-circle-fill";
  }

  contenedor.className = "mt-3";

  contenedor.innerHTML = `

    <div
      class="alert alert-${tipo}
             d-flex align-items-start gap-2 mb-0">

      <i
        class="bi ${icono} mt-1">
      </i>

      <div>

        ${escaparHTML(mensaje)}

      </div>

    </div>

  `;
}
//=====================================================
// GUARDAR CAMBIOS PROVEEDOR PRODUCTO
//=====================================================

async function guardarCambiosProveedorProducto() {
  //=================================================
  // ELEMENTOS
  //=================================================

  const inputIdProducto = document.getElementById("editarProveedorIdProducto");

  const selectProveedor = document.getElementById("editarProveedorIdProveedor");

  const botonGuardar = document.getElementById("btnGuardarProveedorProducto");

  //=================================================
  // VALIDAR ELEMENTOS
  //=================================================

  if (!inputIdProducto || !selectProveedor || !botonGuardar) {
    console.error(
      "No se encontraron los elementos necesarios para actualizar el proveedor.",
    );

    return;
  }

  //=================================================
  // OBTENER VALORES
  //=================================================

  const idProducto = parseInt(inputIdProducto.value, 10);

  const idProveedor = parseInt(selectProveedor.value, 10);

  //=================================================
  // VALIDAR PRODUCTO
  //=================================================

  if (!idProducto || idProducto <= 0) {
    mostrarMensajeEditarProveedor(
      "danger",
      "El producto seleccionado no es válido.",
    );

    return;
  }

  //=================================================
  // VALIDAR PROVEEDOR
  //=================================================

  if (!idProveedor || idProveedor <= 0) {
    selectProveedor.classList.add("is-invalid");

    mostrarMensajeEditarProveedor("warning", "Debes seleccionar un proveedor.");

    selectProveedor.focus();

    return;
  }

  //=================================================
  // QUITAR VALIDACIÓN
  //=================================================

  selectProveedor.classList.remove("is-invalid");

  selectProveedor.classList.add("is-valid");

  //=================================================
  // DESHABILITAR BOTÓN
  //=================================================

  botonGuardar.disabled = true;

  const textoOriginal = botonGuardar.innerHTML;

  botonGuardar.innerHTML = `

    <span
      class="spinner-border spinner-border-sm me-2"
      role="status"
      aria-hidden="true">
    </span>

    Guardando...

  `;

  //=================================================
  // MENSAJE
  //=================================================

  mostrarMensajeEditarProveedor("info", "Guardando cambios...");

  try {
    //=================================================
    // FORM DATA
    //=================================================

    const formData = new URLSearchParams();

    formData.append("idProducto", String(idProducto));

    formData.append("idProveedor", String(idProveedor));

    //=================================================
    // PETICIÓN
    //=================================================

    const respuesta = await fetch(URL_AJAX_ACTUALIZAR_PROVEEDOR_PRODUCTO, {
      method: "POST",

      headers: {
        "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",

        Accept: "application/json",
      },

      body: formData.toString(),
    });

    //=================================================
    // HTTP
    //=================================================

    if (!respuesta.ok) {
      throw new Error("Error HTTP: " + respuesta.status);
    }

    //=================================================
    // JSON
    //=================================================

    const datos = await respuesta.json();

    //=================================================
    // VALIDAR
    //=================================================

    if (!datos.success) {
      throw new Error(datos.mensaje || "No se pudieron guardar los cambios.");
    }

    //=================================================
    // ÉXITO
    //=================================================

    mostrarMensajeEditarProveedor(
      "success",
      datos.mensaje || "El proveedor del producto se actualizó correctamente.",
    );

    //=================================================
    // RESTAURAR BOTÓN
    //=================================================

    botonGuardar.innerHTML = `
      <i class="bi bi-check-lg me-1"></i>
      Guardar cambios
    `;

    //=================================================
    // CERRAR MODAL
    //=================================================

    setTimeout(function () {
      const elementoModal = document.getElementById(
        "modalEditarProveedorProducto",
      );

      if (elementoModal) {
        const modal = bootstrap.Modal.getInstance(elementoModal);

        if (modal) {
          modal.hide();
        }
      }

      //=================================================
      // LIMPIAR VALIDACIÓN
      //=================================================

      selectProveedor.classList.remove("is-valid", "is-invalid");

      //=================================================
      // RECARGAR TABLA
      //=================================================

      cargarProductosProveedor();

      //=================================================
      // ACTUALIZAR KPI
      //=================================================

      cargarKPIProductosProveedor();
    }, 800);
  } catch (error) {
    console.error("Error al actualizar proveedor:", error);

    //=================================================
    // ERROR
    //=================================================

    mostrarMensajeEditarProveedor("danger", error.message);

    //=================================================
    // RESTAURAR BOTÓN
    //=================================================

    botonGuardar.disabled = false;

    botonGuardar.innerHTML = textoOriginal;
  }
}
//=====================================================
// ABRIR DETALLE PRODUCTO
//=====================================================

async function abrirDetalleProductoProveedor(idProducto) {
  productoProveedorSeleccionado = idProducto;

  if (!modalVerProductoProveedor) {
    inicializarModalProductoProveedor();
  }

  const elementoModal = document.getElementById("modalVerProductoProveedor");

  if (!elementoModal) {
    console.warn("No existe #modalVerProductoProveedor");

    return;
  }

  mostrarCargaDetalleProductoProveedor();

  modalVerProductoProveedor.show();

  try {
    const formData = new URLSearchParams();

    formData.append("idProducto", idProducto);

    const respuesta = await fetch(URL_AJAX_DETALLE_PRODUCTO, {
      method: "POST",

      headers: {
        "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
      },

      body: formData.toString(),
    });

    if (!respuesta.ok) {
      throw new Error("Error HTTP: " + respuesta.status);
    }

    const datos = await respuesta.json();

    if (!datos.success) {
      throw new Error(datos.mensaje || "No se pudo obtener el detalle.");
    }

    renderizarDetalleProductoProveedor(datos.producto);
  } catch (error) {
    console.error("Error al obtener detalle del producto:", error);

    mostrarErrorDetalleProductoProveedor(error.message);
  }
}

//=====================================================
// LOADING MODAL
//=====================================================

function mostrarCargaDetalleProductoProveedor() {
  const contenedor = document.getElementById(
    "contenidoDetalleProductoProveedor",
  );

  if (!contenedor) {
    return;
  }

  contenedor.innerHTML = `

        <div class="text-center py-5">

            <div
                class="spinner-border text-primary mb-3"
                role="status">

                <span class="visually-hidden">
                    Cargando...
                </span>

            </div>

            <div class="text-muted">

                Cargando información del producto...

            </div>

        </div>

    `;
}

//=====================================================
// ERROR MODAL
//=====================================================

function mostrarErrorDetalleProductoProveedor(mensaje) {
  const contenedor = document.getElementById(
    "contenidoDetalleProductoProveedor",
  );

  if (!contenedor) {
    return;
  }

  contenedor.innerHTML = `

        <div class="text-center py-5">

            <i
                class="bi bi-exclamation-triangle-fill
                       text-danger fs-1">
            </i>

            <h6 class="mt-3">

                No se pudo cargar el producto

            </h6>

            <p class="text-muted small">

                ${escaparHTML(mensaje)}

            </p>

        </div>

    `;
}

//=====================================================
// RENDERIZAR DETALLE PRODUCTO
//=====================================================

function renderizarDetalleProductoProveedor(producto) {
  const contenedor = document.getElementById(
    "contenidoDetalleProductoProveedor",
  );

  if (!contenedor) {
    console.warn("No existe #contenidoDetalleProductoProveedor");

    return;
  }

  if (!producto) {
    mostrarErrorDetalleProductoProveedor(
      "No se recibió información del producto.",
    );

    return;
  }

  //=================================================
  // DATOS
  //=================================================

  const nombre = producto.nombre || "Sin nombre";

  const codigo = producto.codigo || "-";

  const tipo = producto.tipo || "Producto";

  const categoria = producto.categoria || "Sin categoría";

  const marca = producto.marca || "Sin marca";

  const proveedor = producto.proveedor || "Sin proveedor";

  const stock = parseInt(producto.stock || 0, 10);

  const costo = parseFloat(producto.costo_compra || 0);

  const precio = parseFloat(producto.precio || 0);

  const precioAnterior = parseFloat(producto.precio_anterior || 0);

  const descuento = parseInt(producto.descuento || 0, 10);

  const descripcion = producto.descripcion || "Sin descripción.";

  const imagen = producto.imagen || "assets/img/producto_default.png";

  const eliminado = parseInt(producto.Eliminado || 0, 10);

  //=================================================
  // ESTADO
  //=================================================

  let estadoHTML = "";

  if (eliminado === 0) {
    estadoHTML = `

      <span class="badge bg-success-subtle text-success">

        <i class="bi bi-check-circle-fill me-1"></i>

        Activo

      </span>

    `;
  } else {
    estadoHTML = `

      <span class="badge bg-secondary-subtle text-secondary">

        <i class="bi bi-pause-circle-fill me-1"></i>

        Inactivo

      </span>

    `;
  }

  //=================================================
  // STOCK
  //=================================================

  let stockHTML = "";

  if (stock <= 0) {
    stockHTML = `

      <span class="badge bg-danger-subtle text-danger">

        <i class="bi bi-box-seam me-1"></i>

        Agotado

      </span>

    `;
  } else if (stock <= 5) {
    stockHTML = `

      <span class="badge bg-warning-subtle text-warning-emphasis">

        ${formatearNumero(stock)}

        unidades

      </span>

    `;
  } else {
    stockHTML = `

      <span class="badge bg-success-subtle text-success">

        ${formatearNumero(stock)}

        unidades

      </span>

    `;
  }

  //=================================================
  // CARACTERÍSTICAS
  //=================================================

  let caracteristicasHTML = "";

  if (Number(producto.oferta) === 1) {
    caracteristicasHTML += `

      <span class="badge bg-danger-subtle text-danger me-1 mb-1">

        <i class="bi bi-tag-fill me-1"></i>

        Oferta

      </span>

    `;
  }

  if (Number(producto.destacado) === 1) {
    caracteristicasHTML += `

      <span class="badge bg-warning-subtle text-warning-emphasis me-1 mb-1">

        <i class="bi bi-star-fill me-1"></i>

        Destacado

      </span>

    `;
  }

  if (Number(producto.nuevo) === 1) {
    caracteristicasHTML += `

      <span class="badge bg-primary-subtle text-primary me-1 mb-1">

        <i class="bi bi-sparkles me-1"></i>

        Nuevo

      </span>

    `;
  }

  if (Number(producto.envio_gratis) === 1) {
    caracteristicasHTML += `

      <span class="badge bg-success-subtle text-success me-1 mb-1">

        <i class="bi bi-truck me-1"></i>

        Envío gratis

      </span>

    `;
  }

  if (!caracteristicasHTML) {
    caracteristicasHTML = `

      <span class="text-muted small">

        Sin características especiales

      </span>

    `;
  }

  //=================================================
  // PRECIO ANTERIOR
  //=================================================

  let precioAnteriorHTML = "";

  if (precioAnterior > 0) {
    precioAnteriorHTML = `

      <div class="text-muted text-decoration-line-through small">

        S/ ${formatearMoneda(precioAnterior)}

      </div>

    `;
  }

  //=================================================
  // DESCUENTO
  //=================================================

  let descuentoHTML = "";

  if (descuento > 0) {
    descuentoHTML = `

      <span class="badge bg-danger ms-2">

        -${descuento}%

      </span>

    `;
  }

  //=================================================
  // FECHA
  //=================================================

  const fechaRegistro = formatearFecha(producto.fecha_registro);

  //=================================================
  // HTML
  //=================================================

  contenedor.innerHTML = `

    <div class="row g-4">


      <!--===========================================
          IMAGEN
      ============================================-->

      <div class="col-12 col-lg-4">


        <div
          class="border rounded-4 p-3 bg-light text-center h-100"
        >


          <div
            class="d-flex align-items-center justify-content-center"
            style="min-height:280px;"
          >

            <img
              src="${escaparAtributo(imagen)}"
              alt="${escaparAtributo(nombre)}"
              class="img-fluid rounded-3"
              style="
                max-width:100%;
                max-height:280px;
                width:auto;
                height:auto;
                object-fit:contain;
              "
              onerror="
                this.onerror=null;
                this.src='assets/img/producto_default.png';
              "
            >

          </div>


          <hr>


          <h5 class="fw-semibold mb-1">

            ${escaparHTML(nombre)}

          </h5>


          <span
            class="badge bg-light text-dark border"
          >

            ${escaparHTML(codigo)}

          </span>


        </div>


      </div>



      <!--===========================================
          INFORMACIÓN
      ============================================-->

      <div class="col-12 col-lg-8">


        <!-- DATOS PRINCIPALES -->

        <div class="row g-3">


          <div class="col-12 col-sm-6">

            <div class="small text-muted">

              Proveedor

            </div>

            <div class="fw-semibold">

              ${escaparHTML(proveedor)}

            </div>

          </div>


          <div class="col-12 col-sm-6">

            <div class="small text-muted">

              Categoría

            </div>

            <div class="fw-semibold">

              ${escaparHTML(categoria)}

            </div>

          </div>


          <div class="col-12 col-sm-6">

            <div class="small text-muted">

              Marca

            </div>

            <div class="fw-semibold">

              ${escaparHTML(marca)}

            </div>

          </div>


          <div class="col-12 col-sm-6">

            <div class="small text-muted">

              Tipo

            </div>

            <div class="fw-semibold">

              ${escaparHTML(tipo)}

            </div>

          </div>


        </div>


        <hr>


        <!-- PRECIOS -->

        <div class="row g-3">


          <div class="col-12 col-sm-4">

            <div class="small text-muted">

              Costo de compra

            </div>

            <div class="fs-5 fw-semibold">

              S/ ${formatearMoneda(costo)}

            </div>

          </div>


          <div class="col-12 col-sm-4">

            <div class="small text-muted">

              Precio de venta

            </div>

            <div class="fs-5 fw-semibold text-primary">

              S/ ${formatearMoneda(precio)}

              ${descuentoHTML}

            </div>

            ${precioAnteriorHTML}

          </div>


          <div class="col-12 col-sm-4">

            <div class="small text-muted">

              Stock actual

            </div>

            <div class="mt-1">

              ${stockHTML}

            </div>

          </div>


        </div>


        <hr>


        <!-- ESTADO -->

        <div class="mb-3">

          <div class="small text-muted mb-2">

            Estado y características

          </div>


          <div>

            ${estadoHTML}

            ${caracteristicasHTML}

          </div>

        </div>


        <!-- DESCRIPCIÓN -->

        <div class="mb-3">

          <div class="small text-muted mb-1">

            Descripción

          </div>

          <div class="border rounded-3 p-3 bg-light">

            ${escaparHTML(descripcion)}

          </div>

        </div>


        <!-- FECHA -->

        <div>

          <div class="small text-muted">

            Fecha de registro

          </div>

          <div class="fw-semibold">

            ${escaparHTML(fechaRegistro)}

          </div>

        </div>


      </div>


    </div>

  `;
}

//=====================================================
// CARGAR KPI
//=====================================================

async function cargarKPIProductosProveedor() {
  if (solicitudKPIProductosProveedorActual) {
    solicitudKPIProductosProveedorActual.abort();
  }

  const controller = new AbortController();

  solicitudKPIProductosProveedorActual = controller;

  try {
    const respuesta = await fetch(URL_AJAX_KPI_PRODUCTOS_PROVEEDOR, {
      method: "POST",

      headers: {
        "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
      },

      body: "",
      signal: controller.signal,
    });

    if (!respuesta.ok) {
      throw new Error("Error HTTP: " + respuesta.status);
    }

    const datos = await respuesta.json();

    if (!datos.success) {
      throw new Error(datos.mensaje || "No se pudieron cargar los KPI.");
    }

    actualizarKPIProductosProveedor(datos);
  } catch (error) {
    if (error.name === "AbortError") {
      return;
    }

    console.error("Error al cargar KPI:", error);
  } finally {
    solicitudKPIProductosProveedorActual = null;
  }
}

//=====================================================
// ACTUALIZAR KPI
//=====================================================

function actualizarKPIProductosProveedor(datos) {
  const total = document.getElementById("kpiTotalProductosProveedor");

  const activos = document.getElementById("kpiProductosActivosProveedor");

  const sinStock = document.getElementById("kpiProductosSinStockProveedor");

  const valorInventario = document.getElementById(
    "kpiValorInventarioProveedor",
  );

  if (total) {
    total.textContent = formatearNumero(datos.total || 0);
  }

  if (activos) {
    activos.textContent = formatearNumero(datos.activos || 0);
  }

  if (sinStock) {
    sinStock.textContent = formatearNumero(datos.sin_stock || 0);
  }

  if (valorInventario) {
    valorInventario.textContent =
      "S/ " + formatearMoneda(datos.valor_inventario || 0);
  }
}

//=====================================================
// CARGAR PROVEEDORES
//=====================================================

async function cargarProveedoresFiltro() {
  const select = document.getElementById("filtroProveedorProducto");

  if (!select) {
    return;
  }

  try {
    const respuesta = await fetch(URL_AJAX_PROVEEDORES);

    if (!respuesta.ok) {
      throw new Error("Error HTTP: " + respuesta.status);
    }

    const datos = await respuesta.json();

    if (!datos.success) {
      throw new Error(
        datos.mensaje || "No se pudieron cargar los proveedores.",
      );
    }

    select.innerHTML = `

            <option value="">

                Todos los proveedores

            </option>

        `;

    const proveedores = Array.isArray(datos.proveedores)
      ? datos.proveedores
      : [];

    proveedores.forEach(function (proveedor) {
      const option = document.createElement("option");

      option.value = proveedor.id_provedor;

      option.textContent = proveedor.nombre;

      select.appendChild(option);
    });
  } catch (error) {
    console.error("Error al cargar proveedores:", error);
  }
}

//=====================================================
// CARGAR CATEGORÍAS
//=====================================================

async function cargarCategoriasFiltro() {
  const select = document.getElementById("filtroCategoriaProductoProveedor");

  if (!select) {
    return;
  }

  try {
    const respuesta = await fetch(URL_AJAX_CATEGORIAS);

    if (!respuesta.ok) {
      throw new Error("Error HTTP: " + respuesta.status);
    }

    const datos = await respuesta.json();

    if (!datos.success) {
      throw new Error(datos.mensaje || "No se pudieron cargar las categorías.");
    }

    select.innerHTML = `

            <option value="">

                Todas

            </option>

        `;

    const categorias = Array.isArray(datos.categorias) ? datos.categorias : [];

    categorias.forEach(function (categoria) {
      const option = document.createElement("option");

      option.value = categoria.id_categorias;

      option.textContent = categoria.nombre;

      select.appendChild(option);
    });
  } catch (error) {
    console.error("Error al cargar categorías:", error);
  }
}

//=====================================================
// CARGAR MARCAS
//=====================================================

async function cargarMarcasFiltro() {
  const select = document.getElementById("filtroMarcaProductoProveedor");

  if (!select) {
    return;
  }

  try {
    const respuesta = await fetch(URL_AJAX_MARCAS);

    if (!respuesta.ok) {
      throw new Error("Error HTTP: " + respuesta.status);
    }

    const datos = await respuesta.json();

    if (!datos.success) {
      throw new Error(datos.mensaje || "No se pudieron cargar las marcas.");
    }

    select.innerHTML = `

            <option value="">

                Todas

            </option>

        `;

    const marcas = Array.isArray(datos.marcas) ? datos.marcas : [];

    marcas.forEach(function (marca) {
      const option = document.createElement("option");

      option.value = marca.id_marca;

      option.textContent = marca.nombre;

      select.appendChild(option);
    });
  } catch (error) {
    console.error("Error al cargar marcas:", error);
  }
}

//=====================================================
// PAGINACIÓN
//=====================================================

function actualizarPaginacionProductosProveedor(datos) {
  const contenedor = document.getElementById("paginacionProductosProveedor");

  if (!contenedor) {
    return;
  }

  contenedor.innerHTML = "";

  const pagina = parseInt(datos.pagina || paginaActualProductosProveedor, 10);

  const totalPaginas = parseInt(datos.total_paginas || 1, 10);

  paginaActualProductosProveedor = pagina;

  if (totalPaginas <= 1) {
    return;
  }

  //=================================================
  // ANTERIOR
  //=================================================

  const liAnterior = document.createElement("li");

  liAnterior.className = "page-item" + (pagina <= 1 ? " disabled" : "");

  liAnterior.innerHTML = `

        <button
            class="page-link"
            type="button"
            aria-label="Anterior">

            <i class="bi bi-chevron-left"></i>

        </button>

    `;

  liAnterior.querySelector("button").addEventListener("click", function () {
    if (pagina > 1) {
      cambiarPaginaProductosProveedor(pagina - 1);
    }
  });

  contenedor.appendChild(liAnterior);

  //=================================================
  // NÚMEROS
  //=================================================

  const maxPaginasVisibles = 5;

  let inicio = Math.max(1, pagina - Math.floor(maxPaginasVisibles / 2));

  let fin = Math.min(totalPaginas, inicio + maxPaginasVisibles - 1);

  if (fin - inicio + 1 < maxPaginasVisibles) {
    inicio = Math.max(1, fin - maxPaginasVisibles + 1);
  }

  for (let i = inicio; i <= fin; i++) {
    const li = document.createElement("li");

    li.className = "page-item" + (i === pagina ? " active" : "");

    li.innerHTML = `

            <button
                class="page-link"
                type="button">

                ${i}

            </button>

        `;

    li.querySelector("button").addEventListener("click", function () {
      cambiarPaginaProductosProveedor(i);
    });

    contenedor.appendChild(li);
  }

  //=================================================
  // SIGUIENTE
  //=================================================

  const liSiguiente = document.createElement("li");

  liSiguiente.className =
    "page-item" + (pagina >= totalPaginas ? " disabled" : "");

  liSiguiente.innerHTML = `

        <button
            class="page-link"
            type="button"
            aria-label="Siguiente">

            <i class="bi bi-chevron-right"></i>

        </button>

    `;

  liSiguiente.querySelector("button").addEventListener("click", function () {
    if (pagina < totalPaginas) {
      cambiarPaginaProductosProveedor(pagina + 1);
    }
  });

  contenedor.appendChild(liSiguiente);
}

//=====================================================
// CAMBIAR PÁGINA
//=====================================================

function cambiarPaginaProductosProveedor(pagina) {
  if (pagina < 1) {
    return;
  }

  paginaActualProductosProveedor = pagina;

  cargarProductosProveedor();

  const tabla = document.getElementById("tablaProductosProveedor");

  if (tabla) {
    tabla.closest(".adm-productos-proveedor-card")?.scrollIntoView({
      behavior: "smooth",
      block: "start",
    });
  }
}

//=====================================================
// TEXTO RESULTADOS
//=====================================================

function actualizarTextoResultadosProductosProveedor(datos) {
  const texto = document.getElementById("textoResultadosProductosProveedor");

  const info = document.getElementById("infoPaginacionProductosProveedor");

  const total = parseInt(datos.total_registros || 0, 10);

  const desde =
    total > 0
      ? (paginaActualProductosProveedor - 1) *
          registrosPorPaginaProductosProveedor +
        1
      : 0;

  const hasta = Math.min(
    paginaActualProductosProveedor * registrosPorPaginaProductosProveedor,
    total,
  );

  if (texto) {
    texto.textContent =
      total === 1
        ? "1 producto encontrado"
        : `${formatearNumero(total)} productos encontrados`;
  }

  if (info) {
    info.textContent = `Mostrando ${formatearNumero(desde)} - ${formatearNumero(hasta)} de ${formatearNumero(total)} productos`;
  }
}

//=====================================================
// LIMPIAR FILTROS
//=====================================================

function limpiarFiltrosProductosProveedor() {
  const inputBusqueda = document.getElementById("buscarProductoProveedor");

  const filtroProveedor = document.getElementById("filtroProveedorProducto");

  const filtroCategoria = document.getElementById(
    "filtroCategoriaProductoProveedor",
  );

  const filtroMarca = document.getElementById("filtroMarcaProductoProveedor");

  const filtroStock = document.getElementById("filtroStockProductoProveedor");

  const filtroEstado = document.getElementById("filtroEstadoProductoProveedor");

  if (inputBusqueda) {
    inputBusqueda.value = "";
  }

  if (filtroProveedor) {
    filtroProveedor.value = "";
  }

  if (filtroCategoria) {
    filtroCategoria.value = "";
  }

  if (filtroMarca) {
    filtroMarca.value = "";
  }

  if (filtroStock) {
    filtroStock.value = "todos";
  }

  if (filtroEstado) {
    filtroEstado.value = "todos";
  }

  if (flatpickrFechaProductoProveedor) {
    flatpickrFechaProductoProveedor.clear();
  }

  paginaActualProductosProveedor = 1;

  cargarProductosProveedor();
}

//=====================================================
// FORMATEAR MONEDA
//=====================================================

function formatearMoneda(valor) {
  const numero = parseFloat(valor) || 0;

  return numero.toLocaleString("es-PE", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
}

//=====================================================
// FORMATEAR NÚMERO
//=====================================================

function formatearNumero(valor) {
  const numero = parseInt(valor, 10) || 0;

  return numero.toLocaleString("es-PE");
}

//=====================================================
// FORMATEAR FECHA
//=====================================================

function formatearFecha(fecha) {
  if (!fecha) {
    return "-";
  }

  const partes = String(fecha).split("-");

  if (partes.length !== 3) {
    return escaparHTML(fecha);
  }

  return partes[2] + "/" + partes[1] + "/" + partes[0];
}

//=====================================================
// ESCAPAR HTML
//=====================================================

function escaparHTML(valor) {
  if (valor === null || valor === undefined) {
    return "";
  }

  const div = document.createElement("div");

  div.textContent = String(valor);

  return div.innerHTML;
}

//=====================================================
// ESCAPAR ATRIBUTO HTML
//=====================================================

function escaparAtributo(valor) {
  return escaparHTML(valor).replace(/"/g, "&quot;");
}

//=====================================================
// EXPONER FUNCIONES NECESARIAS
//=====================================================

window.cargarProductosProveedor = cargarProductosProveedor;

window.cargarKPIProductosProveedor = cargarKPIProductosProveedor;

window.cambiarPaginaProductosProveedor = cambiarPaginaProductosProveedor;

window.limpiarFiltrosProductosProveedor = limpiarFiltrosProductosProveedor;
