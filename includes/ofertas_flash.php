<section class="container my-5">

    <div class="flash-sale shadow-lg">

        <div class="container py-5">

            <div class="row align-items-center">

                <div class="col-lg-6">

                    <span class="offer-badge">

                        🔥 OFERTAS FLASH

                    </span>

                    <h2 class="display-4 fw-bold text-white mt-3">

                        Hasta

                        <span class="text-warning">

                            40%

                        </span>

                        de descuento

                    </h2>

                    <p class="text-light fs-5">

                        Aprovecha nuestras promociones por tiempo limitado en laptops,
                        componentes, cámaras CCTV, routers y accesorios tecnológicos.

                    </p>

                    <div class="d-flex gap-2 flex-wrap my-4">

                        <div class="count-box">

                            <h2 id="dias">

                                00

                            </h2>

                            <small>Días</small>

                        </div>

                        <div class="count-box">

                            <h2 id="horas">

                                00

                            </h2>

                            <small>Horas</small>

                        </div>

                        <div class="count-box">

                            <h2 id="minutos">

                                00

                            </h2>

                            <small>Minutos</small>

                        </div>

                        <div class="count-box">

                            <h2 id="segundos">

                                00

                            </h2>

                            <small>Segundos</small>

                        </div>

                    </div>

                    <div class="d-flex gap-3 flex-wrap">

                        <a href="ofertas.php" class="btn btn-warning btn-lg">

                            <i class="bi bi-lightning-charge-fill"></i>

                            Ver Ofertas

                        </a>

                        <a href="tienda.php" class="btn btn-outline-light btn-lg">

                            <i class="bi bi-cart3"></i>

                            Comprar Ahora

                        </a>

                    </div>

                </div>

                <div class="col-lg-6 text-center">

                    <img
                        src="assets/banners/ofertas.png"
                        class="img-fluid flash-img">

                </div>

            </div>

        </div>

    </div>

</section>

<script>
    const fechaFinal = new Date();

    fechaFinal.setHours(fechaFinal.getHours() + 24);

    function actualizarContador() {

        const ahora = new Date().getTime();

        const distancia = fechaFinal - ahora;

        if (distancia <= 0) {

            return;

        }

        const dias = Math.floor(distancia / (1000 * 60 * 60 * 24));

        const horas = Math.floor((distancia % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));

        const minutos = Math.floor((distancia % (1000 * 60 * 60)) / (1000 * 60));

        const segundos = Math.floor((distancia % (1000 * 60)) / 1000);

        document.getElementById("dias").innerHTML = dias.toString().padStart(2, "0");
        document.getElementById("horas").innerHTML = horas.toString().padStart(2, "0");
        document.getElementById("minutos").innerHTML = minutos.toString().padStart(2, "0");
        document.getElementById("segundos").innerHTML = segundos.toString().padStart(2, "0");

    }

    setInterval(actualizarContador, 1000);

    actualizarContador();
</script>