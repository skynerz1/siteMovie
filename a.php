<?php
// خريطة الروابط حسب القيمة
$links = [
    'mtv' => 'https://t.me/MTVMSLSL1',
    'dev' => 'https://t.me/wgggk',
];

// التقاط القيمة من الباراميتر to
$key = $_GET['to'] ?? null;

// التوجيه إذا كانت القيمة موجودة
if ($key && isset($links[$key])) {
    header("Location: " . $links[$key]);
    exit;
} else {
    echo "الرابط غير موجود.";
}
?>
