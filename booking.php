<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/utils/helpers.php';

startAppSession();

if (!isset($_SESSION['patient_id'])) {
    $_SESSION['redirect_after_login'] = '../booking.php';
    redirectTo('patient/login.php');
}

$connection = Database::getConnection();
$patientId = (int) $_SESSION['patient_id'];
$statement = $connection->prepare('SELECT user_code, name, email, phone FROM patients WHERE id = ? LIMIT 1');
$statement->bind_param('i', $patientId);
$statement->execute();
$result = $statement->get_result();
$patient = $result ? $result->fetch_assoc() : null;

if (!$patient) {
    session_destroy();
    redirectTo('patient/login.php');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reservation</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<section class="booking-section" id="book">
  <div class="booking-container">
    <div style="margin-bottom:18px;display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;">
      <a href="index.html" style="color:#0f766e;text-decoration:none;font-weight:600;">&larr; Retour a l'accueil</a>
      <a href="patient/mes_rendez_vous.php" style="color:#0f766e;text-decoration:none;font-weight:600;">Mes rendez-vous</a>
    </div>

    <h1>Reserver un rendez-vous</h1>

    <div style="margin-bottom:18px;padding:14px;border-radius:10px;background:#f8fafc;border:1px solid #dbeafe;">
      <strong>Patient :</strong> <?php echo htmlspecialchars($patient['name'], ENT_QUOTES, 'UTF-8'); ?><br>
      <strong>ID :</strong> <?php echo htmlspecialchars($patient['user_code'], ENT_QUOTES, 'UTF-8'); ?>
    </div>

    <form class="booking-form" id="bookingForm" method="POST">
      <input type="text" value="<?php echo htmlspecialchars($patient['name'], ENT_QUOTES, 'UTF-8'); ?>" readonly>
      <input type="email" value="<?php echo htmlspecialchars($patient['email'], ENT_QUOTES, 'UTF-8'); ?>" readonly>
      <input type="tel" value="<?php echo htmlspecialchars((string) $patient['phone'], ENT_QUOTES, 'UTF-8'); ?>" readonly>

      <select id="doctorSelect" name="doctor_id" required>
        <option value="">Chargement des medecins...</option>
      </select>

      <div class="datetime">
        <input type="date" name="appointment_date" required>
        <input type="time" name="appointment_time" required>
      </div>

      <button type="submit">Confirmer le rendez-vous</button>
    </form>
  </div>
</section>

<script src="js/booking.js"></script>
</body>
</html>
