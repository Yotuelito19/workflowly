# 🚀 Guía de Instalación - WorkFlowly

## 📋 Requisitos previos

- **XAMPP** instalado (Apache + PHP 7.4+)
- **MySQL Workbench** instalado
- Navegador web Chrome

---

## 📂 Estructura del proyecto

```
C:\Sourcetree\WorkFlowly\
│
├── .htaccess                    # Configuración Apache
├── index.php                    # Página principal
├── GUIA-INSTALACION.md          # Este documento
├── test-navigation.html         # Test de navegación
├── test_mail.php                # Test de envío de correos
│
├── api/
│   ├── acciones_cuenta.php      # Acciones de cuenta de usuario
│   ├── favoritos.php            # Gestión de favoritos
│   ├── login.php                # API de login
│   ├── logout.php               # API para cerrar sesión
│   ├── metodos-pago.php         # Métodos de pago
│   ├── register.php             # API de registro
│   ├── _utils_upload.php        # Utilidades para subida de archivos
│   │
│   ├── admin/
│   │   ├── events/
│   │   │   ├── create.php       # Crear evento
│   │   │   ├── delete.php       # Eliminar evento
│   │   │   ├── inactivate.php   # Inactivar evento
│   │   │   ├── update.php       # Actualizar evento
│   │   │   └── uploads/         # Imágenes subidas de eventos
│   │   │
│   │   ├── lugares/
│   │   │   ├── crear.php        # Crear lugar
│   │   │   └── listar.php       # Listar lugares
│   │   │
│   │   ├── organizadores/
│   │   │   └── listar.php       # Listar organizadores
│   │   │
│   │   └── tipos/
│   │       ├── crear.php        # Crear tipo de entrada
│   │       ├── eliminar.php     # Eliminar tipo de entrada
│   │       ├── listar.php       # Listar tipos de entrada
│   │       └── upsert.php       # Crear o actualizar tipo
│   │
│   └── contact/
│       └── contact_organizer.php # Contactar con organizador
│
├── assets/
│   ├── css/
│   │   ├── account.css          # Estilos cuenta usuario
│   │   ├── admin.css            # Estilos panel admin
│   │   ├── checkout.css         # Estilos checkout
│   │   ├── confirmation.css     # Estilos confirmación
│   │   ├── event-detail.css     # Estilos detalle evento
│   │   ├── footer.css           # Estilos footer
│   │   ├── header.css           # Estilos header
│   │   ├── inicio.css           # Estilos página inicio
│   │   ├── login.css            # Estilos login/registro
│   │   └── search-events.css    # Estilos búsqueda
│   │
│   ├── images/
│   │   ├── carousel-1.jpg       # Imágenes del carrusel
│   │   ├── carousel-2.jpg
│   │   ├── carousel-3.jpg
│   │   ├── carousel-4.jpg
│   │   ├── carousel-5.jpg
│   │   ├── logo.png             # Logo de WorkFlowly
│   │   └── LEEME.txt            # Instrucciones imágenes
│   │
│   └── js/
│       ├── main.js              # JavaScript principal
│       └── payments.js          # JavaScript de pagos
│
├── config/
│   ├── config.php               # Configuración general
│   └── database.php             # Conexión a base de datos
│
├── database/
│   ├── workflowly.sql           # Script para crear la base de datos
│   └── migrations/              # Migraciones de BBDD
│
├── includes/
│   ├── footer.php               # Footer reutilizable
│   └── header.php               # Header reutilizable
│
├── models/
│   ├── Compra.php               # Modelo de compras
│   ├── Evento.php               # Modelo de eventos
│   └── Usuario.php              # Modelo de usuarios
│
├── tests/
│   └── resultados/              # Resultados de pruebas
│
└── views/
    ├── account.php              # Cuenta de usuario
    ├── checkout.php             # Proceso de compra
    ├── confirmation.php         # Confirmación de compra
    ├── event-detail.php         # Detalle de evento
    ├── index.php                # Vista principal
    ├── login.php                # Página de login/registro
    ├── search-events.php        # Búsqueda de eventos
    │
    └── admin/
        └── events.php           # Gestión de eventos (admin)
```

---

## 🛠️ Pasos de instalación

### 1️⃣ **Copiar el proyecto**

Copiar toda la carpeta de `C:\Sourcetree\WorkFlowly\` a la de htdocs en el XAMPP para poder lanzar el proyecto

### 2️⃣ **Iniciar Apache en XAMPP**

1. Abrir **XAMPP Control Panel**
2. Click en **Start** solo en **Apache** ✅
3. **NO iniciar MySQL** (lo gestionamos desde Workbench)

### 3️⃣ **Configurar MySQL Workbench**

1. Abrir **MySQL Workbench**
2. Click en el **+** junto a "MySQL Connections"
3. Crear nueva conexión:
   - **Connection Name:** `WorkFlowly`
   - **Hostname:** `localhost`
   - **Port:** `3306`
   - **Username:** `root`
   - **Password:** `WorkFlowly`
4. Click en **Test Connection**
   - Debe decir "Successfully made the MySQL connection"
   - Si funciona → **OK**
   - Si falla → Verificar que MySQL está instalado
   - No debería fallar debido a que es un script de la BBDD que se usó en la versión DEMO. La mas estable

### 4️⃣ **Crear la base de datos**

1. Hacer **doble click** en la conexión `WorkFlowly`
2. Click en **File** → **Open SQL Script**
3. Buscar: `C:\Sourcetree\WorkFlowly\database\workflowly.sql`
4. Click en el **rayo** (Execute)
5. Esperar a que termine (unos segundos)
6. Verás mensajes de éxito al final

---

## Probar la instalación

### 1. **Página principal**
Abrir en el navegador:
```
http://localhost/workflowly
```
Debes ver los eventos con sus imágenes y precios

### 2. **Crear usuario**
```
http://localhost/workflowly/views/login.php
```
1. Click en **"Regístrate aquí"**
2. Completar el formulario
3. **Registrar**
4. **Iniciar sesión** con ese usuario

### 3. **Ver detalle de evento**
1. Click en cualquier evento
2. Deben aparecer los tipos de entrada con precios

### 4. **Panel de administración**
```

## Problemas comunes

### Error: "Failed to connect to database"

**Causa:** MySQL no está corriendo desde Workbench  
**Solución:**
1. Abrir MySQL Workbench
2. Conectar a `WorkFlowly`
3. MySQL debe estar corriendo en segundo plano

**Causa alternativa:** Contraseña incorrecta en config  
**Solución:** Verificar que en `config/database.php` la contraseña sea `WorkFlowly`

### Error: Apache no inicia en XAMPP

**Causa:** Puerto 80 ocupado (Skype, IIS, otro programa)  
**Solución:**
1. Cerrar programas que usen puerto 80
2. O en XAMPP → Config (Apache) → httpd.conf
3. Cambiar `Listen 80` por `Listen 8080`
4. Reiniciar Apache
5. Acceder a `http://localhost:8080/workflowly`
6. Ejecutar estos comandos abriendo CMD como administrador.
   Es para matar procesos que puedan estar empleando los puertos en uso:
   - net stop w3svc
   - net stop was

### No aparecen eventos en la página

**Causa:** La base de datos no se creó correctamente  
**Solución:**
1. Abrir MySQL Workbench
2. Conectar a `WorkFlowly`
3. Ejecutar: `SELECT * FROM Evento;`
4. Si no aparece nada → Ejecutar el SQL de nuevo y revisar errores en el log

### Error al registrar usuario

**Causa:** Archivos PHP desactualizados  
**Solución:**
Verificar que tienes las versiones corregidas de:
- `models/Usuario.php`
- `views/login.php`
- `api/register.php`

### Error al ver detalle de evento

**Causa:** Archivos PHP desactualizados  
**Solución:**
Verificar que tienes las versiones corregidas de:
- `models/Evento.php`
- `views/event-detail.php`

### Error al subir imágenes de eventos

**Causa:** Permisos de escritura en carpeta uploads  
**Solución:**
Verificar que la carpeta `api/admin/events/uploads/` tiene permisos de escritura

---

## Datos de prueba incluidos

El script SQL crea automáticamente:

### 4 Eventos (siempre en fechas futuras):
1. Concierto Rock Madrid - En 2 meses
2. Festival Electrónico Summer - En 3 meses  
3. Teatro Musical: El Rey Le