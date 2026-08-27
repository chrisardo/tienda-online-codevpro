//======================================================
// CoDevPro Technology
// Archivo: js/adm_lista_productos.js
// Módulo: Lista / Detalle de Productos
// Sistema: Inventa
//======================================================

"use strict";

document.addEventListener("DOMContentLoaded", function () {
  //==================================================
  // VARIABLES
  //==================================================

  let paginaActual = 1;

  let idProductoEliminar = 0;

  let idsEliminarMasivo = [];

  let tipoExportacion = "";

  //==================================================
  // ELEMENTOS PRINCIPALES
  //==================================================

  const txtBuscar = document.getElementById("buscarProducto");

  const filtroCategoria = document.getElementById("filtroCategoria");

  const filtroMarca = document.getElementById("filtroMarca");

  const filtroProveedor = document.getElementById("filtroProveedor");

  const filtroTipo = document.getElementById("filtroTipo");

  //==================================================
  // FUNCIONES AUXILIARES
  //==================================================

  function elementoExiste(id) {
    return document.getElementById(id) !== null;
  }

  //==================================================
  // MODAL EXPORTAR
  //==================================================

  let modalExportar = null;

  const elementoModalExportar = document.getElementById("modalExportar");

  if (elementoModalExportar && typeof bootstrap !== "undefined") {
    modalExportar = bootstrap.Modal.getOrCreateInstance(elementoModalExportar);
  }

  //==================================================
  // BOTÓN EXCEL
  //==================================================

  const btnModalExcel = document.getElementById("btnModalExcel");

  if (btnModalExcel) {
    btnModalExcel.addEventListener("click", function () {
      tipoExportacion = "excel";

      const tipo = document.getElementById("tipoExportacion");

      if (tipo) {
        tipo.value = "excel";
      }

      if (modalExportar) {
        modalExportar.show();
      }
    });
  }

  //==================================================
  // BOTÓN PDF
  //==================================================

  const btnModalPDF = document.getElementById("btnModalPDF");

  if (btnModalPDF) {
    btnModalPDF.addEventListener("click", function () {
      tipoExportacion = "pdf";

      const tipo = document.getElementById("tipoExportacion");

      if (tipo) {
        tipo.value = "pdf";
      }

      if (modalExportar) {
        modalExportar.show();
      }
    });
  }

  //==================================================
  // CONFIRMAR EXPORTACIÓN
  //==================================================

  const btnConfirmarExportacion = document.getElementById(
    "btnConfirmarExportacion",
  );

  if (btnConfirmarExportacion) {
    btnConfirmarExportacion.addEventListener("click", function () {
      const radioSeleccionado = document.querySelector(
        'input[name="exportScope"]:checked',
      );

      if (!radioSeleccionado) {
        mostrarAlerta("Seleccione qué desea exportar.", "warning");

        return;
      }

      const scope = radioSeleccionado.value;

      const opciones = {
        codigo: document.getElementById("expCodigo")?.checked || false,

        nombre: document.getElementById("expNombre")?.checked || false,

        tipo: document.getElementById("expTipo")?.checked || false,

        categoria: document.getElementById("expCategoria")?.checked || false,

        marca: document.getElementById("expMarca")?.checked || false,

        proveedor: document.getElementById("expProveedor")?.checked || false,

        sucursal: document.getElementById("expSucursal")?.checked || false,

        precio: document.getElementById("expPrecio")?.checked || false,

        precioAnterior:
          document.getElementById("expPrecioAnterior")?.checked || false,

        costoCompra:
          document.getElementById("expCostoCompra")?.checked || false,

        stock: document.getElementById("expStock")?.checked || false,

        vendidos: document.getElementById("expVendidos")?.checked || false,

        oferta: document.getElementById("expOferta")?.checked || false,

        destacado: document.getElementById("expDestacado")?.checked || false,

        nuevo: document.getElementById("expNuevo")?.checked || false,

        descuento: document.getElementById("expDescuento")?.checked || false,

        envioGratis:
          document.getElementById("expEnvioGratis")?.checked || false,

        fechaRegistro:
          document.getElementById("expFechaRegistro")?.checked || false,

        fechaActualizado:
          document.getElementById("expFechaActualizado")?.checked || false,

        descripcion:
          document.getElementById("expDescripcion")?.checked || false,
      };

      if (modalExportar) {
        modalExportar.hide();
      }

      if (tipoExportacion === "excel") {
        exportarExcel(scope, opciones);
      } else {
        exportarPDF(scope, opciones);
      }
    });
  }

  //==================================================
  // RESUMEN EXPORTACIÓN
  //==================================================

  document
    .querySelectorAll('input[name="exportScope"]')
    .forEach(function (item) {
      item.addEventListener("change", function () {
        const resumen = document.getElementById("resumenExportacion");

        if (!resumen) {
          return;
        }

        const label = item.nextElementSibling;

        const texto = label ? label.innerText : item.value;

        resumen.innerHTML = `Se exportarán: <b>${texto}</b>`;
      });
    });

  //==================================================
  // PAGINACIÓN
  //==================================================

  document.addEventListener("click", function (e) {
    const boton = e.target.closest(".btn-pagina");

    if (!boton) {
      return;
    }

    paginaActual = parseInt(boton.dataset.pagina, 10) || 1;

    cargarProductos();
  });

  //==================================================
  // CARGAR PRODUCTOS
  //==================================================

  function cargarProductos() {
    /*
     * Si no estamos en la lista de productos,
     * no hacemos la petición.
     */

    if (!elementoExiste("tablaProductos")) {
      return;
    }

    const buscar = txtBuscar?.value.trim() || "";

    const categoria = filtroCategoria?.value || "";

    const marca = filtroMarca?.value || "";

    const proveedor = filtroProveedor?.value || "";

    const tipo = filtroTipo?.value || "";

    mostrarLoader();

    fetch("ajax/obtener_productos_admin.php", {
      method: "POST",

      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },

      body: new URLSearchParams({
        buscar,
        categoria,
        marca,
        proveedor,
        tipo,
        pagina: paginaActual,
      }),
    })
      .then(function (response) {
        if (!response.ok) {
          throw new Error("Error HTTP " + response.status);
        }

        return response.json();
      })

      .then(function (data) {
        ocultarLoader();

        if (!data.estado) {
          mostrarAlerta(data.mensaje || "Error al cargar productos.", "danger");

          return;
        }

        //==========================================
        // TABLA
        //==========================================

        const tabla = document.getElementById("tablaProductos");

        if (tabla) {
          tabla.innerHTML = data.tabla || "";
        }

        //==========================================
        // INFORMACIÓN REGISTROS
        //==========================================

        const info = document.getElementById("infoRegistros");

        if (info) {
          info.innerHTML = `Mostrando ${data.totalRegistros ?? 0} registros`;
        }

        //==========================================
        // PAGINACIÓN
        //==========================================

        const paginacion = document.getElementById("paginacionProductos");

        if (paginacion) {
          paginacion.innerHTML = data.paginacion || "";
        }

        //==========================================
        // KPIs
        //==========================================

        actualizarKPIs(data.kpis);

        //==========================================
        // TOP VENDIDOS
        //==========================================

        if (data.topVendidos) {
          const top = document.getElementById("topVendidos");

          if (top) {
            top.innerHTML = data.topVendidos;
          }
        }

        //==========================================
        // TOP FAVORITOS
        //==========================================

        if (data.topFavoritos) {
          const favoritos = document.getElementById("topFavoritos");

          if (favoritos) {
            favoritos.innerHTML = data.topFavoritos;
          }
        }

        //==========================================
        // STOCK CRÍTICO
        //==========================================

        if (data.stockCritico) {
          const stock = document.getElementById("stockCritico");

          if (stock) {
            stock.innerHTML = data.stockCritico;
          }
        }
      })

      .catch(function (error) {
        ocultarLoader();

        console.error("Error cargarProductos:", error);

        mostrarAlerta("Error al obtener productos.", "danger");
      });
  }

  //==================================================
  // HACER GLOBAL
  //==================================================

  window.cargarProductos = cargarProductos;

  //==================================================
  // BUSCADOR
  //==================================================

  if (txtBuscar) {
    let timeout = null;

    txtBuscar.addEventListener("keyup", function () {
      clearTimeout(timeout);

      timeout = setTimeout(function () {
        paginaActual = 1;

        cargarProductos();
      }, 300);
    });
  }

  //==================================================
  // FILTROS
  //==================================================

  [filtroCategoria, filtroMarca, filtroProveedor, filtroTipo].forEach(
    function (elemento) {
      if (!elemento) {
        return;
      }

      elemento.addEventListener("change", function () {
        paginaActual = 1;

        cargarProductos();
      });
    },
  );

  //==================================================
  // RESTABLECER FILTROS
  //==================================================

  const btnRestablecerFiltros = document.getElementById(
    "btnRestablecerFiltros",
  );

  if (btnRestablecerFiltros) {
    btnRestablecerFiltros.addEventListener("click", function () {
      if (txtBuscar) {
        txtBuscar.value = "";
      }

      if (filtroCategoria) {
        filtroCategoria.value = "";
      }

      if (filtroMarca) {
        filtroMarca.value = "";
      }

      if (filtroProveedor) {
        filtroProveedor.value = "";
      }

      if (filtroTipo) {
        filtroTipo.value = "";
      }

      paginaActual = 1;

      cargarProductos();

      mostrarAlerta("Filtros restablecidos.", "success");
    });
  }

  //==================================================
  // SELECCIONAR TODOS
  //==================================================

  const checkTodos = document.getElementById("checkTodos");

  if (checkTodos) {
    checkTodos.addEventListener("change", function () {
      document.querySelectorAll(".check-producto").forEach(function (check) {
        check.checked = checkTodos.checked;
      });
    });
  }

  //==================================================
  // BOTÓN ELIMINAR SELECCIONADOS
  //==================================================

  const btnEliminarSeleccionados = document.getElementById(
    "btnEliminarSeleccionados",
  );

  if (btnEliminarSeleccionados) {
    btnEliminarSeleccionados.addEventListener("click", function () {
      idsEliminarMasivo = obtenerSeleccionados();

      if (idsEliminarMasivo.length === 0) {
        mostrarAlerta("Seleccione productos.", "warning");

        return;
      }

      const cantidad = document.getElementById("cantidadProductosEliminar");

      if (cantidad) {
        cantidad.textContent = idsEliminarMasivo.length;
      }

      const modalElemento = document.getElementById("modalEliminarMasivo");

      if (modalElemento && typeof bootstrap !== "undefined") {
        bootstrap.Modal.getOrCreateInstance(modalElemento).show();
      }
    });
  }

  //==================================================
  // DESTACAR MASIVO
  //==================================================

  const btnDestacarSeleccionados = document.getElementById(
    "btnDestacarSeleccionados",
  );

  if (btnDestacarSeleccionados) {
    btnDestacarSeleccionados.addEventListener("click", function () {
      const ids = obtenerSeleccionados();

      if (ids.length === 0) {
        mostrarAlerta("Seleccione productos.", "warning");

        return;
      }

      ejecutarAccionMasiva(
        "ajax/adm_destacar_productos.php",
        ids,
        "Error al destacar productos.",
      );
    });
  }

  //==================================================
  // QUITAR DESTACADOS
  //==================================================

  const btnQuitarDestacados = document.getElementById("btnQuitarDestacados");

  if (btnQuitarDestacados) {
    btnQuitarDestacados.addEventListener("click", function () {
      const ids = obtenerSeleccionados();

      if (ids.length === 0) {
        mostrarAlerta("Seleccione productos.", "warning");

        return;
      }

      ejecutarAccionMasiva(
        "ajax/adm_quitar_destacados.php",
        ids,
        "Error al quitar destacados.",
      );
    });
  }

  //==================================================
  // ACTIVAR OFERTA MASIVA
  //==================================================

  const btnOfertaSeleccionados = document.getElementById(
    "btnOfertaSeleccionados",
  );

  if (btnOfertaSeleccionados) {
    btnOfertaSeleccionados.addEventListener("click", function () {
      const ids = obtenerSeleccionados();

      if (ids.length === 0) {
        mostrarAlerta("Seleccione productos.", "warning");

        return;
      }

      ejecutarAccionMasiva(
        "ajax/adm_oferta_productos.php",
        ids,
        "Error al activar ofertas.",
      );
    });
  }

  //==================================================
  // QUITAR OFERTA MASIVA
  //==================================================

  const btnQuitarOferta = document.getElementById("btnQuitarOferta");

  if (btnQuitarOferta) {
    btnQuitarOferta.addEventListener("click", function () {
      const ids = obtenerSeleccionados();

      if (ids.length === 0) {
        mostrarAlerta("Seleccione productos.", "warning");

        return;
      }

      ejecutarAccionMasiva(
        "ajax/adm_quitar_oferta_productos.php",
        ids,
        "Error al quitar ofertas.",
      );
    });
  }

  //==================================================
  // EJECUTAR ACCIÓN MASIVA
  //==================================================

  function ejecutarAccionMasiva(url, ids, mensajeError) {
    fetch(url, {
      method: "POST",

      headers: {
        "Content-Type": "application/json",
      },

      body: JSON.stringify({
        ids,
      }),
    })
      .then(function (response) {
        return response.json();
      })

      .then(function (data) {
        mostrarAlerta(
          data.mensaje || "Operación realizada.",
          data.estado ? "success" : "danger",
        );

        if (data.estado) {
          cargarProductos();
        }
      })

      .catch(function (error) {
        console.error(error);

        mostrarAlerta(mensajeError, "danger");
      });
  }

  //==================================================
  // ABRIR MODAL EDITAR
  //==================================================

  document.addEventListener("click", function (e) {
    const boton = e.target.closest(".btn-editar");

    if (!boton) {
      return;
    }

    const idProducto = boton.dataset.id;

    if (!idProducto) {
      mostrarAlerta("Producto inválido.", "danger");

      return;
    }

    abrirModalEditar(idProducto);
  });

  //==================================================
  // ABRIR MODAL EDITAR
  //==================================================

  function abrirModalEditar(idProducto) {
    fetch("ajax/obtener_producto_editar.php", {
      method: "POST",

      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },

      body: new URLSearchParams({
        idProducto,
      }),
    })
      .then(function (response) {
        if (!response.ok) {
          throw new Error("Error HTTP " + response.status);
        }

        return response.json();
      })

      .then(function (data) {
        if (!data.estado) {
          mostrarAlerta(data.mensaje || "Producto no encontrado.", "danger");

          return;
        }

        const p = data.producto;

        if (String(p.tipo).toLowerCase() === "servicio") {
          cargarFormularioServicio(p);
        } else {
          cargarFormularioProducto(p);
        }

        const modalElemento = document.getElementById("modalEditarProducto");

        if (modalElemento && typeof bootstrap !== "undefined") {
          bootstrap.Modal.getOrCreateInstance(modalElemento).show();
        }
      })

      .catch(function (error) {
        console.error("Error abrirModalEditar:", error);

        mostrarAlerta("Error al obtener los datos del producto.", "danger");
      });
  }

  //==================================================
  // FORMULARIO PRODUCTO
  //==================================================

  function cargarFormularioProducto(p) {
    const contenedor = document.getElementById("contenidoEditarProducto");

    if (!contenedor) {
      return;
    }

    contenedor.innerHTML = `

            <div class="row g-3">

                <!-- ID -->

                <input
                    type="hidden"
                    name="idProducto"
                    value="${escapeHTML(p.idProducto ?? "")}">

                <input
                    type="hidden"
                    name="tipo"
                    value="Producto">


                <!-- CÓDIGO -->

                <div class="col-md-6">

                    <label class="form-label">
                        Código
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        name="codigo"
                        value="${escapeHTML(p.codigo ?? "")}">

                </div>


                <!-- NOMBRE -->

                <div class="col-md-6">

                    <label class="form-label">
                        Nombre Producto
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        name="nombre"
                        value="${escapeHTML(p.nombre ?? "")}">

                </div>


                <!-- PRECIO -->

                <div class="col-md-6">

                    <label class="form-label">
                        Precio Actual
                    </label>

                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        class="form-control"
                        name="precio"
                        value="${escapeHTML(p.precio ?? 0)}">

                </div>


                <!-- IMPUESTO -->

                <div class="col-md-6">

                    <label class="form-label">
                        Impuesto
                    </label>

                    <div class="form-check form-switch mt-2">

                        <input
                            class="form-check-input"
                            type="checkbox"
                            role="switch"
                            id="editAplicaImpuesto"
                            name="aplica_impuesto"
                            value="1"
                            ${Number(p.aplica_impuesto) === 1 ? "checked" : ""}>

                        <label
                            class="form-check-label"
                            for="editAplicaImpuesto">

                            Aplicar IGV

                        </label>

                    </div>

                    <small class="text-muted">

                        Activa esta opción si el producto
                        está sujeto al IGV.

                    </small>

                </div>


                <!-- PRECIO ANTERIOR -->

                <div class="col-md-6">

                    <label class="form-label">
                        Precio Anterior
                    </label>

                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        class="form-control"
                        name="precio_anterior"
                        value="${escapeHTML(p.precio_anterior ?? 0)}">

                </div>


                <!-- DESCUENTO -->

                <div class="col-md-6">

                    <label class="form-label">
                        Descuento (%)
                    </label>

                    <input
                        type="number"
                        min="0"
                        max="100"
                        class="form-control"
                        name="descuento"
                        value="${escapeHTML(p.descuento ?? 0)}">

                </div>


                <!-- STOCK -->

                <div class="col-md-6">

                    <label class="form-label">
                        Stock
                    </label>

                    <input
                        type="number"
                        min="0"
                        class="form-control"
                        name="stock"
                        value="${escapeHTML(p.stock ?? 0)}">

                </div>


                <!-- COSTO COMPRA -->

                <div class="col-md-6">

                    <label class="form-label">
                        Costo Compra
                    </label>

                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        class="form-control"
                        name="costo_compra"
                        value="${escapeHTML(p.costo_compra ?? 0)}">

                </div>


                <!-- CATEGORÍA -->

                <div class="col-md-6">

                    <label class="form-label">
                        Categoría
                    </label>

                    <select
                        class="form-select"
                        id="editCategoria"
                        name="id_categorias">

                    </select>

                </div>


                <!-- MARCA -->

                <div class="col-md-6">

                    <label class="form-label">
                        Marca
                    </label>

                    <select
                        class="form-select"
                        id="editMarca"
                        name="id_marca">

                    </select>

                </div>


                <!-- PROVEEDOR -->

                <div class="col-md-6">

                    <label class="form-label">
                        Proveedor
                    </label>

                    <select
                        class="form-select"
                        id="editProveedor"
                        name="id_provedor">

                    </select>

                </div>


                <!-- SUCURSAL -->

                <div class="col-md-6">

                    <label class="form-label">
                        Sucursal
                    </label>

                    <select
                        class="form-select"
                        id="editSucursal"
                        name="id_sucursal">

                    </select>

                </div>


                <!-- DESCRIPCIÓN -->

                <div class="col-12">

                    <label class="form-label">
                        Descripción
                    </label>

                    <textarea
                        class="form-control"
                        rows="5"
                        name="descripcion">${escapeHTML(
                          p.descripcion ?? "",
                        )}</textarea>

                </div>

            </div>

        `;

    cargarCategorias(p.id_categorias);

    cargarMarcas(p.id_marca);

    cargarProveedores(p.id_provedor);

    cargarSucursales(p.id_sucursal, "producto");
  }

  //==================================================
  // CATEGORÍAS
  //==================================================

  function cargarCategorias(idSeleccionado = 0) {
    fetch("ajax/obtener_categorias.php")
      .then(function (response) {
        return response.json();
      })

      .then(function (data) {
        const combo = document.getElementById("editCategoria");

        if (!combo) {
          return;
        }

        combo.innerHTML = `<option value="">
                    Seleccione
                </option>`;

        if (!Array.isArray(data)) {
          return;
        }

        data.forEach(function (categoria) {
          combo.innerHTML += `

                    <option
                        value="${escapeHTML(categoria.id_categorias)}"
                        ${
                          categoria.id_categorias == idSeleccionado
                            ? "selected"
                            : ""
                        }>

                        ${escapeHTML(categoria.nombre)}

                    </option>

                `;
        });
      })

      .catch(function (error) {
        console.error("Error cargando categorías:", error);
      });
  }

  //==================================================
  // MARCAS
  //==================================================

  function cargarMarcas(idSeleccionado = 0) {
    fetch("ajax/obtener_marcas.php")
      .then(function (response) {
        return response.json();
      })

      .then(function (data) {
        const combo = document.getElementById("editMarca");

        if (!combo) {
          return;
        }

        combo.innerHTML = `<option value="">
                    Seleccione
                </option>`;

        if (!Array.isArray(data)) {
          return;
        }

        data.forEach(function (marca) {
          combo.innerHTML += `

                    <option
                        value="${escapeHTML(marca.id_marca)}"
                        ${marca.id_marca == idSeleccionado ? "selected" : ""}>

                        ${escapeHTML(marca.nombre)}

                    </option>

                `;
        });
      })

      .catch(function (error) {
        console.error("Error cargando marcas:", error);
      });
  }

  //==================================================
  // PROVEEDORES
  //==================================================

  function cargarProveedores(idSeleccionado = 0) {
    fetch("ajax/obtener_proveedores.php")
      .then(function (response) {
        return response.json();
      })

      .then(function (data) {
        const combo = document.getElementById("editProveedor");

        if (!combo) {
          return;
        }

        combo.innerHTML = `<option value="">
                    Seleccione
                </option>`;

        if (!Array.isArray(data)) {
          return;
        }

        data.forEach(function (proveedor) {
          combo.innerHTML += `

                    <option
                        value="${escapeHTML(proveedor.id_provedor)}"
                        ${
                          proveedor.id_provedor == idSeleccionado
                            ? "selected"
                            : ""
                        }>

                        ${escapeHTML(proveedor.nombre)}

                    </option>

                `;
        });
      })

      .catch(function (error) {
        console.error("Error cargando proveedores:", error);
      });
  }

  //==================================================
  // SUCURSALES
  //==================================================

  function cargarSucursales(idSeleccionado = 0, tipo = "") {
    fetch("ajax/obtener_sucursales.php")
      .then(function (response) {
        return response.json();
      })

      .then(function (data) {
        let combo = null;

        if (tipo === "producto") {
          combo = document.getElementById("editSucursal");
        }

        if (tipo === "servicio") {
          combo = document.getElementById("editSucursalServicio");
        }

        if (!combo) {
          return;
        }

        combo.innerHTML = `<option value="">
                    Seleccione
                </option>`;

        if (!Array.isArray(data)) {
          return;
        }

        data.forEach(function (sucursal) {
          combo.innerHTML += `

                    <option
                        value="${escapeHTML(sucursal.id_sucursal)}"
                        ${
                          sucursal.id_sucursal == idSeleccionado
                            ? "selected"
                            : ""
                        }>

                        ${escapeHTML(sucursal.nombre)}

                    </option>

                `;
        });
      })

      .catch(function (error) {
        console.error("Error cargando sucursales:", error);
      });
  }

  //==================================================
  // FORMULARIO SERVICIO
  //==================================================

  function cargarFormularioServicio(p) {
    const contenedor = document.getElementById("contenidoEditarProducto");

    if (!contenedor) {
      return;
    }

    contenedor.innerHTML = `

            <div class="row g-3">

                <input
                    type="hidden"
                    name="idProducto"
                    value="${escapeHTML(p.idProducto ?? "")}">

                <input
                    type="hidden"
                    name="tipo"
                    value="Servicio">


                <div class="col-md-6">

                    <label class="form-label">
                        Código
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        name="codigo"
                        value="${escapeHTML(p.codigo ?? "")}">

                </div>


                <div class="col-md-6">

                    <label class="form-label">
                        Nombre del Servicio
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        name="nombre"
                        value="${escapeHTML(p.nombre ?? "")}">

                </div>


                <div class="col-md-6">

                    <label class="form-label">
                        Precio
                    </label>

                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        class="form-control"
                        name="precio"
                        value="${escapeHTML(p.precio ?? 0)}">

                </div>


                <div class="col-md-6">

                    <label class="form-label">
                        Impuesto
                    </label>

                    <div class="form-check form-switch mt-2">

                        <input
                            class="form-check-input"
                            type="checkbox"
                            role="switch"
                            id="editAplicaImpuestoServicio"
                            name="aplica_impuesto"
                            value="1"
                            ${Number(p.aplica_impuesto) === 1 ? "checked" : ""}>

                        <label
                            class="form-check-label"
                            for="editAplicaImpuestoServicio">

                            Aplicar IGV

                        </label>

                    </div>

                </div>


                <div class="col-md-6">

                    <label class="form-label">
                        Sucursal
                    </label>

                    <select
                        class="form-select"
                        name="id_sucursal"
                        id="editSucursalServicio">

                        <option value="">
                            Seleccione
                        </option>

                    </select>

                </div>


                <div class="col-md-6">

                    <label class="form-label">
                        Estado
                    </label>

                    <select
                        class="form-select"
                        name="estado_publicacion">

                        <option value="PUBLICADO"
                            ${
                              p.estado_publicacion === "PUBLICADO"
                                ? "selected"
                                : ""
                            }>

                            Publicado

                        </option>

                        <option value="BORRADOR"
                            ${
                              p.estado_publicacion === "BORRADOR"
                                ? "selected"
                                : ""
                            }>

                            Borrador

                        </option>

                        <option value="OCULTO"
                            ${
                              p.estado_publicacion === "OCULTO"
                                ? "selected"
                                : ""
                            }>

                            Oculto

                        </option>

                    </select>

                </div>


                <div class="col-12">

                    <label class="form-label">
                        Detalle del Servicio
                    </label>

                    <textarea
                        class="form-control"
                        rows="5"
                        name="descripcion">${escapeHTML(
                          p.descripcion ?? "",
                        )}</textarea>

                </div>

            </div>

        `;

    cargarSucursales(p.id_sucursal, "servicio");
  }

  //==================================================
  // GUARDAR CAMBIOS
  //==================================================

  document.addEventListener("click", function (e) {
    const boton = e.target.closest("#btnGuardarCambiosProducto");

    if (!boton) {
      return;
    }

    guardarCambiosProducto();
  });

  //==================================================
  // GUARDAR CAMBIOS
  //==================================================

  function guardarCambiosProducto() {
    const formulario = document.getElementById("contenidoEditarProducto");

    if (!formulario) {
      mostrarAlerta("No se encontró el formulario.", "danger");

      return;
    }

    const inputs = formulario.querySelectorAll("input, select, textarea");

    const datos = {};

    inputs.forEach(function (input) {
      if (!input.name) {
        return;
      }

      if (input.type === "checkbox") {
        datos[input.name] = input.checked ? 1 : 0;
      } else {
        datos[input.name] = input.value;
      }
    });

    if (!datos.idProducto) {
      mostrarAlerta("ID de producto inválido.", "danger");

      return;
    }

    if (!datos.codigo) {
      mostrarAlerta("El código es obligatorio.", "warning");

      return;
    }

    if (!datos.nombre) {
      mostrarAlerta("El nombre es obligatorio.", "warning");

      return;
    }

    if (datos.precio === "" || isNaN(Number(datos.precio))) {
      mostrarAlerta("El precio es obligatorio.", "warning");

      return;
    }

    botonGuardarEstado(true);

    fetch("ajax/actualizar_producto.php", {
      method: "POST",

      headers: {
        "Content-Type": "application/json",
      },

      body: JSON.stringify(datos),
    })
      .then(async function (response) {
        const texto = await response.text();

        console.log("Respuesta actualizar_producto.php:", texto);

        let data;

        try {
          data = JSON.parse(texto);
        } catch (error) {
          throw new Error("El servidor devolvió una respuesta no válida.");
        }

        return data;
      })

      .then(function (data) {
        botonGuardarEstado(false);

        mostrarAlerta(
          data.mensaje || "Operación finalizada.",
          data.estado ? "success" : "danger",
        );

        if (!data.estado) {
          return;
        }

        //========================================
        // CERRAR MODAL
        //========================================

        const modalElemento = document.getElementById("modalEditarProducto");

        if (modalElemento && typeof bootstrap !== "undefined") {
          const modal = bootstrap.Modal.getInstance(modalElemento);

          if (modal) {
            modal.hide();
          }
        }

        //========================================
        // ACTUALIZAR LISTA
        //========================================

        if (document.getElementById("tablaProductos")) {
          cargarProductos();
        }

        //========================================
        // ACTUALIZAR DETALLE
        //========================================

        const contenedorDetalle = document.getElementById("detalleProducto");

        if (contenedorDetalle) {
          const idProducto = contenedorDetalle.dataset.idProducto;

          /*
           * En la página de detalle hacemos
           * una recarga completa.
           *
           * Esto garantiza que se actualicen:
           * - precio
           * - impuesto
           * - stock
           * - descuento
           * - categoría
           * - marca
           * - proveedor
           * - sucursal
           * - oferta
           * - destacado
           * - etc.
           */

          if (idProducto) {
            setTimeout(function () {
              window.location.reload();
            }, 500);
          }
        }
      })

      .catch(function (error) {
        botonGuardarEstado(false);

        console.error("Error guardarCambiosProducto:", error);

        mostrarAlerta(error.message || "Error al guardar cambios.", "danger");
      });
  }

  //==================================================
  // ESTADO BOTÓN GUARDAR
  //==================================================

  function botonGuardarEstado(cargando) {
    const boton = document.getElementById("btnGuardarCambiosProducto");

    if (!boton) {
      return;
    }

    if (cargando) {
      if (!boton.dataset.textoOriginal) {
        boton.dataset.textoOriginal = boton.innerHTML;
      }

      boton.disabled = true;

      boton.innerHTML = `

                <span
                    class="spinner-border spinner-border-sm me-2">
                </span>

                Guardando...

            `;
    } else {
      boton.disabled = false;

      boton.innerHTML = boton.dataset.textoOriginal || `Guardar cambios`;
    }
  }

  //==================================================
  // ELIMINAR PRODUCTO
  //==================================================

  document.addEventListener("click", function (e) {
    const boton = e.target.closest(".btn-eliminar");

    if (!boton) {
      return;
    }

    idProductoEliminar = boton.dataset.id;

    const nombre = document.getElementById("nombreProductoEliminar");

    if (nombre) {
      nombre.textContent = boton.dataset.nombre || "";
    }

    const modalElemento = document.getElementById("modalEliminarProducto");

    if (modalElemento && typeof bootstrap !== "undefined") {
      bootstrap.Modal.getOrCreateInstance(modalElemento).show();
    }
  });

  //==================================================
  // CONFIRMAR ELIMINACIÓN
  //==================================================

  document.addEventListener("click", function (e) {
    const boton = e.target.closest("#btnConfirmarEliminarProducto");

    if (!boton) {
      return;
    }

    if (!idProductoEliminar || idProductoEliminar <= 0) {
      mostrarAlerta("Producto inválido.", "danger");

      return;
    }

    eliminarProducto(idProductoEliminar);
  });

  //==================================================
  // CONFIRMAR ELIMINACIÓN MASIVA
  //==================================================

  document.addEventListener("click", function (e) {
    const boton = e.target.closest("#btnConfirmarEliminarMasivo");

    if (!boton) {
      return;
    }

    if (idsEliminarMasivo.length === 0) {
      mostrarAlerta("No hay productos seleccionados.", "warning");

      return;
    }

    fetch("ajax/adm_eliminar_productos_masivo.php", {
      method: "POST",

      headers: {
        "Content-Type": "application/json",
      },

      body: JSON.stringify({
        ids: idsEliminarMasivo,
      }),
    })
      .then(function (response) {
        return response.json();
      })

      .then(function (data) {
        const modalElemento = document.getElementById("modalEliminarMasivo");

        if (modalElemento && typeof bootstrap !== "undefined") {
          const modal = bootstrap.Modal.getInstance(modalElemento);

          if (modal) {
            modal.hide();
          }
        }

        mostrarAlerta(
          data.mensaje || "Operación finalizada.",
          data.estado ? "success" : "danger",
        );

        if (data.estado) {
          idsEliminarMasivo = [];

          if (checkTodos) {
            checkTodos.checked = false;
          }

          cargarProductos();
        }
      })

      .catch(function (error) {
        console.error(error);

        mostrarAlerta("Error al eliminar productos.", "danger");
      });
  });

  //==================================================
  // ELIMINAR PRODUCTO INDIVIDUAL
  //==================================================

  function eliminarProducto(idProducto) {
    fetch("ajax/adm_eliminar_producto.php", {
      method: "POST",

      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },

      body: new URLSearchParams({
        idProducto,
      }),
    })
      .then(function (response) {
        return response.json();
      })

      .then(function (data) {
        const modalElemento = document.getElementById("modalEliminarProducto");

        if (modalElemento && typeof bootstrap !== "undefined") {
          const modal = bootstrap.Modal.getInstance(modalElemento);

          if (modal) {
            modal.hide();
          }
        }

        mostrarAlerta(
          data.mensaje || "Operación finalizada.",
          data.estado ? "success" : "danger",
        );

        if (data.estado) {
          /*
           * Si estamos en detalle,
           * regresar a la lista.
           */

          if (
            document.getElementById("detalleProducto") &&
            !document.getElementById("tablaProductos")
          ) {
            setTimeout(function () {
              window.location.href = "adm_lista_productos.php";
            }, 700);
          } else {
            cargarProductos();
          }
        }
      })

      .catch(function (error) {
        console.error(error);

        mostrarAlerta("Error al eliminar producto.", "danger");
      });
  }

  //==================================================
  // OBTENER SELECCIONADOS
  //==================================================

  function obtenerSeleccionados() {
    const ids = [];

    document
      .querySelectorAll(".check-producto:checked")
      .forEach(function (check) {
        ids.push(check.value);
      });

    return ids;
  }

  window.obtenerSeleccionados = obtenerSeleccionados;

  //==================================================
  // LOADER
  //==================================================

  function mostrarLoader() {
    const tabla = document.getElementById("tablaProductos");

    if (!tabla) {
      return;
    }

    tabla.innerHTML = `

            <tr>

                <td
                    colspan="10"
                    class="text-center py-5">

                    <div
                        class="spinner-border text-primary">
                    </div>

                    <div class="mt-2">

                        Cargando productos...

                    </div>

                </td>

            </tr>

        `;
  }

  function ocultarLoader() {
    // No es necesario.
  }

  //==================================================
  // ALERTAS
  //==================================================

  function mostrarAlerta(mensaje, tipo = "success") {
    let contenedor = document.getElementById("alertProductos");

    if (!contenedor) {
      contenedor = document.createElement("div");

      contenedor.id = "alertProductos";

      contenedor.className = "position-fixed top-0 end-0 p-3";

      contenedor.style.zIndex = "99999";

      document.body.appendChild(contenedor);
    }

    const alerta = document.createElement("div");

    alerta.className = `alert alert-${tipo} alert-dismissible fade show shadow`;

    alerta.innerHTML = `

            ${escapeHTML(String(mensaje ?? ""))}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert">
            </button>

        `;

    contenedor.appendChild(alerta);

    setTimeout(function () {
      if (alerta && alerta.parentNode) {
        alerta.remove();
      }
    }, 5000);
  }

  window.mostrarAlerta = mostrarAlerta;

  //==================================================
  // EXPORTAR EXCEL
  //==================================================

  function exportarExcel(scope, opciones) {
    const params = new URLSearchParams();

    params.append("scope", scope);

    params.append(
      "buscar",
      document.getElementById("buscarProducto")?.value || "",
    );

    params.append(
      "categoria",
      document.getElementById("filtroCategoria")?.value || "",
    );

    params.append("marca", document.getElementById("filtroMarca")?.value || "");

    params.append(
      "proveedor",
      document.getElementById("filtroProveedor")?.value || "",
    );

    params.append("tipo", document.getElementById("filtroTipo")?.value || "");

    params.append("ids", JSON.stringify(obtenerSeleccionados()));

    params.append("campos", JSON.stringify(opciones));

    window.open(
      "ajax/exportar_productos_excel.php?" + params.toString(),
      "_blank",
    );
  }

  //==================================================
  // EXPORTAR PDF
  //==================================================

  function exportarPDF(scope, opciones) {
    const params = new URLSearchParams();

    params.append("scope", scope);

    params.append(
      "buscar",
      document.getElementById("buscarProducto")?.value || "",
    );

    params.append(
      "categoria",
      document.getElementById("filtroCategoria")?.value || "",
    );

    params.append("marca", document.getElementById("filtroMarca")?.value || "");

    params.append(
      "proveedor",
      document.getElementById("filtroProveedor")?.value || "",
    );

    params.append("tipo", document.getElementById("filtroTipo")?.value || "");

    params.append("ids", JSON.stringify(obtenerSeleccionados()));

    params.append("campos", JSON.stringify(opciones));

    window.open(
      "ajax/exportar_productos_pdf.php?" + params.toString(),
      "_blank",
    );
  }

  //==================================================
  // ESCAPAR HTML
  //==================================================

  function escapeHTML(valor) {
    return String(valor ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  //==================================================
  // INICIAR LISTA
  //==================================================

  if (document.getElementById("tablaProductos")) {
    cargarProductos();
  }
});

//======================================================
// KPIs
//======================================================

function actualizarKPIs(kpis) {
  if (!kpis) {
    return;
  }

  const totalProductos = document.getElementById("kpiTotalProductos");

  if (totalProductos) {
    totalProductos.textContent = kpis.total_productos ?? 0;
  }

  const servicios = document.getElementById("kpiServicios");

  if (servicios) {
    servicios.textContent = kpis.servicios ?? 0;
  }

  const destacados = document.getElementById("kpiDestacados");

  if (destacados) {
    destacados.textContent = kpis.destacados ?? 0;
  }

  const ofertas = document.getElementById("kpiOfertas");

  if (ofertas) {
    ofertas.textContent = kpis.ofertas ?? 0;
  }

  const stockBajo = document.getElementById("kpiStockBajo");

  if (stockBajo) {
    stockBajo.textContent = kpis.stock_bajo ?? 0;
  }

  const inventario = document.getElementById("kpiInventario");

  if (inventario) {
    const valor = parseFloat(kpis.inventario ?? 0);

    inventario.textContent =
      "S/ " +
      valor.toLocaleString("es-PE", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      });
  }
}
