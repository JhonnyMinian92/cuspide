# Probar Login en Postman

## Flujo real del endpoint
1. El cliente cifra **todo el JSON de credenciales** (`{"username":"...","password":"..."}`) con la **llave pública**.
2. El backend recibe en el body **solo la cadena cifrada Base64**.
3. El service descifra con la **llave privada**, valida JSON/campos/credenciales y emite JWT (1 hora).

## 1) Levantar API local
```bash
php -S localhost:8000
```

## 2) Generar cadena cifrada para Postman
```bash
php scripts/generate_login_payload.php admin admin123
```
Copia la cadena resultante.

## 3) Configurar request
- Método: `POST`
- URL: `http://localhost:8000/app/controllers/api/auth/index.php`
- Header: `Content-Type: text/plain`
- Body (raw): pegar solo la cadena cifrada Base64.

## 4) Respuesta esperada
- `200 OK`: token JWT (`expires_in = 3600`).
- `400`: body vacío o payload inválido.
- `401`: credenciales inválidas.
- `500`: error interno/configuración.
