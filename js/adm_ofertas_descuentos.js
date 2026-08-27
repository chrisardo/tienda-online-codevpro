//=====================================================
// CoDevPro Technology
// Archivo: js/adm_ofertas_descuentos.js
// Módulo: Ofertas y Descuentos
// Sistema: Inventa
//=====================================================

"use strict";

//=====================================================
// VARIABLES GLOBALES
//=====================================================

let paginaActual = 1;

let solicitudOfertas = null;

let temporizadorBusquedaOfertas = null;

let numeroSolicitudOfertas = 0;

//=====================================================
// CONFIGURACIÓN
//=====================================================

const REGISTROS_POR_PAGINA_DEFAULT = 10;

const URL_LISTAR_OFERTAS = "ajax/listar_ofertas_descuentos.php";
const URL_KPI_OFERTAS = "ajax/obtener_kpis_ofertas.php";
//=====================================================
// OBTENER ELEMENTO
//=====================================================

function obtenerElemento(...ids) {
  for (const id of ids) {
    if (!id) {
      continue;
    }

    const elemento = document.getElementById(id);

    if (elemento) {
      return elemento;
    }
  }

  return null;
}

//=====================================================
// ELEMENTOS DEL DOM
//=====================================================

let inputBuscar = null;

let selectOferta = null;

let selectDescuento = null;

let selectCategoria = null;

let selectMarca = null;

let selectOrden = null;

let cuerpoTabla = null;

let contenedorPaginacion = null;

let elementoTotalRegistros = null;

let elementoDesde = null;

let elementoHasta = null;

let elementoInfoOfertas = null;

let elementoInfoPaginacion = null;

let botonLimpiarFiltros = null;

let botonActualizarOfertas = null;
//=====================================================
// ELEMENTOS KPI
//=====================================================

let elementoKpiTotalProductos = null;

let elementoKpiOfertasActivas = null;

let elementoKpiProductosDescuento = null;

let elementoKpiSinDescuento = null;

let elementoKpiDescuentoPromedio = null;
//=====================================================
// INICIALIZAR ELEMENTOS
//=====================================================

function inicializarElementos() {
  //=================================================
  // BUSCAR PRODUCTO
  //=================================================

  inputBuscar = obtenerElemento(
    "buscarOferta",
    "buscarOfertas",
    "inputBuscarOfertas",
    "inputBuscar",
    "buscar",
  );

  //=================================================
  // OFERTA
  //=================================================

  selectOferta = obtenerElemento(
    "filtroOferta",
    "selectOferta",
    "oferta",
    "filtro_oferta",
  );

  //=================================================
  // DESCUENTO
  //=================================================

  selectDescuento = obtenerElemento(
    "filtroDescuento",
    "selectDescuento",
    "descuento",
    "filtro_descuento",
  );

  //=================================================
  // CATEGORÍA
  //=================================================

  // IMPORTANTE:
  // En adm_ofertas_descuentos.php el ID real es:
  // filtroCategoriaOferta

  selectCategoria = obtenerElemento(
    "filtroCategoriaOferta",
    "filtroCategoria",
    "selectCategoria",
    "categoria",
    "filtro_categoria",
  );

  //=================================================
  // MARCA
  //=================================================

  // IMPORTANTE:
  // En adm_ofertas_descuentos.php el ID real es:
  // filtroMarcaOferta

  selectMarca = obtenerElemento(
    "filtroMarcaOferta",
    "filtroMarca",
    "selectMarca",
    "marca",
    "filtro_marca",
  );

  //=================================================
  // ORDENAR POR
  //=================================================

  // IMPORTANTE:
  // En adm_ofertas_descuentos.php el ID REAL es:
  // ordenOfertas

  selectOrden = obtenerElemento(
    "ordenOfertas",
    "filtroOrden",
    "selectOrden",
    "orden",
    "ordenarPor",
    "ordenar",
  );

  //=================================================
  // TABLA
  //=================================================

  cuerpoTabla = obtenerElemento(
    "tablaOfertasDescuentos",
    "tbodyOfertasDescuentos",
    "cuerpoTablaOfertas",
    "tablaOfertas",
    "tablaProductos",
  );

  //=================================================
  // PAGINACIÓN
  //=================================================

  contenedorPaginacion = obtenerElemento(
    "paginacionOfertas",
    "paginacionOfertasDescuentos",
    "paginacion",
  );

  //=================================================
  // TOTAL
  //=================================================

  // IMPORTANTE:
  // En adm_ofertas_descuentos.php el ID REAL es:
  // totalOfertasEncontradas

  elementoTotalRegistros = obtenerElemento(
    "totalOfertasEncontradas",
    "totalRegistros",
    "totalOfertas",
    "totalProductos",
  );

  //=================================================
  // INFORMACIÓN DE OFERTAS
  //=================================================

  elementoInfoOfertas = obtenerElemento("infoOfertas");

  //=================================================
  // INFORMACIÓN PAGINACIÓN
  //=================================================

  elementoInfoPaginacion = obtenerElemento("infoPaginacionOfertas");

  //=================================================
  // DESDE
  //=================================================

  elementoDesde = obtenerElemento("registrosDesde", "desdeRegistros", "desde");

  //=================================================
  // HASTA
  //=================================================

  elementoHasta = obtenerElemento("registrosHasta", "hastaRegistros", "hasta");

  //=================================================
  // BOTÓN LIMPIAR FILTROS
  //=================================================

  // IMPORTANTE:
  // En adm_ofertas_descuentos.php el ID REAL es:
  // btnResetFiltrosOfertas

  botonLimpiarFiltros = obtenerElemento(
    "btnResetFiltrosOfertas",
    "btnLimpiarFiltros",
    "btnLimpiarFiltro",
    "limpiarFiltros",
    "btnLimpiar",
    "btnResetFiltros",
    "btnResetearFiltros",
  );

  //=================================================
  // BOTÓN ACTUALIZAR
  //=================================================

  botonActualizarOfertas = obtenerElemento("btnActualizarOfertas");
  //=================================================
  // KPI - TOTAL PRODUCTOS
  //=================================================

  elementoKpiTotalProductos = obtenerElemento(
    "kpiTotalProductos",
    "totalProductos",
    "total_productos",
    "totalProductosOferta",
    "kpiTotal",
  );

  //=================================================
  // KPI - OFERTAS ACTIVAS
  //=================================================

  elementoKpiOfertasActivas = obtenerElemento(
    "kpiOfertasActivas",
    "ofertasActivas",
    "productosOferta",
    "productos_oferta",
    "kpiOfertas",
  );

  //=================================================
  // KPI - PRODUCTOS CON DESCUENTO
  //=================================================

  elementoKpiProductosDescuento = obtenerElemento(
    "kpiProductosDescuento",
    "productosDescuento",
    "productos_descuento",
    "kpiDescuento",
  );

  //=================================================
  // KPI - SIN DESCUENTO
  //=================================================

  elementoKpiSinDescuento = obtenerElemento(
    "kpiSinDescuento",
    "sinDescuento",
    "productosSinDescuento",
    "productos_sin_descuento",
    "kpiSinDescuento",
  );

  //=================================================
  // KPI - DESCUENTO PROMEDIO
  //=================================================

  elementoKpiDescuentoPromedio = obtenerElemento(
    "kpiDescuentoPromedio",
    "descuentoPromedio",
    "descuento_promedio",
    "promedioDescuento",
  );
  //=================================================
  // DEBUG
  //=================================================

  console.log("============================================");

  console.log("Elementos Ofertas y Descuentos");

  console.log("============================================");

  console.log({
    inputBuscar: inputBuscar,
    selectOferta: selectOferta,
    selectDescuento: selectDescuento,
    selectCategoria: selectCategoria,
    selectMarca: selectMarca,
    selectOrden: selectOrden,
    cuerpoTabla: cuerpoTabla,
    contenedorPaginacion: contenedorPaginacion,
    elementoTotalRegistros: elementoTotalRegistros,
    elementoInfoOfertas: elementoInfoOfertas,
    elementoInfoPaginacion: elementoInfoPaginacion,
    botonLimpiarFiltros: botonLimpiarFiltros,
    botonActualizarOfertas: botonActualizarOfertas,
  });

  console.log(
    "Valor actual de Ordenar por:",
    selectOrden ? selectOrden.value : "NO ENCONTRADO",
  );

  console.log(
    "Botón limpiar:",
    botonLimpiarFiltros ? "ENCONTRADO" : "NO ENCONTRADO",
  );
}
//=====================================================
// CARGAR KPI OFERTAS Y DESCUENTOS
//=====================================================

function cargarKPIsOfertas() {
  console.log("============================================");

  console.log("CARGANDO KPI OFERTAS Y DESCUENTOS");

  console.log("============================================");

  //=================================================
  // MOSTRAR CARGANDO
  //=================================================

  mostrarCargandoKPIsOfertas();

  //=================================================
  // AJAX
  //=================================================

  const xhr = new XMLHttpRequest();

  xhr.open("GET", URL_KPI_OFERTAS + "?_=" + Date.now(), true);

  xhr.responseType = "json";

  //=================================================
  // RESPUESTA
  //=================================================

  xhr.onreadystatechange = function () {
    if (xhr.readyState !== 4) {
      return;
    }

    //=================================================
    // ERROR HTTP
    //=================================================

    if (xhr.status < 200 || xhr.status >= 300) {
      console.error("Error HTTP KPI:", xhr.status);

      mostrarErrorKPIsOfertas();

      return;
    }

    //=================================================
    // RESPUESTA
    //=================================================

    let respuesta = xhr.response;

    //=================================================
    // PARSEAR MANUALMENTE
    //=================================================

    if (typeof respuesta === "string") {
      try {
        respuesta = JSON.parse(respuesta);
      } catch (error) {
        console.error("JSON KPI inválido:", respuesta);

        mostrarErrorKPIsOfertas();

        return;
      }
    }

    //=================================================
    // VALIDAR RESPUESTA
    //=================================================

    if (!respuesta || typeof respuesta !== "object") {
      console.error("Respuesta KPI inválida:", respuesta);

      mostrarErrorKPIsOfertas();

      return;
    }

    //=================================================
    // ERROR BACKEND
    //=================================================

    if (respuesta.success !== true) {
      console.error("Error backend KPI:", respuesta);

      mostrarErrorKPIsOfertas();

      return;
    }

    //=================================================
    // ACTUALIZAR KPI
    //=================================================

    actualizarKPIsOfertas(respuesta);
  };

  //=================================================
  // ERROR DE RED
  //=================================================

  xhr.onerror = function () {
    console.error("Error de conexión al obtener KPI.");

    mostrarErrorKPIsOfertas();
  };

  //=================================================
  // TIMEOUT
  //=================================================

  xhr.timeout = 30000;

  xhr.ontimeout = function () {
    console.error("Timeout al obtener KPI.");

    mostrarErrorKPIsOfertas();
  };

  //=================================================
  // ENVIAR
  //=================================================

  xhr.send();
}
//=====================================================
// ACTUALIZAR KPI
//=====================================================

function actualizarKPIsOfertas(respuesta) {
  console.log("============================================");
  console.log("RESPUESTA COMPLETA KPI");
  console.log(respuesta);
  console.log("============================================");

  //=================================================
  // OBTENER CONTENEDOR DE DATOS
  //=================================================

  const datos =
    respuesta && respuesta.datos && typeof respuesta.datos === "object"
      ? respuesta.datos
      : respuesta;

  //=================================================
  // TOTAL PRODUCTOS
  //=================================================

  const totalProductos =
    parseInt(datos.total_productos ?? datos.totalProductos ?? 0, 10) || 0;

  //=================================================
  // OFERTAS ACTIVAS
  //=================================================

  const productosOferta =
    parseInt(
      datos.productos_oferta ??
        datos.ofertas_activas ??
        datos.productosOferta ??
        0,
      10,
    ) || 0;

  //=================================================
  // PRODUCTOS CON DESCUENTO
  //=================================================

  const productosDescuento =
    parseInt(datos.productos_descuento ?? datos.productosDescuento ?? 0, 10) ||
    0;

  //=================================================
  // PRODUCTOS SIN DESCUENTO
  //=================================================
  //
  // IMPORTANTE:
  // El PHP debe enviar este valor calculado directamente.
  //
  // Solo usamos total - descuento como respaldo.
  //
  //=================================================

  let productosSinDescuento;

  if (
    datos.productos_sin_descuento !== undefined &&
    datos.productos_sin_descuento !== null
  ) {
    productosSinDescuento = parseInt(datos.productos_sin_descuento, 10) || 0;
  } else if (
    datos.productosSinDescuento !== undefined &&
    datos.productosSinDescuento !== null
  ) {
    productosSinDescuento = parseInt(datos.productosSinDescuento, 10) || 0;
  } else {
    productosSinDescuento = totalProductos - productosDescuento;
  }

  //=================================================
  // SEGURIDAD
  //=================================================

  if (productosSinDescuento < 0) {
    productosSinDescuento = 0;
  }

  //=================================================
  // DESCUENTO PROMEDIO
  //=================================================

  const descuentoPromedio =
    parseFloat(datos.descuento_promedio ?? datos.descuentoPromedio ?? 0) || 0;

  //=================================================
  // ACTUALIZAR KPI TOTAL
  //=================================================

  if (elementoKpiTotalProductos) {
    elementoKpiTotalProductos.textContent =
      totalProductos.toLocaleString("es-PE");
  }

  //=================================================
  // ACTUALIZAR KPI OFERTAS ACTIVAS
  //=================================================

  if (elementoKpiOfertasActivas) {
    elementoKpiOfertasActivas.textContent =
      productosOferta.toLocaleString("es-PE");
  }

  //=================================================
  // ACTUALIZAR KPI CON DESCUENTO
  //=================================================

  if (elementoKpiProductosDescuento) {
    elementoKpiProductosDescuento.textContent =
      productosDescuento.toLocaleString("es-PE");
  }

  //=================================================
  // ACTUALIZAR KPI SIN DESCUENTO
  //=================================================

  if (elementoKpiSinDescuento) {
    elementoKpiSinDescuento.textContent =
      productosSinDescuento.toLocaleString("es-PE");
  }

  //=================================================
  // ACTUALIZAR KPI DESCUENTO PROMEDIO
  //=================================================

  if (elementoKpiDescuentoPromedio) {
    elementoKpiDescuentoPromedio.textContent =
      descuentoPromedio.toFixed(2) + "%";
  }

  //=================================================
  // DEBUG
  //=================================================

  console.log("============================================");
  console.log("KPI ACTUALIZADOS");
  console.log("============================================");

  console.table({
    total_productos: totalProductos,
    productos_oferta: productosOferta,
    productos_descuento: productosDescuento,
    productos_sin_descuento: productosSinDescuento,
    descuento_promedio: descuentoPromedio,
  });

  console.log("============================================");
}
//=====================================================
// MOSTRAR CARGANDO KPI
//=====================================================

function mostrarCargandoKPIsOfertas() {
  const elementos = [
    elementoKpiTotalProductos,
    elementoKpiOfertasActivas,
    elementoKpiProductosDescuento,
    elementoKpiSinDescuento,
    elementoKpiDescuentoPromedio,
  ];

  elementos.forEach(function (elemento) {
    if (elemento) {
      elemento.textContent = "—";
    }
  });
}
//=====================================================
// MOSTRAR ERROR KPI
//=====================================================

function mostrarErrorKPIsOfertas() {
  const elementos = [
    elementoKpiTotalProductos,
    elementoKpiOfertasActivas,
    elementoKpiProductosDescuento,
    elementoKpiSinDescuento,
    elementoKpiDescuentoPromedio,
  ];

  elementos.forEach(function (elemento) {
    if (elemento) {
      elemento.textContent = "—";
    }
  });
}
//=====================================================
// OBTENER VALOR SELECT
//=====================================================

function obtenerValorSelect(elemento) {
  if (!elemento) {
    return "";
  }

  return String(elemento.value ?? "").trim();
}

//=====================================================
// OBTENER REGISTROS POR PÁGINA
//=====================================================

function obtenerRegistrosPorPagina() {
  return REGISTROS_POR_PAGINA_DEFAULT;
}

//=====================================================
// CONSTRUIR PARÁMETROS
//=====================================================

function construirParametros(pagina) {
  const parametros = new URLSearchParams();

  //=================================================
  // PÁGINA
  //=================================================

  parametros.set("pagina", String(pagina));

  //=================================================
  // REGISTROS
  //=================================================

  parametros.set("registros", String(obtenerRegistrosPorPagina()));

  //=================================================
  // BUSCAR
  //=================================================

  parametros.set(
    "buscar",
    inputBuscar ? String(inputBuscar.value ?? "").trim() : "",
  );

  //=================================================
  // OFERTA
  //=================================================

  parametros.set("oferta", obtenerValorSelect(selectOferta));

  //=================================================
  // DESCUENTO
  //=================================================

  parametros.set("descuento", obtenerValorSelect(selectDescuento));

  //=================================================
  // CATEGORÍA
  //=================================================

  parametros.set("categoria", obtenerValorSelect(selectCategoria));

  //=================================================
  // MARCA
  //=================================================

  parametros.set("marca", obtenerValorSelect(selectMarca));

  //=================================================
  // ORDEN
  //=================================================

  parametros.set("orden", obtenerValorSelect(selectOrden));

  return parametros;
}

//=====================================================
// CARGAR OFERTAS Y DESCUENTOS
//=====================================================

function cargarOfertasDescuentos(pagina = 1) {
  //=================================================
  // VALIDAR PÁGINA
  //=================================================

  pagina = parseInt(pagina, 10);

  if (isNaN(pagina) || pagina < 1) {
    pagina = 1;
  }

  paginaActual = pagina;

  //=================================================
  // IDENTIFICADOR DE SOLICITUD
  //=================================================

  const solicitudActual = ++numeroSolicitudOfertas;

  //=================================================
  // CANCELAR PETICIÓN ANTERIOR
  //=================================================

  if (solicitudOfertas) {
    try {
      solicitudOfertas.abort();
    } catch (error) {
      console.warn("No se pudo cancelar la solicitud anterior.", error);
    }

    solicitudOfertas = null;
  }

  //=================================================
  // MOSTRAR CARGANDO
  //=================================================

  mostrarCargandoOfertas();

  //=================================================
  // PARÁMETROS
  //=================================================

  const parametros = construirParametros(paginaActual);

  //=================================================
  // URL
  //=================================================

  const url = URL_LISTAR_OFERTAS + "?" + parametros.toString();

  //=================================================
  // DEBUG
  //=================================================

  console.log("============================================");

  console.log("Cargando Ofertas y Descuentos");

  console.log("URL:", url);

  console.log("Orden:", obtenerValorSelect(selectOrden));

  console.log("============================================");

  //=================================================
  // AJAX
  //=================================================

  const xhr = new XMLHttpRequest();

  solicitudOfertas = xhr;

  xhr.open("GET", url, true);

  xhr.responseType = "json";

  //=================================================
  // ESTADO
  //=================================================

  xhr.onreadystatechange = function () {
    if (xhr.readyState !== 4) {
      return;
    }

    //=================================================
    // IGNORAR RESPUESTA VIEJA
    //=================================================

    if (solicitudActual !== numeroSolicitudOfertas) {
      return;
    }

    //=================================================
    // PETICIÓN CANCELADA
    //=================================================

    if (xhr.status === 0) {
      return;
    }

    //=================================================
    // ERROR HTTP
    //=================================================

    if (xhr.status < 200 || xhr.status >= 300) {
      console.error("Error HTTP:", xhr.status);

      mostrarErrorOfertas("No se pudo conectar con el servidor.");

      return;
    }

    //=================================================
    // RESPUESTA
    //=================================================

    let respuesta = xhr.response;

    //=================================================
    // PARSEAR MANUALMENTE
    //=================================================

    if (typeof respuesta === "string") {
      try {
        respuesta = JSON.parse(respuesta);
      } catch (error) {
        console.error("Respuesta JSON inválida:", respuesta);

        mostrarErrorOfertas("El servidor devolvió una respuesta inválida.");

        return;
      }
    }

    //=================================================
    // VALIDAR RESPUESTA
    //=================================================

    if (!respuesta || typeof respuesta !== "object") {
      mostrarErrorOfertas("No se recibió una respuesta válida del servidor.");

      return;
    }

    //=================================================
    // ERROR BACKEND
    //=================================================

    if (respuesta.success !== true) {
      console.error("Error AJAX:", respuesta);

      mostrarErrorOfertas(
        respuesta.mensaje || "No se pudieron cargar las ofertas y descuentos.",
      );

      return;
    }

    //=================================================
    // TABLA
    //=================================================

    if (cuerpoTabla) {
      cuerpoTabla.innerHTML = respuesta.html || respuesta.tabla || "";
    }

    //=================================================
    // PAGINACIÓN
    //=================================================

    if (contenedorPaginacion) {
      contenedorPaginacion.innerHTML = respuesta.paginacion || "";
    }

    //=================================================
    // INFORMACIÓN
    //=================================================

    actualizarInformacionPaginacion(respuesta);

    //=================================================
    // CONFIGURAR PAGINACIÓN
    //=================================================

    configurarEventosPaginacion();

    //=================================================
    // PÁGINA REAL
    //=================================================

    const paginaRespuesta = parseInt(respuesta.pagina_actual, 10);

    if (!isNaN(paginaRespuesta) && paginaRespuesta > 0) {
      paginaActual = paginaRespuesta;
    }

    //=================================================
    // LIMPIAR REFERENCIA
    //=================================================

    if (solicitudActual === numeroSolicitudOfertas) {
      solicitudOfertas = null;
    }
  };

  //=================================================
  // ERROR DE RED
  //=================================================

  xhr.onerror = function () {
    if (solicitudActual !== numeroSolicitudOfertas) {
      return;
    }

    mostrarErrorOfertas("Ocurrió un error de conexión con el servidor.");
  };

  //=================================================
  // TIMEOUT
  //=================================================

  xhr.timeout = 30000;

  xhr.ontimeout = function () {
    if (solicitudActual !== numeroSolicitudOfertas) {
      return;
    }

    mostrarErrorOfertas("La solicitud tardó demasiado tiempo.");
  };

  //=================================================
  // ENVIAR
  //=================================================

  xhr.send();
}

//=====================================================
// MOSTRAR CARGANDO
//=====================================================

function mostrarCargandoOfertas() {
  if (!cuerpoTabla) {
    return;
  }

  cuerpoTabla.innerHTML = `

        <tr>

            <td
                colspan="8"
                class="text-center py-5">

                <div
                    class="spinner-border text-primary mb-3"
                    role="status">

                    <span class="visually-hidden">
                        Cargando...
                    </span>

                </div>

                <div class="text-muted">

                    Cargando ofertas y descuentos...

                </div>

            </td>

        </tr>

    `;
}

//=====================================================
// MOSTRAR ERROR
//=====================================================

function mostrarErrorOfertas(mensaje) {
  if (!cuerpoTabla) {
    return;
  }

  cuerpoTabla.innerHTML = `

        <tr>

            <td
                colspan="8"
                class="text-center py-5">

                <div class="mb-3">

                    <i
                        class="bi bi-exclamation-triangle fs-1 text-danger">
                    </i>

                </div>

                <h6 class="fw-bold mb-1">

                    Ocurrió un problema

                </h6>

                <p class="text-muted mb-3">

                    ${escapeHTML(mensaje)}

                </p>

                <button
                    type="button"
                    class="btn btn-sm btn-primary"
                    onclick="cargarOfertasDescuentos(1)">

                    <i class="bi bi-arrow-clockwise me-1"></i>

                    Reintentar

                </button>

            </td>

        </tr>

    `;
}

//=====================================================
// ESCAPAR HTML
//=====================================================

function escapeHTML(texto) {
  const div = document.createElement("div");

  div.textContent = String(texto ?? "");

  return div.innerHTML;
}

//=====================================================
// ACTUALIZAR INFORMACIÓN PAGINACIÓN
//=====================================================

function actualizarInformacionPaginacion(respuesta) {
  const total = parseInt(respuesta.total_registros, 10) || 0;

  const desde = parseInt(respuesta.desde, 10) || 0;

  const hasta = parseInt(respuesta.hasta, 10) || 0;

  //=================================================
  // TOTAL
  //=================================================

  if (elementoTotalRegistros) {
    elementoTotalRegistros.textContent =
      total.toLocaleString("es-PE") +
      (total === 1 ? " registro" : " registros");
  }

  //=================================================
  // INFORMACIÓN PAGINACIÓN
  //=================================================

  if (elementoInfoPaginacion) {
    if (total > 0) {
      elementoInfoPaginacion.textContent =
        "Mostrando " +
        desde.toLocaleString("es-PE") +
        " - " +
        hasta.toLocaleString("es-PE") +
        " de " +
        total.toLocaleString("es-PE") +
        " productos";
    } else {
      elementoInfoPaginacion.textContent = "No se encontraron productos";
    }
  }

  //=================================================
  // INFORMACIÓN OFERTAS
  //=================================================

  if (elementoInfoOfertas) {
    if (total > 0) {
      elementoInfoOfertas.textContent =
        "Productos encontrados: " + total.toLocaleString("es-PE");
    } else {
      elementoInfoOfertas.textContent = "No se encontraron productos";
    }
  }

  //=================================================
  // ELEMENTO DESDE
  //=================================================

  if (elementoDesde) {
    elementoDesde.textContent = desde.toLocaleString("es-PE");
  }

  //=================================================
  // ELEMENTO HASTA
  //=================================================

  if (elementoHasta) {
    elementoHasta.textContent = hasta.toLocaleString("es-PE");
  }
}

//=====================================================
// CONFIGURAR PAGINACIÓN
//=====================================================

function configurarEventosPaginacion() {
  if (!contenedorPaginacion) {
    return;
  }

  const enlaces = contenedorPaginacion.querySelectorAll("[data-pagina]");

  enlaces.forEach(function (enlace) {
    enlace.addEventListener("click", function (evento) {
      evento.preventDefault();

      const item = enlace.closest(".page-item");

      if (item && item.classList.contains("disabled")) {
        return;
      }

      const pagina = parseInt(enlace.dataset.pagina, 10);

      if (isNaN(pagina) || pagina < 1) {
        return;
      }

      cargarOfertasDescuentos(pagina);
    });
  });
}

//=====================================================
// REINICIAR PÁGINA Y BUSCAR
//=====================================================

function reiniciarPaginaYBuscar() {
  paginaActual = 1;

  cargarOfertasDescuentos(1);
}

//=====================================================
// CONFIGURAR EVENTOS DE FILTROS
//=====================================================

function configurarEventosFiltros() {
  //=================================================
  // OFERTA
  //=================================================

  if (selectOferta) {
    selectOferta.addEventListener("change", function () {
      console.log("Filtro Oferta:", selectOferta.value);

      reiniciarPaginaYBuscar();
    });
  }

  //=================================================
  // DESCUENTO
  //=================================================

  if (selectDescuento) {
    selectDescuento.addEventListener("change", function () {
      console.log("Filtro Descuento:", selectDescuento.value);

      reiniciarPaginaYBuscar();
    });
  }

  //=================================================
  // CATEGORÍA
  //=================================================

  if (selectCategoria) {
    selectCategoria.addEventListener("change", function () {
      console.log("Filtro Categoría:", selectCategoria.value);

      reiniciarPaginaYBuscar();
    });
  }

  //=================================================
  // MARCA
  //=================================================

  if (selectMarca) {
    selectMarca.addEventListener("change", function () {
      console.log("Filtro Marca:", selectMarca.value);

      reiniciarPaginaYBuscar();
    });
  }

  //=================================================
  // ORDENAR POR
  //=================================================

  if (selectOrden) {
    selectOrden.addEventListener("change", function () {
      console.log("============================================");

      console.log("ORDEN CAMBIADO");

      console.log("Valor:", selectOrden.value);

      console.log("============================================");

      paginaActual = 1;

      cargarOfertasDescuentos(1);
    });
  } else {
    console.error("ERROR: No se encontró el select #ordenOfertas.");
  }

  //=================================================
  // BUSCADOR
  //=================================================

  if (inputBuscar) {
    inputBuscar.addEventListener("input", function () {
      clearTimeout(temporizadorBusquedaOfertas);

      temporizadorBusquedaOfertas = setTimeout(function () {
        paginaActual = 1;

        cargarOfertasDescuentos(1);
      }, 300);
    });
  }

  //=================================================
  // BOTÓN LIMPIAR FILTROS
  //=================================================

  if (botonLimpiarFiltros) {
    botonLimpiarFiltros.addEventListener("click", function (evento) {
      evento.preventDefault();

      evento.stopPropagation();

      console.log("BOTÓN LIMPIAR FILTROS PRESIONADO");

      limpiarFiltrosOfertas();
    });
  } else {
    console.error("ERROR: No se encontró #btnResetFiltrosOfertas.");
  }

  //=================================================
  // BOTÓN ACTUALIZAR
  //=================================================

  if (botonActualizarOfertas) {
    botonActualizarOfertas.addEventListener("click", function () {
      console.log("Actualizando ofertas...");

      cargarOfertasDescuentos(paginaActual);
    });
  }
}

//=====================================================
// LIMPIAR TODOS LOS FILTROS
//=====================================================

function limpiarFiltrosOfertas() {
  console.log("============================================");

  console.log("LIMPIANDO FILTROS");

  console.log("============================================");

  //=================================================
  // CANCELAR BÚSQUEDA PENDIENTE
  //=================================================

  if (temporizadorBusquedaOfertas) {
    clearTimeout(temporizadorBusquedaOfertas);

    temporizadorBusquedaOfertas = null;
  }

  //=================================================
  // BUSCAR
  //=================================================

  if (inputBuscar) {
    inputBuscar.value = "";
  }

  //=================================================
  // OFERTA
  //=================================================

  if (selectOferta) {
    seleccionarValorPorDefecto(selectOferta, [""]);
  }

  //=================================================
  // DESCUENTO
  //=================================================

  if (selectDescuento) {
    seleccionarValorPorDefecto(selectDescuento, [""]);
  }

  //=================================================
  // CATEGORÍA
  //=================================================

  if (selectCategoria) {
    seleccionarValorPorDefecto(selectCategoria, [""]);
  }

  //=================================================
  // MARCA
  //=================================================

  if (selectMarca) {
    seleccionarValorPorDefecto(selectMarca, [""]);
  }

  //=================================================
  // ORDENAR POR
  //=================================================

  if (selectOrden) {
    //=================================================
    // En tu HTML:
    //
    // <option value="recientes">
    //     Más recientes
    // </option>
    //
    // Por lo tanto, este es el valor correcto.
    //=================================================

    seleccionarValorPorDefecto(selectOrden, [
      "recientes",
      "reciente",
      "fecha_desc",
      "",
    ]);

    console.log("Orden restaurado a:", selectOrden.value);
  }

  //=================================================
  // REINICIAR PÁGINA
  //=================================================

  paginaActual = 1;

  //=================================================
  // RECARGAR
  //=================================================

  cargarOfertasDescuentos(1);
}

//=====================================================
// SELECCIONAR VALOR POR DEFECTO
//=====================================================

function seleccionarValorPorDefecto(select, valores) {
  if (!select) {
    return false;
  }

  //=================================================
  // BUSCAR VALOR
  //=================================================

  for (const valor of valores) {
    const valorBuscado = String(valor).trim().toLowerCase();

    const opcion = Array.from(select.options).find(function (option) {
      return String(option.value).trim().toLowerCase() === valorBuscado;
    });

    if (opcion) {
      select.value = opcion.value;

      return true;
    }
  }

  //=================================================
  // SI NO ENCUENTRA
  //=================================================

  if (select.options.length > 0) {
    select.selectedIndex = 0;

    return true;
  }

  return false;
}

//=====================================================
// VER DETALLES
//=====================================================

function verDetallesOferta(idProducto) {
  if (!idProducto) {
    return;
  }

  console.log("Ver detalles del producto:", idProducto);
}

//=====================================================
// EDITAR OFERTA / DESCUENTO
//=====================================================

function editarOfertaDescuento(idProducto) {
  //=================================================
  // VALIDAR ID
  //=================================================

  idProducto = parseInt(idProducto, 10);

  if (isNaN(idProducto) || idProducto <= 0) {
    console.error("ID de producto inválido:", idProducto);

    return;
  }

  console.log("============================================");

  console.log("EDITAR OFERTA / DESCUENTO");

  console.log("ID Producto:", idProducto);

  console.log("============================================");

  //=================================================
  // OBTENER MODAL
  //=================================================

  const elementoModal = document.getElementById("modalEditarOfertaDescuento");

  if (!elementoModal) {
    console.error("No se encontró el modal #modalEditarOfertaDescuento.");

    return;
  }

  //=================================================
  // OBTENER ELEMENTOS
  //=================================================

  const inputId = document.getElementById("editarOfertaIdProducto");

  const nombreProducto = document.getElementById("editarOfertaNombreProducto");

  const codigoProducto = document.getElementById("editarOfertaCodigoProducto");

  const stockProducto = document.getElementById("editarOfertaStock");

  const precioProducto = document.getElementById("editarOfertaPrecio");

  const precioAnterior = document.getElementById("editarOfertaPrecioAnterior");

  const ofertaActiva = document.getElementById("editarOfertaActiva");

  const descuento = document.getElementById("editarOfertaDescuento");

  const precioFinal = document.getElementById("editarOfertaPrecioFinal");

  const ahorro = document.getElementById("editarOfertaAhorro");

  const categoria = document.getElementById("editarOfertaCategoria");

  const marca = document.getElementById("editarOfertaMarca");

  const sucursal = document.getElementById("editarOfertaSucursal");

  //=================================================
  // GUARDAR ID
  //=================================================

  if (inputId) {
    inputId.value = String(idProducto);
  }

  //=================================================
  // ESTADO INICIAL DEL MODAL
  //=================================================

  if (nombreProducto) {
    nombreProducto.textContent = "Cargando producto...";
  }

  if (codigoProducto) {
    codigoProducto.textContent = "—";
  }

  if (stockProducto) {
    stockProducto.textContent = "0";
  }

  if (precioProducto) {
    precioProducto.value = "";
  }

  if (precioAnterior) {
    precioAnterior.value = "";
  }

  if (ofertaActiva) {
    ofertaActiva.checked = false;
  }

  if (descuento) {
    descuento.value = "0";
  }

  if (precioFinal) {
    precioFinal.textContent = "S/ 0.00";
  }

  if (ahorro) {
    ahorro.textContent = "Ahorro: S/ 0.00";
  }

  if (categoria) {
    categoria.textContent = "—";
  }

  if (marca) {
    marca.textContent = "—";
  }

  if (sucursal) {
    sucursal.textContent = "—";
  }

  //=================================================
  // MOSTRAR MODAL
  //=================================================

  const modalBootstrap = bootstrap.Modal.getOrCreateInstance(elementoModal);

  modalBootstrap.show();

  //=================================================
  // CARGAR INFORMACIÓN
  //=================================================

  const url =
    "ajax/obtener_oferta_descuento.php?idProducto=" +
    encodeURIComponent(idProducto);

  console.log("URL obtener oferta:", url);

  //=================================================
  // AJAX
  //=================================================

  const xhr = new XMLHttpRequest();

  xhr.open("GET", url, true);

  xhr.responseType = "json";

  //=================================================
  // RESPUESTA
  //=================================================

  xhr.onreadystatechange = function () {
    if (xhr.readyState !== 4) {
      return;
    }

    //=================================================
    // ERROR HTTP
    //=================================================

    if (xhr.status < 200 || xhr.status >= 300) {
      console.error("Error HTTP:", xhr.status);

      mostrarAlertaEditarOferta(
        "danger",
        "No se pudo obtener la información del producto.",
      );

      return;
    }

    //=================================================
    // RESPUESTA
    //=================================================

    let respuesta = xhr.response;

    //=================================================
    // PARSEAR MANUALMENTE
    //=================================================

    if (typeof respuesta === "string") {
      try {
        respuesta = JSON.parse(respuesta);
      } catch (error) {
        console.error("JSON inválido:", respuesta);

        mostrarAlertaEditarOferta(
          "danger",
          "El servidor devolvió una respuesta inválida.",
        );

        return;
      }
    }

    //=================================================
    // VALIDAR
    //=================================================

    if (!respuesta || respuesta.success !== true) {
      console.error("Respuesta obtener oferta:", respuesta);

      mostrarAlertaEditarOferta(
        "danger",
        respuesta?.mensaje || "No se pudo obtener la información del producto.",
      );

      return;
    }

    //=================================================
    // DATOS
    //=================================================

    const producto = respuesta.datos;

    if (!producto || typeof producto !== "object") {
      mostrarAlertaEditarOferta(
        "danger",
        "No se recibió información del producto.",
      );

      return;
    }

    //=================================================
    // LLENAR MODAL
    //=================================================

    llenarModalEditarOferta(producto);
  };

  //=================================================
  // ERROR DE RED
  //=================================================

  xhr.onerror = function () {
    console.error("Error de conexión.");

    mostrarAlertaEditarOferta(
      "danger",
      "Ocurrió un error de conexión con el servidor.",
    );
  };

  //=================================================
  // TIMEOUT
  //=================================================

  xhr.timeout = 30000;

  xhr.ontimeout = function () {
    mostrarAlertaEditarOferta("danger", "La solicitud tardó demasiado tiempo.");
  };

  //=================================================
  // ENVIAR
  //=================================================

  xhr.send();
}
//=====================================================
// MOSTRAR ALERTA MODAL
//=====================================================

function mostrarAlertaEditarOferta(tipo, mensaje) {
  const alerta = document.getElementById("alertaEditarOfertaDescuento");

  const icono = document.getElementById("iconoAlertaEditarOferta");

  const texto = document.getElementById("textoAlertaEditarOferta");

  if (!alerta) {
    return;
  }

  //=================================================
  // CLASES
  //=================================================

  alerta.classList.remove(
    "d-none",
    "alert-success",
    "alert-danger",
    "alert-warning",
    "alert-info",
  );

  alerta.classList.add("alert-" + tipo);

  //=================================================
  // ICONO
  //=================================================

  if (icono) {
    icono.className = "bi me-2 fs-5";

    if (tipo === "success") {
      icono.classList.add("bi-check-circle-fill");
    } else if (tipo === "danger") {
      icono.classList.add("bi-exclamation-triangle-fill");
    } else if (tipo === "warning") {
      icono.classList.add("bi-exclamation-circle-fill");
    } else {
      icono.classList.add("bi-info-circle-fill");
    }
  }

  //=================================================
  // TEXTO
  //=================================================

  if (texto) {
    texto.textContent = String(mensaje ?? "");
  }
}

//=====================================================
// OCULTAR ALERTA
//=====================================================

function ocultarAlertaEditarOferta() {
  const alerta = document.getElementById("alertaEditarOfertaDescuento");

  if (!alerta) {
    return;
  }

  alerta.classList.add("d-none");

  alerta.classList.remove(
    "alert-success",
    "alert-danger",
    "alert-warning",
    "alert-info",
  );
}
//=====================================================
// LLENAR MODAL EDITAR OFERTA
//=====================================================

function llenarModalEditarOferta(producto) {
  console.log("============================================");

  console.log("DATOS PRODUCTO PARA EDITAR");

  console.log(producto);

  console.log("============================================");

  //=================================================
  // ELEMENTOS
  //=================================================

  const inputId = document.getElementById("editarOfertaIdProducto");

  const nombre = document.getElementById("editarOfertaNombreProducto");

  const codigo = document.getElementById("editarOfertaCodigoProducto");

  const stock = document.getElementById("editarOfertaStock");

  const precio = document.getElementById("editarOfertaPrecio");

  const precioAnterior = document.getElementById("editarOfertaPrecioAnterior");

  const oferta = document.getElementById("editarOfertaActiva");

  const descuento = document.getElementById("editarOfertaDescuento");

  const categoria = document.getElementById("editarOfertaCategoria");

  const marca = document.getElementById("editarOfertaMarca");

  const sucursal = document.getElementById("editarOfertaSucursal");

  //=================================================
  // ID
  //=================================================

  if (inputId) {
    inputId.value = producto.idProducto ?? "";
  }

  //=================================================
  // NOMBRE
  //=================================================

  if (nombre) {
    nombre.textContent = producto.nombre || "Sin nombre";
  }

  //=================================================
  // CÓDIGO
  //=================================================

  if (codigo) {
    codigo.textContent = producto.codigo || "—";
  }

  //=================================================
  // STOCK
  //=================================================

  if (stock) {
    stock.textContent = Number(producto.stock || 0).toLocaleString("es-PE");
  }

  //=================================================
  // PRECIO
  //=================================================

  const precioNormal = Number(producto.precio || 0);

  if (precio) {
    precio.value = precioNormal.toFixed(2);
  }

  //=================================================
  // PRECIO ANTERIOR
  //=================================================

  const valorPrecioAnterior = Number(producto.precio_anterior || 0);

  if (precioAnterior) {
    precioAnterior.value =
      valorPrecioAnterior > 0 ? valorPrecioAnterior.toFixed(2) : "";
  }

  //=================================================
  // OFERTA
  //=================================================

  if (oferta) {
    oferta.checked = Number(producto.oferta || 0) === 1;
  }

  //=================================================
  // DESCUENTO
  //=================================================

  const valorDescuento = Number(producto.descuento || 0);

  if (descuento) {
    descuento.value = valorDescuento.toFixed(2);
  }

  //=================================================
  // CATEGORÍA
  //=================================================

  if (categoria) {
    categoria.textContent = producto.categoria || "Sin categoría";
  }

  //=================================================
  // MARCA
  //=================================================

  if (marca) {
    marca.textContent = producto.marca || "Sin marca";
  }

  //=================================================
  // SUCURSAL
  //=================================================

  if (sucursal) {
    sucursal.textContent = producto.sucursal || "Sin sucursal";
  }

  //=================================================
  // CALCULAR PRECIO FINAL
  //=================================================

  calcularPrecioOferta();

  //=================================================
  // OCULTAR ALERTA
  //=================================================

  ocultarAlertaEditarOferta();

  console.log("Modal de edición cargado correctamente.");
}
//=====================================================
// CALCULAR PRECIO OFERTA
//=====================================================

function calcularPrecioOferta() {
  const precio = document.getElementById("editarOfertaPrecio");

  const descuento = document.getElementById("editarOfertaDescuento");

  const precioFinal = document.getElementById("editarOfertaPrecioFinal");

  const ahorro = document.getElementById("editarOfertaAhorro");

  if (!precio || !descuento || !precioFinal || !ahorro) {
    return;
  }

  //=================================================
  // VALORES
  //=================================================

  let precioNormal = parseFloat(precio.value);

  let porcentajeDescuento = parseFloat(descuento.value);

  //=================================================
  // NORMALIZAR
  //=================================================

  if (isNaN(precioNormal) || precioNormal < 0) {
    precioNormal = 0;
  }

  if (isNaN(porcentajeDescuento) || porcentajeDescuento < 0) {
    porcentajeDescuento = 0;
  }

  if (porcentajeDescuento > 100) {
    porcentajeDescuento = 100;
  }

  //=================================================
  // CALCULAR
  //=================================================

  const montoDescuento = precioNormal * (porcentajeDescuento / 100);

  const precioCalculado = precioNormal - montoDescuento;

  //=================================================
  // MOSTRAR
  //=================================================

  precioFinal.textContent = "S/ " + precioCalculado.toFixed(2);

  ahorro.textContent = "Ahorro: S/ " + montoDescuento.toFixed(2);
}
//=====================================================
// CONFIGURAR EVENTOS MODAL EDITAR OFERTA
//=====================================================

function configurarEventosModalEditarOferta() {
  const descuento = document.getElementById("editarOfertaDescuento");

  const precio = document.getElementById("editarOfertaPrecio");

  const botonGuardar = document.getElementById("btnGuardarCambiosOferta");

  if (descuento) {
    descuento.addEventListener("input", function () {
      calcularPrecioOferta();
    });
  }

  if (precio) {
    precio.addEventListener("input", function () {
      calcularPrecioOferta();
    });
  }

  //=================================================
  // GUARDAR CAMBIOS
  //=================================================

  if (botonGuardar) {
    botonGuardar.addEventListener("click", function (evento) {
      evento.preventDefault();

      guardarCambiosOfertaDescuento();
    });
  } else {
    console.error("No se encontró #btnGuardarCambiosOferta");
  }
}
//=====================================================
// GUARDAR CAMBIOS OFERTA / DESCUENTO
//=====================================================

function guardarCambiosOfertaDescuento() {
  //=================================================
  // ELEMENTOS
  //=================================================

  const inputId = document.getElementById("editarOfertaIdProducto");

  const precio = document.getElementById("editarOfertaPrecio");

  const precioAnterior = document.getElementById("editarOfertaPrecioAnterior");

  const oferta = document.getElementById("editarOfertaActiva");

  const descuento = document.getElementById("editarOfertaDescuento");

  //=================================================
  // VALIDAR ELEMENTOS
  //=================================================

  if (!inputId) {
    console.error("No se encontró #editarOfertaIdProducto");

    return;
  }

  if (!precio) {
    console.error("No se encontró #editarOfertaPrecio");

    return;
  }

  if (!precioAnterior) {
    console.error("No se encontró #editarOfertaPrecioAnterior");

    return;
  }

  if (!oferta) {
    console.error("No se encontró #editarOfertaActiva");

    return;
  }

  if (!descuento) {
    console.error("No se encontró #editarOfertaDescuento");

    return;
  }

  //=================================================
  // OBTENER VALORES
  //=================================================

  const idProducto = parseInt(inputId.value, 10);

  let valorPrecio = parseFloat(precio.value);

  let valorPrecioAnterior = parseFloat(precioAnterior.value);

  let valorDescuento = parseFloat(descuento.value);

  const ofertaActiva = oferta.checked ? 1 : 0;

  //=================================================
  // NORMALIZAR
  //=================================================

  if (isNaN(valorPrecio)) {
    valorPrecio = 0;
  }

  if (isNaN(valorPrecioAnterior)) {
    valorPrecioAnterior = 0;
  }

  if (isNaN(valorDescuento)) {
    valorDescuento = 0;
  }

  //=================================================
  // VALIDAR ID
  //=================================================

  if (isNaN(idProducto) || idProducto <= 0) {
    mostrarAlertaEditarOferta(
      "danger",
      "El producto seleccionado no es válido.",
    );

    return;
  }

  //=================================================
  // VALIDAR PRECIO
  //=================================================

  if (valorPrecio < 0) {
    mostrarAlertaEditarOferta("danger", "El precio no puede ser negativo.");

    precio.focus();

    return;
  }

  //=================================================
  // VALIDAR PRECIO ANTERIOR
  //=================================================

  if (valorPrecioAnterior < 0) {
    mostrarAlertaEditarOferta(
      "danger",
      "El precio anterior no puede ser negativo.",
    );

    precioAnterior.focus();

    return;
  }

  //=================================================
  // VALIDAR DESCUENTO
  //=================================================

  if (valorDescuento < 0 || valorDescuento > 100) {
    mostrarAlertaEditarOferta(
      "danger",
      "El descuento debe estar entre 0% y 100%.",
    );

    descuento.focus();

    return;
  }

  //=================================================
  // VALIDAR OFERTA
  //=================================================

  if (ofertaActiva === 1 && valorDescuento <= 0) {
    mostrarAlertaEditarOferta(
      "warning",
      "Para activar la oferta debes ingresar un descuento mayor a 0%.",
    );

    descuento.focus();

    return;
  }

  //=================================================
  // SI NO HAY OFERTA
  //=================================================

  if (ofertaActiva === 0) {
    valorDescuento = 0;
  }

  //=================================================
  // CALCULAR PRECIO FINAL
  //=================================================

  const montoDescuento = valorPrecio * (valorDescuento / 100);

  const precioFinal = valorPrecio - montoDescuento;

  //=================================================
  // CONFIRMAR EN CONSOLA
  //=================================================

  console.log("============================================");

  console.log("GUARDAR OFERTA / DESCUENTO");

  console.log("============================================");

  console.log({
    idProducto: idProducto,
    precio: valorPrecio,
    precioAnterior: valorPrecioAnterior,
    descuento: valorDescuento,
    oferta: ofertaActiva,
    montoDescuento: montoDescuento,
    precioFinal: precioFinal,
  });

  //=================================================
  // OBTENER BOTÓN
  //=================================================

  const botonGuardar = document.getElementById("btnGuardarCambiosOferta");

  //=================================================
  // DESHABILITAR BOTÓN
  //=================================================

  if (botonGuardar) {
    botonGuardar.disabled = true;

    botonGuardar.dataset.textoOriginal = botonGuardar.innerHTML;

    botonGuardar.innerHTML = `
      <span
        class="spinner-border spinner-border-sm me-2"
        role="status"
        aria-hidden="true">
      </span>

      Guardando...
    `;
  }

  //=================================================
  // FORM DATA
  //=================================================

  const datos = new FormData();

  datos.append("idProducto", String(idProducto));

  datos.append("precio", valorPrecio.toFixed(2));

  datos.append("precio_anterior", valorPrecioAnterior.toFixed(2));

  datos.append("descuento", valorDescuento.toFixed(2));

  datos.append("oferta", String(ofertaActiva));

  //=================================================
  // AJAX
  //=================================================

  const xhr = new XMLHttpRequest();

  xhr.open("POST", "ajax/actualizar_oferta_descuento.php", true);

  xhr.responseType = "json";

  //=================================================
  // RESPUESTA
  //=================================================

  xhr.onreadystatechange = function () {
    if (xhr.readyState !== 4) {
      return;
    }

    //=================================================
    // RESTAURAR BOTÓN
    //=================================================

    if (botonGuardar) {
      botonGuardar.disabled = false;

      if (botonGuardar.dataset.textoOriginal) {
        botonGuardar.innerHTML = botonGuardar.dataset.textoOriginal;
      }
    }

    //=================================================
    // ERROR HTTP
    //=================================================

    if (xhr.status < 200 || xhr.status >= 300) {
      console.error("Error HTTP:", xhr.status);

      mostrarAlertaEditarOferta("danger", "No se pudo actualizar la oferta.");

      return;
    }

    //=================================================
    // RESPUESTA
    //=================================================

    let respuesta = xhr.response;

    //=================================================
    // PARSEAR MANUALMENTE
    //=================================================

    if (typeof respuesta === "string") {
      try {
        respuesta = JSON.parse(respuesta);
      } catch (error) {
        console.error("JSON inválido:", respuesta);

        mostrarAlertaEditarOferta(
          "danger",
          "El servidor devolvió una respuesta inválida.",
        );

        return;
      }
    }

    //=================================================
    // VALIDAR RESPUESTA
    //=================================================

    if (!respuesta || typeof respuesta !== "object") {
      mostrarAlertaEditarOferta(
        "danger",
        "No se recibió una respuesta válida del servidor.",
      );

      return;
    }

    //=================================================
    // ERROR BACKEND
    //=================================================

    if (respuesta.success !== true) {
      console.error("Error actualizar oferta:", respuesta);

      mostrarAlertaEditarOferta(
        "danger",
        respuesta.mensaje || "No se pudo actualizar la oferta.",
      );

      return;
    }

    //=================================================
    // MOSTRAR ÉXITO
    //=================================================

    mostrarAlertaEditarOferta(
      "success",
      respuesta.mensaje || "Los cambios se guardaron correctamente.",
    );

    //=================================================
    // ACTUALIZAR PRECIO FINAL DEL MODAL
    //=================================================

    const elementoPrecioFinal = document.getElementById(
      "editarOfertaPrecioFinal",
    );

    const elementoAhorro = document.getElementById("editarOfertaAhorro");

    if (elementoPrecioFinal) {
      elementoPrecioFinal.textContent =
        "S/ " + Number(respuesta.datos?.precio_final || precioFinal).toFixed(2);
    }

    if (elementoAhorro) {
      elementoAhorro.textContent =
        "Ahorro: S/ " +
        Number(respuesta.datos?.monto_descuento || montoDescuento).toFixed(2);
    }

    //=================================================
    // RECARGAR TABLA
    //=================================================

    setTimeout(function () {
      //=================================================
      // OCULTAR MODAL
      //=================================================

      const elementoModal = document.getElementById(
        "modalEditarOfertaDescuento",
      );

      if (elementoModal) {
        const modalBootstrap = bootstrap.Modal.getInstance(elementoModal);

        if (modalBootstrap) {
          modalBootstrap.hide();
        }
      }

      //=================================================
      // RECARGAR KPI
      //=================================================

      cargarKPIsOfertas();

      //=================================================
      // RECARGAR LISTADO
      //=================================================

      cargarOfertasDescuentos(paginaActual);
    }, 1000);
  };

  //=================================================
  // ERROR DE RED
  //=================================================

  xhr.onerror = function () {
    if (botonGuardar) {
      botonGuardar.disabled = false;

      if (botonGuardar.dataset.textoOriginal) {
        botonGuardar.innerHTML = botonGuardar.dataset.textoOriginal;
      }
    }

    mostrarAlertaEditarOferta(
      "danger",
      "Ocurrió un error de conexión con el servidor.",
    );
  };

  //=================================================
  // TIMEOUT
  //=================================================

  xhr.timeout = 30000;

  xhr.ontimeout = function () {
    if (botonGuardar) {
      botonGuardar.disabled = false;

      if (botonGuardar.dataset.textoOriginal) {
        botonGuardar.innerHTML = botonGuardar.dataset.textoOriginal;
      }
    }

    mostrarAlertaEditarOferta("danger", "La solicitud tardó demasiado tiempo.");
  };

  //=================================================
  // ENVIAR
  //=================================================

  xhr.send(datos);
}

//=====================================================
// ACTIVAR OFERTA
//=====================================================

function activarOferta(idProducto) {
  if (!idProducto) {
    mostrarAlertaOferta("error", "El producto seleccionado no es válido.");

    return;
  }

  confirmarCambioOferta(idProducto, 1);
}

//=====================================================
// DESACTIVAR OFERTA
//=====================================================

function desactivarOferta(idProducto) {
  if (!idProducto) {
    mostrarAlertaOferta("error", "El producto seleccionado no es válido.");

    return;
  }

  confirmarCambioOferta(idProducto, 0);
}

//=====================================================
// CONFIRMAR CAMBIO DE OFERTA
//=====================================================

function confirmarCambioOferta(idProducto, nuevoEstado) {
  const activar = nuevoEstado === 1;

  const titulo = activar ? "¿Activar oferta?" : "¿Desactivar oferta?";

  const mensaje = activar
    ? "El producto quedará marcado como oferta activa."
    : "El producto dejará de estar disponible como oferta.";

  /*
    -----------------------------------------------------
    SI EL PROYECTO UTILIZA SWEETALERT2
    -----------------------------------------------------
    */

  if (typeof Swal !== "undefined") {
    Swal.fire({
      title: titulo,

      text: mensaje,

      icon: activar ? "question" : "warning",

      showCancelButton: true,

      confirmButtonText: activar ? "Sí, activar" : "Sí, desactivar",

      cancelButtonText: "Cancelar",

      reverseButtons: true,
    }).then(function (resultado) {
      if (resultado.isConfirmed) {
        cambiarEstadoOferta(idProducto, nuevoEstado);
      }
    });

    return;
  }

  /*
    -----------------------------------------------------
    FALLBACK
    -----------------------------------------------------
    */

  if (confirm(titulo + "\n\n" + mensaje)) {
    cambiarEstadoOferta(idProducto, nuevoEstado);
  }
}

//=====================================================
// CAMBIAR ESTADO OFERTA
//=====================================================

function cambiarEstadoOferta(idProducto, nuevoEstado) {
  const datos = new FormData();

  datos.append("idProducto", idProducto);

  datos.append("oferta", nuevoEstado);

  /*
    -----------------------------------------------------
    MOSTRAR CARGANDO
    -----------------------------------------------------
    */

  if (typeof Swal !== "undefined") {
    Swal.fire({
      title:
        nuevoEstado === 1 ? "Activando oferta..." : "Desactivando oferta...",

      text: "Por favor espera.",

      allowOutsideClick: false,

      allowEscapeKey: false,

      didOpen: function () {
        Swal.showLoading();
      },
    });
  }

  /*
    -----------------------------------------------------
    AJAX
    -----------------------------------------------------
    */

  fetch("ajax/cambiar_estado_oferta.php", {
    method: "POST",

    body: datos,

    cache: "no-cache",
  })
    .then(function (respuesta) {
      if (!respuesta.ok) {
        throw new Error("Error HTTP " + respuesta.status);
      }

      return respuesta.json();
    })

    .then(function (data) {
      if (!data || !data.success) {
        throw new Error(
          data && data.mensaje
            ? data.mensaje
            : "No se pudo actualizar la oferta.",
        );
      }

      //=================================================
      // ACTUALIZAR KPI
      //=================================================

      cargarKPIsOfertas();

      //=================================================
      // RECARGAR LISTADO
      //=================================================

      if (typeof Swal !== "undefined") {
        Swal.fire({
          icon: "success",

          title: "¡Actualizado!",

          text: data.mensaje || "El estado de la oferta fue actualizado.",

          confirmButtonText: "Aceptar",
        }).then(function () {
          recargarListaOfertas();
        });
      } else {
        alert(data.mensaje || "El estado de la oferta fue actualizado.");

        recargarListaOfertas();
      }
    })

    .catch(function (error) {
      console.error("Error cambiar estado oferta:", error);

      if (typeof Swal !== "undefined") {
        Swal.fire({
          icon: "error",

          title: "Error",

          text: error.message || "Ocurrió un error al actualizar la oferta.",

          confirmButtonText: "Aceptar",
        });
      } else {
        alert(error.message || "Ocurrió un error al actualizar la oferta.");
      }
    });
}

//=====================================================
// RECARGAR LISTA DE OFERTAS
//=====================================================

function recargarListaOfertas() {
  /*
    -----------------------------------------------------
    BUSCAR FUNCIÓN EXISTENTE
    -----------------------------------------------------
    */

  if (typeof cargarOfertas === "function") {
    cargarOfertas();

    return;
  }

  if (typeof cargarListaOfertas === "function") {
    cargarListaOfertas();

    return;
  }

  if (typeof obtenerOfertas === "function") {
    obtenerOfertas();

    return;
  }

  /*
    -----------------------------------------------------
    FALLBACK
    -----------------------------------------------------
    */

  location.reload();
}

//=====================================================
// ALERTA SIMPLE
//=====================================================

function mostrarAlertaOferta(tipo, mensaje) {
  if (typeof Swal !== "undefined") {
    Swal.fire({
      icon: tipo,

      title: tipo === "error" ? "Error" : "Información",

      text: mensaje,

      confirmButtonText: "Aceptar",
    });

    return;
  }

  alert(mensaje);
}
//=====================================================
// INICIALIZACIÓN
//=====================================================

document.addEventListener("DOMContentLoaded", function () {
  console.log("============================================");

  console.log("Inicializando Ofertas y Descuentos...");

  console.log("============================================");

  //=================================================
  // OBTENER ELEMENTOS
  //=================================================

  inicializarElementos();

  //=================================================
  // CONFIGURAR MODAL
  //=================================================

  configurarEventosModalEditarOferta();

  //=================================================
  // CONFIGURAR FILTROS
  //=================================================

  configurarEventosFiltros();

  //=================================================
  // CARGAR KPI
  //=================================================

  cargarKPIsOfertas();

  //=================================================
  // CARGAR TABLA
  //=================================================

  cargarOfertasDescuentos(1);
});

//=====================================================
// FUNCIONES GLOBALES
//=====================================================

window.cargarOfertasDescuentos = cargarOfertasDescuentos;

window.limpiarFiltrosOfertas = limpiarFiltrosOfertas;

window.limpiarFiltros = limpiarFiltrosOfertas;

window.reiniciarPaginaYBuscar = reiniciarPaginaYBuscar;

window.verDetallesOferta = verDetallesOferta;

window.editarOfertaDescuento = editarOfertaDescuento;

window.activarOferta = activarOferta;

window.desactivarOferta = desactivarOferta;
window.calcularPrecioOferta = calcularPrecioOferta;
window.guardarCambiosOfertaDescuento = guardarCambiosOfertaDescuento;
