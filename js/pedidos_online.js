//======================================================
// PEDIDOS ONLINE
// CoDevPro Technology
//======================================================

"use strict";

/*======================================================
=            CONFIGURACIÓN
======================================================*/

const PEDIDOS = {
  listar: "ajax/obtener_pedidos.php",

  detalle: "ajax/obtener_detalle_pedido.php",

  actualizarEstado: "ajax/actualizar_estado_pedido.php",
};

/*======================================================
=            ELEMENTOS
======================================================*/

const txtBuscar = document.getElementById("buscar");

const cmbEstado = document.getElementById("estado");

const txtFecha = document.getElementById("fecha");

const tablaPedidos = document.getElementById("tablaPedidos");

const btnBuscar = document.getElementById("btnBuscar");

const btnActualizar = document.getElementById("btnActualizar");

/*======================================================
=            CARGAR PEDIDOS
======================================================*/

function cargarPedidos() {
  let parametros = new URLSearchParams();

  parametros.append(
    "buscar",

    txtBuscar ? txtBuscar.value.trim() : "",
  );

  parametros.append(
    "estado",

    cmbEstado ? cmbEstado.value : "",
  );

  parametros.append(
    "fecha",

    txtFecha ? txtFecha.value : "",
  );

  tablaPedidos.innerHTML = `

        <tr>

            <td colspan="7" class="text-center">

                Cargando pedidos...

            </td>

        </tr>

    `;

  fetch(PEDIDOS.listar + "?" + parametros.toString())
    .then((res) => res.text())

    .then((html) => {
      tablaPedidos.innerHTML = html;
    })

    .catch((error) => {
      console.error(error);

      tablaPedidos.innerHTML = `

            <tr>

                <td colspan="7"

                    class="text-center text-danger">

                    Error al cargar pedidos.

                </td>

            </tr>

        `;
    });
}

/*======================================================
=            BUSCAR
======================================================*/

if (btnBuscar) {
  btnBuscar.addEventListener(
    "click",

    cargarPedidos,
  );
}

/*======================================================
=            ACTUALIZAR
======================================================*/

if (btnActualizar) {
  btnActualizar.addEventListener(
    "click",

    cargarPedidos,
  );
}

/*======================================================
=            ENTER EN BUSCADOR
======================================================*/

if (txtBuscar) {
  txtBuscar.addEventListener(
    "keyup",

    function (e) {
      if (e.key === "Enter") {
        cargarPedidos();
      }
    },
  );
}

/*======================================================
=            CAMBIO DE FILTROS
======================================================*/

if (cmbEstado) {
  cmbEstado.addEventListener(
    "change",

    cargarPedidos,
  );
}

if (txtFecha) {
  txtFecha.addEventListener(
    "change",

    cargarPedidos,
  );
}

/*======================================================
=            CARGA INICIAL
======================================================*/

document.addEventListener(
  "DOMContentLoaded",

  function () {
    cargarPedidos();
  },
);
/*======================================================
=            VER DETALLE DEL PEDIDO
======================================================*/

document.addEventListener("click", function (e) {
  const boton = e.target.closest(".btnDetalle");

  if (!boton) return;

  const idPedido = boton.dataset.id;

  const contenedor = document.getElementById("detallePedido");

  contenedor.innerHTML = `

        <div class="modal-body text-center py-5">

            <div class="spinner-border text-primary"></div>

            <p class="mt-3">

                Cargando pedido...

            </p>

        </div>

    `;

  fetch(PEDIDOS.detalle + "?id=" + idPedido)
    .then((res) => res.text())

    .then((html) => {
      contenedor.innerHTML = html;

      const modal = new bootstrap.Modal(document.getElementById("modalPedido"));

      modal.show();
    })

    .catch((error) => {
      console.error(error);

      Swal.fire({
        icon: "error",

        title: "Error",

        text: "No fue posible cargar el pedido.",
      });
    });
});
/*======================================================
=            GUARDAR ESTADO DEL PEDIDO
======================================================*/

document.addEventListener("click", function (e) {
  const boton = e.target.closest("#btnGuardarEstado");

  if (!boton) return;

  const idPedido = boton.dataset.id;

  const estado = document.getElementById("nuevoEstado").value;

  const observacion = document.getElementById("observacionPedido").value;

  let datos = new FormData();

  datos.append("idPedido", idPedido);

  datos.append("estado", estado);

  datos.append("observacion", observacion);

  boton.disabled = true;

  boton.innerHTML = `

        <span class="spinner-border spinner-border-sm"></span>

        Guardando...

    `;

  fetch(
    PEDIDOS.actualizarEstado,

    {
      method: "POST",

      body: datos,
    },
  )
    .then((res) => res.json())

    .then((resultado) => {
      boton.disabled = false;

      boton.innerHTML = `

            <i class="fas fa-save"></i>

            Guardar cambios

        `;

      if (resultado.estado) {
        Swal.fire({
          icon: "success",

          title: "Correcto",

          text: resultado.mensaje,

          timer: 1800,

          showConfirmButton: false,
        });

        const modal = bootstrap.Modal.getInstance(
          document.getElementById("modalPedido"),
        );

        if (modal) {
          modal.hide();
        }

        cargarPedidos();
      } else {
        Swal.fire({
          icon: "warning",

          title: "Atención",

          text: resultado.mensaje,
        });
      }
    })

    .catch((error) => {
      console.error(error);

      boton.disabled = false;

      boton.innerHTML = `

            <i class="fas fa-save"></i>

            Guardar cambios

        `;

      Swal.fire({
        icon: "error",

        title: "Error",

        text: "No fue posible actualizar el pedido.",
      });
    });
});
