<?php
include('conexion.php');
session_start();

// Obtenemos los productos directamente de la tabla contenido
//FUB-15: listar_todos_los_productos() Devuelve todos los productos disponibles en la base de datos.
$productos = $conn->query("SELECT nombre_prod FROM contenido");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Promoción</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
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
        header h1 a { color: white; text-decoration: none; font-size: 24px; }
        .header-icons { display: flex; gap: 15px; }
        .header-icons a { color: white; text-decoration: none; font-size: 20px; }

        .sidebar { width: 220px; background-color: #c0c0c0; position: fixed; top: 50px; bottom: 0; left: 0; padding-top: 20px; display: flex; flex-direction: column; }
        .sidebar button { background: none; border: none; padding: 15px 20px; text-align: left; font-size: 16px; cursor: pointer; transition: background 0.3s; }
        .sidebar button:hover { background-color: #bbb; }

        .main { margin-left: 240px; margin-top: 70px; padding: 30px; }

        form { background: white; padding: 30px; border-radius: 10px; max-width: 500px; margin: auto; }
        label { font-weight: bold; margin-top: 15px; display: block; }
        input, select { width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ccc; border-radius: 5px; }
        .acciones { margin-top: 20px; display: flex; justify-content: space-between; }
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
    <a href="contenidos_admin.php"><button>Gestión de Contenidos</button></a>
    <a href="categorias_admin.php"><button>Gestión de Categorías</button></a>
    <a href="promociones_admin.php"><button style="background:#bbb;font-weight:bold;">Gestión de Promociones</button></a>
    <a href="calificaciones_admin.php"><button>Gestón de Calificaciones</button></a>
</div>

<div class="main">
    <h2>Crear Promoción</h2>

    <form action="procesar_promocion.php" method="POST">
        <label>Fecha Inicio:</label>
        <input type="text" name="fecha_ini" id="fecha_ini" required>

        <label>Fecha Fin:</label>
        <input type="text" name="fecha_fin" id="fecha_fin" required>

        <label>Descuento (%):</label>
        <input type="number" name="porcentaje" min="5" max="90" required>

        <label>Seleccionar Producto:</label>
        <select name="contenido" required>
            <option value="">Seleccione un producto</option>
            <?php while ($row = $productos->fetch_assoc()): ?>
                <option value="<?= htmlspecialchars($row['nombre_prod']) ?>"><?= htmlspecialchars($row['nombre_prod']) ?></option>
            <?php endwhile; ?>
        </select>

        <div class="acciones">
            <button type="submit" style="background:#6a5acd; color:white; padding:10px 20px; border:none; border-radius:5px;">Crear</button>
            <button type="button" onclick="window.location.href='promociones_admin.php'" style="background:#999; color:white; padding:10px 20px; border:none; border-radius:5px;">Cancelar</button>
        </div>
    </form>
</div>

<script>
flatpickr("#fecha_ini", { dateFormat: "Y-m-d" });
flatpickr("#fecha_fin", { dateFormat: "Y-m-d" });
</script>

</body>
</html>
