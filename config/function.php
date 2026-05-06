<?php
require('dbcon.php');

if (isset($_POST['addItem'])) {

    $name        = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price       = mysqli_real_escape_string($conn, $_POST['price']);
    $quantity    = mysqli_real_escape_string($conn, $_POST['quantity']);
    $image       = '';

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../assets/images/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);
        $ext     = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($ext, $allowed) && getimagesize($_FILES['image']['tmp_name'])) {
            $filename = uniqid('img_') . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $filename);
            $image = $filename;
        }
    }

    $query  = "INSERT INTO items (name, description, price, quantity, image)
               VALUES ('$name', '$description', '$price', '$quantity', '$image')";
    $result = mysqli_query($conn, $query);

    if ($result) {
        header("Location: ../admin/admin.php?msg=added");
    } else {
        echo "Failed to add item.";
    }
}

if (isset($_POST['editItem'])) {

    $id          = mysqli_real_escape_string($conn, $_GET['id']);
    $name        = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price       = mysqli_real_escape_string($conn, $_POST['price']);
    $quantity    = mysqli_real_escape_string($conn, $_POST['quantity']);

    $img_res = mysqli_query($conn, "SELECT image FROM items WHERE id='$id'");
    $img_row = mysqli_fetch_array($img_res);
    $image   = $img_row['image'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../assets/images/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);
        $ext     = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($ext, $allowed) && getimagesize($_FILES['image']['tmp_name'])) {
            $filename = uniqid('img_') . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $filename);
            $image = $filename;
        }
    }

    $query  = "UPDATE items SET name='$name', description='$description', price='$price', quantity='$quantity', image='$image' WHERE id='$id'";
    $result = mysqli_query($conn, $query);

    if ($result) {
        header("Location: ../admin/admin.php?msg=updated");
    } else {
        echo "Failed to update item.";
    }
}
?>
