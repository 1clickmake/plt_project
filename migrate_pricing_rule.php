<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/config.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$db = App\Core\Database::getInstance();

try {
    // 1. Add column if it doesn't exist
    $db->exec("ALTER TABLE quote_requests ADD COLUMN pricing_rule_id INT NULL AFTER vendor_user_id");
    echo "Column pricing_rule_id added.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column pricing_rule_id already exists.\n";
    } else {
        echo "Error adding column: " . $e->getMessage() . "\n";
    }
}

// 2. Migrate existing records
// For each quote_request without a pricing_rule_id, find the latest vendor_pricing_rule that was created BEFORE or ON the quote's created_at time.
// If none exists, find the oldest one (the first one ever uploaded).
$stmt = $db->query("SELECT id, vendor_user_id, created_at FROM quote_requests WHERE pricing_rule_id IS NULL");
$quotes = $stmt->fetchAll();

foreach ($quotes as $q) {
    $vuid = $q['vendor_user_id'];
    $createdAt = $q['created_at'];

    // Find the latest rule that existed at the time of quote creation
    $ruleStmt = $db->prepare("SELECT id FROM vendor_pricing_rules WHERE vendor_id = :vuid AND created_at <= :ca ORDER BY created_at DESC LIMIT 1");
    $ruleStmt->execute(['vuid' => $vuid, 'ca' => $createdAt]);
    $ruleId = $ruleStmt->fetchColumn();

    if (!$ruleId) {
        // Fallback: earliest rule
        $ruleStmt = $db->prepare("SELECT id FROM vendor_pricing_rules WHERE vendor_id = :vuid ORDER BY created_at ASC LIMIT 1");
        $ruleStmt->execute(['vuid' => $vuid]);
        $ruleId = $ruleStmt->fetchColumn();
    }

    if ($ruleId) {
        $updateStmt = $db->prepare("UPDATE quote_requests SET pricing_rule_id = :rid WHERE id = :qid");
        $updateStmt->execute(['rid' => $ruleId, 'qid' => $q['id']]);
        echo "Updated quote_request {$q['id']} with rule {$ruleId}.\n";
    } else {
        echo "No pricing rule found for vendor {$vuid}.\n";
    }
}
echo "Migration complete.\n";
