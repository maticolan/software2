<?php
include('conexion.php');
session_start();

$tipo = $_GET['tipo'] ?? '';

if (!in_array($tipo, ['audio', 'video', 'imagen'])) {
    die("Tipo de producto no válido.");
}

// PRO-05: 
$stmt = $conn->prepare("CALL GetCategorias()");
$extensiones_permitidas = [
    'audio' => ['mp3', 'wav', 'ogg', 'flac'],
    'video' => ['mp4', 'avi', 'mkv', 'webm'],
    'imagen' => ['jpeg', 'jpg', 'png', 'gif', 'webp']
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Añadir Producto</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; }
        header { background-color: #000; color: white; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; position: fixed; top: 0; left: 0; width: 100%; height: 50px; z-index: 1000; }
        header h1 {
            margin: 0;
            font-size: 24px;
        }
        header h1 a { color: white; text-decoration: none;}
        .sidebar { width: 220px; background-color: #c0c0c0; height: 100vh; position: fixed; top: 50px; left: 0; padding-top: 20px; }
        .sidebar button { background: none; border: none; width: 100%; padding: 15px 20px; text-align: left; font-size: 16px; cursor: pointer; color: black; }
        .main { margin-left: 240px; margin-top: 70px; padding: 30px; }
        form { background: white; padding: 30px; border-radius: 10px; }
        label { font-weight: bold; display: block; margin-top: 15px; }
        input, textarea, select { width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ccc; border-radius: 5px; }
        .preview-area { margin-top: 15px; display: flex; gap: 20px; }
        .preview-area img, .preview-area video, .preview-area audio { max-width: 250px; max-height: 200px; object-fit: cover; border: 1px solid #aaa; border-radius: 8px; }
        button { margin-top: 20px; padding: 10px 20px; font-size: 16px; background-color: #6a5acd; color: white; border: none; border-radius: 5px; }
    </style>
</head>
<body>

<header>
    <h1><a href="bienvenida_admin.php">MediaShop</a></h1>
</header>

<div class="sidebar">
    <a href="perfiles_admin.php"><button>Gestión de Usuarios</button></a>
    <a href="contenidos_admin.php"><button style="background-color:#bbb; font-weight:bold;">Gestión de Contenidos</button></a>
    <a href="categorias_admin.php"><button>Gestión de Categorías</button></a>
    <a href="promociones_admin.php"><button>Gestión de Promociones</button></a>
    <a href="calificaciones_admin.php"><button>Gestión de Calificaciones</button></a>
</div>

<div class="main">
    <h2>Añadir Producto: <?php echo strtoupper($tipo); ?></h2>

    <form id="formulario" enctype="multipart/form-data">
        <input type="hidden" name="tipo" value="<?php echo $tipo; ?>">

        <label>Nombre:</label>
        <input type="text" name="nombre" required>

        <label>Descripción:</label>
        <textarea name="descripcion" required></textarea>

        <label>Precio:</label>
        <input type="number" name="precio" step="0.01" min="3" required>

        <label>Categoría:</label>
        <select name="categoria" required>
            <?php while ($cat = $categorias->fetch_assoc()): ?>
                <option value="<?php echo $cat['nombre']; ?>"><?php echo $cat['nombre']; ?></option>
            <?php endwhile; ?>
        </select>

        <label>Autor:</label>
        <input type="text" name="autor" required>

        <label>Archivo principal:</label>
        <input type="file" name="archivo" id="archivo" required onchange="vistaArchivo()">
        <div id="previewArchivo" class="preview-area"></div>

        <?php if ($tipo != 'imagen'): ?>
        <label>Vista Previa (Imagen):</label>
        <input type="file" name="preview" id="preview" required onchange="vistaPreview()">
        <div id="previewImagen" class="preview-area"></div>
        <?php endif; ?>

        <button type="button" onclick="seleccionarFecha()">Guardar</button>
    </form>
</div>

<script>
// FUW-01: vistaArchivo(): Genera una vista previa del producto actual en una pestaña pequeña
function vistaArchivo() {
    const file = document.getElementById('archivo').files[0];
    const url = URL.createObjectURL(file);
    const container = document.getElementById('previewArchivo');
    container.innerHTML = "";
    const mimeType = file.type;
    if (mimeType.startsWith("image/")) {
        const img = document.createElement('img');
        img.src = url;
        container.appendChild(img);
    } else if (mimeType.startsWith("video/")) {
        const vid = document.createElement('video');
        vid.src = url;
        vid.controls = true;
        container.appendChild(vid);
    } else if (mimeType.startsWith("audio/")) {
        const aud = document.createElement('audio');
        aud.src = url;
        aud.controls = true;
        container.appendChild(aud);
    }
}

// FUW-02: vistaPreview(): Genera una vista previa del producto actual en una miniatura
function vistaPreview() {
    const file = document.getElementById('preview').files[0];
    const url = URL.createObjectURL(file);
    const container = document.getElementById('previewImagen');
    container.innerHTML = "";
    const img = document.createElement('img');
    img.src = url;
    container.appendChild(img);
}

let fechaSeleccionada = '';
let horaSeleccionada = '';

//FUW-03: seleccionarFecha(): Permite seleccionar una fecha para la publicación del producto
function seleccionarFecha() {
    Swal.fire({
        title: 'Selecciona la fecha',
        input: 'text',
        inputLabel: 'Fecha de publicación',
        didOpen: () => { flatpickr(Swal.getInput(), { dateFormat: "Y-m-d" }); },
        preConfirm: (fecha) => {
            if (!fecha) return false;
            fechaSeleccionada = fecha;
            seleccionarHora();
        }
    });
}

//FUW-04: seleccionarHora(): Permite seleccionar una hora para la publicación del producto
function seleccionarHora() {
    Swal.fire({
        title: 'Selecciona la hora',
        html: `<input type="number" id="hora_val" min="0" max="23" placeholder="HH" style="width:70px;"> :
               <input type="number" id="minuto_val" min="0" max="59" placeholder="MM" style="width:70px;">`,
        preConfirm: () => {
            const h = document.getElementById('hora_val').value.padStart(2, '0');
            const m = document.getElementById('minuto_val').value.padStart(2, '0');
            if (h === "" || m === "") {
                Swal.showValidationMessage('Debe ingresar hora completa');
                return false;
            }
            horaSeleccionada = `${h}:${m}:00`;
            enviarFormulario();
        }
    });
}

//FUW-05: enviarFormulario(): Envía el formulario con los datos del producto y la fecha/hora seleccionada
function enviarFormulario() {
    const form = document.getElementById('formulario');
    const formData = new FormData(form);
    formData.append('fecha', fechaSeleccionada);
    formData.append('hora', horaSeleccionada);

    fetch('procesar_contenido.php', {
        method: 'POST',
        body: formData
    }).then(res => res.json())
      .then(data => {
        if (data.success) {
            Swal.fire({
                title: 'Producto añadido con éxito',
                icon: 'success'
            }).then(() => {
                window.location.href = `contenido_${data.tipo}.php`;
            });
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    }).catch(() => {
        Swal.fire('Error', 'Ocurrió un error inesperado', 'error');
    });
}
</script>

</body>
</html>
