//==========================================================
// CoDevPro Technology
// js/adm_comprobantes.js
//==========================================================

document.addEventListener("DOMContentLoaded", () => {
  inicializarEventos();
  cargarMetodosPago();
  cargarEmpleados();
  cargarClientes();
  cargarKPI();
  cargarComprobantes();
});
/*==========================================================
=            VARIABLES GLOBALES
==========================================================*/

let fechaInicio = "";

let fechaFin = "";
let paginaActual = 1;

let totalPaginas = 1;
/*==========================================================
=            EVENTOS
==========================================================*/

function inicializarEventos() {
  document
    .querySelector("#tbodyComprobantes")
    ?.addEventListener("click", function (e) {
      const boton = e.target.closest(".btnVerComprobante");

      if (!boton) return;

      const id = boton.dataset.id;

      cargarDetalleComprobante(id);
    });

  document.getElementById("recargarTabla")?.addEventListener("click", () => {
    cargarComprobantes();
  });
  document
    .getElementById("cantidadRegistros")
    ?.addEventListener("change", () => {
      paginaActual = 1;

      cargarComprobantes();
    });
  // Actualizar
  document.getElementById("btnActualizar")?.addEventListener("click", () => {
    limpiarFiltros();
    cargarKPI();
    cargarComprobantes();
  });

  // Aplicar filtros

  document
    .getElementById("btnAplicarFiltros")
    ?.addEventListener("click", () => {
      paginaActual = 1;

      cargarKPI();

      cargarComprobantes();
    });

  // Limpiar filtros

  document
    .getElementById("btnLimpiarFiltros")
    ?.addEventListener("click", () => {
      limpiarFiltros();
      paginaActual = 1;
      cargarKPI();
      cargarComprobantes();
    });
  /*==========================================================
FLATPICKR
==========================================================*/

  if (document.getElementById("rangoFecha")) {
    flatpickr("#rangoFecha", {
      mode: "range",

      dateFormat: "d/m/Y",

      locale: "es",

      allowInput: false,

      onClose: function (selectedDates) {
        if (selectedDates.length === 2) {
          fechaInicio = selectedDates[0].toISOString().slice(0, 10);

          fechaFin = selectedDates[1].toISOString().slice(0, 10);
        } else {
          fechaInicio = "";

          fechaFin = "";
        }

        cargarKPI();
      },
    });
  }
  // Buscador

  document.getElementById("buscarComprobante")?.addEventListener(
    "input",
    debounce(() => {
      paginaActual = 1;
      cargarKPI();
      cargarComprobantes();
    }, 400),
  );

  // Fecha

  document.getElementById("rangoFecha")?.addEventListener("change", () => {
    cargarKPI();
  });
}

/*==========================================================
=            OBTENER TODOS LOS FILTROS
==========================================================*/

function obtenerFiltros() {
  return {
    buscar: document.getElementById("buscarComprobante")?.value.trim() || "",

    fechaInicio,

    fechaFin,

    tipo: document.getElementById("filtroTipo")?.value || "",

    estado: document.getElementById("filtroEstado")?.value || "",

    metodoPago: document.getElementById("filtroMetodoPago")?.value || "",

    empleado: document.getElementById("filtroEmpleado")?.value || "",

    cliente: document.getElementById("filtroCliente")?.value.trim() || "",

    montoMin: document.getElementById("montoMin")?.value || "",

    montoMax: document.getElementById("montoMax")?.value || "",

    ordenar: document.getElementById("ordenarPor")?.value || "fecha_desc",

    soloIGV: document.getElementById("soloIGV")?.checked ? 1 : 0,

    soloAnulados: document.getElementById("soloAnulados")?.checked ? 1 : 0,
  };
}

/*==========================================================
=            LIMPIAR FILTROS
==========================================================*/

function limpiarFiltros() {
  document.getElementById("buscarComprobante").value = "";

  document.getElementById("rangoFecha").value = "";

  document.getElementById("filtroTipo").value = "";

  document.getElementById("filtroEstado").value = "";

  document.getElementById("filtroMetodoPago").value = "";

  document.getElementById("filtroEmpleado").value = "";

  document.getElementById("filtroCliente").value = "";

  document.getElementById("montoMin").value = "";

  document.getElementById("montoMax").value = "";

  document.getElementById("ordenarPor").value = "fecha_desc";

  document.getElementById("soloIGV").checked = false;

  document.getElementById("soloAnulados").checked = false;
  fechaInicio = "";

  fechaFin = "";
}
/*==========================================================
=            CARGAR EMPLEADOS
==========================================================*/

function cargarEmpleados() {
  fetch("ajax/obtener_empleados.php")
    .then((r) => r.json())

    .then((res) => {
      if (!res.estado) return;

      const select = document.getElementById("filtroEmpleado");

      if (!select) return;

      select.innerHTML = `

            <option value="">
                Todos
            </option>

      `;

      res.empleados.forEach((empleado) => {
        select.insertAdjacentHTML(
          "beforeend",

          `

              <option value="${empleado.id}">

                    ${empleado.nombre}

              </option>

              `,
        );
      });
    })

    .catch((error) => {
      console.error("Empleados:", error);
    });
}
/*==========================================================
=            CARGAR CLIENTES
==========================================================*/

function cargarClientes() {
  fetch("ajax/obtener_clientes_comprobantes.php")
    .then((r) => r.json())

    .then((res) => {
      if (!res.estado) return;

      const select = document.getElementById("filtroCliente");

      if (!select) return;

      select.innerHTML = `

        <option value="">
            Todos los clientes
        </option>

      `;

      res.clientes.forEach((cliente) => {
        select.insertAdjacentHTML(
          "beforeend",

          `

          <option value="${cliente.id}">

              ${cliente.nombre} - ${cliente.documento}

          </option>

          `,
        );
      });
    })

    .catch((error) => {
      console.error("Clientes:", error);
    });
}
/*==========================================================
=            CARGAR MÉTODOS DE PAGO
==========================================================*/

function cargarMetodosPago() {
  fetch("ajax/obtener_metodos_pago.php")
    .then((r) => r.json())

    .then((res) => {
      if (!res.estado) return;

      const select = document.getElementById("filtroMetodoPago");

      if (!select) return;

      select.innerHTML = `
                <option value="">Todos</option>
            `;

      res.metodos.forEach((metodo) => {
        select.insertAdjacentHTML(
          "beforeend",

          `
                    <option value="${metodo.id}">
                        ${metodo.nombre}
                    </option>
                    `,
        );
      });
    })

    .catch((error) => {
      console.error("Métodos de pago:", error);
    });
}
/*==========================================================
=            CARGAR KPI
==========================================================*/

function cargarKPI() {
  const filtros = obtenerFiltros();

  fetch("ajax/obtener_kpi_comprobantes.php", {
    method: "POST",

    headers: {
      "Content-Type": "application/json",
    },

    body: JSON.stringify(filtros),
  })
    .then((r) => r.json())

    .then((res) => {
      if (!res.estado) return;

      const k = res.kpi;

      document.getElementById("kpiComprobantes").textContent = k.total;

      document.getElementById("kpiBoletas").textContent = k.boletas;

      document.getElementById("kpiFacturas").textContent = k.facturas;

      document.getElementById("kpiNotasVenta").textContent = k.notas;

      document.getElementById("kpiAnulados").textContent = k.anulados;

      document.getElementById("kpiMonto").textContent =
        "S/ " +
        Number(k.monto).toLocaleString("es-PE", {
          minimumFractionDigits: 2,
          maximumFractionDigits: 2,
        });
    })

    .catch((error) => {
      console.error("Error KPI:", error);
    });
}
/*==========================================================
=            CARGAR TABLA COMPROBANTES
==========================================================*/

function cargarComprobantes() {
  const filtros = obtenerFiltros();

  fetch("ajax/obtener_comprobantes.php", {
    method: "POST",

    headers: {
      "Content-Type": "application/json",
    },

    body: JSON.stringify({
      ...filtros,

      pagina: paginaActual,

      limite: document.getElementById("cantidadRegistros")?.value || 20,
    }),
  })
    .then((r) => r.json())

    .then((res) => {
      if (!res.estado) return;
      paginaActual = res.paginaActual;

      totalPaginas = res.totalPaginas;

      generarPaginacion();
      const tbody = document.getElementById("tbodyComprobantes");

      tbody.innerHTML = "";
      let inicioRegistro =
        (paginaActual - 1) *
          Number(document.getElementById("cantidadRegistros").value) +
        1;

      let finRegistro = inicioRegistro + res.comprobantes.length - 1;

      document.getElementById("textoRegistros").innerHTML =
        `Mostrando ${res.comprobantes.length > 0 ? inicioRegistro : 0}
        a ${finRegistro}
        de ${res.totalRegistros} registros`;
      if (res.comprobantes.length === 0) {
        tbody.innerHTML = `

<tr>

<td colspan="12"
class="text-center text-muted py-4">

<i class="bi bi-inbox fs-3"></i>

<br>

No existen comprobantes registrados

</td>

</tr>

`;

        return;
      }

      res.comprobantes.forEach((c, index) => {
        tbody.insertAdjacentHTML(
          "beforeend",

          `

<tr>


<td>

<input 
type="checkbox"
class="form-check-input checkComprobante"
value="${c.id_ticket_ventas}">

</td>


<td>${index + 1}</td>


<td>

<span class="badge bg-primary">

${c.tipo_comprobante}

</span>

<br>

${c.serie}-${c.numero}

</td>



<td>

${c.cliente ?? "Cliente eliminado"}

</td>


<td>

${c.dni_o_ruc ?? "-"}

</td>


<td>

${c.fecha_venta}

</td>


<td>

${c.hora_venta}

</td>


<td>

${c.metodo_pago ?? "-"}

</td>


<td>

${c.empleado ?? "-"}

</td>



<td>

S/ ${Number(c.total_venta).toFixed(2)}

</td>



<td>

<span class="badge 

${c.estado_venta == "ANULADO" ? "bg-danger" : "bg-success"}">

${c.estado_venta}

</span>

</td>



<td class="text-center">


<button 
class="btn btn-sm btn-outline-primary btnVerComprobante"
data-id="${c.id_ticket_ventas}"
data-bs-toggle="modal"
data-bs-target="#modalVerComprobante">

<i class="bi bi-eye"></i>

</button>


</td>



</tr>

`,
        );
      });
    })

    .catch((error) => {
      console.error("Tabla comprobantes:", error);
    });
}
/*==========================================================
 PAGINACION REAL
==========================================================*/

function generarPaginacion() {
  const paginacion = document.getElementById("paginacionComprobantes");

  if (!paginacion) return;

  paginacion.innerHTML = "";

  let html = "";

  html += `

<li class="page-item ${paginaActual == 1 ? "disabled" : ""}">

<a class="page-link"
href="#"
onclick="cambiarPagina(${paginaActual - 1})">

<i class="bi bi-chevron-left"></i>

</a>

</li>

`;

  for (let i = 1; i <= totalPaginas; i++) {
    html += `

<li class="page-item ${i == paginaActual ? "active" : ""}">

<a class="page-link"
href="#"
onclick="cambiarPagina(${i})">

${i}

</a>

</li>


`;
  }

  html += `

<li class="page-item ${paginaActual == totalPaginas ? "disabled" : ""}">

<a class="page-link"
href="#"
onclick="cambiarPagina(${paginaActual + 1})">

<i class="bi bi-chevron-right"></i>

</a>

</li>


`;

  paginacion.innerHTML = html;
}

/* CAMBIAR PAGINA */

function cambiarPagina(pagina) {
  if (pagina < 1 || pagina > totalPaginas) return;

  paginaActual = pagina;

  cargarComprobantes();
}
/*==========================================================
=            DEBOUNCE
==========================================================*/

function debounce(fn, delay = 300) {
  let timer;

  return (...args) => {
    clearTimeout(timer);

    timer = setTimeout(() => {
      fn.apply(this, args);
    }, delay);
  };
}
/*==========================================================
=            CARGAR DETALLE COMPROBANTE
==========================================================*/

function cargarDetalleComprobante(idTicket) {
  fetch("ajax/obtener_detalle_comprobante.php", {
    method: "POST",

    headers: {
      "Content-Type": "application/json",
    },

    body: JSON.stringify({
      id: idTicket,
    }),
  })
    .then((r) => r.json())

    .then((res) => {
      if (!res.estado) {
        alert(res.mensaje);

        return;
      }

      /* DATOS CABECERA */

      document.getElementById("modalSerieNumero").innerHTML = `
${res.comprobante.tipo_comprobante}
<br>
<b>
${res.comprobante.serie}-${res.comprobante.numero}
</b>
`;

      document.getElementById("modalFecha").textContent =
        res.comprobante.fecha_venta;

      document.getElementById("modalCliente").textContent =
        res.comprobante.cliente;

      document.getElementById("modalDocumento").textContent =
        res.comprobante.dni_o_ruc;

      document.getElementById("modalMetodoPago").textContent =
        res.comprobante.metodo_pago;

      document.getElementById("modalEmpleado").textContent =
        res.comprobante.empleado;
      document.getElementById("btnImprimirComprobante").dataset.id = idTicket;

      document.getElementById("btnAnularComprobante").dataset.id = idTicket;

      /*==========================================================
=            CONTROL DE ACCIONES SEGÚN ESTADO
==========================================================*/

      const btnImprimir = document.getElementById("btnImprimirComprobante");

      const btnAnular = document.getElementById("btnAnularComprobante");

      if (res.comprobante.estado_venta === "ANULADO") {
        /*====================================
    BLOQUEAR IMPRESIÓN
  ====================================*/

        btnImprimir.disabled = true;

        btnImprimir.classList.add("disabled");

        btnImprimir.innerHTML = `

    <i class="bi bi-ban me-1"></i>

    Comprobante Anulado

  `;

        /*====================================
    OCULTAR BOTÓN ANULAR
  ====================================*/

        btnAnular.style.display = "none";
      } else {
        /*====================================
    HABILITAR IMPRESIÓN
  ====================================*/

        btnImprimir.disabled = false;

        btnImprimir.classList.remove("disabled");

        btnImprimir.innerHTML = `

    <i class="bi bi-printer me-1"></i>

    Imprimir PDF

  `;

        /*====================================
    MOSTRAR BOTÓN ANULAR
  ====================================*/

        btnAnular.style.display = "inline-block";
      }
      document.getElementById("modalEstado").innerHTML = `
<span class="badge bg-${
        res.comprobante.estado_venta == "ANULADO" ? "danger" : "success"
      }">

${res.comprobante.estado_venta}

</span>
`;

      /* PRODUCTOS */

      let html = "";

      res.detalle.forEach((p) => {
        html += `

<tr>


<td>
${p.producto}
</td>


<td class="text-center">
${p.cantidad}
</td>


<td>
S/ ${Number(p.precio).toFixed(2)}
</td>


<td>
S/ ${Number(p.subtotal).toFixed(2)}
</td>


</tr>


`;
      });

      document.getElementById("tbodyDetalleComprobante").innerHTML = html;

      /* TOTALES */

      document.getElementById("modalSubtotal").textContent =
        "S/ " + Number(res.comprobante.subtotal).toFixed(2);

      document.getElementById("modalIGV").textContent =
        "S/ " + Number(res.comprobante.igv).toFixed(2);

      document.getElementById("modalTotal").textContent =
        "S/ " + Number(res.comprobante.total_venta).toFixed(2);
    })

    .catch((error) => {
      console.error("Detalle comprobante:", error);
    });
}
document
  .getElementById("btnImprimirComprobante")
  ?.addEventListener("click", function () {
    if (this.disabled) {
      alert("Este comprobante está anulado y no puede imprimirse.");

      return;
    }

    let id = this.dataset.id;

    window.open("pdf/comprobante_pdf.php?id=" + id, "_blank");
  });
document
  .getElementById("btnAnularComprobante")
  ?.addEventListener("click", function () {
    let id = this.dataset.id;

    if (
      !confirm("¿Desea anular este comprobante?\n\nEl stock será restaurado.")
    )
      return;

    fetch("ajax/anular_comprobante.php", {
      method: "POST",

      headers: {
        "Content-Type": "application/json",
      },

      body: JSON.stringify({
        id: id,
      }),
    })
      .then((r) => r.json())

      .then((res) => {
        alert(res.mensaje);

        if (res.estado) {
          cargarComprobantes();
        }
      });
  });
