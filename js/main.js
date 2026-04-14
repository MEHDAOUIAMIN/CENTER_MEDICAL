window.addEventListener('DOMContentLoaded', () => {
    fetch('api_doctors.php')
        .then(response => response.json())
        .then(data => {
            const grid = document.getElementById('doctor-grid');
            if(data.status === 'success') {
                data.data.forEach(doctor => {
                    const name = doctor.name.replace(/</g, "&lt;").replace(/>/g, "&gt;");
                    const spec = doctor.speciality.replace(/</g, "&lt;").replace(/>/g, "&gt;");
                    
                    const card = document.createElement('div');
                    card.className = 'card';
                    card.innerHTML = `
                        <img src="images/doctor.png" alt="Doctor ${name}">
                        <h3>${name}</h3>
                        <p>${spec}</p>
                        <button onclick="bookDoctor('${name}')">📅 Book</button>
                    `;
                    grid.appendChild(card);
                });
            } else {
                grid.innerHTML = '<p>Error loading doctors.</p>';
            }
        })
        .catch(err => {
            console.error(err);
            document.getElementById('doctor-grid').innerHTML = '<p>Network error.</p>';
        });
});

function bookDoctor(doctorName){
    localStorage.setItem('selectedDoctor', doctorName);
    window.location.href = 'booking.html';
}
