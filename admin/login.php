<?php
// نبدأ الجلسة (Session) لحفظ بيانات المستخدم بعد تسجيل الدخول
session_start();

// نستدعي ملف الاتصال بقاعدة البيانات
require_once __DIR__ . '/../config/db.php';

// متغير لحفظ رسائل الخطأ إن وجدت
$error = '';

// التحقق من أن النموذج تم إرساله (عن طريق زر Submit)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // استقبال البيانات من الفورم
    $login_type = $_POST['login_type']; // إما 'id' أو 'email'
    $login_value = $_POST['login_value']; // القيمة التي أدخلها المستخدم
    $password = $_POST['password']; // كلمة المرور
    
    // التأكد من أن الحقول ليست فارغة
    if (empty($login_value) || empty($password)) {
        $error = 'الرجاء إدخال جميع البيانات المطلوبة';
    } else {
        // الاتصال بقاعدة البيانات
        $conn = Database::getConnection();
        
        // --- أولاً: التحقق إذا كان المستخدم طبيباً ---
        // تحديد نوع البحث بناءً على اختيار المستخدم (ID أو إيميل)
        if ($login_type == 'id') {
            $sql_doctor = "SELECT * FROM doctors WHERE user_code = ?";
        } else {
            $sql_doctor = "SELECT * FROM doctors WHERE email = ?";
        }

        // تجهيز وتنفيذ الاستعلام للأطباء
        $stmt_doc = $conn->prepare($sql_doctor);
        $stmt_doc->bind_param("s", $login_value);
        $stmt_doc->execute();
        $result_doc = $stmt_doc->get_result();
        
        // إذا وجدنا الطبيب في قاعدة البيانات
        if ($result_doc->num_rows == 1) {
            $doctor = $result_doc->fetch_assoc();
            
            // التحقق من صحة كلمة المرور المكتوبة
            if (password_verify($password, $doctor['password'])) {
                // حفظ البيانات في الجلسة (Session)
                $_SESSION['doctor_id'] = $doctor['id'];
                $_SESSION['user_role'] = 'doctor';
                $_SESSION['logged_in'] = true;
                
                // توجيه الطبيب إلى لوحة التحكم الخاصة به
                header("Location: doctor-dashboard.html");
                exit; // إنهاء السكريبت بعد التوجيه
            } else {
                $error = 'كلمة المرور غير صحيحة';
            }
        } 
        else {
            // --- ثانياً: إذا لم يكن طبيباً، نتحقق إذا كان مديراً (Admin) ---
            if ($login_type == 'id') {
                $sql_admin = "SELECT * FROM admins WHERE user_code = ?";
            } else {
                $sql_admin = "SELECT * FROM admins WHERE email = ?";
            }

            // تجهيز وتنفيذ الاستعلام للمدراء
            $stmt_admin = $conn->prepare($sql_admin);
            $stmt_admin->bind_param("s", $login_value);
            $stmt_admin->execute();
            $result_admin = $stmt_admin->get_result();

            // إذا وجدنا المدير في قاعدة البيانات
            if ($result_admin->num_rows == 1) {
                $admin = $result_admin->fetch_assoc();
                
                // التحقق من صحة كلمة المرور
                if (password_verify($password, $admin['password'])) {
                    // حفظ البيانات في الجلسة للمدير
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['user_role'] = 'admin';
                    $_SESSION['logged_in'] = true;
                    
                    // توجيه المدير إلى لوحة التحكم الرئيسية
                    header("Location: dashboard.html");
                    exit;
                } else {
                    $error = 'كلمة المرور غير صحيحة';
                }
            } else {
                // إذا لم يتم إيجاده لا كطبيب ولا كمدير
                $error = 'لم يتم العثور على حساب بهذا الـ ID أو البريد الإلكتروني';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تسجيل دخول الإدارة / الأطباء (نسخة مبسطة)</title>
    <link rel="stylesheet" href="style2.css">
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
      <h2>دخول الإدارة / الطبيب</h2>
      
      <!-- إظهار رسائل الخطأ البسيطة والواضحة -->
      <?php if($error != ''): ?>
          <div class="error-message"><?php echo $error; ?></div>
      <?php endif; ?>

      <form method="POST" action="login.php">
         <!-- طريقة الدخول: ID أو Email -->
         <label style="display:block; margin-bottom:10px;">اختر طريقة تسجيل الدخول:</label>
         <div class="toggle-group">
            <input type="radio" id="login_id_opt" name="login_type" value="id" checked>
            <label for="login_id_opt" class="toggle-btn">بالمعرف (ID)</label>

            <input type="radio" id="login_email_opt" name="login_type" value="email">
            <label for="login_email_opt" class="toggle-btn">بالإيميل (Email)</label>
         </div>

         <label>أدخل الـ ID أو البريد هنا:</label>
         <input type="text" name="login_value" required placeholder="مثال: 30001 أو admin@test.com">
         
         <label>كلمة المرور:</label>
         <input type="password" name="password" required>

         <button type="submit" class="btn">تسجيل الدخول</button>
      </form>

      <div style="margin-top:15px; text-align: center;">
         <a href="doctor-register.html" style="color:#0f766e; text-decoration: none;">إنشاء حساب طبيب جديد</a>
      </div>
   </div>
</div>
</body>
</html>
