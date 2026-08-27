<div class="border-bottom p-3">

<div class="row align-items-center">

<div class="col-3">

<img

src="mostrar_imagen.php?id=<?= $item['idProducto'];?>"

class="img-fluid rounded">

</div>

<div class="col-9">

<h6 class="mb-1">

<?= htmlspecialchars($item['nombre']);?>

</h6>

<div class="text-primary fw-bold">

S/

<?= number_format($item['precio'],2);?>

</div>

<div class="mt-2">

<div class="btn-group">

<button

class="btn btn-sm btn-outline-secondary btnRestar"

data-id="<?= $item['idCarrito'];?>">

<i class="bi bi-dash"></i>

</button>

<button

class="btn btn-sm btn-light">

<?= $item['cantidad'];?>

</button>

<button

class="btn btn-sm btn-outline-secondary btnSumar"

data-id="<?= $item['idCarrito'];?>">

<i class="bi bi-plus"></i>

</button>

</div>

<button

class="btn btn-sm btn-outline-danger float-end btnEliminar"

data-id="<?= $item['idCarrito'];?>">

<i class="bi bi-trash"></i>

</button>

</div>

</div>

</div>

</div>