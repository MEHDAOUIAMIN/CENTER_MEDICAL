ALTER TABLE appointments
ADD COLUMN patient_id INT(10) UNSIGNED NULL AFTER id;

ALTER TABLE appointments
ADD INDEX idx_appointments_patient (patient_id);

ALTER TABLE appointments
ADD CONSTRAINT fk_appointments_patient
FOREIGN KEY (patient_id) REFERENCES patients(id)
ON DELETE SET NULL
ON UPDATE CASCADE;

UPDATE appointments a
INNER JOIN patients p ON p.email = a.email
SET a.patient_id = p.id
WHERE a.patient_id IS NULL;
