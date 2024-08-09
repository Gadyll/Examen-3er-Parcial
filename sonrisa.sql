CREATE DATABASE IF NOT EXISTS sonrisas;
USE sonrisas;

CREATE TABLE auditoria (
    id_auditoria INT AUTO_INCREMENT PRIMARY KEY,
    accion VARCHAR(100),
    fecha DATE,
    hora TIME,
    detalles VARCHAR(100),
    id_usuario INT
);

CREATE TABLE cita (
    id_cita INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATE,
    hora TIME,
    id_diagnostico INT,
    id_paciente INT,
    id_medico INT
);

CREATE TABLE ciudad (
    id_ciudad INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100)
);

CREATE TABLE diagnostico (
    id_diagnostico INT AUTO_INCREMENT PRIMARY KEY,
    descripcion VARCHAR(1000),
    observaciones VARCHAR(500)
);

CREATE TABLE documentos_adjuntos (
    id_documento INT AUTO_INCREMENT PRIMARY KEY,
    nombre_documento VARCHAR(500),
    url_documento VARCHAR(1000),
    fecha_subida DATE,
    id_usuario INT
);

CREATE TABLE especialidades (
    id_especialidad INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100)
);

CREATE TABLE estado (
    id_estado INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100)
);

CREATE TABLE generos (
    id_genero INT AUTO_INCREMENT PRIMARY KEY,
    genero VARCHAR(50)
);

CREATE TABLE historial_medico (
    id_historial_medico INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATE,
    observacion VARCHAR(500),
    id_diagnostico INT,
    id_paciente INT
);

CREATE TABLE medico (
    id_medico INT AUTO_INCREMENT PRIMARY KEY,
    horarios TIME,
    id_usuario INT,
    id_especialidad INT
);

CREATE TABLE notificaciones (
    id_notificacion INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(100),
    mensaje VARCHAR(500),
    fecha DATE,
    hora TIME,
    estdo VARCHAR(100),
    id_medico INT,
    id_paciente INT
);

CREATE TABLE pacientes (
    id_paciente INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT
);

CREATE TABLE permisos (
    id_permiso INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100)
);

CREATE TABLE roles (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100)
);

CREATE TABLE roles_permisos (
    id_rol_permiso INT AUTO_INCREMENT PRIMARY KEY,
    id_rol INT,
    id_permiso INT
);

CREATE TABLE tipo_usuarios (
    id_tipo_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100)
);

CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100),
    apellido VARCHAR(100),
    correo VARCHAR(100),
    contraseña VARCHAR(100),
    telefono VARCHAR(100),
    id_tipo_usuario INT,
    id_rol INT,
    id_estado INT,
    id_ciudad INT,
    id_genero INT
);

INSERT INTO auditoria (accion, fecha, hora, detalles, id_usuario) VALUES
('Creación de usuario', '2024-08-01', '10:00:00', 'Se creó un nuevo usuario.', 1),
('Actualización de paciente', '2024-08-01', '11:30:00', 'Se actualizaron los datos del paciente.', 2),
('Eliminación de diagnóstico', '2024-08-02', '12:00:00', 'Se eliminó un diagnóstico.', 3),
('Creación de cita', '2024-08-02', '13:00:00', 'Se creó una nueva cita.', 4),
('Subida de documento', '2024-08-03', '14:00:00', 'Se subió un nuevo documento adjunto.', 5);

INSERT INTO cita (fecha, hora, id_diagnostico, id_paciente, id_medico) VALUES
('2024-08-10', '09:00:00', 1, 1, 1),
('2024-08-11', '10:00:00', 2, 2, 2),
('2024-08-12', '11:00:00', 3, 3, 3),
('2024-08-13', '14:00:00', 4, 4, 4),
('2024-08-14', '15:00:00', 5, 5, 5);

INSERT INTO ciudad (nombre) VALUES
('Madrid'),
('Barcelona'),
('Valencia'),
('Sevilla'),
('Bilbao');

INSERT INTO diagnostico (descripcion, observaciones) VALUES
('Dolor abdominal', 'Paciente presenta dolor en la zona abdominal.'),
('Fiebre alta', 'Temperatura corporal superior a 38°C.'),
('Cefalea intensa', 'Dolor de cabeza persistente.'),
('Tos seca', 'Paciente presenta tos seca y constante.'),
('Dolor muscular', 'Dolor en músculos y articulaciones.');

INSERT INTO documentos_adjuntos (nombre_documento, url_documento, fecha_subida, id_usuario) VALUES
('Informe médico.pdf', 'http://example.com/docs/informe1.pdf', '2024-08-01', 1),
('Radiografía.png', 'http://example.com/docs/radiografia.png', '2024-08-02', 2),
('Receta.pdf', 'http://example.com/docs/receta.pdf', '2024-08-03', 3),
('Informe laboratorio.xlsx', 'http://example.com/docs/informe_lab.xlsx', '2024-08-04', 4),
('Certificado de salud.docx', 'http://example.com/docs/certificado.docx', '2024-08-05', 5);

INSERT INTO especialidades (nombre) VALUES
('Cardiología'),
('Dermatología'),
('Neurología'),
('Pediatría'),
('Gastroenterología');

INSERT INTO estado (nombre) VALUES
('Activo'),
('Inactivo'),
('Pendiente'),
('Suspendido'),
('Baja');

INSERT INTO generos (genero) VALUES
('Masculino'),
('Femenino'),
('No binario'),
('Prefiero no decirlo'),
('Otro');

INSERT INTO historial_medico (fecha, observacion, id_diagnostico, id_paciente) VALUES
('2024-07-01', 'Paciente con dolor abdominal.', 1, 1),
('2024-07-02', 'Paciente con fiebre alta.', 2, 2),
('2024-07-03', 'Paciente con cefalea intensa.', 3, 3),
('2024-07-04', 'Paciente con tos seca.', 4, 4),
('2024-07-05', 'Paciente con dolor muscular.', 5, 5);

INSERT INTO medico (horarios, id_usuario, id_especialidad) VALUES
('08:00:00', 1, 1),
('09:00:00', 2, 2),
('10:00:00', 3, 3),
('11:00:00', 4, 4),
('12:00:00', 5, 5);

INSERT INTO notificaciones (titulo, mensaje, fecha, hora, estdo, id_medico, id_paciente) VALUES
('Recordatorio de cita', 'Recuerda tu cita médica para mañana.', '2024-08-01', '08:00:00', 'Enviado', 1, 1),
('Nuevo diagnóstico', 'Se ha añadido un nuevo diagnóstico a tu historial.', '2024-08-02', '09:00:00', 'Enviado', 2, 2),
('Documentación requerida', 'Por favor sube los documentos solicitados.', '2024-08-03', '10:00:00', 'Pendiente', 3, 3),
('Cambio de horario', 'Tu cita ha sido reprogramada.', '2024-08-04', '11:00:00', 'Enviado', 4, 4),
('Aviso de consulta', 'Consulta médica pendiente de confirmación.', '2024-08-05', '12:00:00', 'Enviado', 5, 5);

INSERT INTO pacientes (id_usuario) VALUES
(1),
(2),
(3),
(4),
(5);

INSERT INTO permisos (nombre) VALUES
('Leer'),
('Escribir'),
('Actualizar'),
('Eliminar'),
('Administrar');

INSERT INTO roles (nombre) VALUES
('Administrador'),
('Doctor'),
('Enfermero'),
('Recepcionista'),
('Paciente');

INSERT INTO roles_permisos (id_rol, id_permiso) VALUES
(1, 1),
(1, 2),
(2, 1),
(2, 3),
(3, 1),
(3, 4),
(4, 1),
(4, 2),
(5, 1);

INSERT INTO tipo_usuarios (nombre) VALUES
('Paciente'),
('Doctor'),
('Enfermero'),
('Administrativo'),
('Administrador');

INSERT INTO usuarios (nombre, apellido, correo, contraseña, telefono, id_tipo_usuario, id_rol, id_estado, id_ciudad, id_genero) VALUES
('Juan', 'Pérez', 'juan.perez@example.com', 'contraseña123', '555-1234', 1, 1, 1, 1, 1),
('Ana', 'García', 'ana.garcia@example.com', 'contraseña456', '555-5678', 2, 2, 2, 2, 2),
('Luis', 'Martínez', 'luis.martinez@example.com', 'contraseña789', '555-8765', 3, 3, 3, 3, 3),
('Laura', 'Hernández', 'laura.hernandez@example.com', 'contraseña000', '555-4321', 4, 4, 4, 4, 4),
('Pedro', 'Gómez', 'pedro.gomez@example.com', 'contraseña111', '555-6789', 5, 5, 5, 5, 5);
