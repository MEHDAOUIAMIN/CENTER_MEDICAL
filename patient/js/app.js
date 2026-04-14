function logoutPatient() {
    fetch('api_logout.php').then(() => window.location.href = 'login.html');
}

document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('patientLoginForm');
    if(loginForm) {
        loginForm.addEventListener('submit', function(e){
            e.preventDefault();
            const formData = new FormData(this);
            fetch('api_login.php', { method: 'POST', body: formData })
            .then(r => r.json()).then(data => {
                if(data.status === 'success') window.location.href = data.redirect;
                else document.getElementById('errorMsg').innerText = data.message;
            }).catch(() => document.getElementById('errorMsg').innerText = "Network Error");
        });
    }

    const tbody = document.getElementById('patientApptsBody');
    if(tbody) {
        fetch('api_dashboard.php').then(r => r.json()).then(data => {
            if(data.status === 'error') { window.location.href = 'login.html'; return; }
            document.getElementById('patientNameSidebar').innerText = data.data.patient_name;
            tbody.innerHTML = '';
            if(data.data.appointments.length === 0){ tbody.innerHTML = "<tr><td colspan='4'>Aucun rendez-vous</td></tr>"; return; }
            data.data.appointments.forEach(a => {
                tbody.innerHTML += `<tr>
                    <td>Dr. ${a.doctor_name}</td>
                    <td>${a.appointment_date} ${a.appointment_time}</td>
                    <td class="${a.status}">${a.status}</td>
                    <td>${a.notes ? a.notes : 'Non consulté'}</td>
                </tr>`;
            });
        });
    }
});
