<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/helpers.php';

startAppSession();

$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loginType = $_POST['login_type'] ?? 'id';
    $loginValue = cleanInput($_POST['login_value'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($loginValue === '' || $password === '') {
        $errorMessage = 'Veuillez remplir tous les champs.';
    } else {
        $connection = Database::getConnection();
        $field = $loginType === 'email' ? 'email' : 'user_code';

        $doctorStatement = $connection->prepare("SELECT id, user_code, name, email, password FROM doctors WHERE {$field} = ? LIMIT 1");
        $doctorStatement->bind_param('s', $loginValue);
        $doctorStatement->execute();
        $doctorResult = $doctorStatement->get_result();
        $doctor = $doctorResult ? $doctorResult->fetch_assoc() : null;

        if ($doctor && password_verify($password, $doctor['password'])) {
            $_SESSION['doctor_id'] = (int) $doctor['id'];
            $_SESSION['doctor_code'] = $doctor['user_code'];
            $_SESSION['doctor_name'] = $doctor['name'];
            $_SESSION['doctor_email'] = $doctor['email'];
            $_SESSION['user_role'] = 'doctor';
            $_SESSION['logged_in'] = true;

            header('Location: doctor-dashboard.html');
            exit;
        }

        $adminStatement = $connection->prepare("SELECT id, user_code, name, email, password FROM admins WHERE {$field} = ? LIMIT 1");
        $adminStatement->bind_param('s', $loginValue);
        $adminStatement->execute();
        $adminResult = $adminStatement->get_result();
        $admin = $adminResult ? $adminResult->fetch_assoc() : null;

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = (int) $admin['id'];
            $_SESSION['admin_code'] = $admin['user_code'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['user_role'] = 'admin';
            $_SESSION['logged_in'] = true;

            header('Location: dashboard.html');
            exit;
        }

        $errorMessage = 'Identifiants invalides.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion administration</title>
    <link rel="stylesheet" href="style2.css">
    <style>
        .toggle-group {
            display: flex;
            gap: 8px;
            margin-bottom: 20px;
            padding: 6px;
            background: #e2e8f0;
            border-radius: 12px;
        }

        .toggle-group input[type="radio"] {
            display: none;
        }

        .toggle-btn {
            flex: 1;
            padding: 12px;
            border-radius: 8px;
            text-align: center;
            color: #475569;
            cursor: pointer;
            font-weight: 600;
            transition: 0.2s ease;
        }

        .toggle-group input[type="radio"]:checked + .toggle-btn {
            background: #0f766e;
            color: #fff;
        }

        .error-message {
            margin-bottom: 16px;
            padding: 12px;
            border: 1px solid #fecaca;
            border-radius: 8px;
            background: #fef2f2;
            color: #b91c1c;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="login-container">
    <div class="login-box">
        <a href="../index.html" style="display:inline-block;margin-bottom:15px;color:#0f766e;text-decoration:none;">&larr; Retour a l'accueil</a>
        <h2>Connexion administration / medecin</h2>

        <?php if ($errorMessage !== ''): ?>
            <div class="error-message"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <form method="post" action="login.php">
            <label>Mode de connexion</label>
            <div class="toggle-group">
                <input type="radio" id="admin_login_id" name="login_type" value="id" checked>
                <label class="toggle-btn" for="admin_login_id">ID</label>

                <input type="radio" id="admin_login_email" name="login_type" value="email">
                <label class="toggle-btn" for="admin_login_email">Email</label>
            </div>

            <label>ID ou email</label>
            <input type="text" name="login_value" required>

            <label>Mot de passe</label>
            <input type="password" name="password" required>

            <button type="submit" class="btn">Se connecter</button>
        </form>

        <div style="margin-top:15px;">
            <a href="doctor-register.html" style="color:#0f766e;text-decoration:none;">Creer un compte medecin</a>
        </div>
    </div>
</div>
</body>
</html>
