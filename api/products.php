<?php
include 'db.php';

header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $result = $conn->query("SELECT * FROM products WHERE id=$id");
            $data = $result->fetch_assoc();
            echo json_encode($data);
        } else {
            $result = $conn->query("SELECT * FROM products");
            $products = [];
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
            echo json_encode($products);
        }
        break;

    case 'POST':
        $name = $input['name'];
        $price = $input['price'];
        $category = $input['category'];
        $conn->query("INSERT INTO products (name, price, category) VALUES ('$name', '$price', '$category')");
        echo json_encode(["message" => "Product added successfully"]);
        break;

    case 'PUT':
        $id = $_GET['id'];
        $name = $input['name'];
        $price = $input['price'];
        $category = $input['category'];
        var_dump("UPDATE products SET name='$name',
                     price='$price', category='$category' WHERE id=$id");
        $conn->query("UPDATE products SET name='$name',
                     price='$price', category='$category' WHERE id=$id");
        echo json_encode(["message" => "Product updated successfully"]);
        break;

    case 'DELETE':
        $id = $_GET['id'];
        $conn->query("DELETE FROM products WHERE id=$id");
        echo json_encode(["message" => "Product deleted successfully"]);
        break;

    default:
        echo json_encode(["message" => "Invalid request method"]);
        break;
}

$conn->close();
?>