<?php

namespace App\Controllers;

class PaymentController extends BaseController {
    public function index() {
        // Show payment options or pricing
        $db = \App\Core\Database::getInstance();
        $products = $db->query("SELECT * FROM products WHERE is_active = 1 ORDER BY display_order ASC")->fetchAll();
        
        $this->view('shop/payment/index', [
            'products' => $products
        ]);
    }

    public function checkout($vars) {
        $productId = $vars['plan_id'] ?? null;
        $db = \App\Core\Database::getInstance();
        
        $stmt = $db->prepare("SELECT * FROM products WHERE id = ? AND is_active = 1");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();

        if (!$product) {
            $this->redirect('/payment?error=not_found');
            return;
        }

        global $user;

        $this->view('shop/payment/checkout', [
            'product' => $product,
            'buyer_id' => $user['user_id'] ?? 'guest',
            'paddle_client_token' => $_ENV['PADDLE_CLIENT_TOKEN'] ?? '',
            'paddle_env' => $_ENV['PADDLE_ENVIRONMENT'] ?? 'sandbox'
        ]);
    }

    public function webhook() {
        $payload = json_decode(file_get_contents('php://input'), true);
        $signature = $_SERVER['HTTP_PADDLE_SIGNATURE'] ?? '';

        // TODO: Verify signature properly using Paddle SDK
        
        $eventType = $payload['event_type'] ?? '';
        
        if ($eventType === 'transaction.completed') {
            $transaction = $payload['data'];
            $customData = $transaction['custom_data'] ?? [];
            
            $productId = $customData['product_id'] ?? null;
            $buyerId = $customData['user_id'] ?? 'guest';
            
            if ($productId) {
                $db = \App\Core\Database::getInstance();
                
                // Fetch product details
                $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
                $stmt->execute([$productId]);
                $product = $stmt->fetch();
                
                if ($product) {
                    // Create Order
                    $orderNo = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
                    $amount = isset($transaction['details']['totals']['grand_total']) 
                                ? $transaction['details']['totals']['grand_total'] / 100 
                                : $product['price'];
                    
                    $status = ($product['type'] === 'digital') ? 'completed' : 'paid';
                    
                    $stmt = $db->prepare("INSERT INTO orders (order_no, buyer_id, seller_id, product_id, amount, point_reward, status) VALUES (:order_no, :buyer_id, :seller_id, :product_id, :amount, :point_reward, :status)");
                    $stmt->execute([
                        'order_no' => $orderNo,
                        'buyer_id' => $buyerId,
                        'seller_id' => $product['seller_id'],
                        'product_id' => $product['id'],
                        'amount' => $amount,
                        'point_reward' => $product['point_reward'],
                        'status' => $status
                    ]);
                   
                    // Update Stock if Physical
                    if ($product['type'] === 'physical' && $product['stock'] > 0) {
                        $db->prepare("UPDATE products SET stock = stock - 1 WHERE id = ?")->execute([$productId]);
                    }

                    // Log activity or handle digital delivery (send email etc)
                }
            }
        }
        
        $this->json(['status' => 'processed']);
    }

    public function success() {
        $this->view('shop/payment/success');
    }

    public function secureDownload($vars) {
        $orderId = $vars['id'];
        $db = \App\Core\Database::getInstance();
        
        global $is_member, $user;
        if (!$is_member) {
            $this->redirect('/login?msg=Login required');
            return;
        }

        // 1. Fetch Order and Product
        $stmt = $db->prepare("SELECT o.*, p.digital_link, p.name as product_name 
                              FROM orders o 
                              JOIN products p ON o.product_id = p.id 
                              WHERE o.id = ? AND o.buyer_id = ? AND o.status IN ('paid', 'completed')");
        $stmt->execute([$orderId, $user['user_id']]);
        $order = $stmt->fetch();

        if (!$order || !$order['digital_link']) {
            die("Invalid order or no download available");
        }

        $link = trim($order['digital_link']);
        if (filter_var($link, FILTER_VALIDATE_URL)) {
            $this->redirect($link);
        } else {
            echo "Download Info: " . htmlspecialchars($link);
        }
    }

    public function confirmOrder() {
        global $is_member, $user;
        if (!$is_member) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Login required']);
            return;
        }

        $orderId = $_POST['order_id'] ?? null;
        if (!$orderId) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
            return;
        }

        $db = \App\Core\Database::getInstance();
        
        // Check if order exists and belongs to user
        $stmt = $db->prepare("SELECT id, status FROM orders WHERE id = ? AND buyer_id = ?");
        $stmt->execute([$orderId, $user['user_id']]);
        $order = $stmt->fetch();

        if (!$order) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            return;
        }

        if ($order['status'] === 'completed') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Order already completed']);
            return;
        }

        // Update status to completed
        $stmt = $db->prepare("UPDATE orders SET status = 'completed' WHERE id = ?");
        $stmt->execute([$orderId]);

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    }
}
