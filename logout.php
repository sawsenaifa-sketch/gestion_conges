<?php
// 1. التأكد من بدء الجلسة إذا لم تكن مفعّلة
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. تفريغ جميع متغيرات الجلسة المسجلة
$_SESSION = array();

// 3. حذف كوكيز الجلسة من متصفح المستخدم نهائياً
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. تدمير الجلسة بالكامل من السيرفر
session_destroy();

// 5. إعادة التوجيه لصفحة تسجيل الدخول الرئيسية
header("Location: index.php");
exit;
?>