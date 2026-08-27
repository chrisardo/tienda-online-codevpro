//Toda esta parte es de js/editar_imagenes.js
document.addEventListener("DOMContentLoaded", () => {
  const modal = document.getElementById("modalEditarImagenes");

  if (!modal) return;

  modal.addEventListener("show.bs.modal", function (event) {
    const boton = event.relatedTarget;

    const idProducto = boton.dataset.id;

    document.getElementById("img_idProducto").value = idProducto;

    // Limpiar imágenes editadas
    Object.keys(imagenesEditadas).forEach((k) => delete imagenesEditadas[k]);
    hashesImagenes.clear();

    cargarImagenes(idProducto);
  });
});

/*=========================================
=           VARIABLES GLOBALES
=========================================*/

const imagenesEditadas = {};

const hashesImagenes = new Set();

const MAX_SIZE = 2.7 * 1024 * 1024; //2.7 MB

/*=========================================
=           CARGAR IMÁGENES
=========================================*/

async function cargarImagenes(idProducto) {
  const contenedor = document.getElementById("contenedorImagenes");

  contenedor.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary"></div>
        </div>
    `;

  try {
    const respuesta = await fetch(
      "ajax/listar_imagenes_producto.php?idProducto=" + idProducto,
    );

    const data = await respuesta.json();

    if (data.estado !== "ok") {
      contenedor.innerHTML = `
                <div class="alert alert-danger">
                    ${data.mensaje}
                </div>
            `;

      return;
    }

    pintarImagenes(data.imagenes);
  } catch (error) {
    console.error(error);

    contenedor.innerHTML = `
            <div class="alert alert-danger">
                Error al cargar imágenes.
            </div>
        `;
  }
}

/*=========================================
=           PINTAR IMÁGENES
=========================================*/

function pintarImagenes(imagenes) {
  const contenedor = document.getElementById("contenedorImagenes");

  contenedor.innerHTML = "";

  const MAXIMO = 4;

  // Pintar imágenes existentes
  imagenes.forEach((img) => {
    contenedor.innerHTML += crearCardImagen(
      img.id_imagen,
      img.imagen,
      img.orden,
      false,
    );
  });

  const faltan = MAXIMO - imagenes.length;

  for (let i = 0; i < faltan; i++) {
    const tempId = "new_" + i;

    contenedor.innerHTML += crearCardImagen(
      tempId,
      "img/sin_imagen.png",
      imagenes.length + i + 1,
      true,
    );
  }
}
/*=========================================
=           CREAR CARD IMAGEN
=========================================*/
function crearCardImagen(id, imagen, orden, esNueva) {
  return `
    <div class="col-md-3">
        <div class="card shadow-sm h-100">

            <img
                id="img_${id}"
                src="${imagen}"
                class="card-img-top"
                style="height:220px;object-fit:cover;">

            <div class="card-body text-center">
                <br>

                <button
                    type="button"
                    class="btn ${esNueva ? "btn-success" : "btn-primary"} btn-sm btnCambiarImagen"
                    data-id="${id}"
                    data-nuevo="${esNueva}">

                    <i class="fas fa-image"></i>
                    ${esNueva ? "Agregar" : "Cambiar"}

                </button>

                <input
                    type="file"
                    class="d-none inputImagen"
                    accept="image/*"
                    data-id="${id}"
                    data-nuevo="${esNueva}">

                ${
                  !esNueva
                    ? `
                    <button
                        type="button"
                        class="btn btn-danger btn-sm btnEliminarImagen mt-2"
                        data-id="${id}">
                        <i class="fas fa-trash"></i>
                        Eliminar
                    </button>
                  `
                    : ""
                }

                <div id="info_${id}" class="small mt-2 text-muted">
                    ${esNueva ? "Sin imagen" : "Sin cambios"}
                </div>

            </div>
        </div>
    </div>
  `;
}
/*=========================================
=      CALCULAR HASH SHA256
=========================================*/

async function obtenerHash(file) {
  const buffer = await file.arrayBuffer();

  const hashBuffer = await crypto.subtle.digest("SHA-256", buffer);

  return Array.from(new Uint8Array(hashBuffer))
    .map((b) => b.toString(16).padStart(2, "0"))
    .join("");
}

/*=========================================
=      ABRIR INPUT FILE
=========================================*/

document.addEventListener("click", function (e) {
  const boton = e.target.closest(".btnCambiarImagen");

  if (!boton) return;

  const id = boton.dataset.id;

  document.querySelector(`.inputImagen[data-id="${id}"]`).click();
});

/*=========================================
=      CAMBIAR IMAGEN
=========================================*/

document.addEventListener("change", async function (e) {
  if (!e.target.classList.contains("inputImagen")) return;

  const archivo = e.target.files[0];
  if (!archivo) return;

  const id = e.target.dataset.id;
  const esNueva = e.target.dataset.nuevo === "true";

  const info = document.getElementById("info_" + id);

  // ❌ VALIDACIÓN TAMAÑO
  if (archivo.size > MAX_SIZE) {
    alert("La imagen supera 2.7 MB y no puede cargarse.");

    e.target.value = "";
    return;
  }

  // ❌ VALIDACIÓN DUPLICADOS (opcional)
  const buffer = await archivo.arrayBuffer();
  const hashBuffer = await crypto.subtle.digest("SHA-256", buffer);
  const hash = Array.from(new Uint8Array(hashBuffer))
    .map((b) => b.toString(16).padStart(2, "0"))
    .join("");

  if (hashesImagenes.has(hash)) {
    alert("Esta imagen ya fue seleccionada.");
    e.target.value = "";
    return;
  }

  hashesImagenes.add(hash);

  // 🧠 guardar estado correcto
  imagenesEditadas[id] = {
    estado: esNueva ? "insert" : "update",
    archivo: archivo,
  };

  // preview
  const reader = new FileReader();
  reader.onload = function (ev) {
    document.getElementById("img_" + id).src = ev.target.result;
  };
  reader.readAsDataURL(archivo);

  info.innerHTML = `<span class="text-warning">Pendiente de guardar</span>`;
});

/*=========================================
=      GUARDAR CAMBIOS
=========================================*/

document
  .getElementById("btnGuardarImagenes")
  .addEventListener("click", guardarImagenes);

async function guardarImagenes() {
  const idProducto = document.getElementById("img_idProducto").value;

  const promesas = [];

  for (const id in imagenesEditadas) {
    const data = imagenesEditadas[id];

    const formData = new FormData();
    formData.append("idProducto", idProducto);

    // 🗑️ ELIMINAR
    if (data.estado === "delete") {
      formData.append("id_imagen", id);

      promesas.push(
        fetch("ajax/eliminar_imagen_producto.php", {
          method: "POST",
          body: formData,
        }),
      );

      continue;
    }

    // 🟢 INSERTAR (nueva imagen)
    if (data.estado === "insert") {
      formData.append("imagen", data.archivo);

      promesas.push(
        fetch("ajax/agregar_imagen_producto.php", {
          method: "POST",
          body: formData,
        }),
      );

      continue;
    }

    // 🔵 ACTUALIZAR (existente)
    if (data.estado === "update") {
      formData.append("id_imagen", id);
      formData.append("imagen", data.archivo);

      promesas.push(
        fetch("ajax/actualizar_imagen_producto.php", {
          method: "POST",
          body: formData,
        }),
      );
    }
  }

  try {
    const respuestas = await Promise.all(promesas);

    for (const r of respuestas) {
      const data = await r.json();

      if (data.estado !== "ok") {
        alert(data.mensaje);
        return;
      }
    }

    //alert("Cambios guardados correctamente");

    location.reload(); // o mejor: recargar modal con AJAX
  } catch (err) {
    console.error(err);
    alert("Error al guardar cambios");
  }
}
//Eliminar imagen
document.addEventListener("click", function (e) {
  const btn = e.target.closest(".btnEliminarImagen");
  if (!btn) return;

  const id = btn.dataset.id;

  if (!confirm("¿Eliminar esta imagen? Se liberará este espacio.")) return;

  // registrar eliminación
  imagenesEditadas[id] = {
    estado: "delete",
    archivo: null,
  };

  const card = btn.closest(".col-md-3");

  // UI: imagen vacía
  card.querySelector("img").src = "img/sin_imagen.png";

  const info = document.getElementById("info_" + id);
  info.innerHTML = `<span class="text-danger">Espacio libre (puedes agregar imagen)</span>`;

  // convertir a "Agregar"
  const btnCambiar = card.querySelector(".btnCambiarImagen");

  btnCambiar.classList.remove("btn-primary");
  btnCambiar.classList.add("btn-success");
  btnCambiar.innerHTML = `<i class="fas fa-plus"></i> Agregar`;
  btnCambiar.dataset.nuevo = "true";

  // reset input file
  const input = card.querySelector(".inputImagen");
  input.value = "";
});
async function subirNuevaImagen(idProducto, archivo) {
  const formData = new FormData();
  formData.append("idProducto", idProducto);
  formData.append("imagen", archivo);

  try {
    const res = await fetch("ajax/agregar_imagen_producto.php", {
      method: "POST",
      body: formData,
    });

    const data = await res.json();

    if (data.estado !== "ok") {
      alert(data.mensaje);
      return;
    }

    // 🔥 IMPORTANTE: refrescar UI inmediatamente
    await cargarImagenes(idProducto);

    // limpiar memoria local de selección
    Object.keys(imagenesEditadas).forEach((k) => delete imagenesEditadas[k]);
    hashesImagenes.clear();
  } catch (error) {
    console.error(error);
    alert("Error al agregar imagen");
  }
}
async function actualizarImagen(idImagen, archivo) {
  const formData = new FormData();
  formData.append("id_imagen", idImagen);
  formData.append("imagen", archivo);

  const res = await fetch("Ajax/actualizar_imagen_producto.php", {
    method: "POST",
    body: formData,
  });

  const data = await res.json();

  if (data.estado !== "ok") {
    alert(data.mensaje);
  }
}
document.addEventListener("click", function (e) {
  const miniatura = e.target.closest(".miniatura");

  if (!miniatura) return;

  const imagenPrincipal = document.getElementById("imagenPrincipal");

  if (!imagenPrincipal) return;

  // Cambiar imagen principal
  imagenPrincipal.src = miniatura.src;

  // Quitar selección anterior
  document.querySelectorAll(".miniatura").forEach((img) => {
    img.classList.remove("border-primary", "border-3", "shadow");
  });

  // Marcar miniatura seleccionada
  miniatura.classList.add("border-primary", "border-3", "shadow");
});
document.addEventListener("DOMContentLoaded", () => {
  const primeraMiniatura = document.querySelector(".miniatura");

  if (primeraMiniatura) {
    primeraMiniatura.classList.add("border-primary", "border-3", "shadow");
  }
});
