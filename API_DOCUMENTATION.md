# CUSPIDE — Documentación de API REST

Sistema de gestión integral para instituciones educativas con control de personas, matrículas, pagos, asignaturas, actividades deportivas, inventario y ventas.

---

## Tabla de Contenidos

1. [Información General](#1-información-general)
2. [Autenticación](#2-autenticación)
3. [Formato de Respuesta](#3-formato-de-respuesta)
4. [Códigos de Estado HTTP](#4-códigos-de-estado-http)
5. [Autenticación — Login](#5-autenticación--login)
6. [Usuarios Autenticados](#6-usuarios-autenticados)
7. [Personas](#7-personas)
8. [Instituciones](#8-instituciones)
9. [Usuarios del Sistema](#9-usuarios-del-sistema)
10. [Matrículas](#10-matrículas)
11. [Membresías](#11-membresías)
12. [Pagos](#12-pagos)
13. [Facturas](#13-facturas)
14. [Cuentas por Cobrar](#14-cuentas-por-cobrar)
15. [Asignaturas](#15-asignaturas)
16. [Clases](#16-clases)
17. [Actividades Deportivas](#17-actividades-deportivas)
18. [Deportes](#18-deportes)
19. [Horarios](#19-horarios)
20. [Productos](#20-productos)
21. [Inventario](#21-inventario)
22. [Ventas](#22-ventas)
23. [Empresa / Menú](#23-empresa--menú)
24. [Resumen de Endpoints](#24-resumen-de-endpoints)

---

## 1. Información General

| Parámetro | Valor |
|---|---|
| Base URL | `http://localhost/cuspide-microservices/app/controllers/api` |
| Formato | JSON (`Content-Type: application/json`) |
| Codificación | UTF-8 |
| Protocolo | HTTP/HTTPS |

### Detección de Recurso

Todos los endpoints aceptan dos formas para indicar el sub-recurso:

**Opción 1 — Segmento de URL:**
```
GET /api/personas/search
```

**Opción 2 — Parámetro de consulta `?api=`:**
```
GET /api/personas/index.php?api=search
```

Ambas formas son equivalentes y producen el mismo resultado.

---

## 2. Autenticación

Todos los endpoints (excepto el login) requieren un token JWT enviado como `Bearer Token` en el header `Authorization`.

```
Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```

- Algoritmo: **HS256**
- Expiración: **1 hora** desde la emisión
- El token se obtiene al hacer login exitoso

---

## 3. Formato de Respuesta

Todos los endpoints retornan la misma estructura JSON:

### Respuesta exitosa
```json
{
  "status": "success",
  "data": { },
  "message": "Consulta ejecutada correctamente."
}
```

### Respuesta de error
```json
{
  "status": "error",
  "data": null,
  "message": "Descripción del error."
}
```

El campo `data` puede ser un objeto `{}`, un arreglo `[]`, o `null` dependiendo del endpoint.

---

## 4. Códigos de Estado HTTP

| Código | Significado |
|---|---|
| `200` | Solicitud exitosa |
| `400` | Datos inválidos o faltantes |
| `401` | Token ausente, inválido o expirado |
| `403` | Sin permisos para el recurso |
| `404` | Recurso no encontrado |
| `405` | Método HTTP no permitido |
| `500` | Error interno del servidor |

---

## 5. Autenticación — Login

### `POST /auth/index.php`

Genera un token JWT para autenticar las demás peticiones.

> **No requiere token JWT.**  
> El payload debe ser enviado cifrado con la llave pública RSA del servidor.

**Body (texto plano — payload RSA cifrado en Base64):**
```
"<BASE64_ENCRYPTED_JSON>"
```

El JSON interno antes de cifrar:
```json
{
  "username": "admin",
  "password": "mi_clave"
}
```

**Respuesta exitosa `200`:**
```json
{
  "status": "success",
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
  },
  "message": "Autenticación exitosa."
}
```

**Errores posibles:**
| Código | Mensaje |
|---|---|
| `400` | El body cifrado es obligatorio. |
| `401` | Credenciales incorrectas. |
| `500` | Error interno del servidor. |

---

## 6. Usuarios Autenticados

Endpoints para obtener información del usuario que posee el token JWT activo.

Base: `/users/index.php`

---

### `GET /users/find`

Retorna identificación y nombre del usuario autenticado.

**Headers:**
```
Authorization: Bearer <token>
```

**Respuesta `200`:**
```json
{
  "status": "success",
  "data": {
    "identificacion": "1712345678",
    "primer_nombre": "Juan",
    "primer_apellido": "Pérez",
    "tipo": "ESTUDIANTE"
  },
  "message": "Consulta ejecutada correctamente."
}
```

---

### `GET /users/profile`

Retorna el perfil completo del usuario autenticado con todos sus datos, contactos, información médica y tallas.

**Respuesta `200`:**
```json
{
  "status": "success",
  "data": {
    "datos_personales": {
      "identificacion": "1712345678",
      "primer_nombre": "Juan",
      "segundo_nombre": null,
      "primer_apellido": "Pérez",
      "segundo_apellido": "Torres",
      "fecha_nacimiento": "2000-05-15",
      "nacionalidad": "ECU",
      "genero": "M",
      "etnia": "MESTIZO"
    },
    "rol_institucion": {
      "tipo": "ESTUDIANTE",
      "institucion_ruc": "1790123456001"
    },
    "ubicacion": {
      "ciudad": "QUITO",
      "direccion": "Av. Siempre Viva 123"
    },
    "contactos": {
      "telefonos": [
        { "numero": "0991234567", "operadora": "CLARO", "tipo": "MOVIL" }
      ],
      "correos": [
        { "email": "juan@ejemplo.com", "tipo": "PERSONAL" }
      ]
    },
    "registros_medicos": {
      "grupo_sanguineo": "O+",
      "alergias": "Polen",
      "enfermedades_preexistentes": null,
      "medicamentos_actuales": null,
      "contacto_emergencia": {
        "nombre": "María Pérez",
        "telefono": "0998765432"
      }
    },
    "medidas_tallas": [
      {
        "tipo": "CAMISETA",
        "talla": "M",
        "medida_cm": 90,
        "fecha_medicion": "2024-01-10"
      }
    ]
  },
  "message": "Consulta ejecutada correctamente."
}
```

---

## 7. Personas

Control completo de personas del sistema (estudiantes, docentes, personal, etc.).

Base: `/personas/index.php`

---

### `GET /personas/search`

Busca personas por cédula, nombre o apellido (búsqueda parcial con LIKE).

**Query Params:**

| Parámetro | Tipo | Requerido | Descripción |
|---|---|---|---|
| `q` | string | Sí | Texto a buscar (cédula, nombre o apellido) |

**Ejemplo:**
```
GET /personas/index.php?api=search&q=perez
```

**Respuesta `200`:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 12,
      "identificacion": "1712345678",
      "nombre_completo": "Juan Pérez Torres",
      "tipo": "ESTUDIANTE",
      "institucion": "Academia Olímpica"
    }
  ],
  "message": "OK."
}
```

> Retorna máximo 50 resultados.

---

### `GET /personas/profile`

Retorna el perfil completo de una persona específica incluyendo contactos y roles.

**Query Params:**

| Parámetro | Tipo | Requerido | Descripción |
|---|---|---|---|
| `id` | int | Cond. | ID interno de la persona |
| `identificacion` | string | Cond. | Número de cédula |

> Se requiere `id` **o** `identificacion`.

**Respuesta `200`:**
```json
{
  "status": "success",
  "data": {
    "id": 12,
    "identificacion": "1712345678",
    "nombre_completo": "Juan Pérez Torres",
    "primer_nombre": "Juan",
    "segundo_nombre": null,
    "primer_apellido": "Pérez",
    "segundo_apellido": "Torres",
    "fecha_nacimiento": "2000-05-15",
    "tipo": "ESTUDIANTE",
    "institucion": "Academia Olímpica",
    "institucion_id": 3,
    "telefonos": [ { "numero": "0991234567", "operadora": "CLARO", "tipo": "MOVIL" } ],
    "correos": [ { "email": "juan@ejemplo.com", "tipo": "PERSONAL" } ]
  },
  "message": "OK."
}
```

---

### `GET /personas/sections`

Retorna todos los datos de una persona organizados por secciones. Ideal para construir formularios de edición con pestañas.

**Query Params:**

| Parámetro | Tipo | Requerido | Descripción |
|---|---|---|---|
| `id` | int | Sí | ID interno de la persona |

**Respuesta `200`:**
```json
{
  "status": "success",
  "data": {
    "datos_personales": {
      "id": 12,
      "identificacion": "1712345678",
      "primer_nombre": "Juan",
      "segundo_nombre": null,
      "primer_apellido": "Pérez",
      "segundo_apellido": "Torres",
      "fecha_nacimiento": "2000-05-15",
      "nacionalidad": { "id": 1 },
      "genero": { "id": 1 },
      "etnia": { "id": 2 },
      "ciudad": { "id": 5 },
      "direccion": "Av. Siempre Viva 123"
    },
    "contacto": {
      "telefonos": [ { "numero": "0991234567", "operadora": "CLARO", "tipo": "MOVIL" } ],
      "correos": [ { "email": "juan@ejemplo.com", "tipo": "PERSONAL" } ]
    },
    "medico": {
      "grupo_sanguineo": "O+",
      "alergias": "Polen",
      "enfermedades_preexistentes": null,
      "medicamentos_actuales": null,
      "contacto_emergencia_nombre": "María Pérez",
      "contacto_emergencia_telefono": "0998765432"
    },
    "fisico": [
      { "tipo_prenda": "CAMISETA", "talla": "M", "medida_cm": 90, "fecha_medicion": "2024-01-10" }
    ],
    "roles_instituciones": [
      { "tipo": "ESTUDIANTE", "institucion": "Academia Olímpica", "institucion_id": 3, "estado": 1 }
    ],
    "matriculas": [
      { "id": 45, "fecha_inicio": "2024-01-01", "fecha_fin": "2024-12-31", "institucion": "Academia Olímpica" }
    ]
  },
  "message": "OK."
}
```

---

### `POST /personas/save` — Crear persona

Crea una nueva persona con todos sus datos. Si se proveen `username` y `password`, también crea un usuario del sistema.

**Body JSON:**
```json
{
  "identificacion": "1712345678",
  "primer_nombre": "Juan",
  "segundo_nombre": null,
  "primer_apellido": "Pérez",
  "segundo_apellido": "Torres",
  "nacionalidad": 1,
  "genero": 1,
  "etnia": 2,
  "fecha_nacimiento": "2000-05-15",
  "ciudad": 5,
  "direccion": "Av. Siempre Viva 123",
  "institucion": 3,
  "tipo_persona": 1,
  "username": "jperez",
  "password": "Clave123!",
  "tipo_user": "ESTUDIANTE",
  "grupo_sanguineo": "O+",
  "alergias": "Polen",
  "contacto_emergencia_nombre": "María Pérez",
  "contacto_emergencia_telefono": "0998765432"
}
```

**Campos requeridos:** `identificacion`, `primer_nombre`, `primer_apellido`, `institucion`, `tipo_persona`

**Respuesta `200`:**
```json
{
  "status": "success",
  "data": {
    "id": 12,
    "identificacion": "1712345678",
    "nombre_completo": "Juan Pérez Torres"
  },
  "message": "OK."
}
```

---

### `PUT /personas/save` — Actualizar persona

Actualiza los datos de una persona existente. Mismo body que el POST.

**Query Params adicionales:**

| Parámetro | Tipo | Descripción |
|---|---|---|
| `id` | int | ID de la persona a actualizar |

> También se puede incluir `"id"` en el body JSON.

---

## 8. Instituciones

Gestión de las instituciones educativas del sistema.

Base: `/instituciones/index.php`

---

### `GET /instituciones/data`

Obtiene los datos de una institución específica.

**Query Params:**

| Parámetro | Tipo | Requerido | Descripción |
|---|---|---|---|
| `id` | int | Sí | ID de la institución |

**Respuesta `200`:**
```json
{
  "status": "success",
  "data": {
    "id": 3,
    "ruc": "1790123456001",
    "razon_social": "Academia Deportiva Olímpica S.A.",
    "nombre": "Academia Olímpica",
    "logotipo": "logo.png",
    "estado": 1
  },
  "message": "OK."
}
```

---

### `GET /instituciones/list`

Lista todas las instituciones activas.

**Respuesta `200`:**
```json
{
  "status": "success",
  "data": [
    { "id": 1, "ruc": "1790000001001", "nombre": "Instituto Central", ... },
    { "id": 3, "ruc": "1790123456001", "nombre": "Academia Olímpica", ... }
  ],
  "message": "OK."
}
```

---

### `POST /instituciones/save` — Crear institución

**Body JSON:**
```json
{
  "ruc": "1790123456001",
  "razon_social": "Academia Deportiva Olímpica S.A.",
  "nombre": "Academia Olímpica",
  "logotipo": "logo.png",
  "estado": 1
}
```

**Campos requeridos:** `razon_social`, `nombre`

---

### `PUT /instituciones/save` — Actualizar institución

**Query Params:** `id` (int, requerido)

Mismo body que el POST.

---

## 9. Usuarios del Sistema

Gestión de cuentas de usuario, botones de menú y permisos.

Base: `/usuarios/index.php`

---

### `GET /usuarios/buttons`

Lista los botones/acciones de menú disponibles para un tipo de usuario.

**Query Params:**

| Parámetro | Tipo | Requerido | Descripción |
|---|---|---|---|
| `tipo_user` | string | Sí | Nemónico del tipo de usuario (ej. `ADMIN`, `ESTUDIANTE`) |
| `institucion` | int | No | Filtra botones por institución |

**Respuesta `200`:**
```json
{
  "status": "success",
  "data": [
    { "id": 1, "nombre": "Matrículas", "ruta": "/matriculas", "icono": "fa-list" },
    { "id": 2, "nombre": "Pagos", "ruta": "/pagos", "icono": "fa-dollar-sign" }
  ],
  "message": "OK."
}
```

---

### `POST /usuarios/button`

Crea un nuevo botón de menú.

**Body JSON:**
```json
{
  "nombre": "Reportes",
  "ruta": "/reportes",
  "icono": "fa-chart-bar",
  "tipo_user": "ADMIN",
  "institucion": 3
}
```

**Campos requeridos:** `nombre`, `ruta`, `tipo_user`

**Respuesta `200`:**
```json
{ "status": "success", "data": { "id": 15, "nombre": "Reportes" }, "message": "OK." }
```

---

### `PUT /usuarios/button`

Habilita o deshabilita un botón.

**Query Params:** `id` (int, requerido)

**Body JSON:**
```json
{ "estado": 0 }
```

> `estado`: `1` = activo, `0` = deshabilitado

---

### `POST /usuarios/save` — Crear usuario

**Body JSON:**
```json
{
  "username": "jperez",
  "password": "Clave123!",
  "tipo_user": "ESTUDIANTE",
  "imagen": null
}
```

**Campos requeridos:** `username`, `password`

**Respuesta `200`:**
```json
{
  "status": "success",
  "data": { "id": 25, "username": "jperez", "tipo_user": "ESTUDIANTE" },
  "message": "OK."
}
```

---

### `PUT /usuarios/save` — Actualizar usuario

**Query Params:** `id` (int, requerido)

**Body JSON:** Mismo que POST. Si `password` no se envía, la contraseña no cambia.

---

### `PUT /usuarios/disable`

Deshabilita un usuario del sistema.

**Query Params:** `id` (int, requerido)

**Respuesta `200`:**
```json
{
  "status": "success",
  "data": { "id": 25, "message": "Usuario desactivado correctamente." },
  "message": "OK."
}
```

---

## 10. Matrículas

Control de matrículas que vinculan personas con clases y/o actividades deportivas. La creación de una matrícula genera automáticamente una factura.

Base: `/matriculas/index.php`

---

### `GET /matriculas/list`

Lista las matrículas de una persona con sus clases y actividades inscritas.

**Query Params:**

| Parámetro | Tipo | Requerido | Descripción |
|---|---|---|---|
| `persona_id` | int | Cond. | ID interno de la persona |
| `identificacion` | string | Cond. | Cédula de la persona |

> Se requiere `persona_id` **o** `identificacion`.

**Respuesta `200`:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 45,
      "fecha_registro": "2024-01-10 09:30:00",
      "fecha_inicio": "2024-01-15",
      "fecha_fin": "2024-12-31",
      "membresia": "Plan Anual",
      "institucion": "Academia Olímpica",
      "estado": "ACTIVO",
      "clases": [
        { "id": 12, "clase": 5, "modalidad": 1, "horario": 3, "fecha_inicio": "2024-01-15", "fecha_fin": "2024-12-31", "estado": 1 }
      ],
      "actividades": [
        { "id": 8, "actividad": 2, "modalidad": 1, "categoria_deportiva": null, "horario": 7, "fecha_inicio": "2024-01-15", "fecha_fin": "2024-12-31", "estado": 1 }
      ]
    }
  ],
  "message": "OK."
}
```

---

### `POST /matriculas/create`

Crea una nueva matrícula con sus clases y/o actividades. Genera automáticamente una factura (`Venta` tipo `MATRICULA`).

**Body JSON:**
```json
{
  "persona": 12,
  "fecha_inicio": "2024-01-15",
  "fecha_fin": "2024-12-31",
  "membresia": 2,
  "institucion": 3,
  "descuento": 10.00,
  "clases": [
    {
      "clase_id": 5,
      "modalidad": 1,
      "horario": 3,
      "fecha_inicio": "2024-01-15",
      "fecha_fin": "2024-12-31"
    }
  ],
  "actividades": [
    {
      "actividad_id": 2,
      "modalidad": 1,
      "horario": 7,
      "categoria_deportiva": null,
      "fecha_inicio": "2024-01-15",
      "fecha_fin": "2024-12-31"
    }
  ]
}
```

**Campos requeridos:** `persona`, `fecha_inicio`, `fecha_fin`, `institucion`, al menos una `clase` o `actividad`

| Campo | Tipo | Descripción |
|---|---|---|
| `persona` | int | ID de la persona |
| `fecha_inicio` | date | Inicio de la matrícula |
| `fecha_fin` | date | Fin de la matrícula |
| `membresia` | int | ID de membresía (opcional) |
| `institucion` | int | ID de institución |
| `descuento` | float | Monto de descuento global |
| `clases` | array | Lista de clases (`clase_id`, `modalidad`, `horario?`, `fecha_inicio`, `fecha_fin`) |
| `actividades` | array | Lista de actividades (`actividad_id`, `modalidad`, `horario?`, `categoria_deportiva?`, `fecha_inicio`, `fecha_fin`) |

**Respuesta `200`:**
```json
{
  "status": "success",
  "data": {
    "matricula_id": 45,
    "numero_factura": "FAC-20240115-000045",
    "subtotal": 150.00,
    "descuento": 10.00,
    "impuesto": 0.00,
    "total": 140.00
  },
  "message": "OK."
}
```

---

### `PUT /matriculas/cancel`

Anula una matrícula y todos sus ítems de clase y actividad.

**Query Params:** `id` (int, requerido)

**Respuesta `200`:**
```json
{ "status": "success", "data": { "id": 45, "estado": "ANULADO" }, "message": "OK." }
```

---

### `PUT /matriculas/removeitem`

Anula un ítem específico (clase o actividad) dentro de una matrícula activa.

**Query Params:**

| Parámetro | Tipo | Requerido | Descripción |
|---|---|---|---|
| `id` | int | Sí | ID del ítem en `clases_matricula` o `actividades_matricula` |
| `tipo` | string | Sí | `"clase"` o `"actividad"` |

**Respuesta `200`:**
```json
{ "status": "success", "data": { "id": 12, "tipo": "clase", "estado": "ANULADO" }, "message": "OK." }
```

---

## 11. Membresías

Planes de membresía asignables a las matrículas.

Base: `/membresias/index.php`

---

### `GET /membresias/list`

Lista todas las membresías disponibles.

**Respuesta `200`:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "titulo": "Plan Mensual",
      "precio": "45.00",
      "porcentaje": 0,
      "descripcion": "Acceso mensual a todas las clases",
      "url_boton": "/membresias/mensual",
      "fecha_inicio": "2024-01-01",
      "fecha_fin": "2024-12-31",
      "estado": 1
    }
  ],
  "message": "OK."
}
```

---

### `POST /membresias/save` — Crear membresía

**Body JSON:**
```json
{
  "titulo": "Plan Trimestral",
  "precio": "120.00",
  "porcentaje": 5.0,
  "descripcion": "Acceso trimestral con 5% de descuento",
  "url_boton": "/membresias/trimestral",
  "fecha_inicio": "2024-01-01",
  "fecha_fin": "2024-12-31",
  "estado": 1
}
```

**Campos requeridos:** `titulo`, `fecha_inicio`, `fecha_fin`

---

### `PUT /membresias/save` — Actualizar membresía

**Query Params:** `id` (int, requerido). Mismo body que POST.

---

## 12. Pagos

Registro y consulta de pagos. Los pagos se aplican de forma FIFO (del más antiguo al más reciente).

Base: `/pagos/index.php`

---

### `GET /pagos/pending`

Lista todas las facturas con saldo pendiente de pago en una institución.

**Query Params:**

| Parámetro | Tipo | Requerido | Descripción |
|---|---|---|---|
| `institucion` | int | Sí | ID de la institución |

**Respuesta `200`:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 88,
      "numero": "FAC-20240115-000045",
      "fecha": "2024-01-15",
      "total": 140.00,
      "saldo": 140.00,
      "tipo": "MATRICULA",
      "identificacion": "1712345678",
      "nombre": "Juan Pérez"
    }
  ],
  "message": "OK."
}
```

---

### `GET /pagos/history`

Historial de pagos y facturas de una persona.

**Query Params:**

| Parámetro | Tipo | Requerido | Descripción |
|---|---|---|---|
| `persona_id` | int | Cond. | ID de la persona |
| `identificacion` | string | Cond. | Cédula de la persona |

**Respuesta `200`:**
```json
{
  "status": "success",
  "data": {
    "persona_id": 12,
    "pagos": [
      {
        "id": 30,
        "numero": "PAG-20240120-abc123",
        "fecha": "2024-01-20",
        "monto": 70.00,
        "observacion": "Primer pago",
        "estado": 1
      }
    ],
    "facturas": [
      {
        "id": 88,
        "numero": "FAC-20240115-000045",
        "fecha": "2024-01-15",
        "total": 140.00,
        "saldo": 70.00,
        "tipo": "MATRICULA",
        "pagada": false
      }
    ]
  },
  "message": "OK."
}
```

---

### `POST /pagos/register`

Registra un pago. Se aplica automáticamente a las facturas pendientes más antiguas (FIFO).

**Body JSON:**
```json
{
  "persona": 12,
  "forma_pago": 1,
  "institucion": 3,
  "monto": 70.00,
  "referencia": "TRF-00123",
  "observacion": "Primer pago matrícula enero",
  "venta": null,
  "matricula": null
}
```

| Campo | Tipo | Requerido | Descripción |
|---|---|---|---|
| `persona` | int | Sí | ID de la persona |
| `forma_pago` | int | Sí | ID de la forma de pago |
| `institucion` | int | Sí | ID de la institución |
| `monto` | float | Sí | Monto a pagar (> 0) |
| `referencia` | string | No | Número de referencia bancaria |
| `observacion` | string | No | Nota de la transacción |
| `venta` | int | No | Si se quiere pagar una factura específica |
| `matricula` | int | No | Si se quiere asociar a una matrícula específica |

**Respuesta `200`:**
```json
{
  "status": "success",
  "data": {
    "numero_pago": "PAG-20240120-abc123",
    "monto_aplicado": 70.00,
    "ventas_saldadas": [
      { "venta_id": 88, "monto_aplicado": 70.00, "saldo_restante": 70.00 }
    ],
    "saldo_restante": 0.00
  },
  "message": "OK."
}
```

---

## 13. Facturas

Consulta y gestión de facturas/ventas generadas por el sistema.

Base: `/facturas/index.php`

---

### `GET /facturas/list`

Lista paginada de facturas de una institución.

**Query Params:**

| Parámetro | Tipo | Requerido | Descripción |
|---|---|---|---|
| `institucion` | int | Sí | ID de la institución |
| `limit` | int | No | Registros por página (default: 100) |
| `offset` | int | No | Desplazamiento (default: 0) |

**Respuesta `200`:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 88,
      "numero": "FAC-20240115-000045",
      "fecha": "2024-01-15 09:30:00",
      "total": 140.00,
      "saldo": 140.00,
      "tipo": "MATRICULA",
      "estado": 1,
      "identificacion": "1712345678",
      "nombre": "Juan Pérez"
    }
  ],
  "message": "OK."
}
```

---

### `GET /facturas/byclient`

Facturas de un cliente específico.

**Query Params:**

| Parámetro | Tipo | Requerido | Descripción |
|---|---|---|---|
| `persona_id` | int | Cond. | ID de la persona |
| `identificacion` | string | Cond. | Cédula de la persona |

---

### `GET /facturas/pending`

Facturas con saldo pendiente de una institución.

**Query Params:** `institucion` (int, requerido)

---

### `PUT /facturas/cancel`

Anula una factura.

**Query Params:** `id` (int, requerido)

**Respuesta `200`:**
```json
{ "status": "success", "data": { "id": 88, "estado": "ANULADO" }, "message": "OK." }
```

---

## 14. Cuentas por Cobrar

Vista consolidada de todas las deudas pendientes.

Base: `/cuentascobrar/index.php`

---

### `GET /cuentascobrar/list`

Lista cuentas por cobrar con detalle de facturas, líneas de detalle y días vencidos.

**Query Params:**

| Parámetro | Tipo | Requerido | Descripción |
|---|---|---|---|
| `institucion` | int | Sí | ID de la institución |
| `persona_id` | int | No | Filtrar por persona |
| `identificacion` | string | No | Filtrar por cédula |

**Respuesta `200`:**
```json
{
  "status": "success",
  "data": {
    "total_por_cobrar": 560.00,
    "cuentas": [
      {
        "id": 88,
        "numero": "FAC-20240115-000045",
        "fecha": "2024-01-15",
        "tipo": "MATRICULA",
        "total": 140.00,
        "saldo": 140.00,
        "dias_vencido": 30,
        "identificacion": "1712345678",
        "nombre_cliente": "Juan Pérez",
        "detalle": [
          { "descripcion": "Clase Natación", "cantidad": 1, "precio_unitario": 80.00, "subtotal": 80.00 },
          { "descripcion": "Actividad Fútbol", "cantidad": 1, "precio_unitario": 60.00, "subtotal": 60.00 }
        ]
      }
    ]
  },
  "message": "OK."
}
```

---

## 15. Asignaturas

Materias o asignaturas académicas que puede tener una institución.

Base: `/asignaturas/index.php`

---

### `GET /asignaturas/list`

Lista asignaturas de una institución con conteo de estudiantes matriculados.

**Query Params:** `institucion` (int, requerido)

**Respuesta `200`:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 7,
      "nombre": "Matemáticas",
      "descripcion": "Algebra y cálculo básico",
      "estado": 1,
      "total_matriculados": 25
    }
  ],
  "message": "OK."
}
```

---

### `POST /asignaturas/save` — Crear asignatura

**Body JSON:**
```json
{
  "nombre": "Matemáticas",
  "descripcion": "Algebra y cálculo básico",
  "institucion": 3,
  "estado": 1
}
```

**Campos requeridos:** `nombre`, `institucion`

---

### `PUT /asignaturas/save` — Actualizar asignatura

**Query Params:** `id` (int, requerido). Mismo body que POST.

---

### `PUT /asignaturas/delete` o `DELETE /asignaturas/delete`

Desactiva una asignatura (soft delete).

**Query Params:** `id` (int, requerido)

**Respuesta `200`:**
```json
{ "status": "success", "data": { "id": 7, "estado": 2 }, "message": "OK." }
```

---

## 16. Clases

Grupos de estudio dentro de una institución con docente, cupo y horario.

Base: `/clases/index.php`

---

### `GET /clases/list`

Lista clases con estadísticas de asistencia y matrícula.

**Query Params:** `institucion` (int, requerido)

**Respuesta `200`:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 5,
      "nombre": "Natación Avanzado A",
      "cupo_maximo": 20,
      "estado": 1,
      "periodo": "2024-I",
      "primer_nombre": "Carlos",
      "primer_apellido": "Mendoza",
      "matriculados": 18,
      "asistencias": 92,
      "faltas": 8
    }
  ],
  "message": "OK."
}
```

---

### `GET /clases/capacity`

Consulta la capacidad y disponibilidad de cupo de una clase.

**Query Params:** `id` (int, requerido)

**Respuesta `200`:**
```json
{
  "status": "success",
  "data": {
    "cupo_maximo": 20,
    "ocupados": 18,
    "disponible": 2
  },
  "message": "OK."
}
```

---

### `POST /clases/save` — Crear clase

**Body JSON:**
```json
{
  "nombre": "Natación Avanzado A",
  "periodo_academico": 4,
  "institucion": 3,
  "docente": 15,
  "cupo_maximo": 20,
  "estado": 1
}
```

**Campos requeridos:** `nombre`, `institucion`, `cupo_maximo`

---

### `PUT /clases/save` — Actualizar clase

**Query Params:** `id` (int, requerido). Mismo body que POST.

---

### `PUT /clases/delete`

Desactiva una clase.

**Query Params:** `id` (int, requerido)

---

### `POST /clases/attendance`

Registra la asistencia de un estudiante a una clase.

**Body JSON:**
```json
{
  "matricula": 45,
  "clase": 5,
  "fecha": "2024-03-15",
  "estado": 1,
  "horario": 3,
  "hora_entrada": "08:00:00",
  "hora_salida": "09:30:00",
  "observacion": ""
}
```

| Campo `estado` | Significado |
|---|---|
| `1` | Presente |
| `2` | Ausente |
| `3` | Justificado |

**Respuesta `200`:**
```json
{ "status": "success", "data": { "id": 203, "fecha": "2024-03-15", "estado": 1 }, "message": "OK." }
```

---

## 17. Actividades Deportivas

Actividades físicas y deportivas ofrecidas por la institución.

Base: `/actividades/index.php`

---

### `GET /actividades/list`

Lista actividades con estadísticas de asistencia y matrícula.

**Query Params:** `institucion` (int, requerido)

**Respuesta `200`:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 2,
      "nombre": "Fútbol Infantil",
      "deporte": "Fútbol",
      "nivel": "INICIACIÓN",
      "precio": 60.00,
      "estado": 1,
      "matriculados": 15,
      "asistencias": 120,
      "faltas": 5
    }
  ],
  "message": "OK."
}
```

---

### `GET /actividades/capacity`

Consulta el cupo disponible de una actividad según su horario activo.

**Query Params:** `id` (int, requerido — ID de la actividad)

**Respuesta `200`:**
```json
{
  "status": "success",
  "data": {
    "cupo_maximo": 25,
    "ocupados": 15,
    "disponible": 10
  },
  "message": "OK."
}
```

---

### `POST /actividades/save` — Crear actividad

**Body JSON:**
```json
{
  "nombre": "Fútbol Infantil",
  "deporte": 1,
  "precio": 60.00,
  "nivel": 2,
  "observacion": "Para niños de 6 a 10 años",
  "institucion": 3,
  "estado": 1
}
```

**Campos requeridos:** `nombre`, `deporte`, `institucion`

---

### `PUT /actividades/save` — Actualizar actividad

**Query Params:** `id` (int, requerido). Mismo body que POST.

---

### `PUT /actividades/delete`

Desactiva una actividad.

**Query Params:** `id` (int, requerido)

---

### `POST /actividades/attendance`

Registra la asistencia de un estudiante a una actividad.

**Body JSON:**
```json
{
  "matricula": 45,
  "actividad": 2,
  "fecha": "2024-03-15",
  "estado": 1,
  "horario": 7,
  "hora_entrada": "16:00:00",
  "hora_salida": "17:30:00",
  "observacion": ""
}
```

**Respuesta `200`:**
```json
{ "status": "success", "data": { "id": 204, "fecha": "2024-03-15", "estado": 1 }, "message": "OK." }
```

---

## 18. Deportes

Catálogo de disciplinas deportivas del sistema.

Base: `/deportes/index.php`

---

### `GET /deportes/list`

Lista todos los deportes activos.

**Respuesta `200`:**
```json
{
  "status": "success",
  "data": [
    { "id": 1, "nombre": "Fútbol", "descripcion": "Fútbol de campo", "estado": 1 },
    { "id": 2, "nombre": "Natación", "descripcion": "Natación en piscina olímpica", "estado": 1 }
  ],
  "message": "OK."
}
```

---

### `POST /deportes/save` — Crear deporte

**Body JSON:**
```json
{
  "nombre": "Baloncesto",
  "descripcion": "Baloncesto 5 vs 5",
  "estado": 1
}
```

**Campo requerido:** `nombre`

---

### `PUT /deportes/save` — Actualizar deporte

**Query Params:** `id` (int, requerido). Mismo body que POST.

---

## 19. Horarios

Gestión de horarios para clases y actividades deportivas.

Base: `/horarios/index.php`

---

### `GET /horarios/list`

Lista los horarios filtrados por clase, actividad o institución.

**Query Params (usar uno):**

| Parámetro | Tipo | Descripción |
|---|---|---|
| `clase` | int | ID de la clase |
| `actividad` | int | ID de la actividad |
| `institucion` | int | ID de la institución (todos los horarios) |

**Respuesta `200`:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 3,
      "nombre": "Natación Mañana",
      "dia_semana": 1,
      "dia_nombre": "Lunes",
      "hora_inicio": "08:00:00",
      "hora_fin": "09:30:00",
      "cupo_maximo": 20,
      "ubicacion": "Piscina A",
      "estado": 1
    }
  ],
  "message": "OK."
}
```

**Días de semana:**

| Valor | Día |
|---|---|
| `1` | Lunes |
| `2` | Martes |
| `3` | Miércoles |
| `4` | Jueves |
| `5` | Viernes |
| `6` | Sábado |
| `7` | Domingo |

---

### `POST /horarios/save` — Crear horario

**Body JSON:**
```json
{
  "nombre": "Natación Mañana",
  "institucion": 3,
  "clase": 5,
  "actividad": null,
  "entrenador": 15,
  "categoria_deportiva": null,
  "dia_semana": 1,
  "hora_inicio": "08:00:00",
  "hora_fin": "09:30:00",
  "fecha_inicio": "2024-01-15",
  "fecha_fin": "2024-12-31",
  "cupo_maximo": 20,
  "ubicacion": "Piscina A",
  "estado": 1
}
```

**Campos requeridos:** `nombre`, `institucion`, `hora_inicio`, `hora_fin`, y al menos `clase` o `actividad`

---

### `PUT /horarios/save` — Actualizar horario

**Query Params:** `id` (int, requerido). Mismo body que POST.

---

### `PUT /horarios/delete`

Desactiva un horario.

**Query Params:** `id` (int, requerido)

---

## 20. Productos

Catálogo de productos e inventario (uniformes, equipos deportivos, materiales).

Base: `/productos/index.php`

---

### `GET /productos/list`

Lista productos con stock actual.

**Query Params:**

| Parámetro | Tipo | Requerido | Descripción |
|---|---|---|---|
| `institucion` | int | No | Filtrar por institución |
| `categoria` | int | No | Filtrar por categoría |

**Respuesta `200`:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 10,
      "codigo": "UNI-001",
      "nombre": "Camiseta Oficial",
      "descripcion": "Camiseta deportiva talla M",
      "precio": 25.00,
      "costo": 12.00,
      "controla_stock": true,
      "estado": 1,
      "categoria_nombre": "Uniformes",
      "stock": 45
    }
  ],
  "message": "OK."
}
```

---

### `GET /productos/categories`

Lista todas las categorías activas.

**Respuesta `200`:**
```json
{
  "status": "success",
  "data": [
    { "id": 1, "nombre": "Uniformes", "descripcion": "Ropa deportiva oficial" },
    { "id": 2, "nombre": "Equipamiento", "descripcion": "Implementos deportivos" }
  ],
  "message": "OK."
}
```

---

### `POST /productos/save` — Crear producto

**Body JSON:**
```json
{
  "codigo": "UNI-001",
  "nombre": "Camiseta Oficial",
  "descripcion": "Camiseta deportiva talla M",
  "categoria": 1,
  "precio": 25.00,
  "costo": 12.00,
  "controla_stock": true,
  "institucion": 3,
  "estado": 1
}
```

**Campos requeridos:** `codigo`, `nombre`, `institucion`

---

### `PUT /productos/save` — Actualizar producto

**Query Params:** `id` (int, requerido). Mismo body que POST.

---

### `PUT /productos/disable`

Desactiva un producto.

**Query Params:** `id` (int, requerido)

**Respuesta `200`:**
```json
{ "status": "success", "data": { "id": 10, "estado": "INACTIVO" }, "message": "OK." }
```

---

## 21. Inventario

Control de stock de productos con movimientos de entrada, salida y ajuste.

Base: `/inventario/index.php`

---

### `GET /inventario/list`

Lista el inventario de productos con alerta de stock mínimo.

**Query Params:** `institucion` (int, requerido)

**Respuesta `200`:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 10,
      "codigo": "UNI-001",
      "nombre": "Camiseta Oficial",
      "precio": 25.00,
      "categoria": "Uniformes",
      "stock_actual": 5,
      "stock_minimo": 10,
      "alerta_stock": 1
    }
  ],
  "message": "OK."
}
```

> `alerta_stock = 1` indica que el stock actual está en o por debajo del mínimo.

---

### `GET /inventario/movements`

Historial de movimientos de un producto.

**Query Params:**

| Parámetro | Tipo | Requerido | Descripción |
|---|---|---|---|
| `producto_id` | int | Sí | ID del producto |
| `limit` | int | No | Número de registros (default: 50) |

**Respuesta `200`:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 55,
      "tipo_movimiento": "ENTRADA",
      "cantidad": 20,
      "fecha": "2024-03-01",
      "referencia": "OC-00045",
      "observacion": "Compra proveedor XYZ"
    }
  ],
  "message": "OK."
}
```

---

### `POST /inventario/adjust`

Registra un movimiento de inventario (entrada, salida o ajuste de stock).

**Body JSON:**
```json
{
  "producto": 10,
  "tipo_movimiento": "ENTRADA",
  "cantidad": 20,
  "institucion": 3,
  "variante": null,
  "referencia": "OC-00045",
  "observacion": "Compra proveedor XYZ"
}
```

| Campo `tipo_movimiento` | Efecto |
|---|---|
| `ENTRADA` | Suma la cantidad al stock |
| `SALIDA` | Resta la cantidad del stock |
| `AJUSTE` | Establece el stock directamente al valor indicado |

**Respuesta `200`:**
```json
{
  "status": "success",
  "data": { "producto": 10, "stock_nuevo": 25 },
  "message": "OK."
}
```

---

## 22. Ventas

Registro de ventas de productos con actualización automática de inventario.

Base: `/ventas/index.php`

---

### `GET /ventas/list`

Lista ventas de una institución con filtros de fecha.

**Query Params:**

| Parámetro | Tipo | Requerido | Descripción |
|---|---|---|---|
| `institucion` | int | Sí | ID de la institución |
| `desde` | date | No | Fecha desde (formato `YYYY-MM-DD`) |
| `hasta` | date | No | Fecha hasta (formato `YYYY-MM-DD`) |
| `limit` | int | No | Límite de registros (default: 100) |

---

### `GET /ventas/byclient`

Ventas de un cliente específico.

**Query Params:** `identificacion` (string, requerido)

---

### `GET /ventas/detail`

Detalle completo de una venta: líneas de ítem y pagos aplicados.

**Query Params:** `id` (int, requerido)

**Respuesta `200`:**
```json
{
  "status": "success",
  "data": {
    "venta": {
      "id": 92,
      "numero": "VTA-20240320-abc456",
      "fecha": "2024-03-20",
      "tipo": "VENTA",
      "subtotal": 50.00,
      "descuento": 0.00,
      "total": 50.00,
      "saldo": 0.00,
      "estado": 2
    },
    "detalles": [
      { "descripcion": "Camiseta Oficial", "cantidad": 2, "precio_unitario": 25.00, "subtotal": 50.00 }
    ],
    "pagos_aplicados": [
      { "numero": "PAG-20240320-xyz", "fecha": "2024-03-20", "monto": 50.00, "forma_pago": "Efectivo" }
    ]
  },
  "message": "OK."
}
```

---

### `POST /ventas/create`

Crea una venta. Si se indica `forma_pago`, registra el pago inmediatamente (saldo=0). Si no, queda como cuenta por cobrar.

**Body JSON:**
```json
{
  "persona": 12,
  "institucion": 3,
  "tipo": "VENTA",
  "descuento": 0.00,
  "forma_pago": 1,
  "referencia": "EFE-001",
  "matricula": null,
  "items": [
    {
      "producto_id": 10,
      "descripcion": "Camiseta Oficial",
      "cantidad": 2,
      "precio_unitario": 25.00,
      "descuento": 0.00
    }
  ]
}
```

| Campo | Tipo | Requerido | Descripción |
|---|---|---|---|
| `persona` | int | Sí | ID de la persona compradora |
| `institucion` | int | Sí | ID de la institución |
| `tipo` | string | Sí | Tipo: `VENTA`, `MATRICULA`, etc. |
| `items` | array | Sí | Al menos un ítem |
| `descuento` | float | No | Descuento total |
| `forma_pago` | int | No | Si se paga al momento |
| `referencia` | string | No | Referencia del pago |
| `matricula` | int | No | Si se asocia a una matrícula |

**Respuesta `200`:**
```json
{
  "status": "success",
  "data": {
    "id": 92,
    "numero": "VTA-20240320-abc456",
    "total": 50.00,
    "saldo": 0.00
  },
  "message": "OK."
}
```

---

## 23. Empresa / Menú

Endpoints de soporte para el frontend.

---

### `GET /company/index.php`

Datos de la empresa/institución del usuario autenticado.

**Respuesta `200`:**
```json
{
  "status": "success",
  "data": {
    "nombre": "Academia Olímpica",
    "ruc": "1790123456001"
  },
  "message": "Consulta ejecutada correctamente."
}
```

---

### `GET /buttom/index.php`

Menú de navegación del usuario autenticado.

**Respuesta `200`:**
```json
{
  "status": "success",
  "data": [
    { "orden": 1, "nombre": "Dashboard", "icon": "fa-home", "tipo": "MENU" },
    { "orden": 2, "nombre": "Matrículas", "icon": "fa-users", "tipo": "MENU" }
  ],
  "message": "Consulta ejecutada correctamente."
}
```

---

## 24. Resumen de Endpoints

| # | Dominio | Método | Recurso | Autenticación |
|---|---|---|---|---|
| 1 | Auth | POST | `/auth` | No |
| 2 | Usuarios Auth | GET | `/users/find` | JWT |
| 3 | Usuarios Auth | GET | `/users/profile` | JWT |
| 4 | Personas | GET | `/personas/search` | JWT |
| 5 | Personas | GET | `/personas/profile` | JWT |
| 6 | Personas | GET | `/personas/sections` | JWT |
| 7 | Personas | POST | `/personas/save` | JWT |
| 8 | Personas | PUT | `/personas/save` | JWT |
| 9 | Instituciones | GET | `/instituciones/data` | JWT |
| 10 | Instituciones | GET | `/instituciones/list` | JWT |
| 11 | Instituciones | POST | `/instituciones/save` | JWT |
| 12 | Instituciones | PUT | `/instituciones/save` | JWT |
| 13 | Usuarios | GET | `/usuarios/buttons` | JWT |
| 14 | Usuarios | POST | `/usuarios/button` | JWT |
| 15 | Usuarios | PUT | `/usuarios/button` | JWT |
| 16 | Usuarios | POST | `/usuarios/save` | JWT |
| 17 | Usuarios | PUT | `/usuarios/save` | JWT |
| 18 | Usuarios | PUT | `/usuarios/disable` | JWT |
| 19 | Matrículas | GET | `/matriculas/list` | JWT |
| 20 | Matrículas | POST | `/matriculas/create` | JWT |
| 21 | Matrículas | PUT | `/matriculas/cancel` | JWT |
| 22 | Matrículas | PUT | `/matriculas/removeitem` | JWT |
| 23 | Membresías | GET | `/membresias/list` | JWT |
| 24 | Membresías | POST | `/membresias/save` | JWT |
| 25 | Membresías | PUT | `/membresias/save` | JWT |
| 26 | Pagos | GET | `/pagos/pending` | JWT |
| 27 | Pagos | GET | `/pagos/history` | JWT |
| 28 | Pagos | POST | `/pagos/register` | JWT |
| 29 | Facturas | GET | `/facturas/list` | JWT |
| 30 | Facturas | GET | `/facturas/byclient` | JWT |
| 31 | Facturas | GET | `/facturas/pending` | JWT |
| 32 | Facturas | PUT | `/facturas/cancel` | JWT |
| 33 | Cuentas x Cobrar | GET | `/cuentascobrar/list` | JWT |
| 34 | Asignaturas | GET | `/asignaturas/list` | JWT |
| 35 | Asignaturas | POST | `/asignaturas/save` | JWT |
| 36 | Asignaturas | PUT | `/asignaturas/save` | JWT |
| 37 | Asignaturas | PUT/DELETE | `/asignaturas/delete` | JWT |
| 38 | Clases | GET | `/clases/list` | JWT |
| 39 | Clases | GET | `/clases/capacity` | JWT |
| 40 | Clases | POST | `/clases/save` | JWT |
| 41 | Clases | PUT | `/clases/save` | JWT |
| 42 | Clases | PUT | `/clases/delete` | JWT |
| 43 | Clases | POST | `/clases/attendance` | JWT |
| 44 | Actividades | GET | `/actividades/list` | JWT |
| 45 | Actividades | GET | `/actividades/capacity` | JWT |
| 46 | Actividades | POST | `/actividades/save` | JWT |
| 47 | Actividades | PUT | `/actividades/save` | JWT |
| 48 | Actividades | PUT | `/actividades/delete` | JWT |
| 49 | Actividades | POST | `/actividades/attendance` | JWT |
| 50 | Deportes | GET | `/deportes/list` | JWT |
| 51 | Deportes | POST | `/deportes/save` | JWT |
| 52 | Deportes | PUT | `/deportes/save` | JWT |
| 53 | Horarios | GET | `/horarios/list` | JWT |
| 54 | Horarios | POST | `/horarios/save` | JWT |
| 55 | Horarios | PUT | `/horarios/save` | JWT |
| 56 | Horarios | PUT | `/horarios/delete` | JWT |
| 57 | Productos | GET | `/productos/list` | JWT |
| 58 | Productos | GET | `/productos/categories` | JWT |
| 59 | Productos | POST | `/productos/save` | JWT |
| 60 | Productos | PUT | `/productos/save` | JWT |
| 61 | Productos | PUT | `/productos/disable` | JWT |
| 62 | Inventario | GET | `/inventario/list` | JWT |
| 63 | Inventario | GET | `/inventario/movements` | JWT |
| 64 | Inventario | POST | `/inventario/adjust` | JWT |
| 65 | Ventas | GET | `/ventas/list` | JWT |
| 66 | Ventas | GET | `/ventas/byclient` | JWT |
| 67 | Ventas | GET | `/ventas/detail` | JWT |
| 68 | Ventas | POST | `/ventas/create` | JWT |
| 69 | Empresa | GET | `/company` | JWT |
| 70 | Menú | GET | `/buttom` | JWT |

---

**Total: 70 endpoints en 20 dominios**

---

*Documentación generada para CUSPIDE Microservices — Sistema de Gestión de Instituciones Educativas*
