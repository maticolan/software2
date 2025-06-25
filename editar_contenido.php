<?php
include('conexion.php');
session_start();

if (!isset($_GET['id'])) {
    echo "ID no especificado.";
    exit;
}

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM contenido WHERE nombre_prod = ?");
$stmt->bind_param("s", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    echo "Contenido no encontrado.";
    exit;
}

$contenido = $resultado->fetch_assoc();

// Obtener categorías
$categorias = $conn->query("SELECT nombre FROM categoria");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Contenido - MediaShop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
        }
        header {
            background-color: #000;
            color: white;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 50px;
            z-index: 1000;
        }
        header h1 a {
            color: white;
            text-decoration: none;
        }
        .header-icons a {
            color: white;
            text-decoration: none;
            margin-left: 15px;
        }
        .sidebar {
            width: 220px;
            background-color: #c0c0c0;
            height: 100vh;
            position: fixed;
            top: 50px;
            left: 0;
            padding-top: 20px;
        }
        .sidebar button {
            background: none;
            border: none;
            width: 100%;
            padding: 15px 20px;
            text-align: left;
            font-size: 16px;
            cursor: pointer;
        }
        .main {
            margin-left: 240px;
            margin-top: 70px;
            padding: 30px;
        }
        form {
            background: white;
            padding: 30px;
            border-radius: 10px;
        }
        label {
            font-weight: bold;
            display: block;
            margin-top: 15px;
        }
        input[type="text"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .image-preview {
            margin-top: 20px;
            display: flex;
            gap: 20px;
        }
        .image-preview img,
        .image-preview video,
        .image-preview audio {
            width: 200px;
            height: 150px;
            object-fit: cover;
        }
        .buttons {
            margin-top: 20px;
            display: flex;
            gap: 20px;
        }
        .buttons button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .guardar { background-color: #6a5acd; color: white; }
        .eliminar { background-color: #e74c3c; color: white; }
    </style>
</head>
<body>

<header>
    <h1><a href="bienvenida_admin.php">MediaShop</a></h1>
    <div class="header-icons">
        <a href="#">🔍</a>
        <a href="#">👤</a>
    </div>
</header>

<div class="sidebar">
    <a href="perfiles_admin.php"><button>Gestión de Usuarios</button></a>
    <a href="contenidos_admin.php"><button style="background-color:#bbb; font-weight:bold;">Gestión de Contenidos</button></a>
    <a href="categorias_admin.php"><button>Gestión de Categorías</button></a>
    <a href="promociones_admin.php"><button>Gestión de Promociones</button></a>
    <a href="calificaciones_admin.php"><button>Gestión de Calificaciones</button></a>
</div>

<div class="main">
    <h2>Editar producto</h2>
    <form action="actualizar_contenido.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="nombre_original" value="<?php echo htmlspecialchars($contenido['nombre_prod']); ?>">

        <label>Nombre</label>
        <input type="text" name="nombre" value="<?php echo htmlspecialchars($contenido['nombre_prod']); ?>" required>

        <label>Descripción</label>
        <textarea name="descripcion"><?php echo htmlspecialchars($contenido['descripcion']); ?></textarea>

        <label>Precio</label>
        <input type="number" step="0.01" name="precio" value="<?php echo $contenido['precio']; ?>">

        <label>Tipo de Archivo</label>
        <input type="text" value="<?php echo $contenido['tipo_archivo_id']; ?>" disabled>

        <label>Categoría</label>
        <select name="categoria">
            <?php while ($cat = $categorias->fetch_assoc()): ?>
                <option value="<?php echo $cat['nombre']; ?>" <?php echo $cat['nombre'] == $contenido['categoria_nombre'] ? 'selected' : ''; ?>>
                    <?php echo $cat['nombre']; ?>
                </option>
            <?php endwhile; ?>
        </select>

        <div class="image-preview">
            <div>
                <label>Imagen Preview</label><br>
                <img src="<?php echo $contenido['ruta_preview']; ?>" onclick="document.getElementById('preview').click()">
                <input type="file" name="preview" id="preview" style="display:none;">
            </div>
            <div>
                <label>Archivo</label><br>
                <?php if ($contenido['tipo_archivo_id'] <= 4): ?>
                    <audio src="<?php echo $contenido['ruta_archivo']; ?>" controls onclick="document.getElementById('archivo').click()"></audio>
                <?php elseif ($contenido['tipo_archivo_id'] <= 8): ?>
                    <video src="<?php echo $contenido['ruta_archivo']; ?>" controls onclick="document.getElementById('archivo').click()"></video>
                <?php else: ?>
                    <img src="<?php echo $contenido['ruta_archivo']; ?>" onclick="document.getElementById('archivo').click()">
                <?php endif; ?>
                <input type="file" name="archivo" id="archivo" style="display:none;">
            </div>
        </div>

        <div class="buttons">
            <button type="submit" class="guardar">Guardar</button>
            <button type="button" class="eliminar" onclick="confirmarEliminacion('<?php echo $contenido['nombre_prod']; ?>')">Eliminar</button>
        </div>
    </form>
</div>

<!-- Modal JS -->
<script>
function confirmarEliminacion(id) {
    if (confirm("¿Está seguro de eliminar este producto?")) {
        window.location.href = "eliminar_contenido.php?id=" + encodeURIComponent(id);
    }
}
</script>

</body>
</html>
