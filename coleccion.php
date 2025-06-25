<?php
session_start();
include 'conexion.php';


if (!isset($_SESSION['correo'])) {
    echo "<script>alert('Debes iniciar sesión para acceder a tu colección'); window.location='login.php';</script>";
    exit();
}

$correo = $_SESSION['correo'];

//FUB-11: obtener_productos_descargados_usuario (IN correo_usuario VARCHAR(100)): Devuelve los productos comprados por el usuario, incluyendo estado, nota y rutas de archivos.
$stmt = $conn->prepare("
    SELECT d.contenido, d.estado, d.nota, ct.tipo_archivo_id, ct.ruta_archivo, ct.ruta_preview
    FROM descarga d
    JOIN contenido ct ON d.contenido = ct.nombre_prod
    WHERE d.correo_usuario = ?
");
$stmt->bind_param("s", $correo);
$stmt->execute();
$result = $stmt->get_result();

$productos = [];
while ($row = $result->fetch_assoc()) {
    $productos[] = $row;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Colección | MediaShop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; font-family: Arial, sans-serif; margin: 0; padding: 0; }
        body { background-color: #ffffff; color: #333; }
        .top-bar { background-color: #fff; color: #777; padding: 10px 0; text-align: center; font-size: 14px; display: flex; justify-content: space-around; border-bottom: 1px solid #ddd; }
        .main-bar { background-color: #e0e0e0; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .main-bar h1 { font-size: 28px; }
        .icons i { margin-left: 20px; font-size: 20px; cursor: pointer; }
        h2 { padding: 30px; }
        .coleccion { display: flex; flex-wrap: wrap; gap: 30px; padding: 30px; }
        .item { width: 200px; border: 1px solid #ccc; border-radius: 10px; padding: 15px; text-align: center; background: #f9f9f9; }
        .item img, .item video, .item audio { width: 100%; max-height: 120px; object-fit: cover; border-radius: 8px; }
        .botones { margin-top: 10px; display: flex; flex-direction: column; gap: 8px; }
        .botones button, .botones a { padding: 8px; border: none; border-radius: 5px; background: #6a0dad; color: white; cursor: pointer; text-decoration: none; text-align: center; }
        .botones button.disabled { background: #888; cursor: not-allowed; }
        .modal, .calificacion-modal { display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0, 0, 0, 0.85); justify-content: center; align-items: center; z-index: 1000; }
        .modal-content, .calificacion-content { background: white; padding: 20px; max-width: 90%; max-height: 90%; position: relative; }
        .modal-content img, .modal-content video, .modal-content audio { max-width: 100%; max-height: 100%; }
        .modal .close, .calificacion-modal .close { position: absolute; top: 10px; right: 15px; font-size: 24px; color: #333; cursor: pointer; }
    </style>
</head>
<body>

<div class="main-bar">
    <h1>MediaShop</h1>
    <div class="icons">
        <i class="fas fa-search" onclick="window.location='tienda.php'"></i>
        <i class="fas fa-user" onclick="window.location='perfil.php'"></i>
        <i class="fas fa-heart" onclick="window.location='deseos.php'"></i>
        <i class="fas fa-shopping-cart" onclick="window.location='carrito.php'"></i>
        <i class="fas fa-download" onclick="window.location='coleccion.php'"></i>
    </div>
</div>

<h2>Mi Colección</h2>

<div class="coleccion">
    <?php foreach ($productos as $p): ?>
    <div class="item">
        <?php if ($p['tipo_archivo_id'] >= 9 && $p['tipo_archivo_id'] <= 12): ?>
            <img src="<?= $p['ruta_preview'] ?>" alt="preview" onclick="verProducto('<?= $p['ruta_archivo'] ?>', 'imagen')">
            <?php $detalle = 'detalle_imagen.php'; ?>
        <?php elseif ($p['tipo_archivo_id'] >= 5 && $p['tipo_archivo_id'] <= 8): ?>
            <img src="<?= $p['ruta_preview'] ?>" alt="preview" onclick="verProducto('<?= $p['ruta_archivo'] ?>', 'video')">
            <?php $detalle = 'detalle_video.php'; ?>
        <?php elseif ($p['tipo_archivo_id'] >= 1 && $p['tipo_archivo_id'] <= 4): ?>
            <img src="<?= $p['ruta_preview'] ?>" alt="preview" onclick="verProducto('<?= $p['ruta_archivo'] ?>', 'audio')">
            <?php $detalle = 'detalle_audio.php'; ?>
        <?php endif; ?>

        <p><?= htmlspecialchars($p['contenido']) ?></p>
        <div class="botones">
            <a href="<?= $detalle ?>?nombre=<?= urlencode($p['contenido']) ?>">Ver detalles</a>
            <?php if ($p['estado'] === 'pendiente'): ?>
                <form method="POST" action="descargar_individual.php">
                    <input type="hidden" name="contenido" value="<?= htmlspecialchars($p['contenido']) ?>">
                    <button type="submit">Descargar</button>
                </form>
            <?php else: ?>
                <button class="disabled" disabled>Ya descargado</button>
                <?php if (empty($p['nota'])): ?>
                    <button onclick="abrirCalificacion('<?= htmlspecialchars($p['contenido']) ?>')">Calificar</button>
                <?php else: ?>
                    <span>Calificación: <?= $p['nota'] ?>/10</span>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="modal" id="modal">
    <div class="modal-content" id="modalContent">
        <span class="close" onclick="cerrarModal()">&times;</span>
    </div>
</div>

<div class="calificacion-modal" id="calificacionModal">
    <div class="calificacion-content">
        <span class="close" onclick="cerrarCalificacion()">&times;</span>
        <h3>Califica el producto</h3>
        <form id="formCalificacion">
            <input type="hidden" id="contenidoCalificacion" name="contenido">
            <label for="nota">Nota (1-10):</label>
            <input type="number" name="nota" id="nota" min="1" max="10" required>
            <button type="submit">Guardar</button>
        </form>
    </div>
</div>

<script>

//FUW-10: verProducto(url, tipo): abre una ventana en el que se nos permite ver el contenido seleccionado
function verProducto(url, tipo) {
    const modal = document.getElementById("modal");
    const content = document.getElementById("modalContent");
    content.innerHTML = '<span class="close" onclick="cerrarModal()">&times;</span>';
    if (tipo === 'imagen') {
        content.innerHTML += '<img src="' + url + '">';
    } else if (tipo === 'video') {
        content.innerHTML += '<video controls autoplay src="' + url + '"></video>';
    } else if (tipo === 'audio') {
        content.innerHTML += '<audio controls autoplay src="' + url + '"></audio>';
    }
    modal.style.display = 'flex';
}

//FUW-11: cerrarModal(): cierra la ventana de FUW-10
function cerrarModal() {
    document.getElementById("modal").style.display = "none";
}

//FUW-12: abrirCalificacion(contenido): nos abre una ventan en donde se nos permitirá calificar el contenido seleccionado en una escala de 1 a 10
function abrirCalificacion(contenido) {
    document.getElementById("contenidoCalificacion").value = contenido;
    document.getElementById("calificacionModal").style.display = "flex";
}

//FUW-13: cerrarCalificacion(): cierra la ventana de FUW-12
function cerrarCalificacion() {
    document.getElementById("calificacionModal").style.display = "none";
}

document.getElementById("formCalificacion").addEventListener("submit", function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch("guardar_calificacion.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        alert("Calificación guardada");
        location.reload();
    })
    .catch(error => {
        console.error("Error:", error);
        alert("Ocurrió un error al guardar la calificación.");
    });
});
</script>

</body>
</html>
