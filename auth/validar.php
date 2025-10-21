<?php
include_once("../includes/db.php");
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "UPDATE usuarios SET estado='activo' WHERE id=$id AND rol='dueno'";
    if ($conn->query($sql)) {
        header("Location: ../admin/admin.php?msg=dueno_aprobado");
    } else {
        echo "Error al aprobar: " . $conn->error;
    }
}
cerrarConexion($conn);
?>
