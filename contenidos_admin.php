<?php
include('conexion.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Contenidos - MediaShop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background-color: #f4f4f4; }
        header { background-color: #000; color: white; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; position: fixed; top: 0; left: 0; width: 100%; height: 50px; z-index: 1000; }
        header h1 { margin: 0; font-size: 24px; }
        header h1 a { color: white; text-decoration: none; }
        .header-icons { display: flex; gap: 15px; }
        .header-icons a { color: white; text-decoration: none; font-size: 20px; }
        .sidebar { width: 220px; background-color: #c0c0c0; height: 100vh; position: fixed; top: 50px; left: 0; padding-top: 20px; display: flex; flex-direction: column; }
        .sidebar button { background: none; border: none; padding: 15px 20px; text-align: left; font-size: 16px; cursor: pointer; transition: background 0.3s; }
        .sidebar button:hover { background-color: #bbb; }
        .main { margin-left: 240px; margin-top: 70px; padding: 20px; }
        .breadcrumb { font-size: 14px; color: #555; margin-bottom: 15px; }
        h2 { text-align: center; }
        .category-container { display: flex; justify-content: center; gap: 30px; margin-top: 40px; }
        .category-box { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; width: 160px; cursor: pointer; transition: transform 0.3s; text-decoration: none; color: black; }
        .category-box:hover { transform: scale(1.05); }
        .category-box img { width: 80px; height: 80px; }
        .footer { display: flex; justify-content: space-around; flex-wrap: wrap; background-color: #c0c0c0; padding: 20px; margin-top: 60px; }
        .footer-section { flex: 1; min-width: 200px; margin: 10px; }
        .footer-section h4 { margin-bottom: 10px; }
        .footer-section div { margin: 5px 0; }
        .footer-section i { margin-right: 8px; }
        .copy-bar { text-align: center; padding: 10px; background-color: #aaa; font-size: 14px; }
    </style>
</head>
<body>

<header>
    <h1><a href="bienvenida_admin.php">MediaShop</a></h1>
    <div class="header-icons">
       
    </div>
</header>

<div class="sidebar">
    <a href="perfiles_admin.php"><button>Gestión de Usuarios</button></a>
    <a href="contenidos_admin.php"><button style="background-color: #bbb; font-weight: bold;">Gestión de Contenidos</button></a>
    <a href="categorias_admin.php"><button>Gestión de Categorías</button></a>
    <a href="promociones_admin.php"><button>Gestión de Promociones</button></a>
    <a href="calificaciones_admin.php"><button>Gestión de Calificaciones</button></a>
</div>

<div class="main">
    <div class="breadcrumb">Inicio > Gestión de Contenidos > Añadir Producto</div>
    <h2>Tipos de Contenido</h2>
    <div class="category-container">
        <a href="contenido_video.php" class="category-box">
            <img src="imagenes/icons/video.png" alt="Videos">
            <p>Videos</p>
        </a>
        <a href="contenido_imagen.php" class="category-box">
            <img src="imagenes/icons/imagen.png" alt="Imágenes">
            <p>Imágenes</p>
        </a>
        <a href="contenido_audio.php" class="category-box">
            <img src="imagenes/icons/audio.png" alt="Sonidos">
            <p>Sonidos</p>
        </a>
    </div>

    <div style="text-align: center; margin-top: 50px;">
        <button onclick="abrirModal()" style="padding: 10px 20px; font-size: 16px;">Buscar por Autor</button>
    </div>
</div>

<div id="modalAutor" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.7); z-index:9999;">
    <div style="background:white; width:600px; max-height:80%; overflow-y:auto; margin:100px auto; padding:20px; border-radius:10px; position:relative;">
        <h3>Selecciona un Autor</h3>
        <select id="autorSelect" style="width:100%; padding:10px;">
            <option value="">Seleccione un autor...</option>
            <?php 
            $resultAutores = $conn->query("SELECT DISTINCT autor FROM contenido ORDER BY autor");
            while ($row = $resultAutores->fetch_assoc()) {
                echo "<option value='".htmlspecialchars($row['autor'])."'>".htmlspecialchars($row['autor'])."</option>";
            }
            ?>
        </select>
        <div style="margin-top:20px; text-align:right;">
            <button onclick="buscarAutor()" style="padding:8px 16px;">Buscar</button>
            <button onclick="cerrarModal()" style="padding:8px 16px;">Cerrar</button>
        </div>
        <div id="resultadosAutor" style="margin-top:20px;"></div>
    </div>
</div>

<script>
function abrirModal() {
    document.getElementById('modalAutor').style.display = 'block';
}
function cerrarModal() {
    document.getElementById('modalAutor').style.display = 'none';
    document.getElementById('resultadosAutor').innerHTML = '';
}

function buscarAutor() {
    const autor = document.getElementById('autorSelect').value;
    if (autor === "") {
        alert("Seleccione un autor");
        return;
    }

    fetch('buscar_contenidos_por_autor.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'autor=' + encodeURIComponent(autor)
    })
    .then(response => response.text())
    .then(data => {
        document.getElementById('resultadosAutor').innerHTML = data;
    })
    .catch(error => {
        console.error('Error:', error);
        alert("Hubo un error al buscar.");
    });
}
</script>

</body>
</html>
