# 🚀 Guía de Inicio Rápido - POS Arepas Boyacenses

## 📋 Requisitos Previos

- **PHP 8.2 o superior** instalado
- **Composer** instalado
- **NO necesitas XAMPP ni MySQL** (usamos SQLite local)

## 🎯 Inicio Rápido (Primera Vez)

### 1. Configurar el Proyecto

```bash
# Instalar dependencias
composer install

# Copiar archivo de configuración
copy .env.sqlite .env

# Generar clave de aplicación
php artisan key:generate
```

### 2. Iniciar el Sistema

**Simplemente haz doble clic en:**
```
start-pos.bat
```

El script automáticamente:
- ✅ Verifica que PHP esté instalado
- ✅ Crea la base de datos SQLite si no existe
- ✅ Ejecuta las migraciones
- ✅ Carga los datos iniciales (seeders)
- ✅ Limpia los caches
- ✅ Abre el navegador automáticamente
- ✅ Inicia el servidor en http://127.0.0.1:8000

## 🔑 Credenciales por Defecto

Revisa el archivo `database/seeders/UserSeeder.php` para las credenciales de administrador.

## 🛑 Detener el Servidor

**Opción 1:** Cierra la ventana del terminal  
**Opción 2:** Ejecuta `stop-pos.bat`

## 📊 Modos de Operación

### 🏠 Modo Local (SQLite)
- Base de datos: `database/database.sqlite`
- Funciona **sin internet**
- Ideal para desarrollo y uso offline

### ☁️ Modo Cloud (Supabase)
- Base de datos: PostgreSQL en Supabase
- Requiere internet
- Configurar variables `CLOUD_DB_*` en `.env`

### 🔄 Sincronización
Para sincronizar datos entre local y cloud:
```
http://127.0.0.1:8000/admin/sync
```

## 📁 Estructura de Archivos

```
pos_arepas/
├── start-pos.bat          ← Inicia el servidor (doble clic)
├── stop-pos.bat           ← Detiene el servidor
├── .env                   ← Configuración (copiar de .env.sqlite)
├── database/
│   └── database.sqlite    ← Base de datos local (se crea automáticamente)
└── storage/
    └── app/public/        ← Imágenes de productos
```

## 🆘 Solución de Problemas

### Error: "PHP no está instalado"
- Descarga e instala PHP 8.2+ desde https://windows.php.net/download/
- Agrega PHP al PATH de Windows

### Error: "composer: command not found"
- Descarga e instala Composer desde https://getcomposer.org/download/

### La base de datos no se crea
- Verifica que la carpeta `database/` existe
- Ejecuta manualmente: `type nul > database\database.sqlite`
- Luego: `php artisan migrate --seed`

### Problemas con permisos
- Ejecuta como Administrador
- Verifica permisos de escritura en `storage/` y `bootstrap/cache/`

## 🔧 Comandos Útiles

```bash
# Limpiar todos los caches
php artisan optimize:clear

# Ver rutas disponibles
php artisan route:list

# Crear un nuevo usuario administrador
php artisan db:seed --class=UserSeeder

# Resetear base de datos (¡CUIDADO! Borra todo)
php artisan migrate:fresh --seed
```

## 📞 Soporte

Para más información, revisa:
- `README.md` - Documentación completa
- `database/seeders/` - Datos iniciales
- `routes/web.php` - Rutas disponibles
