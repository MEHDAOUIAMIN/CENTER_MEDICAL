window.addEventListener('DOMContentLoaded', () => {
    fetch('api_doctors.php')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('doctorSelect');
            select.innerHTML = '<option value="">Select Doctor</option>';
            
            if(data.status === 'success') {
                data.data.forEach(doctor => {
                    const option = document.createElement('option');
                    option.value = doctor.id;
                    option.textContent = doctor.name + ' - ' + doctor.speciality;
                    select.appendChild(option);
                });

                const selectedDoctor = localStorage.getItem('selectedDoctor');
                if(selectedDoctor){
                    for (let i = 0; i < select.options.length; i++) {
                        if (select.options[i].textContent.includes(selectedDoctor)) {
                            select.selectedIndex = i;
                            break;
                        }
                    }
                    localStorage.removeItem('selectedDoctor');
                }
            } else {
                select.innerHTML = '<option value="">Error loading doctors</option>';
            }
        })
        .catch(error => {
            console.error('Error fetching doctors:', error);
            document.getElementById('doctorSelect').innerHTML = '<option value="">Error loading doctors</option>';
        });

    const form = document.getElementById('bookingForm');
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        const formData = new FormData(form);
        fetch('api_submit_booking.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                alert('Appointment booked successfully!');
                window.location.href = 'patient/mes_rendez_vous.php';
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(err => alert('Network error.'));
    });
});
