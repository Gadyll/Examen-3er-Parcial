document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.tabs a');
    const sections = document.querySelectorAll('.info-section');

    tabs.forEach(tab => {
        tab.addEventListener('click', function(event) {
            event.preventDefault();

            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');

            sections.forEach(section => section.style.display = 'none');
            const targetSection = document.querySelector(tab.getAttribute('data-section'));
            targetSection.style.display = 'flex';
        });
    });

    // Mostrar la primera sección por defecto
    if (sections.length > 0) {
        sections.forEach(section => section.style.display = 'none');
        sections[0].style.display = 'flex';
        tabs[0].classList.add('active');
    }
});
