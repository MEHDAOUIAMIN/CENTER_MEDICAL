function logoutPatient() {
    fetch('api_logout.php').then(() => window.location.href = 'login.html');
}

document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('patientLoginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('api_login.php', { method: 'POST', body: formData })
                .then(r => r.json()).then(data => {
                    if (data.status === 'success') window.location.href = data.redirect;
                    else document.getElementById('errorMsg').innerText = data.message;
                }).catch(() => document.getElementById('errorMsg').innerText = 'Network Error');
        });
    }

    const registerForm = document.getElementById('patientRegisterForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('api_register.php', { method: 'POST', body: formData })
                .then(r => r.json()).then(data => {
                    const msg = document.getElementById('registerMsg');
                    msg.innerText = data.status === 'success'
                        ? 'Compte cree avec succes.'
                        : data.message;
                    msg.style.color = data.status === 'success' ? 'green' : 'red';
                    if (data.status === 'success') {
                        const resultBox = document.getElementById('registerResult');
                        const idBox = document.getElementById('registeredPatientId');
                        if (resultBox && idBox) {
                            resultBox.style.display = 'block';
                            idBox.innerText = data.data.user_code;
                        }
                        this.reset();
                    }
                }).catch(() => {
                    const msg = document.getElementById('registerMsg');
                    msg.innerText = 'Network Error';
                    msg.style.color = 'red';
                });
        });
    }

    const changePasswordForm = document.getElementById('patientChangePasswordForm');
    if (changePasswordForm) {
        changePasswordForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const payload = {
                current_password: this.current_password.value,
                new_password: this.new_password.value,
                confirm_password: this.confirm_password.value
            };

            fetch('api_change_password.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            }).then(r => r.json()).then(data => {
                const msg = document.getElementById('changePasswordMsg');
                msg.innerText = data.message || '';
                msg.style.color = data.status === 'success' ? 'green' : 'red';
                if (data.status === 'success') this.reset();
            }).catch(() => {
                const msg = document.getElementById('changePasswordMsg');
                msg.innerText = 'Network Error';
                msg.style.color = 'red';
            });
        });
    }

    const tbody = document.getElementById('patientApptsBody');
    if (tbody) {
        fetch('api_dashboard.php').then(r => r.json()).then(data => {
            if (data.status === 'error') { window.location.href = 'login.html'; return; }
            const patientTitle = document.getElementById('patientNameSidebar');
            const patientCode = data.data.patient_code ? ' (' + data.data.patient_code + ')' : '';
            patientTitle.innerText = data.data.patient_name + patientCode;
            tbody.innerHTML = '';
            if (data.data.appointments.length === 0) {
                tbody.innerHTML = "<tr><td colspan='5'>Aucun rendez-vous</td></tr>";
                return;
            }

            data.data.appointments.forEach(a => {
                tbody.innerHTML += `<tr>
                    <td>${data.data.patient_code || '-'}</td>
                    <td>Dr. ${a.doctor_name}</td>
                    <td>${a.appointment_date} ${a.appointment_time}</td>
                    <td class="${a.status}">${a.status}</td>
                    <td>${a.notes ? a.notes : 'Non consulte'}</td>
                </tr>`;
            });
        });
    }
});
