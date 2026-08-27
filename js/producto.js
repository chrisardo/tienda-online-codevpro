document.addEventListener("DOMContentLoaded", function () {
  const principal = document.getElementById("imagenPrincipal");

  document.querySelectorAll(".miniatura").forEach(function (img) {
    img.addEventListener("click", function () {
      principal.src = this.dataset.imagen;
    });
  });
});
// =============================
// BOTONES + Y -
// =============================

const cantidad = document.getElementById("cantidadProducto");

const btnMas = document.getElementById("btnMas");

const btnMenos = document.getElementById("btnMenos");

if (btnMas) {
  btnMas.addEventListener("click", () => {
    let max = parseInt(cantidad.max);

    let valor = parseInt(cantidad.value);

    if (valor < max) {
      cantidad.value = valor + 1;
    }
  });
}

if (btnMenos) {
  btnMenos.addEventListener("click", () => {
    let valor = parseInt(cantidad.value);

    if (valor > 1) {
      cantidad.value = valor - 1;
    }
  });
}
