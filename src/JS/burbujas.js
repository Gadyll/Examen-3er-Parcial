const contenedor = document.getElementById('contenedor');

const crearBurbuja = () => {
    const burbuja = document.createElement('div');
    burbuja.classList.add('burbuja');

    // Tamaño aleatorio entre 20 y 100 píxeles
    const tamano = Math.random() * 400 + 400;
    burbuja.style.width = tamano + 'px';
    burbuja.style.height = tamano + 'px';

    // Color aleatorio
    const colores = [
        '0, 0, 255',    // azul
        '128, 0, 128',  // púrpura
        '255, 0, 0',    // rojo
        '255, 0, 255',  // magenta
        '255, 255, 0'   // amarillo
    ];

    // Elegir un color aleatorio de la lista
    const indiceColor = Math.floor(Math.random() * colores.length);
    const color = colores[indiceColor];
    
    // Agregar transparencia al color
    const transparencia = 0.9; // Valor entre 0 y 1 (0 = opaco, 1 = transparente)
    const colorConTransparencia = `rgba(${color}, ${transparencia})`; // Formato rgba
    
    burbuja.style.backgroundColor = colorConTransparencia;

    // Posición aleatoria dentro del contenedor
    const x = Math.random() * (contenedor.offsetWidth - tamano);
    const y = Math.random() * (contenedor.offsetHeight - tamano);
    burbuja.style.left = x + 'px';
    burbuja.style.top = y + 'px';

    // Propiedades adicionales
    burbuja.direccionX = Math.random() * 2 - 1;
    burbuja.direccionY = Math.random() * 2 - 1;
    burbuja.velocidad = Math.random() * 0.8 - 0.9;

    // Agregar la burbuja al contenedor
    contenedor.appendChild(burbuja);

    // Animación de movimiento
    animarBurbuja(burbuja);
}

const animarBurbuja = (burbuja) => {
    // Función para actualizar la posición de la burbuja
    const actualizarPosicion = () => {
        let x = parseFloat(burbuja.style.left);
        let y = parseFloat(burbuja.style.top);

        x += burbuja.direccionX * burbuja.velocidad;
        y += burbuja.direccionY * burbuja.velocidad;

        
        // Evitar que las burbujas se queden estancadas
        if (Math.abs(burbuja.direccionX) < 0.1 && Math.abs(burbuja.direccionY) < 0.1) {
            burbuja.direccionX = Math.random() * 2 - 1;
            burbuja.direccionY = Math.random() * 2 - 1;
        }

        // Actualizar la posición de la burbuja
        burbuja.style.left = x + 'px';
        burbuja.style.top = y + 'px';

        // Solicitar la siguiente actualización
        requestAnimationFrame(actualizarPosicion);
    };

    // Iniciar la animación
    requestAnimationFrame(actualizarPosicion);
}

// Crear una cantidad inicial de burbujas
for (let i = 0; i < 1; i++) {
    
    crearBurbuja();
}
setInterval(crearBurbuja, 10000);