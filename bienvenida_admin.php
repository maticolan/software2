<?php
include('conexion.php');
session_start();

// PRO-06:
$resUsuarios = $conn->prepare("CALL GetTotalUsuarios()");
$resUsuarios->execute();
$resUsuarios = $resUsuarios->get_result();
$rowUsuarios = $resUsuarios->fetch_assoc();
$totalUsuarios = $rowUsuarios['total'];
$resUsuarios->close();

// PRO-07:
$resDescargas = $conn->prepare("CALL GetTotalDescargas()");
$resDescargas->execute();
$resDescargas = $resDescargas->get_result();
$rowDescargas = $resDescargas->fetch_assoc();
$totalDescargas = $rowDescargas['total'];
$resDescargas->close();

// PRO-08:
$resIngresos = $conn->prepare("CALL GetTotalIngresos()");
$resIngresos->execute();
$resIngresos = $resIngresos->get_result();
$rowIngresos = $resIngresos->fetch_assoc();
$totalIngresos = $rowIngresos['total'] ?? 0;
$resIngresos->close();

// PRO-09:
$resVendidos = $conn->prepare("CALL GetContenidosMasVendidos()");
$resVendidos->execute();
$resVendidos = $resVendidos->get_result();
$vendidos = [];
while ($fila = $resVendidos->fetch_assoc()) {
    $vendidos[] = $fila;
}
$resVendidos->close();

// PRO-10:
$resClientes = $conn->prepare("CALL GetClientesMasDescargadores()");
$resClientes->execute();
$resClientes = $resClientes->get_result();
$clientes = [];
while ($fila = $resClientes->fetch_assoc()) {
    $clientes[] = $fila;
}
$resClientes->close();

// PRO-11:
$resValorados = $conn->prepare("CALL GetContenidoValorados()");
$resValorados->execute();
$resValorados = $resValorados->get_result();
$valorados = [];
while ($fila = $resValorados->fetch_assoc()) {
    $valorados[] = $fila;
}
$resValorados->close();



?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>MediaShop - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background-color: #f4f4f4; }
        header { background-color: #000; color: white; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; position: fixed; top: 0; left: 0; width: 100%; height: 50px; z-index: 1000; }
        header h1 { margin: 0; font-size: 24px; }
        .header-icons { display: flex; gap: 15px; }
        .header-icons a { color: white; text-decoration: none; font-size: 20px; }
        .sidebar { width: 220px; background-color: #c0c0c0; height: 100vh; position: fixed; top: 50px; left: 0; padding-top: 20px; display: flex; flex-direction: column; }
        .sidebar button { background: none; border: none; padding: 15px 20px; text-align: left; font-size: 16px; cursor: pointer; transition: background 0.3s; }
        .sidebar button:hover { background-color: #bbb; }
        .main { margin-left: 240px; margin-top: 70px; padding: 20px; }
        .main h2 { text-align: center; margin-top: 0; }
        .main h3 { text-align: center; font-weight: normal; color: #555; }
        .cards { display: flex; justify-content: space-around; margin: 30px 0; }
        .card { background-color: #c0c0c0; width: 28%; padding: 20px; border-radius: 8px; text-align: center; font-size: 18px; }
        .card strong { display: block; margin-top: 10px; font-size: 24px; }
        .data-block { background-color: #c0c0c0; margin: 20px 0; padding: 20px; border-radius: 8px; }
        .data-block h4 { text-align: center; margin: 0 0 15px 0; }
        table { width: 100%; border-collapse: collapse; background-color: white; }
        th, td { border: 1px solid #999; padding: 8px; text-align: center; }
        th { background-color: #ddd; }
        .footer { display: flex; justify-content: space-around; flex-wrap: wrap; background-color: #c0c0c0; padding: 20px; margin-top: 40px; }
        .footer-section { flex: 1; min-width: 200px; margin: 10px; }
        .footer-section h4 { margin-bottom: 10px; }
        .footer-section div { margin: 5px 0; }
        .footer-section i { margin-right: 8px; }
        .copy-bar { text-align: center; padding: 10px; background-color: #aaa; font-size: 14px; }
        #todosClientes { display: none; margin-top: 20px; }
        .show-more { cursor: pointer; color: blue; text-decoration: underline; }
    </style>
    <script>
        // FUW-06: 
        function mostrarTodos() {
            document.getElementById("todosClientes").style.display = "block";
            document.getElementById("btnMostrarMas").style.display = "none";
        }
    </script>
</head>
<body>

<header>
    <h1><a href="bienvenida_admin.php" style="color:white;text-decoration:none;">MediaShop</a></h1>
    <div class="header-icons">
        <a href="indexadmin.php">🔍</a>
        <a href="#">👤</a>
    </div>
</header>

<div class="sidebar">
    <a href="perfiles_admin.php"><button>Gestión de Usuarios</button></a>
    <a href="contenidos_admin.php"><button>Gestión de Contenidos</button></a>
    <a href="categorias_admin.php"><button>Gestión de Categorías</button></a>
    <a href="promociones_admin.php"><button>Gestión de Promociones</button></a>
    <a href="calificaciones_admin.php"><button>Gestión de Calificaciones</button></a>
</div>

<div class="main">
    <h2>¡Bienvenido Administrador!</h2>
    <h3>Empieza tus tareas con la barra lateral de la izquierda</h3>

    <div class="cards">
        <div class="card">
            Usuarios Activos
            <strong><?= $totalUsuarios ?></strong>
        </div>
        <div class="card">
            Total Descargas
            <strong><?= $totalDescargas ?></strong>
        </div>
        <div class="card">
            Total Ingresos
            <strong>$<?= number_format($totalIngresos, 2) ?></strong>
        </div>
    </div>

    <div class="data-block">
        <h4>Contenidos más vendidos</h4>
        <table>
            <tr><th>Posición</th><th>Contenido</th><th>Ventas</th></tr>
            <?php 
            for ($i=0; $i < min(10, count($vendidos)); $i++): ?>
            <tr>
                <td><?= $i+1 ?></td>
                <td><?= htmlspecialchars($vendidos[$i]['contenido']) ?></td>
                <td><?= $vendidos[$i]['total_descargas'] ?></td>
            </tr>
            <?php endfor; ?>
        </table>
    </div>

    <div class="data-block">
        <h4>Clientes con más descargas </h4>
        <table>
            <tr><th>Posición</th><th>Cliente</th><th>Descargas</th></tr>
            <?php 
            for ($i=0; $i < min(5, count($clientes)); $i++): ?>
            <tr>
                <td><?= $i+1 ?></td>
                <td><?= htmlspecialchars($clientes[$i]['nombre']) ?> (<?= htmlspecialchars($clientes[$i]['correo']) ?>)</td>
                <td><?= $clientes[$i]['total_descargas'] ?></td>
            </tr>
            <?php endfor; ?>
        </table>

        <div id="todosClientes">
            <table style="margin-top:20px;">
                <tr><th>Posición</th><th>Cliente</th><th>Descargas</th></tr>
                <?php 
                for ($i=0; $i < count($clientes); $i++): ?>
                <tr>
                    <td><?= $i+1 ?></td>
                    <td><?= htmlspecialchars($clientes[$i]['nombre']) ?> (<?= htmlspecialchars($clientes[$i]['correo']) ?>)</td>
                    <td><?= $clientes[$i]['total_descargas'] ?></td>
                </tr>
                <?php endfor; ?>
            </table>
        </div>
    </div>

    <div class="data-block">
        <h4>Contenidos mejor valorados</h4>
        <table>
            <tr><th>Posición</th><th>Contenido</th><th>Promedio Nota</th></tr>
            <?php 
            for ($i=0; $i < min(10, count($valorados)); $i++): ?>
            <tr>
                <td><?= $i+1 ?></td>
                <td><?= htmlspecialchars($valorados[$i]['contenido']) ?></td>
                <td><?= number_format($valorados[$i]['promedio'], 2) ?></td>
            </tr>
            <?php endfor; ?>
        </table>
    </div>


</div>

</body>
</html>
