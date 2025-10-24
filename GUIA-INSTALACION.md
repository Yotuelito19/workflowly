# 🚀 Guía de Instalación - WorkFlowly

## 📋 Requisitos previos

- **XAMPP** instalado (Apache + PHP 7.4+)
- **MySQL Workbench** instalado
- Navegador web moderno

---

## 📂 Estructura del proyecto

```
C:\xampp\htdocs\workflowly\
│
├── database/
│   └── workflowly.sql           # Script para crear la base de datos
│
├── config/
│   ├── config.php               # Configuración general
│   ├── database.php             # Conexión a base de datos
│   └── admin_auth.php           # Autenticación admin (opcional)
│
├── models/
│   ├── Usuario.php              # Modelo de usuarios
│   ├── Evento.php               # Modelo de eventos
│   └── ...                      # Otros modelos
│
├── views/
│   ├── login.php                # Página de login/registro
│   ├── event-detail.php         # Detalle de evento
│   ├── search-events.php        # Búsqueda de eventos
│   ├── checkout.php             # Proceso de compra
│   ├── confirmation.php         # Confirmación de compra
│   ├── account.php              # Cuenta de usuario
│   └── ...                      # Otras vistas
│
├── assets/
│   ├── css/
│   │   ├── login.css
│   │   ├── event-detail.css
│   │   ├── search-events.css
│   │   ├── checkout.css
│   │   ├── confirmation.css
│   │   ├── account.css
│   │   └── inicio.css
│   └── images/                  # Imágenes del sitio
│
├── api/
│   └── logout.php               # API para cerrar sesión
│
└── index.php                    # Página principal
```

---

## 🛠️ Pasos de instalación

### 1️⃣ **Copiar el proyecto**

Copiar toda la carpeta a: `C:\xampp\htdocs\workflowly\`

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
   - **Password:** (dejar vacío o tu password)
4. Click en **Test Connection**
   - Debe decir "Successfully made the MySQL connection"
   - ✅ Si funciona → **OK**
   - ❌ Si falla → Verificar que MySQL está instalado

### 4️⃣ **Crear la base de datos**

1. Hacer **doble click** en la conexión `WorkFlowly`
2. Click en **File** → **Open SQL Script**
3. Buscar: `C:\xampp\htdocs\workflowly\database\workflowly.sql`
4. Click en el **rayo** ⚡ (Execute)
5. Esperar a que termine (unos segundos)
6. ✅ Verás mensajes de éxito al final

---

## 🧪 Probar la instalación

### 1. **Página principal**
Abrir en el navegador:
```
http://localhost/workflowly
```
✅ Debes ver 4 eventos con sus imágenes y precios

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
2. ✅ Deben aparecer los tipos de entrada con precios

---

## ❌ Problemas comunes

### 🔴 Error: "Failed to connect to database"

**Causa:** MySQL no está corriendo desde Workbench  
**Solución:**
1. Abrir MySQL Workbench
2. Conectar a `WorkFlowly`
3. MySQL debe estar corriendo en segundo plano

### 🔴 Error: Apache no inicia en XAMPP

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

### 🔴 No aparecen eventos en la página

**Causa:** La base de datos no se creó correctamente  
**Solución:**
1. Abrir MySQL Workbench
2. Conectar a `WorkFlowly`
3. Ejecutar: `SELECT * FROM Evento;`
4. Si no aparece nada → Ejecutar el SQL de nuevo y revisar errores en el log

### 🔴 Error al registrar usuario

**Causa:** Archivos PHP desactualizados  
**Solución:**
Verificar que tienes las versiones corregidas de:
- `models/Usuario.php`
- `views/login.php`

### 🔴 Error al ver detalle de evento

**Causa:** Archivos PHP desactualizados  
**Solución:**
Verificar que tienes las versiones corregidas de:
- `models/Evento.php`
- `views/event-detail.php`

---

## 🎯 Datos de prueba incluidos

El script SQL crea automáticamente:

   ### 📅 **4 Eventos** (siempre en fechas futuras):
   1. Concierto Rock Madrid - En 2 meses
   2. Festival Electrónico Summer - En 3 meses  
   3. Teatro Musical: El Rey León - En 1 mes
   4. Copa del Rey - Final - En 4 meses

   ### 🎫 **11 Tipos de entrada**:
   - Concierto: General (45€), VIP (150€), Palco (300€)
   - Festival: 1 Día (55€), Completo (90€), VIP Weekend (250€)
   - Teatro: Platea (80€), Anfiteatro (50€)
   - Fútbol: Gradas (60€), Preferente (180€), Palco (500€)

   ### 👤 **Usuario Administrador** (opcional):
   - Email: `admin@workflowly.com`
   - Password: `12345678`
   - Tipo: Organizador

---

## ✅ Checklist rápido

- [ ] Proyecto en `C:\xampp\htdocs\workflowly\`
- [ ] Apache iniciado en XAMPP (✅ verde)
- [ ] MySQL Workbench con conexión `WorkFlowly` creada
- [ ] Test Connection exitoso
- [ ] Script SQL ejecutado sin errores
- [ ] `http://localhost/workflowly` muestra eventos
- [ ] Puedo registrarme y hacer login
- [ ] Puedo ver detalle de eventos

---

## 🔧 Comandos SQL útiles

### Ver todos los eventos:
```sql
SELECT * FROM Evento;
```

### Ver todos los usuarios:
```sql
SELECT * FROM Usuario;
```

### Borrar todo y empezar de cero:
```sql
DROP DATABASE workflowly;
```
Luego ejecutar el script `workflowly.sql` de nuevo.

---

**🎉 ¡Listo para desarrollar!**

Si algo no funciona, revisar la sección de **Problemas comunes** ☝️

**IMPORTANTE**
Una vez que tengais todo lanzado y podáis acceder al entorno, comienza la fase de pruebas y arreglos:
 - Cada vez quye se vea un bug, se reporta en trello
 - Cada vez que se vaya a hacer un nuevo desarrollo, se pone en trello
Va a haber muchos errores de front o cosas por desarrollar, como crear un gestor en el front para admins que cree eventos.
Queda trabajo por delante para que creemos chachi WorkFlowly
