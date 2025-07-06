<?php
$redirectMap = [
    'squad' => './series.php?id=6410',
    'ommi' => './series.php?id=24072',
    // تقدر تضيف هنا قيم اخرى
];

$s = $_GET['s'] ?? '';

// إذا القيمة موجودة بالخريطة
if (array_key_exists($s, $redirectMap)) {
    $redirectUrl = $redirectMap[$s];
} else {
    // إذا غير موجود نعيد التوجيه لصفحة خطأ أو صفحة مخصصة
    $redirectUrl = './error.php';  // مثلا صفحة خطأ جاهزة عندك
}

header("Location: $redirectUrl");
exit;
?>
