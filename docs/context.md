[Rol] FullStack Dev Senior, Data Engineer, UI/UX Senior.
[Contexto] Sistema multi-tenant educativo: gestión de instituciones, clases, actividades pagas, productos, inventario, estudiantes y empleados (roles).

[Reglas & Arquitectura]

UI/UX: Mantener patrón visual actual. Front debe soportar CRUD completo de todas las tablas DB.

Frontend Dir (/client/): Contiene css/, js/, presentation/. Archivos HTML van en /client/presentation/pages/ (base existente).

Assets Dir (/assets/): Contiene icon/, logo/.

Backend Dir (/app/): APIs REST. Estructura:

/controllers/: Controladores JS. Contiene /api (rutas) y /request (modelos DTO entrada).

/properties/: Llave privada y clave secreta.

/implements/: Lógica y DB. Contiene /interfaces, /services (lógica negocio), /utils, /response (modelos DTO salida).

/implements/persistence/: Base de datos. Contiene conexion (clase DB), entitys (mapeo relacional/tablas), repository (operaciones CRUD).

Seguridad: Login retorna JWT. Todo consumo API posterior exige JWT en header Authorization.

Multi-tenancy (Aislamiento DB): Toda API REST devuelve estrictamente información de la institución correspondiente al JWT activo (Cadena de relación: JWT -> Usuario -> Persona -> Institución).