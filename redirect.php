<?php
// خريطة إعادة التوجيه حسب قيمة المعامل s
$redirectMap = [
    'squad' => './series.php?id=6410,
    'ommi' => './series.php?id=24072',
    // تقدر تضيف هنا قيم اخرى
];

// قراءة قيمة المعامل 's' من الرابط
$s = $_GET['s'] ?? '';

// إذا القيمة موجودة بالخريطة، نعيد التوجيه عليها
if (array_key_exists($s, $redirectMap)) {
    $redirectUrl = $redirectMap[$s];
} else {
    // لو القيمة غير موجودة نعيد التوجيه لصفحة افتراضية
    $redirectUrl = './'; // أو اي رابط تحب
}

// إعادة التوجيه
header("Location: $redirectUrl");
exit;
?>
