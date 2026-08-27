//======================================================
// CoDevPro Technology
// js/adm_nuevo_producto.js
//======================================================
function mostrarAlertProducto(tipo, mensaje) {
  const alertBox = document.getElementById("alertProducto");

  if (!alertBox) return;

  alertBox.className = "alert alert-" + tipo;

  alertBox.innerHTML = `

        <i class="bi bi-check-circle-fill me-2"></i>

        ${mensaje}

    `;

  alertBox.classList.remove("d-none");

  window.scrollTo({
    top: 0,
    behavior: "smooth",
  });

  setTimeout(() => {
    alertBox.classList.add("d-none");
  }, 5000);
}
function mensajeError(texto) {
  const mensajeProducto = document.getElementById("mensajeProducto");

  if (mensajeProducto) {
    mensajeProducto.innerHTML = `

        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">

            <i class="bi bi-exclamation-triangle-fill me-2"></i>

            ${texto}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>

        </div>

        `;

    window.scrollTo({
      top: 0,
      behavior: "smooth",
    });

    setTimeout(() => {
      const alerta = document.querySelector("#mensajeProducto .alert");

      if (alerta) {
        alerta.remove();
      }
    }, 5000);
  } else {
    console.error(texto);
  }
}
/*======================================================
VARIABLES GLOBALES
======================================================*/

let imagenesSeleccionadas = [];

const dropZone = document.getElementById("dropZone");

const inputImagenes = document.getElementById("imagenesProducto");

const previewImagenes = document.getElementById("previewImagenes");

const contadorImagenes = document.getElementById("contadorImagenes");

const resumenImagenes = document.getElementById("resImagenes");

/*======================================================
CONFIGURACIÓN IMÁGENES
======================================================*/

const MAX_IMAGENES = 4;

const MAX_SIZE = 2.7 * 1024 * 1024;

const FORMATOS_PERMITIDOS = [
  "image/jpeg",
  "image/jpg",
  "image/png",
  "image/webp",
];

/*======================================================
CLICK EN DROPZONE
======================================================*/

if (dropZone && inputImagenes) {
  dropZone.addEventListener("click", function (e) {
    if (e.target.tagName !== "BUTTON") {
      inputImagenes.click();
    } else {
      inputImagenes.click();
    }
  });
}

/*======================================================
DRAG & DROP ARCHIVOS
======================================================*/

if (dropZone) {
  dropZone.addEventListener("dragover", function (e) {
    e.preventDefault();

    dropZone.classList.add("border-primary");
  });

  dropZone.addEventListener("dragleave", function () {
    dropZone.classList.remove("border-primary");
  });

  dropZone.addEventListener("drop", function (e) {
    e.preventDefault();

    dropZone.classList.remove("border-primary");

    const archivos = Array.from(e.dataTransfer.files);

    procesarImagenes(archivos);
  });
}

/*======================================================
SELECCIONAR IMÁGENES INPUT
======================================================*/

if (inputImagenes) {
  inputImagenes.addEventListener("change", function () {
    const archivos = Array.from(this.files);

    procesarImagenes(archivos);

    this.value = "";
  });
}

/*======================================================
PROCESAR IMÁGENES
======================================================*/

function procesarImagenes(archivos) {
  let nuevasImagenes = [];

  archivos.forEach((archivo) => {
    if (!validarImagen(archivo)) {
      return;
    }

    nuevasImagenes.push(archivo);
  });

  let todas = [...imagenesSeleccionadas, ...nuevasImagenes];

  /*
    =========================================
    ELIMINAR DUPLICADOS
    =========================================
    */

  todas = todas.filter(
    (archivo, index, self) =>
      index ===
      self.findIndex((a) => a.name === archivo.name && a.size === archivo.size),
  );

  /*
    =========================================
    LIMITE IMÁGENES
    =========================================
    */

  if (todas.length > MAX_IMAGENES) {
    mensajeError("Solo puede subir máximo 4 imágenes.");

    todas = todas.slice(0, MAX_IMAGENES);
  }

  imagenesSeleccionadas = todas;

  reconstruirInputFiles();

  renderizarImagenes();
}

/*======================================================
VALIDAR IMAGEN
======================================================*/

function validarImagen(archivo) {
  if (!FORMATOS_PERMITIDOS.includes(archivo.type)) {
    mensajeError(
      "Formato no permitido:\n" +
        archivo.name +
        "\nSolo JPG, JPEG, PNG o WEBP.",
    );

    return false;
  }

  if (archivo.size > MAX_SIZE) {
    mensajeError(archivo.name + "\nSupera el límite permitido de 2.7 MB.");

    return false;
  }

  return true;
}

/*======================================================
RECONSTRUIR INPUT FILE
======================================================*/

function reconstruirInputFiles() {
  if (!inputImagenes) return;

  const dataTransfer = new DataTransfer();

  imagenesSeleccionadas.forEach((imagen) => {
    dataTransfer.items.add(imagen);
  });

  inputImagenes.files = dataTransfer.files;
}

/*======================================================
RENDERIZAR GALERÍA
======================================================*/

function renderizarImagenes() {
  if (!previewImagenes) return;

  previewImagenes.innerHTML = "";

  imagenesSeleccionadas.forEach((archivo, index) => {
    const reader = new FileReader();

    reader.onload = function (e) {
      previewImagenes.innerHTML += `


                <div class="col-md-3 imagen-item"
                     data-index="${index}">


                    <div class="card shadow-sm h-100 position-relative">


                        <div class="drag-handle">

                            <i class="bi bi-grip-vertical"></i>

                        </div>



                        ${
                          index === 0
                            ? `
                            <span class="badge bg-warning text-dark badge-principal">

                                Principal

                            </span>
                            `
                            : ""
                        }





                        <img src="${e.target.result}"

                             class="card-img-top img-producto-preview">






                        <div class="card-body p-2">


                            <small class="d-block text-truncate">

                                ${archivo.name}

                            </small>





                            <button type="button"

                                    class="btn btn-danger btn-sm w-100 mt-2"

                                    onclick="eliminarImagen(${index})">


                                <i class="bi bi-trash"></i>

                                Eliminar


                            </button>



                        </div>




                    </div>



                </div>


                `;
    };

    reader.readAsDataURL(archivo);
  });

  actualizarContadorImagenes();

  setTimeout(
    inicializarSortable,

    200,
  );
}

/*======================================================
CONTADOR IMÁGENES
======================================================*/

function actualizarContadorImagenes() {
  const total = imagenesSeleccionadas.length;

  if (contadorImagenes) {
    contadorImagenes.textContent = total + " imágenes";
  }

  if (resumenImagenes) {
    resumenImagenes.textContent = total;
  }
}

/*======================================================
ELIMINAR IMAGEN
======================================================*/

window.eliminarImagen = function (index) {
  imagenesSeleccionadas.splice(
    index,

    1,
  );

  reconstruirInputFiles();

  renderizarImagenes();
};

/*======================================================
ORDENAR IMÁGENES SORTABLE
======================================================*/

function inicializarSortable() {
  if (!previewImagenes) return;

  if (previewImagenes.sortableInicializado) {
    return;
  }

  Sortable.create(
    previewImagenes,

    {
      animation: 250,

      handle: ".drag-handle",

      ghostClass: "sortable-ghost",

      onEnd: function () {
        let nuevoOrden = [];

        document.querySelectorAll(".imagen-item").forEach((item) => {
          const indice = parseInt(item.dataset.index);

          nuevoOrden.push(imagenesSeleccionadas[indice]);
        });

        imagenesSeleccionadas = nuevoOrden;

        reconstruirInputFiles();

        renderizarImagenes();
      },
    },
  );

  previewImagenes.sortableInicializado = true;
}
//======================================================
// PARTE 2/3
// RESUMEN + GANANCIA + CALIDAD + VALIDACIONES
//======================================================

document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("formNuevoProducto");

  if (!form) return;

  /*======================================================
  ELEMENTOS
  ======================================================*/

  const nombre = document.getElementById("nombre");

  const tipo = document.getElementById("tipo");

  const codigo = document.getElementById("codigo");

  const categoria = document.getElementById("id_categorias");

  const marca = document.getElementById("id_marca");

  const proveedor = document.getElementById("id_provedor");

  const sucursal =
    document.getElementById("id_sucursal") ||
    document.getElementById("sucursal_servicio");
  if (sucursal) {
    sucursal.required = true;
  }
  const costoCompra = document.getElementById("costo_compra");

  const precio = document.getElementById("precio");

  const precioServicio = document.getElementById("precio_servicio");

  const stock = document.getElementById("stock");

  const ganancia = document.getElementById("ganancia");

  const margen = document.getElementById("margen");

  const camposProducto = document.getElementById("camposProducto");

  const camposServicio = document.getElementById("camposServicio");

  /*======================================================
  RESUMEN LATERAL
  ======================================================*/

  function actualizarResumen() {
    const resCategoria = document.getElementById("resCategoria");
    const resMarca = document.getElementById("resMarca");
    const resStock = document.getElementById("resStock");
    const resImagenes = document.getElementById("resImagenes");

    document.getElementById("resNombre").textContent =
      nombre.value.trim() || "-";

    /*=========================================
    PRODUCTO
    =========================================*/
    if (tipo.value === "Producto") {
      document.getElementById("resPrecio").textContent =
        "S/ " + parseFloat(precio.value || 0).toFixed(2);

      if (resCategoria) {
        resCategoria.textContent =
          categoria.options[categoria.selectedIndex]?.text || "-";
      }

      if (resMarca) {
        resMarca.textContent = marca.options[marca.selectedIndex]?.text || "-";
      }

      if (resStock) {
        resStock.textContent = stock.value || "0";
      }

      if (resImagenes) {
        resImagenes.textContent = imagenesSeleccionadas.length;
      }
    } else if (tipo.value === "Servicio") {
      /*=========================================
      SERVICIO
      =========================================*/
      document.getElementById("resPrecio").textContent =
        "S/ " + parseFloat(precioServicio.value || 0).toFixed(2);

      if (resCategoria) {
        resCategoria.textContent = "No aplica";
      }

      if (resMarca) {
        resMarca.textContent = "No aplica";
      }

      if (resStock) {
        resStock.textContent = "∞";
      }

      if (resImagenes) {
        resImagenes.textContent = "-";
      }
    } else {
      /*=========================================
      SIN TIPO
      =========================================*/
      document.getElementById("resPrecio").textContent = "S/ 0.00";

      if (resCategoria) resCategoria.textContent = "-";

      if (resMarca) resMarca.textContent = "-";

      if (resStock) resStock.textContent = "-";

      if (resImagenes) resImagenes.textContent = "-";
    }
  }

  /*======================================================
CALCULAR GANANCIA
======================================================*/

  function calcularGanancia() {
    if (tipo.value === "Servicio") {
      if (ganancia) ganancia.value = "0.00";

      if (margen) margen.value = "0";

      return;
    }

    const costo = parseFloat(costoCompra.value) || 0;

    const venta = parseFloat(precio.value) || 0;

    const utilidad = venta - costo;

    if (ganancia) ganancia.value = utilidad.toFixed(2);

    if (margen) {
      let porcentaje = 0;

      if (costo > 0) {
        porcentaje = (utilidad / costo) * 100;
      }

      margen.value = porcentaje.toFixed(2);
    }
  }

  /*======================================================
  CALIDAD PRODUCTO
  ======================================================*/

  function actualizarCalidad() {
    let puntos = 0;
    let total = 0;
    let checks = [];

    /*=========================================
    PRODUCTO
    =========================================*/
    if (tipo.value === "Producto") {
      total = 9;

      if (codigo.value.trim()) {
        puntos++;
        checks.push("✔ Código");
      }

      if (nombre.value.trim()) {
        puntos++;
        checks.push("✔ Nombre");
      }

      if (precio.value > 0) {
        puntos++;
        checks.push("✔ Precio");
      }

      if (parseFloat(costoCompra.value) > 0) {
        puntos++;
        checks.push("✔ Costo Compra");
      }

      if (parseInt(stock.value) >= 0) {
        puntos++;
        checks.push("✔ Stock");
      }

      if (categoria.value) {
        puntos++;
        checks.push("✔ Categoría");
      }

      if (marca.value) {
        puntos++;
        checks.push("✔ Marca");
      }

      if (sucursal.value) {
        puntos++;
        checks.push("✔ Sucursal");
      }

      if (imagenesSeleccionadas.length > 0) {
        puntos++;
        checks.push("✔ Imágenes");
      }
    } else if (tipo.value === "Servicio") {
      /*=========================================
      SERVICIO
      =========================================*/
      total = 5;

      if (codigo.value.trim()) {
        puntos++;
        checks.push("✔ Código");
      }

      if (nombre.value.trim()) {
        puntos++;
        checks.push("✔ Nombre");
      }

      if (parseFloat(precioServicio.value) > 0) {
        puntos++;
        checks.push("✔ Precio Servicio");
      }

      if (document.getElementById("sucursal_servicio")?.value) {
        puntos++;
        checks.push("✔ Sucursal");
      }

      if (document.getElementById("descripcion_servicio")?.value.trim()) {
        puntos++;
        checks.push("✔ Descripción");
      }
    }

    const porcentaje = total > 0 ? Math.round((puntos * 100) / total) : 0;

    const barra = document.getElementById("barraProducto");

    const porcentajeTexto = document.getElementById("porcentajeProducto");

    if (barra) {
      barra.style.width = porcentaje + "%";

      barra.classList.remove("bg-danger", "bg-warning", "bg-success");

      if (porcentaje < 40) {
        barra.classList.add("bg-danger");
      } else if (porcentaje < 80) {
        barra.classList.add("bg-warning");
      } else {
        barra.classList.add("bg-success");
      }
    }

    if (porcentajeTexto) {
      porcentajeTexto.textContent = porcentaje + "%";
    }

    const check = document.getElementById("checkProducto");

    if (check) {
      if (checks.length > 0) {
        check.innerHTML = checks.join("<br>");
      } else {
        check.innerHTML =
          '<div class="text-muted">Complete la información.</div>';
      }
    }

    /*=========================================
    CAMBIAR TITULO
    =========================================*/
    const tituloCalidad = document.querySelector(
      ".card-header h5 .bi-graph-up-arrow",
    )?.parentElement;

    if (tituloCalidad) {
      tituloCalidad.innerHTML =
        tipo.value === "Servicio"
          ? '<i class="bi bi-tools text-success me-2"></i> Calidad del Servicio'
          : '<i class="bi bi-graph-up-arrow text-success me-2"></i> Calidad del Producto';
    }
  }

  /*======================================================
  CAMBIO PRODUCTO / SERVICIO
  ======================================================*/

  function cambiarTipo() {
    const valor = tipo.value;

    const precio = document.getElementById("precio");
    const costoCompra = document.getElementById("costo_compra");
    const stock = document.getElementById("stock");
    const categoria = document.getElementById("id_categorias");
    const sucursal = document.getElementById("id_sucursal");

    const precioServicio = document.getElementById("precio_servicio");

    if (valor === "Producto") {
      camposProducto.style.display = "block";
      camposServicio.style.display = "none";

      if (precio) precio.required = true;
      precio.disabled = false;

      if (costoCompra) costoCompra.required = true;
      costoCompra.disabled = false;

      if (stock) stock.required = true;
      stock.disabled = false;

      if (categoria) categoria.required = true;
      categoria.disabled = false;

      if (sucursal) sucursal.required = true;
      sucursal.disabled = false;

      if (precioServicio) {
        precioServicio.required = false;
        precioServicio.disabled = true;
      }
    } else if (valor === "Servicio") {
      camposProducto.style.display = "none";
      camposServicio.style.display = "block";

      precio.required = false;
      precio.disabled = true;

      costoCompra.required = false;
      costoCompra.disabled = true;

      stock.required = false;
      stock.disabled = true;

      categoria.required = false;
      categoria.disabled = true;

      sucursal.required = false;
      sucursal.disabled = true;

      if (precioServicio) {
        precioServicio.required = true;
        precioServicio.disabled = false;
      }
    } else {
      camposProducto.style.display = "none";
      camposServicio.style.display = "none";

      precio.required = false;
      costoCompra.required = false;
      stock.required = false;
      categoria.required = false;
      sucursal.required = false;

      precioServicio.required = false;
    }
  }

  tipo.addEventListener("change", cambiarTipo);

  /*======================================================
EVENTOS GENERALES
======================================================*/

  document.querySelectorAll("input,select,textarea").forEach((elemento) => {
    elemento.addEventListener("input", () => {
      actualizarResumen();

      calcularGanancia();

      actualizarCalidad();
    });

    elemento.addEventListener("change", () => {
      actualizarResumen();

      calcularGanancia();

      actualizarCalidad();
    });
  });

  /*======================================================
VALIDACIONES COMPLETAS
======================================================*/
  window.validarProducto = () => {
    const tipo = document.getElementById("tipo")?.value || "";

    const codigo = document.getElementById("codigo")?.value.trim() || "";

    const nombre = document.getElementById("nombre")?.value.trim() || "";

    if (!codigo) {
      mensajeError("Ingrese el código.");
      return false;
    }

    if (!nombre) {
      mensajeError("Ingrese el nombre.");
      return false;
    }

    if (!tipo) {
      mensajeError("Seleccione el tipo.");
      return false;
    }

    /*=========================================
    PRODUCTO
    =========================================*/

    if (tipo === "Producto") {
      const costoCompra = parseFloat(
        document.getElementById("costo_compra")?.value || 0,
      );

      if (costoCompra <= 0) {
        mensajeError("Ingrese el costo de compra.");
        return false;
      }

      const stock = parseInt(document.getElementById("stock")?.value || 0);

      const categoria = document.getElementById("id_categorias")?.value || "";
      const marca = document.getElementById("id_marca")?.value || "";
      const proveedor = document.getElementById("id_provedor")?.value || "";
      const sucursal = document.getElementById("id_sucursal")?.value || "";

      const precio = parseFloat(document.getElementById("precio")?.value || 0);

      if (precio <= 0) {
        mensajeError("Ingrese un precio válido.");
        return false;
      }

      if (!categoria) {
        mensajeError("Seleccione una categoría.");

        return false;
      }
      if (!marca) {
        mensajeError("Seleccione una marca.");

        return false;
      }
      if (!proveedor) {
        mensajeError("Seleccione un proveedor.");

        return false;
      }
      if (!sucursal) {
        mensajeError("Seleccione una sucursal.");

        return false;
      }

      if (stock < 0) {
        mensajeError("El stock no puede ser negativo.");

        return false;
      }
    }

    /*=========================================
    SERVICIO
    =========================================*/

    if (tipo === "Servicio") {
      const precioServicio = parseFloat(
        document.getElementById("precio_servicio")?.value || 0,
      );

      const sucursalServicio =
        document.getElementById("sucursal_servicio")?.value || "";

      if (precioServicio <= 0) {
        mensajeError("Ingrese el precio del servicio.");

        return false;
      }

      if (!sucursalServicio) {
        mensajeError("Seleccione una sucursal.");

        return false;
      }
    }
    if (tipo === "Producto") {
      if (imagenesSeleccionadas.length === 0) {
        mensajeError("Seleccione al menos una imagen del producto.");

        return false;
      }
    }

    return true;
  };
  /*======================================================
INICIALIZAR
======================================================*/

  cambiarTipo();

  actualizarResumen();

  calcularGanancia();

  actualizarCalidad();
});
//======================================================
// PARTE 3/3
// ENVÍO AJAX + IMÁGENES + LOADING
//======================================================

document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("formNuevoProducto");

  if (!form) return;
  if (!form) {
    console.error("No existe formNuevoProducto");
    return;
  }

  console.log("Formulario encontrado");
  const btnPublicar = document.getElementById("btnPublicarProducto");

  const loading = document.getElementById("loadingProducto");

  /*======================================================
MOSTRAR / OCULTAR LOADING
======================================================*/

  function mostrarLoading() {
    if (loading) {
      loading.classList.remove("d-none");
    }
  }

  function ocultarLoading() {
    if (loading) {
      loading.classList.add("d-none");
    }
  }

  /*======================================================
PREPARAR CHECKBOX
======================================================*/

  function obtenerCheckbox(id) {
    const elemento = document.getElementById(id);

    if (!elemento) return 0;

    return elemento.checked ? 1 : 0;
  }

  /*======================================================
BOTÓN VISTA PREVIA
======================================================*/

  const btnVistaPrevia = document.getElementById("btnVistaPrevia");

  if (btnVistaPrevia) {
    btnVistaPrevia.addEventListener("click", () => {
      const nombre = document.getElementById("nombre").value;

      const precio =
        document.getElementById("tipo").value === "Servicio"
          ? document.getElementById("precio_servicio").value
          : document.getElementById("precio").value;

      mensajeError(
        "Vista previa\n\n" +
          "Producto: " +
          (nombre || "Sin nombre") +
          "\nPrecio: S/ " +
          (precio || "0.00"),
      );
    });
  }

  /*======================================================
SUBMIT FORMULARIO
======================================================*/

  form.addEventListener("submit", async function (e) {
    e.preventDefault();
    console.log("SUBMIT EJECUTADO");

    if (typeof validarProducto === "function") {
      if (!validarProducto()) {
        return;
      }
    }

    btnPublicar.disabled = true;

    mostrarLoading();

    try {
      /*======================================================
      CREAR FORMDATA
      ======================================================*/

      const formData = new FormData(form);

      /*======================================================
      NORMALIZAR PRODUCTO / SERVICIO
      ======================================================*/

      const tipo = document.getElementById("tipo").value;

      // Precio servicio pasa a precio

      if (tipo === "Servicio") {
        formData.set(
          "precio",

          document.getElementById("precio_servicio").value,
        );

        const sucursalServicio = document.getElementById("sucursal_servicio");

        if (sucursalServicio) {
          formData.set("id_sucursal", sucursalServicio.value);
        }
      }

      /*======================================================
      CHECKBOX 0 / 1
      ======================================================*/

      formData.set(
        "oferta",

        obtenerCheckbox("oferta"),
      );

      formData.set(
        "destacado",

        obtenerCheckbox("destacado"),
      );

      formData.set(
        "nuevo",

        obtenerCheckbox("nuevo"),
      );

      formData.set(
        "envio_gratis",

        obtenerCheckbox("envio_gratis"),
      );
      formData.set("aplica_impuesto", obtenerCheckbox("aplica_impuesto"));
      /*======================================================
      ENVIAR PRODUCTO
      ======================================================*/

      const respuesta = await fetch("ajax/adm_registrar_producto.php", {
        method: "POST",
        body: formData,
      });

      const texto = await respuesta.text();

      console.log("RESPUESTA PHP:");
      console.log(texto);

      const resultado = JSON.parse(texto);

      if (!resultado.estado) {
        mensajeError(resultado.mensaje);

        btnPublicar.disabled = false;

        ocultarLoading();

        return;
      }

      const idProducto = resultado.idProducto;

      /*======================================================
      SUBIR IMÁGENES
      ======================================================*/

      if (imagenesSeleccionadas.length > 0) {
        const datosImagen = new FormData();

        datosImagen.append(
          "idProducto",

          idProducto,
        );

        imagenesSeleccionadas.forEach((imagen, index) => {
          datosImagen.append(
            "imagenes[]",

            imagen,
          );

          datosImagen.append(
            "orden[]",

            index + 1,
          );
        });

        const subirImagen = await fetch(
          "ajax/adm_subir_imagen_producto.php",

          {
            method: "POST",

            body: datosImagen,
          },
        );

        const resultadoImagen = await subirImagen.json();

        if (!resultadoImagen.estado) {
          mensajeError(resultadoImagen.mensaje);

          btnPublicar.disabled = false;

          ocultarLoading();

          return;
        }
      }

      /*======================================================
      FINALIZADO
      ======================================================*/
      const mensajeProducto = document.getElementById("mensajeProducto");

      mensajeProducto.innerHTML = `
          <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">

              <i class="bi bi-check-circle-fill me-2"></i>

              ${
                tipo === "Servicio"
                  ? "Servicio registrado correctamente."
                  : "Producto registrado correctamente."
              }

              <button type="button"
                      class="btn-close"
                      data-bs-dismiss="alert">
              </button>

          </div>
      `;

      window.scrollTo({
        top: 0,
        behavior: "smooth",
      });

      /*=========================================
      LIMPIAR FORMULARIO
      =========================================*/

      form.reset();

      imagenesSeleccionadas = [];

      reconstruirInputFiles();

      renderizarImagenes();
      /*=========================================
      REINICIAR RESUMEN
      =========================================*/

      document.getElementById("resNombre").textContent = "-";

      document.getElementById("resCategoria").textContent = "-";

      document.getElementById("resMarca").textContent = "-";

      document.getElementById("resPrecio").textContent = "S/ 0.00";

      document.getElementById("resStock").textContent = "0";

      document.getElementById("resImagenes").textContent = "0";

      /*=========================================
      REINICIAR CALIDAD
      =========================================*/

      const barra = document.getElementById("barraProducto");

      barra.style.width = "0%";

      barra.classList.remove("bg-success", "bg-warning", "bg-danger");

      document.getElementById("porcentajeProducto").textContent = "0%";

      document.getElementById("checkProducto").innerHTML = `
    <div class="text-muted">
        Complete la información del producto.
    </div>
`;

      /*=========================================
      REINICIAR GANANCIA
      =========================================*/

      const ganancia = document.getElementById("ganancia");

      if (ganancia) {
        ganancia.value = "";
      }

      const margen = document.getElementById("margen");

      if (margen) {
        margen.value = "";
      }

      /*=========================================
      OCULTAR CAMPOS
      =========================================*/

      document.getElementById("camposProducto").style.display = "none";

      document.getElementById("camposServicio").style.display = "none";

      /*=========================================
      REINICIAR TÍTULO CALIDAD
      =========================================*/

      const tituloCalidad = document.querySelector(
        ".card-header h5 .bi-graph-up-arrow",
      )?.parentElement;

      if (tituloCalidad) {
        tituloCalidad.innerHTML = `
      <i class="bi bi-graph-up-arrow text-success me-2"></i>
      Calidad del Producto
  `;
      }

      /*=========================================
      REINICIAR CONTADOR IMÁGENES
      =========================================*/

      const contador = document.getElementById("contadorImagenes");

      if (contador) {
        contador.textContent = "0 imágenes";
      }

      /*=========================================
      REINICIAR RESUMEN
      =========================================*/

      if (typeof actualizarResumen === "function") {
        actualizarResumen();
      }

      if (typeof calcularGanancia === "function") {
        calcularGanancia();
      }

      if (typeof actualizarCalidad === "function") {
        actualizarCalidad();
      }
      setTimeout(() => {
        const alerta = document.querySelector("#mensajeProducto .alert");

        if (alerta) {
          alerta.remove();
        }
      }, 5000);
    } catch (error) {
      console.error(error);

      mensajeError("Ocurrió un error inesperado al registrar.");
    } finally {
      btnPublicar.disabled = false;

      ocultarLoading();
    }
  });
});
