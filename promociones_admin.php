<?php
include('conexion.php');
session_start();

// Leer todas las promociones desde la BD
$sql = "SELECT * FROM promocion ORDER BY fecha_ini DESC";
$resultado = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Promociones - MediaShop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; }
        header { background-color: #000;
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
            z-index: 1000;}
        header h1 a,
        header h1 a:visited,
header h1 a:hover,
header h1 a:active {
    color: white;
    text-decoration: none;
    margin: 0;
            font-size: 24px;
}
        .sidebar { width: 220px; background-color: #c0c0c0; position: fixed; top: 50px; bottom: 0; padding-top: 20px; }
        .sidebar button { background: none; border: none; width: 100%; padding: 15px 20px; text-align: left; font-size: 16px; cursor: pointer; color: black; }
        .main { margin-left: 240px; margin-top: 70px; padding: 30px; }
        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .promo-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; }
        .promo-card { background: white; border-radius: 10px; padding: 20px; box-shadow: 0 0 5px rgba(0,0,0,0.1); text-align: center; }
        .promo-card h3 { margin-bottom: 10px; }
        .promo-card p { margin: 5px 0; }
        .btns { margin-top: 15px; display: flex; justify-content: space-around; }
        .btn-editar { background-color: #3b82f6; color: white; padding: 8px 16px; border: none; border-radius: 5px; cursor: pointer; }
        .btn-eliminar { background-color: #ef4444; color: white; padding: 8px 16px; border: none; border-radius: 5px; cursor: pointer; }
        .btn-nuevo { background-color: #22c55e; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>

<header>
    <h1><a href="bienvenida_admin.php">MediaShop</a></h1>
</header>

<div class="sidebar">
    <a href="perfiles_admin.php"><button>Gestión de Usuarios</button></a>
    <a href="contenidos_admin.php"><button>Gestión de Contenidos</button></a>
    <a href="categorias_admin.php"><button>Gestión de Categorías</button></a>
    <a href="promociones_admin.php"><button style="background-color:#bbb; font-weight:bold;">Gestión de Promociones</button></a>
    <a href="calificaciones_admin.php"><button>Gestión de Calificaciones</button></a>
</div>

<div class="main">
    <div class="top-bar">
        <h2>Promociones vigentes</h2>
        <button class="btn-nuevo" onclick="window.location.href='crear_promocion.php'">Nuevo</button>
    </div>

    <div class="promo-grid">
    <?php while($fila = $resultado->fetch_assoc()): ?>
        <div class="promo-card">
            <h3>Descuento a <?= htmlspecialchars($fila['contenido']) ?></h3>
            <p>Descuento: <?= $fila['porcentaje'] ?>%</p>
            <p>Desde: <?= $fila['fecha_ini'] ?></p>
            <p>Hasta: <?= $fila['fecha_fin'] ?></p>

            <div class="btns">
                <button class="btn-editar" onclick="window.location.href='editar_promocion.php?id=<?= $fila['id_promocion'] ?>'">Editar</button>
                <button class="btn-eliminar" onclick="confirmarEliminar(<?= $fila['id_promocion'] ?>)">Eliminar</button>
            </div>
        </div>
    <?php endwhile; ?>
    </div>
</div>

<script>
// Eliminar con confirmación
function confirmarEliminar(id) {
    Swal.fire({
        title: '¿Seguro que quieres eliminar?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'eliminar_promocion.php?id=' + id;
        }
    });
}
</script>

<?php if (isset($_GET['eliminado'])): ?>
<script>
Swal.fire({
    title: 'Eliminado con éxito',
    icon: 'success',
    showConfirmButton: false,
    timer: 1500
});
</script>
<?php endif; ?>

</body>
</html>
