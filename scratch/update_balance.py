import re

with open('app/Controllers/VendorController.php', 'r', encoding='utf-8') as f:
    content = f.read()

new_func = r"""    public function getQuoteBalance($vendorUserId) {
        $db = \App\Core\Database::getInstance();
        $userStmt = $db->prepare("SELECT plan FROM users WHERE user_id = ? OR id = ? LIMIT 1");
        $userStmt->execute([$vendorUserId, $vendorUserId]);
        $userData = $userStmt->fetch();
        $plan = $userData['plan'] ?? 'free';

        return [
            'total_limit'   => 99999999,
            'addon_balance' => 0,
            'used'          => 0,
            'remaining'     => 99999999,
            'plan'          => $plan
        ];
    }"""

pattern = r"    public function getQuoteBalance\(\$vendorUserId\) \{.*?return \[\n            'total_limit'.*?\];\n    \}"

# Need to escape backslashes in replacement string
new_func_escaped = new_func.replace('\\', '\\\\')

new_content = re.sub(pattern, new_func_escaped, content, flags=re.DOTALL)

with open('app/Controllers/VendorController.php', 'w', encoding='utf-8') as f:
    f.write(new_content)
