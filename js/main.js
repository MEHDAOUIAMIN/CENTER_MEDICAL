window.addEventListener('DOMContentLoaded', () => {
    fetch('api_doctors.php')
        .then(response => response.json())
        .then(data => {
            const grid = document.getElementById('doctor-grid');
            if (!grid) return;

            if (data.status === 'success' && Array.isArray(data.data) && data.data.length > 0) {
                data.data.forEach(doctor => {
                    const name = (doctor.name || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                    const spec = (doctor.speciality || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');

                    const card = document.createElement('div');
                    card.className = 'card';
                    card.innerHTML = `
                        <img src="images/doctor.png" alt="صورة ${name}">
                        <h3>${name}</h3>
                        <p>${spec || 'تخصص طبي'}</p>
                        <button onclick="bookDoctor('${name.replace(/'/g, "\\'")}')">احجز مع هذا الطبيب</button>
                    `;
                    grid.appendChild(card);
                });
            } else {
                grid.innerHTML = '<p style="text-align:center;color:#667786;">لا يوجد أطباء مضافون حاليًا.</p>';
            }
        })
        .catch(err => {
            console.error(err);
            const grid = document.getElementById('doctor-grid');
            if (grid) {
                grid.innerHTML = '<p style="text-align:center;color:#667786;">تعذر تحميل قائمة الأطباء الآن.</p>';
            }
        });
});

function bookDoctor(doctorName) {
    localStorage.setItem('selectedDoctor', doctorName);
    window.location.href = 'booking.php';
}
