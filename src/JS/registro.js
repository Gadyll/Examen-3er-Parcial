function mostrarParte(parteActual, parteSiguiente) {
    document.getElementById(parteActual).classList.remove('activo');
    document.getElementById(parteSiguiente).classList.add('activo');
}

function mostrarFormularioEspecifico(tipo) {
    var partes = document.querySelectorAll('.parte-formulario');
    partes.forEach(function(parte) {
        parte.classList.remove('activo');
    });
    
    if (tipo === 'paciente') {
        document.getElementById('formPaciente').reset();
        document.getElementById('parte2Paciente').classList.add('activo');
    } else if (tipo === 'doctor') {
        document.getElementById('formDoctor').reset();
        document.getElementById('parte2Doctor').classList.add('activo');
    }
}

function validarFormulario(formId) {
    var form = document.getElementById(formId);
    var inputs = form.querySelectorAll('input[required], select[required]');

    for (var i = 0; i < inputs.length; i++) {
        if (!inputs[i].value) {
            inputs[i].classList.add('error');
            return false;
        } else {
            inputs[i].classList.remove('error');
        }
    }
    return true;
}
