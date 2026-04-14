function logout() {
    fetch('api_logout.php').then(() => window.location.href = 'login.html');
}

document.addEventListener('DOMContentLoaded', () => {
    // --- LOGIN ---
    const loginForm = document.getElementById('loginForm');
    if(loginForm) {
        loginForm.addEventListener('submit', function(e){
            e.preventDefault();
            const formData = new FormData(this);
            fetch('api_login.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') window.location.href = data.redirect;
                else document.getElementById('errorMsg').innerText = data.message;
            }).catch(() => document.getElementById('errorMsg').innerText = "Network Error");
        });
    }

    // --- ADMIN DASHBOARD ---
    if(document.getElementById('welcomeText') && document.title.includes('Admin')) {
        fetch('api_admin_dashboard.php').then(res => res.json()).then(data => {
            if(data.status === 'error'){ window.location.href = 'login.html'; return; }
            const d = data.data;
            document.getElementById('welcomeText').innerText = 'Dashboard (' + d.admin_name + ')';
            document.getElementById('total_doctors').innerText = d.total_doctors;
            document.getElementById('total_appointments').innerText = d.total_appointments;
            document.getElementById('today_appointments').innerText = d.today_appointments;
            document.getElementById('pending_appointments').innerText = d.pending_appointments;
            document.getElementById('completed_appointments').innerText = d.completed_appointments;
            document.getElementById('cancelled_appointments').innerText = d.cancelled_appointments;
            
            if(document.getElementById('statusChart')){
                new Chart(document.getElementById('statusChart'), { type: 'doughnut', data: { labels: ['Pending','Done','Cancelled'], datasets: [{ data: [d.pending_appointments, d.completed_appointments, d.cancelled_appointments], backgroundColor:['orange','green','red'] }] } });
                new Chart(document.getElementById('dailyChart'), { type: 'bar', data: { labels:['Today'], datasets:[{ label:'Appointments', data:[d.today_appointments], backgroundColor:'blue' }] } });
            }
        });
    }

    // --- ADMIN APPOINTMENTS ---
    const apptsBody = document.getElementById('appointmentsTableBody');
    if(apptsBody) {
        window.loadAppointments = function() {
            fetch('api_appointments.php').then(res => res.json()).then(data => {
                if(data.status === 'error'){ window.location.href = 'login.html'; return; }
                apptsBody.innerHTML = '';
                if(data.data.length === 0){ apptsBody.innerHTML = "<tr><td colspan='7'>No appointments found.</td></tr>"; return; }
                data.data.forEach(row => {
                    apptsBody.innerHTML += `<tr>
                        <td>${row.fullname}</td><td>${row.phone}</td><td>${row.doctor_name}</td>
                        <td>${row.appointment_date}</td><td>${row.appointment_time}</td><td class='${row.status}'>${row.status}</td>
                        <td>
                            <button onclick="updateStatus(${row.id}, 'accept')" style="background:green;color:white;border:none;padding:5px 10px;cursor:pointer;border-radius:4px;">Accept</button>
                            <button onclick="updateStatus(${row.id}, 'cancel')" style="background:red;color:white;border:none;padding:5px 10px;cursor:pointer;border-radius:4px;">Cancel</button>
                        </td>
                    </tr>`;
                });
            });
        };
        window.updateStatus = function(id, action) {
            fetch('api_appointments.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({id, action}) })
            .then(res => res.json()).then(data => { if(data.status === 'success') loadAppointments(); else alert(data.message); });
        };
        loadAppointments();
    }

    // --- DOCTORS LIST ---
    const doctorGrid = document.getElementById('doctorGrid');
    if(doctorGrid) {
        window.loadDoctors = function() {
            fetch('api_admin_doctors.php').then(res => res.json()).then(data => {
                if(data.status === 'error'){ window.location.href = 'login.html'; return; }
                doctorGrid.innerHTML = '';
                if(data.data.length === 0){ doctorGrid.innerHTML = "<p>No doctors found.</p>"; return; }
                data.data.forEach(doctor => {
                    doctorGrid.innerHTML += `<div class="doctor-card">
                         <h3>${doctor.name}</h3><p>${doctor.speciality}</p>
                         <p>Phone: ${doctor.phone}</p><p>Days: ${doctor.working_days}</p>
                         <a href="edit-doctor.html?id=${doctor.id}" class="edit">Edit</a>
                         <a href="#" class="delete" onclick="deleteDoctor(${doctor.id}); return false;">Delete</a>
                    </div>`;
                });
            });
        };
        window.deleteDoctor = function(id) {
            if(confirm('Are you sure?')){
                fetch('api_admin_doctors.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({action: 'delete', id: id}) })
                .then(res => res.json()).then(data => { if(data.status === 'success') loadDoctors(); else alert(data.message); });
            }
        };
        loadDoctors();
    }

    // --- ADD DOCTOR ---
    const addDoctorForm = document.getElementById('addDoctorForm');
    if(addDoctorForm) {
        addDoctorForm.addEventListener('submit', function(e){
            e.preventDefault();
            const data = { name: this.name.value, speciality: this.speciality.value, phone: this.phone.value, days: this.days.value };
            fetch('api_add_doctor.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(data) })
            .then(res => res.json()).then(data => {
                const msg = document.getElementById('msg');
                msg.style.display = 'block';
                if(data.status === 'success'){
                    msg.innerText = "Doctor Added Successfully";
                    msg.style.color = "green";
                    this.reset();
                } else {
                    msg.innerText = data.message || "Error adding";
                    msg.style.color = "red";
                }
            }).catch(() => alert('Network error'));
        });
    }

    // --- EDIT DOCTOR ---
    const editDoctorForm = document.getElementById('editDoctorForm');
    if(editDoctorForm) {
        const docId = new URLSearchParams(window.location.search).get('id');
        fetch('api_edit_doctor.php?id=' + docId).then(res => res.json()).then(data => {
            if(data.status === 'success'){
                document.getElementById('name').value = data.data.name;
                document.getElementById('speciality').value = data.data.speciality;
                document.getElementById('phone').value = data.data.phone;
                document.getElementById('days').value = data.data.working_days;
            } else {
                alert(data.message); window.location.href = 'doctors.html';
            }
        });
        editDoctorForm.addEventListener('submit', function(e){
            e.preventDefault();
            const data = {
                id: docId, name: document.getElementById('name').value,
                speciality: document.getElementById('speciality').value,
                phone: document.getElementById('phone').value,
                days: document.getElementById('days').value
            };
            fetch('api_edit_doctor.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(data) })
            .then(res => res.json()).then(data => {
                if(data.status === 'success') { alert('Updated'); window.location.href = 'doctors.html'; }
                else alert(data.message);
            });
        });
    }

    // --- DOCTOR DASHBOARD ---
    const docNameSidebar = document.getElementById('docNameSidebar');
    if(docNameSidebar && document.title.includes('Doctor Dashboard')) {
        fetch('api_doctor_dashboard.php').then(res => res.json()).then(data => {
            if(data.status === 'error') { window.location.href = 'login.html'; return; }
            const d = data.data;
            docNameSidebar.innerText = 'Dr. ' + d.doctor_name;
            document.getElementById('todayDate').innerText = new Date().toDateString();
            document.getElementById('total').innerText = d.total;
            document.getElementById('today').innerText = d.today;
            document.getElementById('pending').innerText = d.pending;
            document.getElementById('done').innerText = d.done;
            const tbody = document.getElementById('lastAppts');
            tbody.innerHTML = '';
            if(d.last_appointments.length === 0){ tbody.innerHTML = "<tr><td colspan='4'>No appointments</td></tr>"; return; }
            d.last_appointments.forEach(row => {
                tbody.innerHTML += `<tr><td>${row.fullname}</td><td>${row.appointment_date}</td><td>${row.appointment_time}</td><td class="${row.status}">${row.status}</td></tr>`;
            });
        });
    }

    // --- DOCTOR APPOINTMENTS ---
    const docApptsBody = document.getElementById('docApptsBody');
    if(docApptsBody) {
        window.loadDocAppointments = function() {
            fetch('api_doctor_appointments.php').then(res => res.json()).then(data => {
                if(data.status === 'error') { window.location.href = 'login.html'; return; }
                const d = data.data;
                document.getElementById('docNameSidebar').innerText = 'Dr. ' + d.doctor_name;
                document.getElementById('todayDate').innerText = d.today_date;
                document.getElementById('total_appointments').innerText = d.total_appointments;
                document.getElementById('today_appointments').innerText = d.today_appointments;
                document.getElementById('pending_appointments').innerText = d.pending_appointments;
                document.getElementById('completed_appointments').innerText = d.completed_appointments;
                document.getElementById('cancelled_appointments').innerText = d.cancelled_appointments;

                docApptsBody.innerHTML = '';
                if(d.appointments_list.length === 0){ docApptsBody.innerHTML = "<tr><td colspan='7'>No appointments</td></tr>"; return; }
                d.appointments_list.forEach(row => {
                    docApptsBody.innerHTML += `<tr>
                        <td>${row.fullname}</td><td>${row.email}</td><td>${row.phone}</td>
                        <td>${row.appointment_date}</td><td>${row.appointment_time}</td><td class='status ${row.status}'>${row.status}</td>
                        <td>
                            <button class="action-btn" style="background:#3b82f6;" onclick="window.location.href='add-consultation.html?appt_id=${row.id}'">Consulter</button>
                            <button class="action-btn" style="background:green;" onclick="updateDocStatus(${row.id}, 'accept')">Accept</button>
                            <button class="action-btn" style="background:red;" onclick="updateDocStatus(${row.id}, 'cancel')">Cancel</button>
                        </td>
                    </tr>`;
                });
            });
        };
        window.updateDocStatus = function(id, action){
            fetch('api_doctor_appointments.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({id, action}) })
            .then(res => res.json()).then(data => { if(data.status === 'success') loadDocAppointments(); else alert(data.message); });
        };
        loadDocAppointments();
    }

    // --- PATIENTS LIST ---
    const patientTableBody = document.getElementById('patientTableBody');
    if(patientTableBody) {
        window.loadPatients = function() {
            fetch('api_admin_patients.php').then(res => res.json()).then(data => {
                if(data.status === 'error'){ window.location.href = 'login.html'; return; }
                patientTableBody.innerHTML = '';
                if(data.data.length === 0){ patientTableBody.innerHTML = "<tr><td colspan='4'>Aucun patient trouvé.</td></tr>"; return; }
                data.data.forEach(patient => {
                    patientTableBody.innerHTML += `<tr>
                        <td>${patient.name}</td><td>${patient.email}</td><td>${patient.phone}</td>
                        <td class="action-col"><button onclick="deletePatient(${patient.id})" style="background:red;color:white;border:none;padding:5px;cursor:pointer;border-radius:4px;">Supprimer</button></td>
                    </tr>`;
                });
            });
        };
        window.deletePatient = function(id) {
            if(confirm('Êtes-vous sûr ?')){
                fetch('api_admin_patients.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({action: 'delete', id: id}) })
                .then(res => res.json()).then(data => { if(data.status === 'success') loadPatients(); else alert(data.message); });
            }
        };
        loadPatients();
    }

    const addPatientForm = document.getElementById('addPatientForm');
    if(addPatientForm) {
        addPatientForm.addEventListener('submit', function(e){
            e.preventDefault();
            const data = { action: 'add', name: this.name.value, email: this.email.value, phone: this.phone.value };
            fetch('api_admin_patients.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(data) })
            .then(res => res.json()).then(data => {
                const msg = document.getElementById('msg_patient');
                msg.style.display = 'block';
                if(data.status === 'success'){
                    msg.innerText = "Patient ajouté avec succès";
                    msg.style.color = "green";
                    this.reset();
                } else {
                    msg.innerText = data.message || "Erreur d'ajout";
                    msg.style.color = "red";
                }
            }).catch(() => alert('Network error'));
        });
    }

});
