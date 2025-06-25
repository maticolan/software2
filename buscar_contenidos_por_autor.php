<?php
include('conexion.php');

if (isset($_POST['autor'])) {
    $autor = $_POST['autor'];
    // PRO-12: 
    $stmt = $conn->prepare("CALL GetContenidosPorAutor(?)");
    $stmt->bind_param("s", $autor);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "<p>No hay contenidos de este autor.</p>";
    } else {
        while ($row = $result->fetch_assoc()) {
            echo "<div style='display:flex; align-items:center; margin-bottom:10px;'>
                    <img src='".$row['ruta_preview']."' alt='preview' style='width:50px; height:50px; margin-right:10px;'>
                    <a href='editar_contenido.php?id=".urlencode($row['nombre_prod'])."'>
                        ".htmlspecialchars($row['nombre_prod'])."
                    </a>
                </div>";

        }
    }
    $stmt->close();
}
?>
