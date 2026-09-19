import re

file_path = 'app/Controllers/VendorController.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

delete_func = """
    public function deleteQuote($id) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        header('Content-Type: application/json; charset=utf-8');
        if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['vendor', 'vendor_employee'])) {
            echo json_encode(['success' => false, 'message' => '권한이 없습니다.']);
            return;
        }

        try {
            $db = \App\Core\Database::getInstance();
            $vendorUserId = ($_SESSION['user']['role'] === 'vendor_employee') ? $_SESSION['user']['vendor_user_id'] : $_SESSION['user']['id'];
            
            // Check ownership
            $stmt = $db->prepare("SELECT id FROM quote_requests WHERE id = ? AND vendor_user_id = ?");
            $stmt->execute([$id, $vendorUserId]);
            if (!$stmt->fetch()) {
                throw new \Exception("견적을 찾을 수 없거나 권한이 없습니다.");
            }
            
            // Delete
            $stmt = $db->prepare("DELETE FROM quote_requests WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
"""

if "function deleteQuote" not in content:
    # Remove the very last '}' and append the new function
    content = content.rstrip()
    if content.endswith('}'):
        content = content[:-1] + delete_func
    
    with open(file_path, 'w', encoding='utf-8') as f:
        f.write(content)
    print("Added deleteQuote function to VendorController.php")
else:
    print("deleteQuote already exists")
