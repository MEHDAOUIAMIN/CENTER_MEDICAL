<?php
// نبدأ الجلسة لحفظ معلومات المريض بعد الدخول بنجاح
session_start();

// نستدعي ملف الاتصال بقاعدة البيانات
require_once __DIR__ . '/../config/db.php';

// متغير الخطأ (فارغ في البداية)
$error = '';

// نختبر هل تم إرسال النموذج (الـ form)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // استلام القيم من الفورم في صفحة HTML
    $login_type = $_POST['login_type']; // هل اختار ID أم Email؟
    $login_value = $_POST['login_value']; // القيمة المكتوبة
    $password = $_POST['password']; // الرقم السري
    
    // تأكد أن المستخدم لم يترك الخانات فارغة
    if (empty($login_value) || empty($password)) {
        $error = 'الرجاء ملء جميع الخانات';
    } else {
        $conn = Database::getConnection();
        
        // تجهيز جملة البحث (SQL) بناءً على اختياره (ID أو Email)
        if ($login_type == 'id') {
            $sql = "SELECT * FROM patients WHERE user_code = ?";
        } else {
            $sql = "SELECT * FROM patients WHERE email = ?";
        }

        // إرسال الاستعلام للبحث عن المريض
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $login_value);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // إذا وجدنا حساب المريض في قاعدة البيانات
        if ($result->num_rows == 1) {
            $patient = $result->fetch_assoc();
            
            // تحقق من أن كلمة المرور صحيحة
            if (password_verify($password, $patient['password'])) {
                // بدأ وتسجيل نجاح الدخول في الـ Session
                $_SESSION['patient_id'] = $patient['id'];
                $_SESSION['patient_code'] = $patient['user_code'];
                $_SESSION['patient_name'] = $patient['name'];
                $_SESSION['logged_in'] = true;
                $_SESSION['user_role'] = 'patient';

                // نقل المريض لصفحة لوحة التحكم الخاصة به
                header("Location: dashboard.html");
                exit; // نوقف السكريبت هنا
            } else {
                $error = 'كلمة المرور غير صحيحة';
            }
        } else {
            $error = 'لم يتم العثور على حساب بهذا الـ ID أو البريد الإلكتروني';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تسجيل دخول المرضى (مبسط)</title>
    <link rel="stylesheet" href="../admin/style2.css">
    <style>
        /* تصميم احترافي لأزرار الاختيار (Segmented Control) */
        .toggle-group {
            display: flex;
            background: #e2e8f0; /* لون خلفية ناعم */
            border-radius: 12px;
            padding: 5px;
            margin-bottom: 20px;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.06);
            gap: 5px;
        }

        .toggle-group input[type="radio"] {
            display: none; /* إخفاء الدائرة الافتراضية */
        }

        .toggle-btn {
            flex: 1;
            text-align: center;
            padding: 12px 15px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            color: #64748b;
            transition: all 0.3s ease;
            margin: 0 !important;
        }

        .toggle-group input[type="radio"]:checked + .toggle-btn {
            background: #0f766e; /* لون أخضر زمردي جميل */
            color: white;
            box-shadow: 0 4px 10px rgba(15, 118, 110, 0.4);
            transform: scale(1.02);
        }

        .error-message {
            color: #b91c1c; 
            font-weight: bold; 
            margin-bottom: 20px;
            background: #fef2f2;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #fecaca;
            text-align: center;
            box-shadow: 0 2px 4px rgba(220, 38, 38, 0.1);
        }
    </style>
</head>
<body>
<div class="login-container">
   <div class="login-box">
      <a href="../index.html" style="text-decoration:none;color:#10b981;margin-bottom:15px;display:inline-block;">&rarr; العودة للرئيسية</a>
      <h2>تسجيل دخول المرضى</h2>
      
      <!-- إظهار رسالة الخطأ لو وجدت -->
      <?php if($error != ''): ?>
          <div class="error-message"><?php echo $error; ?></div>
      <?php endif; ?>

      <form method="POST" action="login.php">
         <label style="display:block; margin-bottom:10px;">اختر طريقة تسجيل الدخول:</label>
         <div class="toggle-group">
            <input type="radio" id="login_id_opt" name="login_type" value="id" checked>
            <label for="login_id_opt" class="toggle-btn">بالمعرف (ID)</label>

            <input type="radio" id="login_email_opt" name="login_type" value="email">
            <label for="login_email_opt" class="toggle-btn">بالإيميل (Email)</label>
         </div>

         <label>أدخل الـ ID أو البريد هنا:</label>
         <input type="text" name="login_value" required placeholder="مثال: 20001 أو patient@test.com">
         
         <label>كلمة المرور:</label>
         <input type="password" name="password" required>

         <button type="submit" class="btn">تسجيل الدخول</button>
      </form>

      <div style="margin-top:15px; text-align: center;">
         <a href="register.html" style="color:#0f766e; text-decoration: none;">إنشاء حساب مريض جديد</a>
      </div>
   </div>
</div>
</body>
</html>
