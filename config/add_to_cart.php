<?php
session_start();
require('dbcon.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $item_id  = (int)$_POST['item_id'];
    $quantity = (int)$_POST['quantity'];
    $buy_now  = isset($_POST['buy_now']) && $_POST['buy_now'] == '1';

    $result = mysqli_query($conn, "SELECT * FROM items WHERE id='$item_id'");
    $item   = mysqli_fetch_array($result);

    if ($item && $quantity > 0 && $quantity <= $item['quantity']) {

        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

        $found      = false;
        $cart_index = null;

        foreach ($_SESSION['cart'] as $index => &$cart_item) {
            if ($cart_item['id'] == $item_id) {
                $cart_item['quantity'] += $quantity;
                $found      = true;
                $cart_index = $index;
                break;
            }
        }
        unset($cart_item);

        if (!$found) {
            $_SESSION['cart'][] = [
                'id'       => $item['id'],
                'name'     => $item['name'],
                'price'    => $item['price'],
                'quantity' => $quantity,
                'image'    => $item['image']
            ];
            $cart_index = array_key_last($_SESSION['cart']);
        }

        if ($buy_now) {
            $redirect = 'checkout.php?' . http_build_query(['items' => [$cart_index]]);

            if (!isset($_SESSION['user_id'])) {
                $_SESSION['redirect_after_login'] = $redirect;
                header("Location: ../login.php");
            } else {
                header("Location: ../" . $redirect);
            }

        } else {
            $_SESSION['message'] = "Item added to cart!";
            header("Location: ../index.php");
        }

    } else {
        $_SESSION['message'] = "Invalid quantity.";
        header("Location: ../index.php");
    }
}

exit();
?>
