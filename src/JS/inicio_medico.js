function redirectToSpecialist(doctorId) {
    window.location.href = `especialistas.html?doctor=${doctorId}`;
}

function toggleSearch() {
    const searchBar = document.querySelector('.search-bar');
    searchBar.classList.toggle('active');
}

function buscarEspecialistas() {
    const input = document.getElementById('searchInput').value.toLowerCase();
    const doctorCards = document.querySelectorAll('.doctor-card');

    doctorCards.forEach(card => {
        const doctorName = card.querySelector('h4').textContent.toLowerCase();
        if (doctorName.includes(input)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}
