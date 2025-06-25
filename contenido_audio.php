<?php
include('conexion.php');
session_start();

$busqueda = $_GET['buscar'] ?? '';
$orden = $_GET['orden'] ?? 'fecha DESC';
$pagina = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$por_pagina = 6;
$inicio = ($pagina - 1) * $por_pagina;

$total_sql = "SELECT COUNT(*) as total FROM contenido WHERE tipo_archivo_id BETWEEN 1 AND 4 AND nombre_prod LIKE ?";
$stmt_total = $conn->prepare($total_sql);
$like = "%$busqueda%";
$stmt_total->bind_param("s", $like);
$stmt_total->execute();
$total_result = $stmt_total->get_result();
$total_filas = $total_result->fetch_assoc()['total'];
$total_paginas = ceil($total_filas / $por_pagina);

$sql = "SELECT * FROM contenido 
        WHERE tipo_archivo_id BETWEEN 1 AND 4 
        AND nombre_prod LIKE ? 
        ORDER BY $orden 
        LIMIT ?, ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sii", $like, $inicio, $por_pagina);
$stmt->execute();
$resultado = $stmt->get_result();
?>
<!-- HTML común omitido por brevedad -->


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Contenido de Video - MediaShop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
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
            height: 100vh;
            position: fixed;
            top: 50px;
            left: 0;
            padding-top: 20px;
            display: flex;
            flex-direction: column;
        }
        .sidebar button {
            background: none;
            border: none;
            padding: 15px 20px;
            text-align: left;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .sidebar button:hover {
            background-color: #bbb;
        }
        .main {
            margin-left: 240px;
            margin-top: 70px;
            padding: 20px;
        }
        .breadcrumb {
            font-size: 14px;
            color: #555;
            margin-bottom: 15px;
        }
        .search-sort-bar {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .search-sort-bar input, .search-sort-bar select {
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .productos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }
        .producto-box {
            background-color: white;
            padding: 10px;
            border-radius: 10px;
            text-align: center;
            position: relative;
        }
        .producto-box img {
            width: 100%;
            height: 130px;
            object-fit: cover;
            border-radius: 8px;
        }
        .producto-box h4 {
            margin: 10px 0 5px 0;
        }
        .producto-box .editar-icono {
            position: absolute;
            top: 8px;
            right: 8px;
            background: rgba(0,0,0,0.6);
            color: white;
            padding: 5px;
            border-radius: 50%;
            cursor: pointer;
        }
        .producto-box .editar-icono:hover {
            background: black;
        }
        .paginacion {
            margin-top: 30px;
            text-align: center;
        }
        .paginacion a {
            margin: 0 5px;
            padding: 6px 12px;
            background-color: #ccc;
            text-decoration: none;
            border-radius: 4px;
            color: #000;
        }
        .paginacion a.activo {
            background-color: #6a5acd;
            color: white;
        }
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
    <div class="breadcrumb">Inicio > Gestión de Contenidos > Contenidos</div>
    <h2>Contenidos</h2>

    <div class="search-sort-bar">
        <form method="GET" style="flex: 1; display: flex; gap: 10px;">
            <input type="text" name="buscar" placeholder="Buscar por nombre..." value="<?php echo htmlspecialchars($busqueda); ?>">
            <select name="orden" onchange="this.form.submit()">
                <option value="fecha DESC" <?php if ($orden == 'fecha DESC') echo 'selected'; ?>>Más recientes</option>
                <option value="fecha ASC" <?php if ($orden == 'fecha ASC') echo 'selected'; ?>>Más antiguos</option>
                <option value="precio ASC" <?php if ($orden == 'precio ASC') echo 'selected'; ?>>Precio menor</option>
                <option value="precio DESC" <?php if ($orden == 'precio DESC') echo 'selected'; ?>>Precio mayor</option>
                <option value="nombre_prod ASC" <?php if ($orden == 'nombre_prod ASC') echo 'selected'; ?>>Nombre A-Z</option>
                <option value="nombre_prod DESC" <?php if ($orden == 'nombre_prod DESC') echo 'selected'; ?>>Nombre Z-A</option>
            </select>
            <button type="submit">Buscar</button>
        </form>
    </div>

    <div class="productos-grid">
        <!-- Añadir Producto -->
        <div class="producto-box" style="border: 2px dashed #00aaff; color: #00aaff; cursor: pointer; display: flex; flex-direction: column; justify-content: center; align-items: center; height: 200px;" onclick="location.href='añadir_contenido.php?tipo=audio'">
    <div style="font-size: 50px; margin-bottom: 10px;">+</div>
    <strong style="color: #00aaff;">AÑADIR PRODUCTO</strong>
</div>


        <?php while($fila = $resultado->fetch_assoc()): ?>
        <div class="producto-box">
            <img src="<?php echo htmlspecialchars($fila['ruta_preview']); ?>" alt="preview">
            <div class="editar-icono" onclick="location.href='editar_contenido.php?id=<?php echo $fila['nombre_prod']; ?>'"><i class="fas fa-pen"></i></div>
            <h4><?php echo htmlspecialchars($fila['nombre_prod']); ?></h4>
            <p>$ <?php echo number_format($fila['precio'], 2); ?></p>
        </div>
        <?php endwhile; ?>
    </div>

    <div class="paginacion">
        <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
            <a href="?pagina=<?php echo $i; ?>&buscar=<?php echo urlencode($busqueda); ?>&orden=<?php echo urlencode($orden); ?>" class="<?php echo $i == $pagina ? 'activo' : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>
    </div>
</div>

</body>
</html>