<?php
// Add error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../utils/helpers.php';

// Set JSON header early
header('Content-Type: application/json');

try {
    $conn = Database::getConnection();
    
    // Test connection
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    if($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendJsonResponse('error', 'Invalid method');
        exit;
    }
    
    $loginId = trim($_POST['login_id'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validate input
    if(empty($loginId) || empty($password)) {
        sendJsonResponse('error', 'ID and password are required');
        exit;
    }
    
    // Check doctors table
    $stmt = $conn->prepare("SELECT id, user_code, name, email, password FROM doctors WHERE user_code = ?");
    if(!$stmt) {
        throw new Exception('Prepare failed for doctors query: ' . $conn->error);
    }
    
    $stmt->bind_param("s", $loginId);
    if(!$stmt->execute()) {
        throw new Exception('Execute failed for doctors: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if($result->num_rows === 1){
        $user = $result->fetch_assoc();
        
        // Verify password
        if(password_verify($password, $user['password'])){
            // Start session securely
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            // Regenerate session ID for security
            session_regenerate_id(true);
            
            $_SESSION['doctor_id'] = $user['id'];
            $_SESSION['doctor_code'] = $user['user_code'] ?? '';
            $_SESSION['doctor_name'] = $user['name'];
            $_SESSION['user_role'] = 'doctor';
            $_SESSION['logged_in'] = true;
            
            sendJsonResponse('success', null, [
                'role' => 'doctor', 
                'redirect' => 'doctor-dashboard.html',
                'name' => $user['name']
            ]);
            exit;
        } else {
            sendJsonResponse('error', 'Incorrect password');
            exit;
        }
    }
    
    // If not doctor, check admins table
    $stmt = $conn->prepare("SELECT id, user_code, name, email, password FROM admins WHERE user_code = ?");
    if(!$stmt) {
        throw new Exception('Prepare failed for admins query: ' . $conn->error);
    }
    
    $stmt->bind_param("s", $loginId);
    if(!$stmt->execute()) {
        throw new Exception('Execute failed for admins: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if($result->num_rows === 1){
        $user = $result->fetch_assoc();
        
        if(password_verify($password, $user['password'])){
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            session_regenerate_id(true);
            
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_code'] = $user['user_code'] ?? '';
            $_SESSION['admin_name'] = $user['name'];
            $_SESSION['user_role'] = 'admin';
            $_SESSION['logged_in'] = true;
            
            sendJsonResponse('success', null, [
                'role' => 'admin', 
                'redirect' => 'dashboard.html',
                'name' => $user['name']
            ]);
            exit;
        } else {
            sendJsonResponse('error', 'Incorrect password');
            exit;
        }
    }
    
    // No user found
    sendJsonResponse('error', 'ID not found');
    
} catch (Exception $e) {
    // Log error server-side
    error_log('Login error: ' . $e->getMessage());
    
    // Send user-friendly error
    sendJsonResponse('error', 'Login failed: ' . $e->getMessage());
}
?>
