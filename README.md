# 🌮 POS Arepas Boyacenses

Sistema de Punto de Venta completo para restaurante de arepas, con soporte para modo offline (SQLite) y sincronización cloud (Supabase).

![POS Arepas Icon](pos_arepas_icon_1764822030627.png)

## ✨ Características Principales

- 🏪 **Punto de Venta Completo** - Interfaz rápida e intuitiva
- 📦 **Gestión de Inventario** - Control de stock en tiempo real
- 👥 **Clientes y Proveedores** - Base de datos completa
- 💰 **Control de Caja** - Apertura, cierre y movimientos
- 📊 **Dashboard Analítico** - Métricas y estadísticas
- 🔐 **Roles y Permisos** - Sistema de seguridad robusto
- 📱 **Modo Offline** - Funciona sin internet (SQLite)
- ☁️ **Sincronización Cloud** - Backup automático en Supabase
- 📄 **Reportes PDF/Excel** - Exportación de datos
- 🔔 **Notificaciones** - Alertas de stock bajo

## 🚀 Inicio Rápido

### Iniciar el Servidor

```bash
# Iniciar el servidor de desarrollo
C:\xampp\php\php.exe artisan serve
```

Luego abre tu navegador en: **http://127.0.0.1:8000**

## 📋 Requisitos

- ✅ **PHP 8.2+** (XAMPP recomendado) - [Descargar](https://www.apachefriends.org/download.html)
- ✅ **Composer** - [Descargar](https://getcomposer.org/download/)
- ✅ **Extensiones PHP requeridas:** `zip`, `gd`, `pdo_sqlite`

## 🔧 Instalación (Primera Vez)

### 1. Instalar dependencias

```bash
# Si no tienes composer en PATH, descarga composer.phar
powershell -Command "Invoke-WebRequest -Uri https://getcomposer.org/composer.phar -OutFile composer.phar"

# Instalar dependencias (ignorar requisitos de plataforma si es necesario)
C:\xampp\php\php.exe composer.phar install --ignore-platform-reqs
```

### 2. Configurar entorno

```bash
# Copiar archivo de configuración
copy .env.example .env
```

Edita el archivo `.env` y asegúrate de tener:
```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=sqlite
```

### 3. Habilitar extensiones PHP

Edita `C:\xampp\php\php.ini` y descomenta (quita el `;`):
```ini
extension=zip
extension=gd
extension=pdo_sqlite
```

### 4. Generar clave y base de datos

```bash
# Generar clave de aplicación
C:\xampp\php\php.exe artisan key:generate

# Crear base de datos y cargar datos iniciales
C:\xampp\php\php.exe artisan migrate:fresh --seed
```

### 5. Iniciar servidor

```bash
C:\xampp\php\php.exe artisan serve
```

¡Listo! Abre **http://127.0.0.1:8000** en tu navegador.

## 🗄️ Arquitectura de Base de Datos

### Modo Local (Por Defecto)
- **Motor:** SQLite
- **Archivo:** `database/database.sqlite`
- **Ventajas:** Sin configuración, funciona offline

### Modo Cloud (Opcional)
- **Motor:** PostgreSQL (Supabase)
- **Configuración:** Variables `CLOUD_DB_*` en `.env`
- **Ventajas:** Backup automático, acceso remoto

### Sincronización
```
http://127.0.0.1:8000/admin/sync
```

## 📁 Estructura del Proyecto

```
pos_arepas/
├── 🚀 start-pos.bat              # Inicia el servidor
├── 🛑 stop-pos.bat               # Detiene el servidor
├── 🔗 crear-acceso-directo.bat   # Crea ícono en escritorio
├── 📖 INICIO_RAPIDO.md           # Guía rápida
├── ⚙️ .env.sqlite                # Configuración SQLite
├── 📦 app/                       # Código de la aplicación
│   ├── Http/Controllers/        # Controladores
│   ├── Models/                  # Modelos de datos
│   ├── Services/                # Lógica de negocio
│   └── ...
├── 🗄️ database/
│   ├── database.sqlite          # Base de datos local
│   ├── migrations/              # Esquema de BD
│   └── seeders/                 # Datos iniciales
├── 🎨 resources/
│   └── views/                   # Plantillas Blade
├── 🌐 routes/
│   └── web.php                  # Rutas de la aplicación
└── 💾 storage/
    └── app/public/              # Imágenes de productos
```

## 🎯 Módulos Disponibles

| Módulo | Ruta | Descripción |
|--------|------|-------------|
| 🏠 Dashboard | `/` | Panel principal con métricas |
| 🛒 Ventas | `/admin/ventas` | Punto de venta |
| 📦 Productos | `/admin/productos` | Gestión de productos |
| 👥 Clientes | `/admin/clientes` | Base de clientes |
| 🏢 Proveedores | `/admin/proveedores` | Gestión de proveedores |
| 📥 Compras | `/admin/compras` | Registro de compras |
| 📊 Inventario | `/admin/inventario` | Control de stock |
| 💰 Cajas | `/admin/cajas` | Apertura/cierre de caja |
| 💸 Movimientos | `/admin/movimientos` | Movimientos de efectivo |
| 👤 Usuarios | `/admin/users` | Gestión de usuarios |
| 🔐 Roles | `/admin/roles` | Roles y permisos |
| 🏢 Empresa | `/admin/empresa` | Configuración |
| 📝 Actividad | `/admin/activityLog` | Registro de actividades |

## 🔑 Credenciales por Defecto

Revisa `database/seeders/UserSeeder.php` para las credenciales de administrador.

## 🛠️ Comandos Útiles

```bash
# Limpiar caches
php artisan optimize:clear

# Ver todas las rutas
php artisan route:list

# Resetear base de datos (¡CUIDADO!)
php artisan migrate:fresh --seed

# Ejecutar migraciones
php artisan migrate

# Crear enlace simbólico para storage
php artisan storage:link
```

## 🌐 Deployment en Vercel

El proyecto está configurado para deployment automático en Vercel:

1. Conecta tu repositorio de GitHub
2. Configura las variables de entorno en Vercel
3. Deploy automático en cada push

**Configuración:** `vercel.json`

## 🔄 Sistema de Sincronización

El sistema puede trabajar en dos modos:

1. **Solo Local (SQLite)** - Sin internet
2. **Híbrido (SQLite + Supabase)** - Con sincronización

Para habilitar sincronización, configura las variables `CLOUD_DB_*` en `.env`.

## 📊 Tecnologías Utilizadas

- **Backend:** Laravel 12.0
- **Base de Datos:** SQLite (local) / PostgreSQL (cloud)
- **Frontend:** Blade Templates + Vite
- **Permisos:** Spatie Laravel Permission
- **PDFs:** DomPDF
- **Excel:** Maatwebsite Excel
- **Códigos de Barras:** Picqer Barcode Generator
- **Storage:** Supabase Storage / AWS S3

## 🆘 Solución de Problemas

### El script no inicia
- Verifica que PHP esté instalado: `php -v`
- Ejecuta como Administrador

### Error de permisos
```bash
# Dar permisos a carpetas
icacls storage /grant Users:F /T
icacls bootstrap\cache /grant Users:F /T
```

### Base de datos no se crea
```bash
# Crear manualmente
type nul > database\database.sqlite
php artisan migrate --seed
```

### Problemas con imágenes
```bash
# Recrear enlace simbólico
php artisan storage:link
```

## 📝 Changelog

### v0.5 (Actual)
- ✅ Sistema base funcional (70%)
- ✅ Migración a SQLite local
- ✅ Eliminación de sistema de impuestos
- ✅ Corrección de carga de imágenes
- ✅ Dashboard mejorado
- ✅ Sistema de sincronización

### Próximas Versiones
- 🔄 Completar funcionalidades restantes (30%)
- 🔄 Modo offline completo
- 🔄 Sincronización automática
- 🔄 Reportes avanzados

## 📄 Licencia

Este proyecto está licenciado bajo la Licencia MIT. Ver [LICENSE](LICENSE) para más información.

## 🤝 Contribuir

Las contribuciones son bienvenidas. Por favor:

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📞 Soporte

Para más información:
- 📖 [Guía de Inicio Rápido](INICIO_RAPIDO.md)
- 🌐 [Documentación Original](https://universityproyectx.blogspot.com/2022/10/sistema-de-ventas-web-minersa-srl.html)
- ❓ [FAQ](https://universityproyectx.blogspot.com/2023/06/faq-sobre-el-sistema-de-ventas-de.html)

---

**Desarrollado con ❤️ para Arepas Boyacenses**
