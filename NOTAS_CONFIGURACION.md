# 📝 Notas de Configuración - POS Arepas

## ✅ Cambios Realizados

### 1. Migración a SQLite Local
- ❌ **Antes:** Requería XAMPP + MySQL
- ✅ **Ahora:** SQLite local (sin instalaciones adicionales)
- 📁 **Base de datos:** `database/database.sqlite`

### 2. Scripts de Inicio Mejorados

#### `start-pos.bat`
- ✅ Verifica instalación de PHP
- ✅ Crea base de datos SQLite automáticamente
- ✅ Ejecuta migraciones y seeders en primera ejecución
- ✅ Limpia caches
- ✅ Abre navegador automáticamente
- ✅ Muestra mensajes claros del proceso

#### `stop-pos.bat`
- ✅ Detiene todos los procesos PHP
- ✅ Mensajes de confirmación

#### `crear-acceso-directo.bat` (NUEVO)
- ✅ Crea acceso directo en el escritorio
- ✅ Configurado con descripción y directorio de trabajo

### 3. Archivos de Configuración

#### `.env.sqlite` (NUEVO)
- Plantilla de configuración para SQLite local
- Variables para Supabase (sincronización cloud)
- Sin dependencias de MySQL

#### `INICIO_RAPIDO.md` (NUEVO)
- Guía paso a paso para nuevos usuarios
- Solución de problemas comunes
- Comandos útiles

#### `README.md` (ACTUALIZADO)
- Documentación completa actualizada
- Refleja el uso de SQLite
- Instrucciones claras de instalación

### 4. Ícono del Proyecto
- 🎨 Generado: `pos_arepas_icon.png`
- Diseño profesional con arepa + caja registradora
- Listo para usar como ícono de aplicación

## 🗄️ Configuración de Base de Datos

### SQLite (Local - Por Defecto)
```env
DB_CONNECTION=sqlite
DB_DATABASE=
```
- El path se configura automáticamente a `database/database.sqlite`
- No requiere servidor de base de datos
- Ideal para desarrollo y uso offline

### PostgreSQL (Supabase - Cloud)
```env
CLOUD_DB_CONNECTION=pgsql
CLOUD_DB_HOST=tu-proyecto.supabase.co
CLOUD_DB_PORT=5432
CLOUD_DB_DATABASE=postgres
CLOUD_DB_USERNAME=postgres
CLOUD_DB_PASSWORD=tu-password
```

## 🔄 Sistema de Sincronización

El proyecto incluye un `SyncController` que permite:
- Sincronizar datos entre SQLite local y Supabase
- Acceso: `http://127.0.0.1:8000/admin/sync`
- Servicio: `App\Services\SyncService`

## 📦 Dependencias Clave

```json
{
  "php": "^8.2.0",
  "laravel/framework": "^12.0",
  "spatie/laravel-permission": "^6.0",
  "maatwebsite/excel": "^3.1",
  "barryvdh/laravel-dompdf": "^3.1",
  "picqer/php-barcode-generator": "^3.2",
  "league/flysystem-aws-s3-v3": "^3.30"
}
```

## 🚀 Flujo de Inicio

1. Usuario ejecuta `start-pos.bat` (o acceso directo)
2. Script verifica PHP instalado
3. Si no existe `database.sqlite`:
   - Crea archivo vacío
   - Ejecuta `php artisan migrate --force`
   - Ejecuta `php artisan db:seed --force`
4. Limpia caches de Laravel
5. Inicia servidor en `http://127.0.0.1:8000`
6. Abre navegador automáticamente

## 📊 Estructura de Tablas (36 Migraciones)

### Core
- users, password_resets, failed_jobs, personal_access_tokens

### Permisos (Spatie)
- roles, permissions, model_has_roles, model_has_permissions, role_has_permissions

### Negocio
- empresas, monedas, empleados, cajas, movimientos
- personas, clientes, proveedores, documentos
- categorias, marcas, presentaciones, caracteristicas, productos
- compras, ventas, comprobantes
- compra_producto (pivot), producto_venta (pivot)

### Inventario
- ubicaciones, inventarios, kardexes

### Sistema
- activity_logs, notifications, jobs, sync_states

## 🔐 Sistema de Permisos

Implementado con **Spatie Laravel Permission**:
- Roles configurables
- Permisos granulares por módulo
- Middleware de verificación
- Cache de permisos

## 📁 Almacenamiento de Archivos

### Local (Desarrollo)
```env
FILESYSTEM_DISK=public
```
- Imágenes en `storage/app/public/productos/`
- Enlace simbólico: `php artisan storage:link`

### Cloud (Producción)
```env
FILESYSTEM_DISK=s3
# o
SUPABASE_URL=...
SUPABASE_BUCKET=productos
```

## 🎯 Próximos Pasos

### Completar (30% restante)
- [ ] Optimizar sincronización automática
- [ ] Implementar modo offline completo
- [ ] Mejorar reportes y estadísticas
- [ ] Testing exhaustivo
- [ ] Documentación de API

### Mejoras Sugeridas
- [ ] PWA (Progressive Web App)
- [ ] Notificaciones push
- [ ] Backup automático
- [ ] Multi-tienda
- [ ] App móvil

## 🐛 Problemas Conocidos

### Resueltos
- ✅ Error de impuestos (IVA) - Eliminado completamente
- ✅ Carga de imágenes en Supabase - Corregido
- ✅ Payload too large en Vercel - Configurado `/tmp`
- ✅ Sidebar colapsable - Implementado
- ✅ Redirección después de venta - Corregido

### Por Resolver
- ⚠️ Sistema al 70% funcional (según último commit)
- ⚠️ Sincronización automática pendiente
- ⚠️ Testing completo pendiente

## 📞 Información de Contacto

- **Repositorio:** JohnRojas06289/pos_arepas
- **Versión Actual:** 0.5 (70% funcional)
- **Última Actualización:** 2025-12-03

---

**Notas del Desarrollador:**
Este documento refleja el estado actual del proyecto después de la migración a SQLite local. El sistema ahora es completamente independiente de XAMPP y puede ejecutarse con solo PHP y Composer instalados.
