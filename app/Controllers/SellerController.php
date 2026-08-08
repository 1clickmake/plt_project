<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Csrf;
use PDO;

class SellerController extends BaseController {
    public function __construct() {
        global $is_member;
        if (!$is_member) {
            $this->redirect('/login');
        }
    }

    public function products() {
        global $user;
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM products WHERE seller_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user['user_id']]);
        $products = $stmt->fetchAll();

        $this->view('shop/seller/products', [
            'products' => $products,
            'csrf_token' => Csrf::getToken()
        ]);
    }

    public function createProduct() {
        global $user;
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) die("CSRF validation failed");

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $type = $_POST['type'] ?? 'digital';
        $stock = intval($_POST['stock'] ?? 0);
        $shippingFee = floatval($_POST['shipping_fee'] ?? 0);
        $digitalLink = trim($_POST['digital_link'] ?? '');
        $paddlePriceId = trim($_POST['paddle_price_id'] ?? '');

        if ($name) {
            $db = Database::getInstance();
            $stmt = $db->prepare("INSERT INTO products (name, description, price, type, stock, shipping_fee, digital_link, paddle_price_id, seller_id, status) VALUES (:name, :description, :price, :type, :stock, :shipping_fee, :digital_link, :paddle_price_id, :seller_id, 'pending')");
            $stmt->execute([
                'name' => $name,
                'description' => $description,
                'price' => $price,
                'type' => $type,
                'stock' => $stock,
                'shipping_fee' => $shippingFee,
                'digital_link' => $digitalLink,
                'paddle_price_id' => $paddlePriceId,
                'seller_id' => $user['user_id']
            ]);
        }

        $this->redirect('/seller/products?msg=Product submitted for approval');
    }

    public function settlement() {
        global $user;
        $db = Database::getInstance();
        
        // Calculate balance
        $stmt = $db->prepare("SELECT SUM(amount) FROM orders WHERE seller_id = ? AND status = 'completed'");
        $stmt->execute([$user['user_id']]);
        $totalSales = $stmt->fetchColumn() ?: 0;
        
        $stmt = $db->prepare("SELECT SUM(amount) FROM settlements WHERE seller_id = ? AND status IN ('request', 'approved', 'paid')");
        $stmt->execute([$user['user_id']]);
        $totalSettled = $stmt->fetchColumn() ?: 0;
        
        $balance = $totalSales - $totalSettled;

        $stmt = $db->prepare("SELECT * FROM settlements WHERE seller_id = ? ORDER BY request_date DESC");
        $stmt->execute([$user['user_id']]);
        $history = $stmt->fetchAll();

        // Check for pending request
        $hasPending = false;
        foreach ($history as $h) {
            if ($h['status'] === 'request') {
                $hasPending = true;
                break;
            }
        }

        $config = $db->query("SELECT mall_commission FROM config WHERE id = 1")->fetch();
        $this->view('shop/seller/settlement', [
            'balance' => $balance,
            'history' => $history,
            'hasPending' => $hasPending,
            'commission' => $config['mall_commission'] ?? 10,
            'csrf_token' => Csrf::getToken()
        ]);
    }
    public function requestSettlement() {
        global $user;
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) die("CSRF validation failed");

        $db = Database::getInstance();
        
        // 1. Check for existing pending request
        $stmt = $db->prepare("SELECT id FROM settlements WHERE seller_id = ? AND status = 'request'");
        $stmt->execute([$user['user_id']]);
        if ($stmt->fetch()) {
            $this->redirect('/seller/settlement?msg=Error: Already have a pending request');
            return;
        }

        $amount = floatval($_POST['amount'] ?? 0);
        $memo = trim($_POST['memo'] ?? '');

        if ($amount > 0) {
            $stmt = $db->prepare("INSERT INTO settlements (seller_id, amount, memo) VALUES (?, ?, ?)");
            $stmt->execute([$user['user_id'], $amount, $memo]);
        }

        $this->redirect('/seller/settlement?msg=Settlement requested');
    }

    public function orders() {
        global $user;
        $db = Database::getInstance();
        
        $stmt = $db->prepare("SELECT o.*, p.name as product_name, p.type as product_type 
                              FROM orders o 
                              JOIN products p ON o.product_id = p.id 
                              WHERE o.seller_id = ? 
                              ORDER BY o.created_at DESC");
        $stmt->execute([$user['user_id']]);
        $orders = $stmt->fetchAll();

        $this->view('shop/seller/orders', [
            'orders' => $orders,
            'csrf_token' => Csrf::getToken()
        ]);
    }

    public function updateOrderStatus() {
        global $user;
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) die("CSRF validation failed");

        $orderId = $_POST['order_id'] ?? null;
        $status = $_POST['status'] ?? '';

        if ($orderId && $status) {
            $db = Database::getInstance();
            // Verify ownership
            $stmt = $db->prepare("SELECT id FROM orders WHERE id = ? AND seller_id = ?");
            $stmt->execute([$orderId, $user['user_id']]);
            if ($stmt->fetch()) {
                $stmt = $db->prepare("UPDATE orders SET status = ? WHERE id = ?");
                $stmt->execute([$status, $orderId]);
            }
        }

        $this->redirect('/seller/orders?msg=Order status updated');
    }
}
