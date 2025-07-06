<?php
// المسار الكامل داخل موقعك
$redirectUrl = './?page=2&search=' . urlencode('لعبه الحبار');

// إعادة التوجيه
header("Location: $redirectUrl");
exit;
?>
