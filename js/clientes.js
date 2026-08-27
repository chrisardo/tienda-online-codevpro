//=====================================================
// CoDevPro Technology
// js/clientes.js
//=====================================================

document.addEventListener("DOMContentLoaded", () => {
  cargarClientes(1);
  cargarKPIsClientes();
});

/*=============================================
CARGAR CLIENTES
=============================================*/

function cargarClientes(pagina = 1) {
  const buscar = document.getElementById("buscarCliente")?.value || "";

  const estado = document.getElementById("filtroEstadoCliente")?.value || "";
  const rubro = document.getElementById("filtroRubroCliente")?.value || "";
  const ordenar =
    document.getElementById("ordenarCliente")?.value || "recientes";

  const datos = new FormData();

  datos.append("buscar", buscar);
  datos.append("estado", estado);
  datos.append("rubro", rubro);
  datos.append("ordenar", ordenar);
  datos.append("pagina", pagina);

  fetch("ajax/listar_clientes.php", {
    method: "POST",
    body: datos,
  })
    .then((res) => res.json())

    .then((res) => {
      if (!res.estado) {
        Swal.fire({
          icon: "error",
          title: "Error",
          text: "No se pudieron cargar los clientes",
        });

        return;
      }

      document.getElementById("contenedorTablaClientes").innerHTML = res.tabla;

      document.getElementById("contenedorPaginacionClientes").innerHTML =
        res.paginacion;

      document.getElementById("totalRegistrosClientes").textContent =
        `${res.totalRegistros} registros`;
    })

    .catch((error) => {
      console.error(error);
    });
}
/*=============================================
FILTRO RUBRO
=============================================*/

document.addEventListener("change", function (e) {
  if (e.target.id !== "filtroRubroCliente") return;

  cargarClientes(1);
});
/*=============================================
BUSCADOR
=============================================*/

document.addEventListener("input", function (e) {
  if (e.target.id !== "buscarCliente") return;

  clearTimeout(window.timerClientes);

  window.timerClientes = setTimeout(() => {
    cargarClientes(1);
  }, 300);
});

/*=============================================
FILTRO ESTADO
=============================================*/

document.addEventListener("change", function (e) {
  if (e.target.id !== "filtroEstadoCliente") return;

  cargarClientes(1);
});

/*=============================================
ORDENAR
=============================================*/

document.addEventListener("change", function (e) {
  if (e.target.id !== "ordenarCliente") return;

  cargarClientes(1);
});

/*=============================================
PAGINACIÓN
=============================================*/

document.addEventListener("click", function (e) {
  const boton = e.target.closest(".btn-pagina-cliente");

  if (!boton) return;

  e.preventDefault();

  cargarClientes(boton.dataset.pagina);
});

/*=============================================
KPIs CLIENTES
=============================================*/

function cargarKPIsClientes() {
  fetch("ajax/kpi_clientes.php")
    .then((res) => res.json())

    .then((res) => {
      if (!res.estado) return;

      document.getElementById("kpiTotalClientes").textContent =
        res.totalClientes;

      document.getElementById("kpiClientesActivos").textContent =
        res.clientesActivos;

      document.getElementById("kpiClientesPedidos").textContent =
        res.clientesCompradores;

      document.getElementById("kpiClienteTop").textContent = res.clienteTop;
    })

    .catch((error) => {
      console.error(error);
    });
}

/*=============================================
PREVIEW IMAGEN CLIENTE
=============================================*/

document.addEventListener("change", function (e) {
  if (e.target.id !== "imagenCliente") return;

  const archivo = e.target.files[0];

  if (!archivo) return;

  const reader = new FileReader();

  reader.onload = function (evento) {
    document.getElementById("previewCliente").src = evento.target.result;
  };

  reader.readAsDataURL(archivo);
});

/*=============================================
REGISTRAR CLIENTE
=============================================*/

document.addEventListener("submit", function (e) {
  if (e.target.id !== "formNuevoCliente") return;

  e.preventDefault();

  const form = e.target;

  if (!form.checkValidity()) {
    form.classList.add("was-validated");

    return;
  }

  const pass = document.getElementById("passwordCliente").value;

  const confirmar = document.getElementById("confirmarPasswordCliente").value;

  if (pass !== confirmar) {
    Swal.fire({
      icon: "warning",
      title: "Contraseñas diferentes",
      text: "Las contraseñas no coinciden.",
    });

    return;
  }

  const datos = new FormData(form);

  fetch("ajax/registrar_cliente.php", {
    method: "POST",
    body: datos,
  })
    .then((res) => res.json())

    .then((res) => {
      if (!res.estado) {
        Swal.fire({
          icon: "error",
          title: "Error",
          text: res.mensaje,
        });

        return;
      }

      Swal.fire({
        icon: "success",
        title: "Correcto",
        text: res.mensaje,
        timer: 1800,
        showConfirmButton: false,
      });

      form.reset();

      document.getElementById("previewCliente").src =
        "assets/img/sin_imagen.png";

      bootstrap.Modal.getInstance(
        document.getElementById("modalNuevoCliente"),
      ).hide();

      cargarClientes(1);
      cargarKPIsClientes();
    })

    .catch((error) => {
      console.error(error);
    });
});

/*=============================================
VER CLIENTE
=============================================*/

document.addEventListener("click", function (e) {
  const boton = e.target.closest(".btn-ver-cliente");

  if (!boton) return;

  const idCliente = boton.dataset.id;

  const modalElemento = document.getElementById("modalVerCliente");

  const contenido = document.getElementById("contenidoVerCliente");

  const btnWhatsApp = document.getElementById("btnWhatsAppCliente");

  const btnEmail = document.getElementById("btnEmailCliente");

  /*
  |--------------------------------------------------------------------------
  | REINICIAR BOTONES
  |--------------------------------------------------------------------------
  */

  btnWhatsApp.disabled = true;
  btnEmail.disabled = true;

  btnWhatsApp.removeAttribute("data-celular");
  btnWhatsApp.removeAttribute("data-nombre");

  btnEmail.removeAttribute("data-email");
  btnEmail.removeAttribute("data-nombre");

  /*
  |--------------------------------------------------------------------------
  | MOSTRAR MODAL
  |--------------------------------------------------------------------------
  */

  const modal = new bootstrap.Modal(modalElemento);

  modal.show();

  /*
  |--------------------------------------------------------------------------
  | LOADING
  |--------------------------------------------------------------------------
  */

  contenido.innerHTML = `
    
    <div class="text-center py-5">

        <div class="spinner-border text-primary"
            role="status">
        </div>

        <div class="mt-3">
            Cargando información del cliente...
        </div>

    </div>
    
  `;

  /*
  |--------------------------------------------------------------------------
  | CONSULTAR CLIENTE
  |--------------------------------------------------------------------------
  */

  const datos = new FormData();

  datos.append("idCliente", idCliente);

  fetch("ajax/obtener_cliente.php", {
    method: "POST",

    body: datos,
  })
    .then((res) => res.json())

    .then((res) => {
      if (!res.estado) {
        contenido.innerHTML = `
        
          <div class="alert alert-danger m-4">

              <i class="bi bi-exclamation-triangle-fill me-2"></i>

              ${res.mensaje || "No se pudo obtener la información del cliente."}

          </div>
        
        `;

        return;
      }

      /*
      |--------------------------------------------------------------------------
      | MOSTRAR INFORMACIÓN
      |--------------------------------------------------------------------------
      */

      contenido.innerHTML = res.html;

      /*
      |--------------------------------------------------------------------------
      | DATOS DE CONTACTO
      |--------------------------------------------------------------------------
      |
      | Esperamos que obtener_cliente.php devuelva:
      |
      | res.celular
      | res.email
      | res.nombre
      |
      */

      const celular = res.celular || "";
      const email = res.email || "";
      const nombre = res.nombre || "Cliente";

      /*
      |--------------------------------------------------------------------------
      | CONFIGURAR WHATSAPP
      |--------------------------------------------------------------------------
      */

      if (celular.trim() !== "") {
        btnWhatsApp.disabled = false;

        btnWhatsApp.dataset.celular = celular;

        btnWhatsApp.dataset.nombre = nombre;
      }

      /*
      |--------------------------------------------------------------------------
      | CONFIGURAR EMAIL
      |--------------------------------------------------------------------------
      */

      if (email.trim() !== "") {
        btnEmail.disabled = false;

        btnEmail.dataset.email = email;

        btnEmail.dataset.nombre = nombre;
      }
    })

    .catch((error) => {
      console.error(error);

      contenido.innerHTML = `
      
        <div class="alert alert-danger m-4">

            <i class="bi bi-exclamation-triangle-fill me-2"></i>

            Ocurrió un error al cargar la información del cliente.

        </div>
      
      `;
    });
});
/*=============================================
ENVIAR WHATSAPP CLIENTE
=============================================*/

document.addEventListener("click", function (e) {
  const boton = e.target.closest("#btnWhatsAppCliente");

  if (!boton) return;

  const celular = boton.dataset.celular || "";

  const nombre = boton.dataset.nombre || "Cliente";

  if (!celular.trim()) {
    Swal.fire({
      icon: "warning",

      title: "Sin número de celular",

      text: "Este cliente no tiene un número de celular registrado.",
    });

    return;
  }

  /*
  |--------------------------------------------------------------------------
  | LIMPIAR NÚMERO
  |--------------------------------------------------------------------------
  */

  let numero = celular.replace(/\D/g, "");

  /*
  |--------------------------------------------------------------------------
  | PERÚ
  |--------------------------------------------------------------------------
  |
  | Si el número tiene 9 dígitos y empieza con 9,
  | agregamos automáticamente el código +51.
  |
  */

  if (numero.length === 9 && numero.startsWith("9")) {
    numero = "51" + numero;
  }

  /*
  |--------------------------------------------------------------------------
  | MENSAJE
  |--------------------------------------------------------------------------
  */

  const mensaje = `Hola ${nombre}, le escribimos de CoDevPro Technology.`;

  const url = `https://wa.me/${numero}?text=${encodeURIComponent(mensaje)}`;

  window.open(url, "_blank");
});
/*=============================================
ENVIAR EMAIL CLIENTE
=============================================*/

document.addEventListener("click", function (e) {
  const boton = e.target.closest("#btnEmailCliente");

  if (!boton) return;

  const email = boton.dataset.email || "";

  const nombre = boton.dataset.nombre || "Cliente";

  if (!email.trim()) {
    Swal.fire({
      icon: "warning",

      title: "Sin correo electrónico",

      text: "Este cliente no tiene un correo electrónico registrado.",
    });

    return;
  }

  /*
  |--------------------------------------------------------------------------
  | ASUNTO
  |--------------------------------------------------------------------------
  */

  const asunto = "Comunicación - CoDevPro Technology";

  /*
  |--------------------------------------------------------------------------
  | MENSAJE
  |--------------------------------------------------------------------------
  */

  const mensaje =
    `Hola ${nombre},\n\n` +
    `Le escribimos de CoDevPro Technology.\n\n` +
    `Saludos.`;

  /*
  |--------------------------------------------------------------------------
  | ABRIR CLIENTE DE CORREO
  |--------------------------------------------------------------------------
  */

  const mailto =
    `mailto:${email}` +
    `?subject=${encodeURIComponent(asunto)}` +
    `&body=${encodeURIComponent(mensaje)}`;

  window.location.href = mailto;
});
/*=============================================
EDITAR CLIENTE
=============================================*/

document.addEventListener("click", function (e) {
  const boton = e.target.closest(".btn-editar-cliente");

  if (!boton) return;

  const idCliente = boton.dataset.id;

  const modal = new bootstrap.Modal(
    document.getElementById("modalEditarCliente"),
  );

  modal.show();

  document.getElementById("contenidoEditarCliente").innerHTML = `
    
        <div class="text-center py-5">
            <div class="spinner-border text-warning"></div>
        </div>
    
    `;

  const datos = new FormData();

  datos.append("idCliente", idCliente);

  fetch("ajax/obtener_cliente_editar.php", {
    method: "POST",
    body: datos,
  })
    .then((res) => res.json())

    .then((res) => {
      if (!res.estado) {
        Swal.fire({
          icon: "error",
          title: "Error",
          text: res.mensaje,
        });

        return;
      }

      document.getElementById("contenidoEditarCliente").innerHTML = res.html;

      document.getElementById("editarIdCliente").value = idCliente;
    })

    .catch((error) => {
      console.error(error);
    });
});
/*=============================================
ELIMINAR CLIENTE
=============================================*/

document.addEventListener("click", function (e) {
  const boton = e.target.closest(".btn-eliminar-cliente");

  if (!boton) return;

  const idCliente = boton.dataset.id;

  Swal.fire({
    title: "¿Eliminar cliente?",
    text: "El cliente será eliminado.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Eliminar",
    cancelButtonText: "Cancelar",
  })

    .then((result) => {
      if (!result.isConfirmed) return;

      const datos = new FormData();

      datos.append("idCliente", idCliente);

      fetch("ajax/eliminar_cliente.php", {
        method: "POST",
        body: datos,
      })
        .then((res) => res.json())

        .then((res) => {
          if (!res.estado) {
            Swal.fire({
              icon: "error",
              title: "Error",
              text: res.mensaje,
            });

            return;
          }

          Swal.fire({
            icon: "success",
            title: "Correcto",
            text: res.mensaje,
            timer: 1500,
            showConfirmButton: false,
          });

          cargarClientes(1);
          cargarKPIsClientes();
        });
    });
});

/*=============================================
CAMBIAR VISTA LISTA / TARJETAS
=============================================*/

document.addEventListener("click", function (e) {
  const botonLista = e.target.closest("#btnVistaLista");

  const botonTarjetas = e.target.closest("#btnVistaTarjetas");

  if (botonLista) {
    document.getElementById("vistaListaClientes")?.classList.remove("d-none");

    document.getElementById("vistaTarjetasClientes")?.classList.add("d-none");

    document.getElementById("btnVistaLista")?.classList.add("active");

    document.getElementById("btnVistaTarjetas")?.classList.remove("active");
  }

  if (botonTarjetas) {
    document.getElementById("vistaListaClientes")?.classList.add("d-none");

    document
      .getElementById("vistaTarjetasClientes")
      ?.classList.remove("d-none");

    document.getElementById("btnVistaLista")?.classList.remove("active");

    document.getElementById("btnVistaTarjetas")?.classList.add("active");
  }
});
/*=============================================
CARGAR PAISES AL ABRIR MODAL
=============================================*/

document.addEventListener("DOMContentLoaded", () => {
  cargarClientes(1);

  cargarKPIsClientes();

  cargarPaisesCliente();

  cargarRubrosCliente();
});

function cargarPaisesCliente() {
  fetch("ajax/cargar_paises.php")
    .then((res) => res.text())
    .then((html) => {
      const pais = document.getElementById("paisCliente");

      if (pais) {
        pais.innerHTML = html;
      }
    })
    .catch((error) => {
      console.error(error);
    });
}

/*=============================================
PAIS → DEPARTAMENTO
=============================================*/

document.addEventListener("change", function (e) {
  if (e.target.id !== "paisCliente") return;

  const idPais = e.target.value;

  const departamento = document.getElementById("departamentoCliente");
  const provincia = document.getElementById("provinciaCliente");
  const distrito = document.getElementById("distritoCliente");

  departamento.innerHTML = '<option value="">Cargando...</option>';

  provincia.innerHTML = '<option value="">Seleccione</option>';

  distrito.innerHTML = '<option value="">Seleccione</option>';

  if (!idPais) return;

  const datos = new FormData();

  datos.append("id_pais", idPais);

  fetch("ajax/cargar_departamentos.php", {
    method: "POST",
    body: datos,
  })
    .then((res) => res.text())
    .then((html) => {
      departamento.innerHTML = html;
    })
    .catch((error) => {
      console.error(error);
    });
});

/*=============================================
DEPARTAMENTO → PROVINCIA
=============================================*/

document.addEventListener("change", function (e) {
  if (e.target.id !== "departamentoCliente") return;

  const idDepartamento = e.target.value;

  const provincia = document.getElementById("provinciaCliente");
  const distrito = document.getElementById("distritoCliente");

  provincia.innerHTML = '<option value="">Cargando...</option>';

  distrito.innerHTML = '<option value="">Seleccione</option>';

  if (!idDepartamento) return;

  const datos = new FormData();

  datos.append("id_departamento", idDepartamento);

  fetch("ajax/cargar_provincias.php", {
    method: "POST",
    body: datos,
  })
    .then((res) => res.text())
    .then((html) => {
      provincia.innerHTML = html;
    })
    .catch((error) => {
      console.error(error);
    });
});

/*=============================================
PROVINCIA → DISTRITO
=============================================*/

document.addEventListener("change", function (e) {
  if (e.target.id !== "provinciaCliente") return;

  const idProvincia = e.target.value;

  const distrito = document.getElementById("distritoCliente");

  distrito.innerHTML = '<option value="">Cargando...</option>';

  if (!idProvincia) return;

  const datos = new FormData();

  datos.append("id_provincia", idProvincia);

  fetch("ajax/cargar_distritos.php", {
    method: "POST",
    body: datos,
  })
    .then((res) => res.text())
    .then((html) => {
      distrito.innerHTML = html;
    })
    .catch((error) => {
      console.error(error);
    });
});
/*=============================================
CARGAR RUBROS
=============================================*/

function cargarRubrosCliente() {
  fetch("ajax/cargar_rubros.php")
    .then((res) => res.text())

    .then((html) => {
      const comboRubro = document.querySelector(
        '#formNuevoCliente select[name="id_rubro"]',
      );

      if (comboRubro) {
        comboRubro.innerHTML = html;
      }

      const filtroRubro = document.getElementById("filtroRubroCliente");

      if (filtroRubro) {
        filtroRubro.innerHTML =
          '<option value="">Todos los rubros</option>' +
          html.replace('<option value="">Seleccionar</option>', "");
      }
    })

    .catch((error) => {
      console.error(error);
    });
}
/*=============================================
VER / OCULTAR CONTRASEÑA
=============================================*/

document.addEventListener("click", function (e) {
  const btnPassword = e.target.closest("#btnVerPasswordCliente");

  if (btnPassword) {
    const input = document.getElementById("passwordCliente");

    const icono = document.getElementById("iconoPasswordCliente");

    if (input.type === "password") {
      input.type = "text";

      icono.classList.remove("bi-eye");

      icono.classList.add("bi-eye-slash");
    } else {
      input.type = "password";

      icono.classList.remove("bi-eye-slash");

      icono.classList.add("bi-eye");
    }
  }

  const btnConfirmar = e.target.closest("#btnVerConfirmarPasswordCliente");

  if (btnConfirmar) {
    const input = document.getElementById("confirmarPasswordCliente");

    const icono = document.getElementById("iconoConfirmarPasswordCliente");

    if (input.type === "password") {
      input.type = "text";

      icono.classList.remove("bi-eye");

      icono.classList.add("bi-eye-slash");
    } else {
      input.type = "password";

      icono.classList.remove("bi-eye-slash");

      icono.classList.add("bi-eye");
    }
  }
});
/*=============================================
GUARDAR EDICIÓN CLIENTE
=============================================*/

document.addEventListener("submit", function (e) {
  if (e.target.id !== "formEditarCliente") return;

  e.preventDefault();

  const form = e.target;

  const datos = new FormData(form);

  Swal.fire({
    title: "Actualizando cliente",

    text: "Espere un momento...",

    allowOutsideClick: false,

    didOpen: () => {
      Swal.showLoading();
    },
  });

  fetch("ajax/editar_cliente.php", {
    method: "POST",

    body: datos,
  })
    .then((res) => res.json())

    .then((res) => {
      Swal.close();

      if (!res.estado) {
        Swal.fire({
          icon: "error",

          title: "Error",

          text: res.mensaje,
        });

        return;
      }

      Swal.fire({
        icon: "success",

        title: "Correcto",

        text: res.mensaje,

        timer: 1800,

        showConfirmButton: false,
      });

      const modal = bootstrap.Modal.getInstance(
        document.getElementById("modalEditarCliente"),
      );

      modal.hide();

      cargarClientes(1);

      cargarKPIsClientes();
    })

    .catch((error) => {
      console.error(error);

      Swal.fire({
        icon: "error",

        title: "Error",

        text: "No se pudo actualizar el cliente.",
      });
    });
});
/*=============================================
PREVIEW IMAGEN EDITAR CLIENTE
=============================================*/

document.addEventListener("change", function (e) {
  if (e.target.id !== "editarImagenCliente") return;

  const archivo = e.target.files[0];

  if (!archivo) return;

  const reader = new FileReader();

  reader.onload = function (evento) {
    document.getElementById("previewEditarCliente").src = evento.target.result;
  };

  reader.readAsDataURL(archivo);
});
/*=============================================
EDITAR PAIS → DEPARTAMENTO
=============================================*/

document.addEventListener("change", function (e) {
  if (e.target.id !== "editarPaisCliente") return;

  const idPais = e.target.value;

  const departamento = document.getElementById("editarDepartamentoCliente");

  const provincia = document.getElementById("editarProvinciaCliente");

  const distrito = document.getElementById("editarDistritoCliente");

  departamento.innerHTML = "<option>Cargando...</option>";

  provincia.innerHTML = "<option>Seleccione</option>";

  distrito.innerHTML = "<option>Seleccione</option>";

  const datos = new FormData();

  datos.append("id_pais", idPais);

  fetch("ajax/cargar_departamentos.php", {
    method: "POST",

    body: datos,
  })
    .then((res) => res.text())

    .then((html) => {
      departamento.innerHTML = html;
    });
});
/*=============================================
EDITAR DEPARTAMENTO → PROVINCIA
=============================================*/

document.addEventListener("change", function (e) {
  if (e.target.id !== "editarDepartamentoCliente") return;

  const idDepartamento = e.target.value;

  const provincia = document.getElementById("editarProvinciaCliente");

  const distrito = document.getElementById("editarDistritoCliente");

  provincia.innerHTML = "<option>Cargando...</option>";

  distrito.innerHTML = "<option>Seleccione</option>";

  const datos = new FormData();

  datos.append("id_departamento", idDepartamento);

  fetch("ajax/cargar_provincias.php", {
    method: "POST",

    body: datos,
  })
    .then((res) => res.text())

    .then((html) => {
      provincia.innerHTML = html;
    });
});
/*=============================================
EDITAR PROVINCIA → DISTRITO
=============================================*/

document.addEventListener("change", function (e) {
  if (e.target.id !== "editarProvinciaCliente") return;

  const idProvincia = e.target.value;

  const distrito = document.getElementById("editarDistritoCliente");

  distrito.innerHTML = "<option>Cargando...</option>";

  const datos = new FormData();

  datos.append("id_provincia", idProvincia);

  fetch("ajax/cargar_distritos.php", {
    method: "POST",

    body: datos,
  })
    .then((res) => res.text())

    .then((html) => {
      distrito.innerHTML = html;
    });
});
/*=============================================
EXPORTAR CLIENTES (BOTÓN)
=============================================*/

function exportarExcelClientes() {
  Swal.fire({
    title: "Exportar Clientes",

    width: 700,

    html: `
      <div class="text-start">

        <div class="alert alert-info mb-3">
          Seleccione los datos que desea exportar.
        </div>

        <div class="row">

          <div class="col-md-6">

            <div class="form-check">
              <input class="form-check-input columna-exportar" type="checkbox" value="nombre" checked>
              <label class="form-check-label">Nombre</label>
            </div>

            <div class="form-check">
              <input class="form-check-input columna-exportar" type="checkbox" value="dni_o_ruc" checked>
              <label class="form-check-label">DNI / RUC</label>
            </div>

            <div class="form-check">
              <input class="form-check-input columna-exportar" type="checkbox" value="celular" checked>
              <label class="form-check-label">Celular</label>
            </div>

            <div class="form-check">
              <input class="form-check-input columna-exportar" type="checkbox" value="email" checked>
              <label class="form-check-label">Email</label>
            </div>

            <div class="form-check">
              <input class="form-check-input columna-exportar" type="checkbox" value="direccion">
              <label class="form-check-label">Dirección</label>
            </div>

            <div class="form-check">
              <input class="form-check-input columna-exportar" type="checkbox" value="estado" checked>
              <label class="form-check-label">Estado</label>
            </div>

          </div>

          <div class="col-md-6">

            <div class="form-check">
              <input class="form-check-input columna-exportar" type="checkbox" value="pais">
              <label class="form-check-label">País</label>
            </div>

            <div class="form-check">
              <input class="form-check-input columna-exportar" type="checkbox" value="departamento">
              <label class="form-check-label">Departamento</label>
            </div>

            <div class="form-check">
              <input class="form-check-input columna-exportar" type="checkbox" value="provincia">
              <label class="form-check-label">Provincia</label>
            </div>

            <div class="form-check">
              <input class="form-check-input columna-exportar" type="checkbox" value="distrito">
              <label class="form-check-label">Distrito</label>
            </div>

            <div class="form-check">
              <input class="form-check-input columna-exportar" type="checkbox" value="rubro">
              <label class="form-check-label">Rubro</label>
            </div>

            <div class="form-check">
              <input class="form-check-input columna-exportar" type="checkbox" value="fecha_registro">
              <label class="form-check-label">Fecha Registro</label>
            </div>

          </div>

        </div>

      </div>
    `,

    showCancelButton: true,

    confirmButtonText: "Exportar Excel",

    cancelButtonText: "Cancelar",

    preConfirm: () => {
      const columnas = [];

      document.querySelectorAll(".columna-exportar:checked").forEach((item) => {
        columnas.push(item.value);
      });

      if (columnas.length === 0) {
        Swal.showValidationMessage("Seleccione al menos una columna");

        return false;
      }

      return columnas;
    },
  }).then((resultado) => {
    if (!resultado.isConfirmed) return;

    const columnas = resultado.value;

    const form = document.createElement("form");

    form.method = "POST";

    form.action = "ajax/exportar_clientes_excel.php";

    form.target = "_blank";

    agregarCampo(form, "columnas", JSON.stringify(columnas));

    agregarCampo(
      form,
      "buscar",
      document.getElementById("buscarCliente")?.value || "",
    );

    agregarCampo(
      form,
      "estado",
      document.getElementById("filtroEstadoCliente")?.value || "",
    );

    agregarCampo(
      form,
      "pais",
      document.getElementById("filtroPaisCliente")?.value || "",
    );

    agregarCampo(
      form,
      "rubro",
      document.getElementById("filtroRubroCliente")?.value || "",
    );

    agregarCampo(
      form,
      "ordenar",
      document.getElementById("ordenarCliente")?.value || "",
    );

    document.body.appendChild(form);

    form.submit();

    document.body.removeChild(form);
  });
}
/*=============================================
HELPER FORM
=============================================*/

function agregarCampo(form, nombre, valor) {
  const input = document.createElement("input");

  input.type = "hidden";

  input.name = nombre;

  input.value = valor;

  form.appendChild(input);
}
/*=============================================
ENVIAR WHATSAPP DESDE LISTA DE CLIENTES
=============================================*/

document.addEventListener("click", function (e) {
  const boton = e.target.closest(".btn-whatsapp-cliente");

  if (!boton) return;

  const celular = boton.dataset.celular || "";

  const nombre = boton.dataset.nombre || "Cliente";

  if (!celular.trim()) {
    Swal.fire({
      icon: "warning",
      title: "Sin número de celular",
      text: "Este cliente no tiene un número de celular registrado.",
    });

    return;
  }

  /*=============================================
  LIMPIAR NÚMERO
  =============================================*/

  let numero = celular.replace(/\D/g, "");

  /*=============================================
  PERÚ
  =============================================*/

  if (numero.length === 9 && numero.startsWith("9")) {
    numero = "51" + numero;
  }

  /*=============================================
  VALIDAR
  =============================================*/

  if (numero.length < 10) {
    Swal.fire({
      icon: "warning",
      title: "Número inválido",
      text: "El número de celular registrado no es válido.",
    });

    return;
  }

  /*=============================================
  MENSAJE
  =============================================*/

  const mensaje =
    `Hola ${nombre},\n\n` +
    `Le escribimos de CoDevPro Technology.\n\n` +
    `Saludos.`;

  /*=============================================
  WHATSAPP
  =============================================*/

  const url =
    "https://wa.me/" + numero + "?text=" + encodeURIComponent(mensaje);

  window.open(url, "_blank");
});

/*=============================================
ENVIAR EMAIL DESDE LISTA DE CLIENTES
=============================================*/

document.addEventListener("click", function (e) {
  const boton = e.target.closest(".btn-email-cliente");

  if (!boton) return;

  const email = boton.dataset.email || "";

  const nombre = boton.dataset.nombre || "Cliente";

  if (!email.trim()) {
    Swal.fire({
      icon: "warning",
      title: "Sin correo electrónico",
      text: "Este cliente no tiene un correo electrónico registrado.",
    });

    return;
  }

  /*=============================================
  ASUNTO
  =============================================*/

  const asunto = "Comunicación - CoDevPro Technology";

  /*=============================================
  MENSAJE
  =============================================*/

  const mensaje =
    `Hola ${nombre},\n\n` +
    `Le escribimos de CoDevPro Technology.\n\n` +
    `Saludos.`;

  /*=============================================
  MAILTO
  =============================================*/

  const mailto =
    "mailto:" +
    email +
    "?subject=" +
    encodeURIComponent(asunto) +
    "&body=" +
    encodeURIComponent(mensaje);

  window.location.href = mailto;
});
