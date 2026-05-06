<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/helpers.php';

startAppSession();

if (!isset($_SESSION['patient_id'])) {
    redirectTo('login.php');
}

$connection = Database::getConnection();
$patientId = (int) $_SESSION['patient_id'];

$patientStatement = $connection->prepare('SELECT user_code, name, email FROM patients WHERE id = ? LIMIT 1');
$patientStatement->bind_param('i', $patientId);
$patientStatement->execute();
$patientResult = $patientStatement->get_result();
$patient = $patientResult ? $patientResult->fetch_assoc() : null;

if (!$patient) {
    session_destroy();
    redirectTo('login.php');
}

$hasPatientIdColumn = tableHasColumn($connection, 'appointments', 'patient_id');

if ($hasPatientIdColumn) {
    $statement = $connection->prepare(
        'SELECT a.appointment_date, a.appointment_time, a.status, d.name AS doctor_name, c.notes
         FROM appointments a
         INNER JOIN doctors d ON d.id = a.doctor_id
         LEFT JOIN consultations c ON c.rendez_vous_id = a.id
         WHERE a.patient_id = ?
         ORDER BY a.appointment_date DESC, a.appointment_time DESC'
    );
    $statement->bind_param('i', $patientId);
} else {
    $statement = $connection->prepare(
        'SELECT a.appointment_date, a.appointment_time, a.status, d.name AS doctor_name, c.notes
         FROM appointments a
         INNER JOIN doctors d ON d.id = a.doctor_id
         LEFT JOIN consultations c ON c.rendez_vous_id = a.id
         WHERE a.email = ?
         ORDER BY a.appointment_date DESC, a.appointment_time DESC'
    );
    $statement->bind_param('s', $patient['email']);
}

if (!$statement) {
    $appointments = [];
} else {
    $statement->execute();
    $result = $statement->get_result();

    $appointments = [];

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $appointments[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mes rendez-vous</title>
<link rel="stylesheet" href="../admin/style2.css">
</head>
<body>
<div class="layout">
   <aside class="sidebar">
      <h2><?php echo htmlspecialchars($patient['name'], ENT_QUOTES, 'UTF-8'); ?></h2>
      <a class="active" href="mes_rendez_vous.php">Mes rendez-vous</a>
      <a href="change-password.html">Changer mot de passe</a>
      <a href="#" onclick="logoutPatient(); return false;">Deconnexion</a>
   </aside>
   <main class="main">
      <div class="topbar">
         <h1>Mes rendez-vous</h1>
      </div>
      <div class="table-box">
         <h2>ID patient : <?php echo htmlspecialchars($patient['user_code'], ENT_QUOTES, 'UTF-8'); ?></h2>
         <table>
            <thead>
               <tr>
                  <th>Docteur</th>
                  <th>Date</th>
                  <th>Heure</th>
                  <th>Statut</th>
                  <th>Consultation</th>
               </tr>
            </thead>
            <tbody>
               <?php if (count($appointments) === 0): ?>
                  <tr>
                     <td colspan="5">Aucun rendez-vous trouve</td>
                  </tr>
               <?php else: ?>
                  <?php foreach ($appointments as $appointment): ?>
                     <tr>
                        <td><?php echo htmlspecialchars($appointment['doctor_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($appointment['appointment_date'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($appointment['appointment_time'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($appointment['status'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($appointment['notes'] ?: 'Aucune consultation', ENT_QUOTES, 'UTF-8'); ?></td>
                     </tr>
                  <?php endforeach; ?>
               <?php endif; ?>
            </tbody>
         </table>
      </div>
   </main>
</div>
<script src="js/app.js"></script>
</body>
</html>
