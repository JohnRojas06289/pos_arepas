# 🔑 Credenciales de Acceso - POS Arepas

## 👤 Usuario Administrador

**Email:** `admin@gmail.com`  
**Contraseña:** `12345678`  
**Nombre:** Sak Noel  
**Rol:** Administrador (acceso completo)

---

## 🌐 URL de Acceso

**Local:** http://127.0.0.1:8000

---

## ⚠️ Importante

### Cambiar Contraseña
Por seguridad, se recomienda cambiar la contraseña después del primer inicio de sesión:

1. Inicia sesión con las credenciales anteriores
2. Ve a tu perfil (esquina superior derecha)
3. Cambia la contraseña

### Crear Nuevos Usuarios
Como administrador, puedes crear nuevos usuarios desde:
- **Ruta:** `/admin/users`
- **Menú:** Usuarios → Crear Usuario

### Roles Disponibles
El sistema incluye un sistema de roles y permisos completo:
- **Administrador:** Acceso total al sistema
- Puedes crear roles personalizados desde `/admin/roles`

---

## 📊 Datos Iniciales Cargados

El sistema ha cargado los siguientes datos iniciales:

✅ **Documentos** - Tipos de documentos (DNI, RUC, etc.)  
✅ **Comprobantes** - Tipos de comprobantes de venta  
✅ **Permisos** - Sistema completo de permisos  
✅ **Usuario Admin** - Usuario administrador principal  
✅ **Ubicaciones** - Ubicaciones de inventario  
✅ **Monedas** - COP (Peso Colombiano) y USD  
✅ **Empresa** - Datos de la empresa

---

## 🔐 Seguridad

- Las contraseñas están encriptadas con bcrypt
- El sistema usa Laravel Sanctum para autenticación
- Spatie Permission para control de acceso basado en roles
- Registro de actividades (Activity Log) habilitado

---

## 📝 Notas

- **Base de Datos:** SQLite local (`database/database.sqlite`)
- **Modo:** Offline (no requiere internet)
- **Sincronización Cloud:** Opcional (configurar variables CLOUD_DB_* en .env)

---

**Fecha de Creación:** 2025-12-04  
**Sistema:** POS Arepas Boyacenses v0.5
