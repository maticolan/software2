<?php
include('conexion.php');
session_start();

$busqueda = $_GET['buscar'] ?? '';
$pagina = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$por_pagina = 6;
$inicio = ($pagina - 1) * $por_pagina;

// PRO-36: 
$stmt_total = $conn->prepare("CALL GetTotalCategoriasPorBusqueda(?)");
$like = "%$busqueda%";
$stmt_total->bind_param("s", $like);
$stmt_total->execute();
$total_result = $stmt_total->get_result();
$total_filas = $total_result->fetch_assoc()['total'];
$total_paginas = ceil($total_filas / $por_pagina);
$stmt_total->close();

$stmt = $conn->prepare("CALL GetCategoriasConPaginacion(?, ?, ?)");
$stmt->bind_param("sii", $like, $inicio, $por_pagina);
$stmt->execute();
$resultado = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Categorías - MediaShop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; }
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
        header h1 {
            margin: 0;
            font-size: 24px;
        }
        header h1 a {
            color: white;
            text-decoration: none;
        }
        .header-icons {
            display: flex;
            gap: 15px;
        }
        .header-icons a {
            color: white;
            text-decoration: none;
            font-size: 20px;
        }
        .sidebar {
            width: 220px;
            background-color: #c0c0c0;
            position: fixed;
            top: 50px;
            bottom: 0;
            left: 0;
            padding-top: 20px;
            overflow-y: auto;
        }


        .sidebar button { background: none; border: none; width: 100%; padding: 15px 20px; text-align: left; font-size: 16px; cursor: pointer; color: black; }
        .main { margin-left: 240px; margin-top: 70px; padding: 30px; }
        .productos-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; }
        .producto-box { background-color: white; padding: 10px; border-radius: 10px; text-align: center; position: relative; }
        .producto-box img { width: 100%; height: 130px; object-fit: cover; border-radius: 8px; }
        .producto-box h4 { margin: 10px 0 5px 0; }
        .editar-icono, .eliminar-icono { position: absolute; top: 8px; background: rgba(0,0,0,0.6); color: white; padding: 5px; border-radius: 50%; cursor: pointer; }
        .editar-icono { right: 40px; }
        .eliminar-icono { right: 8px; }
        .paginacion { margin-top: 30px; text-align: center; }
        .paginacion a { margin: 0 5px; padding: 6px 12px; background-color: #ccc; text-decoration: none; border-radius: 4px; color: #000; }
        .paginacion a.activo { background-color: #6a5acd; color: white; }
        #modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); z-index:2000; }
        #modal .modal-content {
            background:white; 
            padding:40px; 
            border-radius:10px; 
            width:400px; 
            max-width: 90%;
            margin: 100px auto;
            position: relative;
        }
        #modal img { width:100%; margin-top:10px; border-radius:10px; }
    </style>
</head>
<body>

<header>
    <h1><a href="bienvenida_admin.php" style="color:white; text-decoration:none;">MediaShop</a></h1>
</header>

<div class="sidebar">
    <a href="perfiles_admin.php"><button>Gestión de Usuarios</button></a>
    <a href="contenidos_admin.php"><button>Gestión de Contenidos</button></a>
    <a href="categorias_admin.php"><button style="background-color:#bbb; font-weight:bold;">Gestión de Categorías</button></a>
    <a href="promociones_admin.php"><button>Gestión de Promociones</button></a>
    <a href="calificaciones_admin.php"><button>Gestión de Calificaciones</button></a>
</div>

<div class="main">
    <h2>Categorías</h2>

    <form method="GET" style="margin-bottom:20px;">
        <input type="text" name="buscar" placeholder="Buscar..." value="<?php echo htmlspecialchars($busqueda); ?>">
        <button type="submit">Buscar</button>
    </form>

    <div class="productos-grid">
        <!-- Botón añadir -->
        <div class="producto-box" style="border: 2px dashed #00aaff; color: #00aaff; cursor: pointer;" onclick="abrirModal('', '')">
            <div style="font-size: 50px; margin-bottom: 10px;">+</div>
            <strong style="color: #00aaff;">AÑADIR CATEGORÍA</strong>
        </div>

        <?php while($fila = $resultado->fetch_assoc()): ?>
        <div class="producto-box">
            <img src="<?php echo htmlspecialchars($fila['ruta_preview']); ?>">
            <div class="editar-icono" onclick="abrirModal('<?php echo $fila['nombre']; ?>', '<?php echo $fila['ruta_preview']; ?>')"><i class="fas fa-pen"></i></div>
            <div class="eliminar-icono" onclick="confirmarEliminar('<?php echo $fila['nombre']; ?>')"><i class="fas fa-trash"></i></div>
            <h4><?php echo htmlspecialchars($fila['nombre']); ?></h4>
        </div>
        <?php endwhile; ?>
    </div>

    <div class="paginacion">
        <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
            <a href="?pagina=<?php echo $i; ?>&buscar=<?php echo urlencode($busqueda); ?>" class="<?php echo $i == $pagina ? 'activo' : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>
    </div>
</div>

<!-- MODAL -->
<div id="modal">
    <div class="modal-content">
        <form action="procesar_categoria.php" method="POST" enctype="multipart/form-data" id="formularioModal">
            <h3 id="tituloModal">Nueva Categoría</h3>
            <input type="hidden" name="original" id="originalNombre">

            <label>Nombre:</label>
            <input type="text" name="nombre" id="nombreInput" required style="width: 100%; padding: 8px; margin-bottom: 10px;">

            <label>Imagen:</label>
            <input type="file" name="imagen" id="imagenInput" onchange="previewImagen()" style="margin-bottom: 10px;">

            <div id="previewContainer"></div>

            <div style="margin-top: 15px; display: flex; justify-content: space-between;">
                <button type="submit" style="padding:10px 20px; background-color:#6a5acd; color:white; border:none; border-radius:5px;">Guardar cambios</button>
                <button type="button" onclick="cerrarModal()" style="padding:10px 20px; background-color:#999; color:white; border:none; border-radius:5px;">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirModal(nombre, ruta) {
    document.getElementById('modal').style.display = 'block';
    document.getElementById('nombreInput').value = nombre;
    document.getElementById('originalNombre').value = nombre;
    document.getElementById('previewContainer').innerHTML = ruta ? `<img src="${ruta}">` : '';
}

function cerrarModal() {
    document.getElementById('modal').style.display = 'none';
}

function previewImagen() {
    const file = document.getElementById('imagenInput').files[0];
    if (file) {
        const url = URL.createObjectURL(file);
        document.getElementById('previewContainer').innerHTML = `<img src="${url}">`;
    }
}

function confirmarEliminar(nombre) {
    Swal.fire({
        title: '¿Eliminar categoría?',
        text: nombre,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "procesar_categoria.php?eliminar=" + encodeURIComponent(nombre);
        }
    });
}

<?php if (isset($_GET['success'])): ?>
Swal.fire({
    title: 'Operación exitosa',
    icon: 'success'
});
<?php elseif (isset($_GET['error'])): ?>
Swal.fire({
    title: 'Error',
    text: '<?php echo htmlspecialchars($_GET['error']); ?>',
    icon: 'error'
});
<?php endif; ?>
</script>

</body>
</html>
