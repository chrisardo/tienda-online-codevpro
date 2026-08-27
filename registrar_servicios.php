<!--todo estos campos pertenece a registrar_servicios.php-->
<!--prcio venta + sucursal tienda -->
<div class="row g-2 mb-2">
    <div class="col">
        <label for="precio" class="form-label">Precio:</label>
        <div class="input-group">
            <span class="input-group-text bg-success text-white">
                <i class="bi bi-cash-coin"></i>
            </span>
            <input type="number" step="0.01" min="0" id="precio_servicio"
                class="form-control" name="precio_servicio"
                placeholder="Precio" data-required="true">
        </div>
    </div>
    <div class="col">
        <label for="sucursal" class="form-label">Sucursal / Tienda</label>
        <div class="input-group">
            <span class="input-group-text bg-success text-white"> <i class="fas fa-store me-2"></i>
            </span>
            <!--poner un select con opciones de rubro y mostrar los rubros de la base de datos-->
            <?php
            $sql = "SELECT id_sucursal, nombre, id_user FROM sucursal where Eliminado = 0 AND id_user=" . intval($_SESSION['usId']) . "";
            $resultado = $conexion->query($sql);
            ?>
            <select class="form-select" id="sucursal_servicio" name="sucursal_servicio" data-required="true">
                <option value="" disabled selected>Selecciona</option>
                <option value="">Sin sucursal</option>
                <?php
                if ($resultado->num_rows > 0) {
                    while ($fila = $resultado->fetch_assoc()) {
                        echo '<option value="' . $fila['id_sucursal'] . '">' . $fila['nombre'] . '</option>';
                    }
                }
                ?>
            </select>
        </div>
    </div>
</div>
<!--Breve descripcion del producto (detalle) -->
<div class="mb-2">
    <label for="descripcion" class="form-label">Más detalle:</label>
    <textarea class="form-control" id="descripcion_servicio" name="descripcion_servicio" rows="4" placeholder="Mas detalle del servicio"></textarea>
</div>