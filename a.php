<?php
// خريطة الروابط بناءً على القيم
$links = [
    'mtv' => 'https://t.me/MTVMSLSL1',
    'dev' => 'https://t.me/wgggk',
];

// التقاط القيمة من الرابط (?=value)
$key = $_GET[''] ?? null;

// إذا كانت القيمة موجودة في المصفوفة، يتم التوجيه
if ($key && isset($links[$key])) {
    header("Location: " . $links[$key]);
    exit;
} else {
    // إذا القيمة غير معروفة، رسالة خطأ أو توجيه افتراضي
    echo "رابط غير معروف.";
}
?>
