function toggleSearch() {
    var searchBar = document.querySelector('.search-bar');
    searchBar.classList.toggle('active');
}

function buscarEspecialistas() {
    var input = document.getElementById('searchInput').value.toLowerCase();
    var doctorCards = document.querySelectorAll('.doctor-card');

    doctorCards.forEach(function(card) {
        var doctorName = card.querySelector('.doctor-name').textContent.toLowerCase();
        if (doctorName.includes(input)) {
            card.style.display = "";
        } else {
            card.style.display = "none";
        }
    });
}
