<?php
include('conexion.php');
session_start();

if (!isset($_GET['correo'])) {
    echo "Correo no especificado.";
    exit;
}

$correo = $_GET['correo'];

$stmt = $conn->prepare("SELECT * FROM usuario WHERE correo = ? AND admin = 0");
$stmt->bind_param("s", $correo);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    echo "Usuario no encontrado o es administrador.";
    exit;
}

$usuario = $resultado->fetch_assoc();

$descargas = [];
$stmtDesc = $conn->prepare("
    SELECT contenido 
    FROM descarga 
    WHERE correo_usuario = ?
");
$stmtDesc->bind_param("s", $correo);
$stmtDesc->execute();
$resDesc = $stmtDesc->get_result();
while ($fila = $resDesc->fetch_assoc()) {
    $descargas[] = $fila;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuario - MediaShop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background-color: #f4f4f4; }
        header { background-color: #000; color: white; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; position: fixed; top: 0; left: 0; width: 100%; height: 50px; z-index: 1000; }
        header h1 { margin: 0; font-size: 24px; }
        header h1 a { color: white; text-decoration: none; }
        .header-icons { display: flex; gap: 15px; }
        .header-icons a { color: white; text-decoration: none; font-size: 20px; }
        .main { margin-left: 240px; margin-top: 70px; padding: 40px; background-color: #f4f4f4; }
        .sidebar { width: 220px; background-color: #c0c0c0; height: 100vh; position: fixed; top: 50px; left: 0; padding-top: 20px; display: flex; flex-direction: column; }
        .sidebar button { background: none; border: none; padding: 15px 20px; text-align: left; font-size: 16px; cursor: pointer; transition: background 0.3s; }
        .sidebar button:hover { background-color: #bbb; }
        .breadcrumb { font-size: 14px; color: #555; margin-bottom: 15px; }
        form { background-color: #fff; padding: 25px; border-radius: 10px; max-width: 500px; margin: 0 auto; box-shadow: 0 0 5px rgba(0,0,0,0.1); }
        label { font-weight: bold; display: block; margin: 10px 0 5px; }
        input[type=text], input[type=email], input[type=number] { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        .toggle-switch { display: flex; align-items: center; gap: 10px; margin-top: 15px; }
        .toggle-switch input[type=checkbox] { transform: scale(1.2); }
        .boton { background-color: #6a5acd; color: white; padding: 10px 20px; border: none; margin-top: 20px; border-radius: 5px; cursor: pointer; }
    </style>
</head>
<body>

<header>
    <h1><a href="bienvenida_admin.php">MediaShop</a></h1>
    <div class="header-icons">
        <a href="indexadmin.php">🔍</a>
        <a href="#">👤</a>
    </div>
</header>

<div class="sidebar">
    <a href="perfiles_admin.php"><button style="background-color: #bbb; font-weight: bold;">Gestión de Usuarios</button></a>
    <a href="contenidos_admin.php"><button>Gestión de Contenidos</button></a>
    <a href="categorias_admin.php"><button>Gestión de Categorías</button></a>
    <a href="promociones_admin.php"><button>Gestión de Promociones</button></a>
    <a href="calificaciones_admin.php"><button>Gestión de Calificaciones</button></a>
</div>

<div class="main">
    <div class="breadcrumb">Inicio > Gestión de Usuarios > Editar Usuario</div>

    <form action="guardar_usuario.php" method="POST">
        <input type="hidden" name="correo" value="<?php echo htmlspecialchars($usuario['correo']); ?>">
        <label>Nombre:</label>
        <input type="text" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
        <label>Correo:</label>
        <input type="email" value="<?php echo htmlspecialchars($usuario['correo']); ?>" disabled>
        <label>Número:</label>
        <input type="text" name="numero" value="<?php echo htmlspecialchars($usuario['numero']); ?>">
        <label>Saldo:</label>
        <input type="number" name="saldo" value="<?php echo htmlspecialchars($usuario['saldo']); ?>" step="0.01">
        <div class="toggle-switch">
            <label>Estado:</label>
            <input type="checkbox" name="estado" <?php echo ($usuario['tipo_cli'] == 'cliente') ? 'checked' : ''; ?>>
            <span><?php echo ucfirst($usuario['tipo_cli']); ?></span>
        </div>
        <button class="boton" type="submit">Guardar Cambios</button>
        <button type="button" class="boton" onclick="document.getElementById('historialDescargas').style.display='block'">Ver Historial de Descargas</button>
    </form>

    <div id="historialDescargas" style="display:none; margin-top:30px; padding:20px; background:#fff; border-radius:10px; box-shadow:0 0 5px rgba(0,0,0,0.2);">
        <h3>Descargas:</h3>
        <?php if (count($descargas) > 0): ?>
            <ul>
                <?php foreach ($descargas as $item): ?>
                    <li><?= htmlspecialchars($item['contenido']) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No hay descargas registradas.</p>
        <?php endif; ?>
    </div>

</div>
</body>
</html>
