<?php
include 'db.php';

header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $result = $conn->query("SELECT * FROM transactions WHERE id=$id");
            $data = $result->fetch_assoc();
            echo json_encode($data);
        } else {
            $result = $conn->query("SELECT * FROM transactions");
            $transactions = [];
            while ($row = $result->fetch_assoc()) {
                $data = [
                    "date" => $row['created_at'],
                    "time" => "04:00 AM",
                    "totalPrice" => 0,
                    "paymentType" => $row['payment_type'],
                    "orderType" => $row['order_type'],
                    "orderNumber" => isset($row['order_number']) && $row['order_number'] !== '' ? $row['order_number'] : "ORD" . $row['id']
                ];

                $result2 = $conn->query("SELECT * FROM transaction_items WHERE transaction_id=". $row['id']);
                
                if ($result2) {
                    $transaction_items = [];
                    while ($item = $result2->fetch_assoc()) {
                        for ($i=1;$i<=$item['quantity'];$i++) {
                            $transaction_items[] = [
                                'id' => $item['id'],
                                'name' => $item['item_name'],
                                'price' => $item['item_price'],
                                'refunded' => $item['refunded'] == 1
                            ];
                            $data['totalPrice'] += !$item['refunded'] ? $item['item_price'] : 0;
                        }
                    }
                    $data['items'] = $transaction_items;
                }
                $transactions[] = $data;
                
            }
            echo json_encode($transactions);
        }
        break;

    case 'POST':
        $order_number = $input['order_number'];
        $total_price = $input['total_price'];
        $order_type = $input['order_type'];
        $payment_type = $input['payment_type'];
        $items = $input['items'];
        $saved = $conn->query("INSERT INTO transactions (total_price, order_type, payment_type, order_number) VALUES ('$total_price', '$order_type', '$payment_type', '$order_number')");
        $last_id = $conn->insert_id;
        $itemSummary = [];
        foreach ($items as $item) {
            if (!isset($itemSummary[$item['name']])) {
                $itemSummary[$item['name']] = [
                    'count' => isset($item['quantity']) ? $item['quantity'] : 1,
                    'price' => $item['price']
                ];
            } else {
                $itemSummary[$item['name']]['count'] += 1;
            }
        }
        foreach ($itemSummary as $name => $item) {
            $conn->query("INSERT INTO transaction_items (
                transaction_id, item_price, item_name, quantity, total_price
            ) VALUES (
                '$last_id',
                '". $item['price']. "',
                '$name',
                '". $item['count'] ."',
                '". ($item['price'] * $item['count']) ."'
            )");
        }
        echo json_encode(["message" => "Product added successfully"]);
        break;

    case 'PUT':
        $items = $input['items'];
        foreach ($items as $item) {
            $conn->query("UPDATE transaction_items SET refunded=". ($item['refunded'] ? 1 : 0) ." WHERE id=" . $item['id']);
        }
        echo json_encode(["message" => "Product updated successfully"]);
        break;

    case 'DELETE':
        $id = $_GET['id'];
        $conn->query("DELETE FROM transactions WHERE id=$id");
        echo json_encode(["message" => "Product deleted successfully"]);
        break;

    default:
        echo json_encode(["message" => "Invalid request method"]);
        break;
}

$conn->close();
?>
