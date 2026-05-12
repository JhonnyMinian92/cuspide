# Huaquillas F.C. — Panel de Administración

Panel de administración web para **Huaquillas Fútbol Club**, construido con **HTML5 puro + Bootstrap 5**. Sin frameworks de JavaScript (React/Vue) ni código PHP. La lógica de negocio se simula únicamente mediante la interfaz (modales, alertas y navegación).

---

## 📁 Estructura del Proyecto

```
/
├── assets/
│   ├── css/
│   │   └── styles.css          ← Estilos globales, variables de tema y botones corporativos
│   ├── js/
│   │   └── theme.js            ← Lógica de cambio de tema (sidebar claro/oscuro)
│   └── images/
│       └── logo-placeholder.svg ← SVG con texto "HFC" (logo del club)
├── index.html                  ← Login (pantalla de acceso)
├── dashboard.html              ← Panel principal con tarjetas, gráficos y tabla
├── alumnos.html                ← Gestión de alumnos
├── membresias.html             ← Gestión de membresías
├── inventario.html             ← Control de inventario
├── categorias.html             ← Categorías (Sub 7 – Sub 17, etc.)
├── configuracion.html          ← Configuración del sistema y gestión de usuarios
└── README.md                   ← Este archivo
```

---

## 🎨 Cómo Cambiar Temas

Los colores del sidebar y la interfaz se controlan mediante variables CSS en `assets/css/styles.css`:

```css
:root {
  --sidebar-bg: #1a2235;       /* Fondo sidebar tema oscuro */
  --sidebar-text: #c8d3e0;     /* Texto sidebar tema oscuro */
  --sidebar-active: #e8b84b;   /* Color ítem activo (dorado HFC) */
  --brand-color: #1a56db;      /* Color corporativo principal */
  --brand-hover: #1648c0;      /* Hover del color corporativo */
}
```

Para el **tema claro**, se aplica la clase `theme-light` al `<body>`, sobreescribiendo las variables:

```css
body.theme-light .sidebar {
  --sidebar-bg: #f0f4f8;
  --sidebar-text: #2d3748;
}
```

El botón de luna/sol en la topbar alterna el tema y lo guarda en `localStorage` para persistir entre páginas.

---

## 🖌️ Cómo Personalizar Colores de Botones

Todos los botones corporativos usan la clase `.btn-brand`. Para cambiar el color de **todos los botones a la vez**, edita únicamente las variables en `assets/css/styles.css`:

```css
:root {
  --brand-color: #1a56db;  /* ← Cambia este hex por tu color corporativo */
  --brand-hover: #1648c0;  /* ← Y este para el estado hover */
}
```

También puedes aplicar un color hex directamente en HTML (sin modificar el CSS):

```html
<!-- Color hex directo (dorado HFC) -->
<button class="btn" style="background-color:#e8b84b; color:#1a2235; border:none;">
  Mi Botón Dorado
</button>

<!-- Botón outline corporativo -->
<button class="btn btn-brand-outline">Botón Outline</button>
```

---

## 🔧 Cómo Integrar con PHP en el Futuro

Este proyecto está diseñado para ser fácilmente migrable a PHP:

### 1. Convertir archivos `.html` a `.php`
Simplemente renombra cada archivo:
```
index.html       → index.php
dashboard.html   → dashboard.php
alumnos.html     → alumnos.php
...
```

### 2. Extraer componentes comunes
El sidebar y la navbar pueden moverse a archivos incluibles:

```
includes/
├── sidebar.php   ← <nav class="sidebar">...</nav>
└── navbar.php    ← <div class="topbar">...</div>
```

Y luego incluirlos en cada página:

```php
<?php include 'includes/sidebar.php'; ?>
<?php include 'includes/navbar.php'; ?>
```

### 3. Conectar formularios a controladores PHP
Los formularios actualmente son estáticos. Para activarlos con PHP:

```html
<!-- Antes (simulado) -->
<form>

<!-- Después (con PHP) -->
<form method="post" action="controllers/guardar_alumno.php">
```

Los campos con `name=""` se reciben en PHP con `$_POST['campo']`.

### 4. Los CDN de Bootstrap no interfieren con PHP
Los enlaces CDN en el `<head>` funcionan exactamente igual en archivos `.php`:

```html
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
```

---

## 📦 Clases Bootstrap 5 Principales Usadas

| Categoría | Clases |
|-----------|--------|
| Layout | `container-fluid`, `row`, `col-*`, `d-flex`, `gap-*` |
| Cards | `card`, `card-body`, `card-title` |
| Tablas | `table`, `table-hover`, `table-responsive`, `align-middle` |
| Modales | `modal`, `modal-dialog`, `modal-content`, `modal-header`, `modal-body`, `modal-footer` |
| Offcanvas | `offcanvas`, `offcanvas-start` |
| Badges | `badge`, `bg-success`, `bg-danger`, `bg-warning` |
| Alertas | `alert`, `alert-success`, `d-none` |
| Botones | `btn`, `btn-brand`, `btn-secondary`, `btn-outline-primary` |
| Formularios | `form-control`, `form-select`, `form-check`, `form-label` |
| Navegación | `nav`, `nav-link`, `navbar`, `dropdown`, `dropdown-menu` |
| Utilidades | `fw-bold`, `fw-semibold`, `text-muted`, `text-danger`, `rounded`, `shadow-sm` |

---

## 🚀 Tecnologías

- **Bootstrap 5.3.3** (vía CDN oficial)
- **Bootstrap Icons 1.11.3** (vía CDN)
- **HTML5** puro
- **CSS3** con variables custom (`--var`)
- **JavaScript** vanilla mínimo (tema y modales)

---

## 📋 Páginas Disponibles

| Página | Descripción |
|--------|-------------|
| `index.html` | Login con toggle de contraseña |
| `dashboard.html` | Panel principal: tarjetas, gráficos SVG, tabla de vencimientos |
| `alumnos.html` | Lista de alumnos con modal para añadir |
| `membresias.html` | Lista de membresías con modal para añadir |
| `inventario.html` | Inventario de equipamiento con modal para añadir |
| `categorias.html` | Categorías por edad con selector de color |
| `configuracion.html` | Cuenta, preferencias, gestión de usuarios, personalización de botones |
