START TRANSACTION;

SET @hash_demo = '$2y$12$Iks1y/l.fhq/e6QdzMTEhu5QvEilwKc1wsxHGCJAx8RlGWoGvXqJe';
SET @institucion = 1;
SET @nacionalidad = 1;
SET @etnia = 1;
SET @ciudad = 1;
SET @tipo_estudiante = 2;
SET @tipo_entrenador = 3;
SET @operadora = 1;
SET @tipo_contacto = 1;
SET @modalidad = 1;
SET @forma_efectivo = 1;
SET @periodo = 1;

INSERT INTO type_users (nemonico, nombre, descripcion, imagen)
SELECT 'ESTUDIANTE', 'Estudiante', 'Usuario estudiante', ''
WHERE NOT EXISTS (SELECT 1 FROM type_users WHERE nemonico = 'ESTUDIANTE');
INSERT INTO type_users (nemonico, nombre, descripcion, imagen)
SELECT 'ENTRENADOR', 'Entrenador', 'Usuario entrenador o profesor', ''
WHERE NOT EXISTS (SELECT 1 FROM type_users WHERE nemonico = 'ENTRENADOR');
INSERT INTO type_users (nemonico, nombre, descripcion, imagen)
SELECT 'ADMIN', 'Administrador', 'Usuario administrativo', ''
WHERE NOT EXISTS (SELECT 1 FROM type_users WHERE nemonico = 'ADMIN');
INSERT INTO type_users (nemonico, nombre, descripcion, imagen)
SELECT 'MEDICO', 'Personal Medico', 'Usuario de personal medico', ''
WHERE NOT EXISTS (SELECT 1 FROM type_users WHERE nemonico = 'MEDICO');

INSERT INTO users (username, password, type, image)
SELECT 'aalvarado', @hash_demo, 'ESTUDIANTE', NULL
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'aalvarado');
SET @user_ana = (SELECT id FROM users WHERE username = 'aalvarado' LIMIT 1);

INSERT INTO users (username, password, type, image)
SELECT 'jmorales', @hash_demo, 'ESTUDIANTE', NULL
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'jmorales');
SET @user_juan = (SELECT id FROM users WHERE username = 'jmorales' LIMIT 1);

INSERT INTO users (username, password, type, image)
SELECT 'projas', @hash_demo, 'ENTRENADOR', NULL
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'projas');
SET @user_pedro = (SELECT id FROM users WHERE username = 'projas' LIMIT 1);

INSERT INTO personas (identificacion, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, nacionalidad, genero, etnia, fecha_nacimiento, ciudad, direccion, user)
SELECT '0700001001', 'Ana', 'Paula', 'Alvarado', 'Lopez', @nacionalidad, 2, @etnia, '2012-04-15 00:00:00', @ciudad, 'Barrio El Bosque, Huaquillas', @user_ana
WHERE NOT EXISTS (SELECT 1 FROM personas WHERE identificacion = '0700001001');
SET @persona_ana = (SELECT id FROM personas WHERE identificacion = '0700001001' LIMIT 1);

INSERT INTO personas (identificacion, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, nacionalidad, genero, etnia, fecha_nacimiento, ciudad, direccion, user)
SELECT '0700001002', 'Juan', 'Diego', 'Morales', 'Perez', @nacionalidad, 1, @etnia, '2010-09-23 00:00:00', @ciudad, 'Cdla. Las Mercedes, Huaquillas', @user_juan
WHERE NOT EXISTS (SELECT 1 FROM personas WHERE identificacion = '0700001002');
SET @persona_juan = (SELECT id FROM personas WHERE identificacion = '0700001002' LIMIT 1);

INSERT INTO personas (identificacion, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, nacionalidad, genero, etnia, fecha_nacimiento, ciudad, direccion, user)
SELECT '0700002001', 'Pedro', 'Andres', 'Rojas', 'Vera', @nacionalidad, 1, @etnia, '1988-01-18 00:00:00', @ciudad, 'Av. Republica y Loja', @user_pedro
WHERE NOT EXISTS (SELECT 1 FROM personas WHERE identificacion = '0700002001');
SET @persona_pedro = (SELECT id FROM personas WHERE identificacion = '0700002001' LIMIT 1);

INSERT INTO persona_institucion (id_persona, id_institucion, tipo, estado)
SELECT @persona_ana, @institucion, @tipo_estudiante, 1
WHERE NOT EXISTS (SELECT 1 FROM persona_institucion WHERE id_persona = @persona_ana AND id_institucion = @institucion);
INSERT INTO persona_institucion (id_persona, id_institucion, tipo, estado)
SELECT @persona_juan, @institucion, @tipo_estudiante, 1
WHERE NOT EXISTS (SELECT 1 FROM persona_institucion WHERE id_persona = @persona_juan AND id_institucion = @institucion);
INSERT INTO persona_institucion (id_persona, id_institucion, tipo, estado)
SELECT @persona_pedro, @institucion, @tipo_entrenador, 1
WHERE NOT EXISTS (SELECT 1 FROM persona_institucion WHERE id_persona = @persona_pedro AND id_institucion = @institucion);

INSERT INTO telefonos (numero, operadora, persona, tipo, estado)
SELECT '0981001001', @operadora, @persona_ana, @tipo_contacto, 1
WHERE NOT EXISTS (SELECT 1 FROM telefonos WHERE numero = '0981001001' AND persona = @persona_ana);
INSERT INTO telefonos (numero, operadora, persona, tipo, estado)
SELECT '0981001002', @operadora, @persona_juan, @tipo_contacto, 1
WHERE NOT EXISTS (SELECT 1 FROM telefonos WHERE numero = '0981001002' AND persona = @persona_juan);

INSERT INTO correos (email, tipo, persona, estado)
SELECT 'ana.alvarado.demo@huaquillasfc.local', @tipo_contacto, @persona_ana, 1
WHERE NOT EXISTS (SELECT 1 FROM correos WHERE email = 'ana.alvarado.demo@huaquillasfc.local');
INSERT INTO correos (email, tipo, persona, estado)
SELECT 'juan.morales.demo@huaquillasfc.local', @tipo_contacto, @persona_juan, 1
WHERE NOT EXISTS (SELECT 1 FROM correos WHERE email = 'juan.morales.demo@huaquillasfc.local');

INSERT INTO fichas_medicas (persona, grupo_sanguineo, alergias, enfermedades_preexistentes, medicamentos_actuales, contacto_emergencia_nombre, contacto_emergencia_telefono, estado)
SELECT @persona_ana, 'O+', 'Ninguna reportada', 'Ninguna', 'Ninguno', 'Maria Lopez', '0987001001', 1
WHERE NOT EXISTS (SELECT 1 FROM fichas_medicas WHERE persona = @persona_ana);
INSERT INTO fichas_medicas (persona, grupo_sanguineo, alergias, enfermedades_preexistentes, medicamentos_actuales, contacto_emergencia_nombre, contacto_emergencia_telefono, estado)
SELECT @persona_juan, 'A+', 'Polvo', 'Asma leve', 'Salbutamol SOS', 'Carlos Morales', '0987001002', 1
WHERE NOT EXISTS (SELECT 1 FROM fichas_medicas WHERE persona = @persona_juan);

INSERT INTO tallas (persona, tipo_prenda, talla, medida_cm, fecha_medicion, estado)
SELECT @persona_ana, 'Camiseta', 'S', 145.00, CURDATE(), 1
WHERE NOT EXISTS (SELECT 1 FROM tallas WHERE persona = @persona_ana AND tipo_prenda = 'Camiseta');
INSERT INTO tallas (persona, tipo_prenda, talla, medida_cm, fecha_medicion, estado)
SELECT @persona_juan, 'Camiseta', 'M', 158.00, CURDATE(), 1
WHERE NOT EXISTS (SELECT 1 FROM tallas WHERE persona = @persona_juan AND tipo_prenda = 'Camiseta');

INSERT INTO clases (nombre, periodo_academico, institucion, docente, cupo_maximo, estado)
SELECT 'Tactica Sub-15 Demo', @periodo, @institucion, @persona_pedro, 20, 1
WHERE NOT EXISTS (SELECT 1 FROM clases WHERE nombre = 'Tactica Sub-15 Demo' AND institucion = @institucion);
SET @clase_tactica = (SELECT id FROM clases WHERE nombre = 'Tactica Sub-15 Demo' AND institucion = @institucion LIMIT 1);

INSERT INTO horarios (nombre, institucion, clase, actividad, dia_semana, hora_inicio, hora_fin, fecha_inicio, fecha_fin, cupo_maximo, ubicacion, estado)
SELECT 'Tactica Sub-15 Demo Lun', @institucion, @clase_tactica, NULL, 1, '16:00:00', '17:30:00', '2025-01-06', '2025-12-31', 20, 'Cancha Principal', 1
WHERE NOT EXISTS (SELECT 1 FROM horarios WHERE nombre = 'Tactica Sub-15 Demo Lun' AND institucion = @institucion);
SET @horario_tactica = (SELECT id FROM horarios WHERE nombre = 'Tactica Sub-15 Demo Lun' AND institucion = @institucion LIMIT 1);

SET @actividad_futbol = (SELECT id FROM actividades WHERE nombre LIKE 'Fútbol Infantil%' AND institucion = @institucion LIMIT 1);
SET @membresia_mensual = (SELECT id FROM membresias ORDER BY id ASC LIMIT 1);

INSERT INTO matricula (persona, fecha_inicio, fecha_fin, membresia, institucion, estado)
SELECT @persona_ana, '2025-05-01', '2025-05-31', @membresia_mensual, @institucion, 1
WHERE NOT EXISTS (SELECT 1 FROM matricula WHERE persona = @persona_ana AND institucion = @institucion AND fecha_inicio = '2025-05-01');
SET @matricula_ana = (SELECT id FROM matricula WHERE persona = @persona_ana AND institucion = @institucion AND fecha_inicio = '2025-05-01' LIMIT 1);

INSERT INTO matricula (persona, fecha_inicio, fecha_fin, membresia, institucion, estado)
SELECT @persona_juan, '2025-05-01', '2025-05-31', @membresia_mensual, @institucion, 1
WHERE NOT EXISTS (SELECT 1 FROM matricula WHERE persona = @persona_juan AND institucion = @institucion AND fecha_inicio = '2025-05-01');
SET @matricula_juan = (SELECT id FROM matricula WHERE persona = @persona_juan AND institucion = @institucion AND fecha_inicio = '2025-05-01' LIMIT 1);

INSERT INTO clases_matricula (clase, matricula, modalidad, horario, fecha_inicio, fecha_fin, estado)
SELECT @clase_tactica, @matricula_ana, @modalidad, @horario_tactica, '2025-05-01', '2025-05-31', 1
WHERE NOT EXISTS (SELECT 1 FROM clases_matricula WHERE clase = @clase_tactica AND matricula = @matricula_ana);
INSERT INTO actividades_matricula (actividad, matricula, modalidad, horario, fecha_inicio, fecha_fin, estado)
SELECT @actividad_futbol, @matricula_juan, @modalidad, NULL, '2025-05-01', '2025-05-31', 1
WHERE @actividad_futbol IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM actividades_matricula WHERE actividad = @actividad_futbol AND matricula = @matricula_juan);

INSERT INTO asistencias (fecha, matricula, clase, actividad, horario, hora_entrada, hora_salida, observacion, estado)
SELECT '2025-05-06', @matricula_ana, @clase_tactica, NULL, @horario_tactica, '16:00:00', '17:30:00', 'Asistencia demo', 1
WHERE NOT EXISTS (SELECT 1 FROM asistencias WHERE fecha = '2025-05-06' AND matricula = @matricula_ana AND clase = @clase_tactica);
INSERT INTO asistencias (fecha, matricula, clase, actividad, horario, observacion, estado)
SELECT '2025-05-07', @matricula_juan, NULL, @actividad_futbol, NULL, 'Permiso medico demo', 3
WHERE @actividad_futbol IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM asistencias WHERE fecha = '2025-05-07' AND matricula = @matricula_juan AND actividad = @actividad_futbol);

INSERT INTO ventas (numero, fecha, persona, matricula, institucion, tipo, subtotal, descuento, impuesto, total, saldo, estado)
SELECT 'DEMO-CXC-001', NOW(), @persona_ana, @matricula_ana, @institucion, 'MATRICULA', 45.00, 0.00, 0.00, 45.00, 45.00, 1
WHERE NOT EXISTS (SELECT 1 FROM ventas WHERE numero = 'DEMO-CXC-001');
SET @venta_cxc = (SELECT id FROM ventas WHERE numero = 'DEMO-CXC-001' LIMIT 1);

INSERT INTO venta_detalle (venta, descripcion, cantidad, precio_unitario, descuento, total)
SELECT @venta_cxc, 'Membresia mensual demo pendiente', 1, 45.00, 0.00, 45.00
WHERE NOT EXISTS (SELECT 1 FROM venta_detalle WHERE venta = @venta_cxc AND descripcion = 'Membresia mensual demo pendiente');

INSERT INTO ventas (numero, fecha, persona, matricula, institucion, tipo, subtotal, descuento, impuesto, total, saldo, estado)
SELECT 'DEMO-PAGADA-001', NOW(), @persona_juan, @matricula_juan, @institucion, 'VENTA', 70.00, 0.00, 0.00, 70.00, 0.00, 1
WHERE NOT EXISTS (SELECT 1 FROM ventas WHERE numero = 'DEMO-PAGADA-001');
SET @venta_pagada = (SELECT id FROM ventas WHERE numero = 'DEMO-PAGADA-001' LIMIT 1);

INSERT INTO venta_detalle (venta, descripcion, cantidad, precio_unitario, descuento, total)
SELECT @venta_pagada, 'Kit deportivo demo pagado', 1, 70.00, 0.00, 70.00
WHERE NOT EXISTS (SELECT 1 FROM venta_detalle WHERE venta = @venta_pagada AND descripcion = 'Kit deportivo demo pagado');

INSERT INTO pagos (numero, fecha, persona, matricula, venta, forma_pago, institucion, monto, referencia, observacion, estado)
SELECT 'DEMO-PAGO-001', NOW(), @persona_juan, @matricula_juan, @venta_pagada, @forma_efectivo, @institucion, 70.00, 'EFECTIVO-DEMO', 'Pago demo', 1
WHERE NOT EXISTS (SELECT 1 FROM pagos WHERE numero = 'DEMO-PAGO-001');

COMMIT;
