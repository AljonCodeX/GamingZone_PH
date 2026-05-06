<?php
require('dbcon.php');

if (isset($_GET['id'])) {
    $id     = mysqli_real_escape_string($conn, $_GET['id']);
    $query  = "DELETE FROM items WHERE id='$id'";
    $result = mysqli_query($conn, $query);

    if ($result) {
        header("Location: ../admin/admin.php?msg=deleted");
    } else {
        echo "Failed to delete item.";
    }
} else {
    header("Location: ../admin/admin.php");
}
?>
