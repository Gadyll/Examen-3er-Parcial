document.addEventListener("DOMContentLoaded", function() {
    const urlParams = new URLSearchParams(window.location.search);
    const doctorId = urlParams.get('doctor');

    if (doctorId) {
        const doctorData = {
            'luis-fernando-espinosa-peraldi': {
                name: 'Luis Fernando Espinosa Peraldi',
                specialty: 'Ophthalmologist',
                address: 'Calle El Vergel 239, Querétaro',
                onlineConsultation: '$800',
                rating: '★★★★★',
                reviews: 49,
                image: 'imagenes/doctor1.jpg',
                prices: 'Consulta en línea: $800<br>Consulta presencial: $1000',
                experience: 'Más de 20 años de experiencia en oftalmología.',
                opinions: ['Excelente doctor, muy profesional.', 'Muy atento y amable.'],
                clinics: [
                    { address: 'Calle El Vergel 239, Querétaro', mapLink: 'https://goo.gl/maps/example1' },
                    { address: 'Calle Falsa 456, Querétaro', mapLink: 'https://goo.gl/maps/example2' }
                ]
            },
            'lic-ana-corina-ballesteros': {
                name: 'Lic. Ana Corina Ballesteros',
                specialty: 'Cardiologist',
                address: 'Calle El Vergel 239, Querétaro',
                onlineConsultation: '$800',
                rating: '★★★★★',
                reviews: 49,
                image: 'imagenes/doctor2.jpg',
                prices: 'Consulta en línea: $800<br>Consulta presencial: $1000',
                experience: 'Más de 15 años de experiencia en cardiología.',
                opinions: ['Excelente doctor, muy profesional.', 'Muy atento y amable.'],
                clinics: [
                    { address: 'Calle El Vergel 239, Querétaro', mapLink: 'https://goo.gl/maps/example1' },
                    { address: 'Calle Falsa 456, Querétaro', mapLink: 'https://goo.gl/maps/example2' }
                ]
            },
            'lic-valeria-slusar': {
                name: 'Lic. Valeria Slusar',
                specialty: 'Psicólogo',
                address: 'Campo Real 1606, Querétaro',
                onlineConsultation: '$600',
                rating: '★★★★',
                reviews: 40,
                image: 'imagenes/doctor3.jpg',
                prices: 'Consulta en línea: $600<br>Consulta presencial: $700',
                experience: 'Más de 10 años de experiencia en psicología.',
                opinions: ['Muy comprensiva y profesional.', 'Gran capacidad de escucha.'],
                clinics: [
                    { address: 'Campo Real 1606, Querétaro', mapLink: 'https://goo.gl/maps/example3' },
                ]
            },
            'dr-maritza-sandoval-rincon': {
                name: 'Maritza Sandoval Rincón',
                specialty: 'Psiquiatra',
                address: 'Ciruelos 137, Jurica Pinar. Interior 109, Querétaro',
                onlineConsultation: '$1000',
                rating: '★★★★☆',
                reviews: 35,
                image: 'imagenes/doctor4.jpg',
                prices: 'Consulta en línea: $1000<br>Consulta presencial: $1200',
                experience: 'Más de 15 años de experiencia en psiquiatría.',
                opinions: ['Muy profesional y atenta.', 'Excelente trato.'],
                clinics: [
                    { address: 'Ciruelos 137, Jurica Pinar. Interior 109, Querétaro', mapLink: 'https://goo.gl/maps/example4' },
                ]
            },
            'mtra-mariela-eula': {
                name: 'Mtra. Mariela Eula',
                specialty: 'Terapeuta complementario',
                address: 'Licenciado Zacarías Oñate 20, Querétaro',
                onlineConsultation: '$700',
                rating: '★★★☆',
                reviews: 30,
                image: 'imagenes/doctor5.jpg',
                prices: 'Consulta en línea: $700<br>Consulta presencial: $800',
                experience: 'Más de 5 años de experiencia en terapia complementaria.',
                opinions: ['Muy buena atención.', 'Recomendada.'],
                clinics: [
                    { address: 'Licenciado Zacarías Oñate 20, Querétaro', mapLink: 'https://goo.gl/maps/example5' },
                ]
            },
        };

        const doctor = doctorData[doctorId];

        if (doctor) {
            document.getElementById('doctor-image').src = doctor.image;
            document.getElementById('doctor-name').textContent = doctor.name;
            document.getElementById('doctor-specialty').textContent = doctor.specialty;
            document.getElementById('doctor-address').textContent = doctor.address;
            document.getElementById('doctor-online-consultation').textContent = doctor.onlineConsultation;
            document.getElementById('doctor-reviews').innerHTML = `${doctor.rating} ${doctor.reviews} opiniones`;
            document.getElementById('doctor-prices').innerHTML = doctor.prices;
            document.getElementById('doctor-experience').textContent = doctor.experience;
            const opinionsContainer = document.getElementById('doctor-opinions');
            opinionsContainer.innerHTML = '';
            doctor.opinions.forEach(opinion => {
                const p = document.createElement('p');
                p.textContent = opinion;
                opinionsContainer.appendChild(p);
            });
            const clinicsContainer = document.getElementById('clinic-list');
            clinicsContainer.innerHTML = '';
            doctor.clinics.forEach(clinic => {
                const clinicDiv = document.createElement('div');
                clinicDiv.classList.add('consultorio');
                clinicDiv.innerHTML = `<p><strong>Dirección:</strong> ${clinic.address}</p><p><a href="${clinic.mapLink}" target="_blank">Ver en Google Maps</a></p>`;
                clinicsContainer.appendChild(clinicDiv);
            });
        }
    }

    const tabButtons = document.querySelectorAll(".tab-button");
    const tabContents = document.querySelectorAll(".tab-content");

    tabButtons.forEach(button => {
        button.addEventListener("click", () => {
            tabButtons.forEach(btn => btn.classList.remove("active"));
            tabContents.forEach(content => content.classList.remove("active"));

            button.classList.add("active");
            document.getElementById(button.textContent.toLowerCase()).classList.add("active");
        });
    });

    const appointmentTabs = document.querySelectorAll('.appointment-tab');
    appointmentTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            appointmentTabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
        });
    });

    document.querySelector('.show-more-hours').addEventListener('click', () => {
        // Código para mostrar más horas
    });
});
