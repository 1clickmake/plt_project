file_path = 'app/Controllers/VendorController.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

import re

# Find the start and end of deleteQuote
start_idx = content.find('public function deleteQuote($id)')
if start_idx != -1:
    end_idx = content.find('    }\n}', start_idx) + 5
    
    new_delete = """public function deleteQuote($id) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        header('Content-Type: application/json; charset=utf-8');
        if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
            echo json_encode(['success' => false, 'message' => '로그인이 필요합니다.']);
            return;
        }

        try {
            $db = \\App\\Core\\Database::getInstance();
            $user = $_SESSION['user'];
            
            $vendorUserId = $user['id'] ?? null;
            $vendorUserStrId = $user['user_id'] ?? '';
            if (isset($user['role']) && $user['role'] === 'vendor_employee') {
                $vendorUserId = $user['vendor_user_id'];
                $vendorUserStrId = '';
            }
            
            // Check ownership
            $stmt = $db->prepare("SELECT id FROM quote_requests WHERE id = :id AND (vendor_user_id = :vuid1 OR vendor_user_id = :vuid2)");
            $stmt->execute([
                'id' => $id,
                'vuid1' => $vendorUserId,
                'vuid2' => $vendorUserStrId
            ]);
            
            // If the user is admin, allow anyway
            $isAdmin = isset($user['role']) && $user['role'] === 'admin';
            
            if (!$isAdmin && !$stmt->fetch()) {
                throw new \\Exception("견적을 찾을 수 없거나 삭제할 권한이 없습니다.");
            }
            
            // Delete
            $stmt = $db->prepare("DELETE FROM quote_requests WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode(['success' => true]);
        } catch (\\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }"""
    
    content = content[:start_idx] + new_delete + content[end_idx:]
    with open(file_path, 'w', encoding='utf-8') as f:
        f.write(content)
    print("Updated deleteQuote function")
else:
    print("Could not find deleteQuote")
