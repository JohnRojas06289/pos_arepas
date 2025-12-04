# 🛡️ Sistema de Backups Automáticos

## 📋 Resumen

Sistema completo de respaldo para proteger tus datos del POS.

---

## 🔧 Scripts Disponibles

### 1. `backup-database.bat` - Crear Backup Manual
**Uso:** Doble clic en el archivo

**Qué hace:**
- ✅ Crea copia de la base de datos con timestamp
- ✅ Guarda en `database/backups/`
- ✅ Mantiene solo los últimos 10 backups
- ✅ Muestra tamaño del backup

**Ejemplo de nombre:** `backup_20251204_143022.sqlite`

---

### 2. `restore-database.bat` - Restaurar Backup
**Uso:** Doble clic en el archivo

**Qué hace:**
- ✅ Lista todos los backups disponibles
- ✅ Permite seleccionar cuál restaurar
- ✅ Crea backup de seguridad antes de restaurar
- ✅ Confirma antes de sobrescribir

---

## 📅 Backup Automático Diario

### Opción 1: Programador de Tareas de Windows

1. Abre **Programador de tareas** (busca "Task Scheduler")
2. Clic en **Crear tarea básica**
3. Nombre: `Backup POS Arepas`
4. Desencadenador: **Diariamente** a las **11:00 PM**
5. Acción: **Iniciar un programa**
6. Programa: `C:\Users\jhonr\OneDrive\Escritorio\pos_arepas\backup-database.bat`
7. Finalizar

**Resultado:** Backup automático cada noche a las 11 PM

---

### Opción 2: Backup al Cerrar el Sistema

Modifica `stop-pos.bat` para incluir backup automático:

```batch
@echo off
echo Creando backup antes de cerrar...
call backup-database.bat
echo Cerrando servidor...
taskkill /F /IM php.exe
```

---

## 💾 Ubicación de Backups

```
pos_arepas/
├── database/
│   ├── database.sqlite          ← Base de datos activa
│   └── backups/                 ← Carpeta de backups
│       ├── backup_20251204_143022.sqlite
│       ├── backup_20251203_230015.sqlite
│       └── ...
```

---

## 🔄 Estrategia de Respaldo Recomendada

### Backups Locales (Automáticos)
- **Diarios:** 11:00 PM (últimos 10 días)
- **Al cerrar:** Backup automático
- **Antes de cambios:** Backup manual

### Backups Externos (Manuales)
**Frecuencia:** Semanal o mensual

**Método 1 - USB:**
1. Ejecuta `backup-database.bat`
2. Copia `database/backups/` a USB
3. Guarda en lugar seguro

**Método 2 - Nube:**
1. Ejecuta `backup-database.bat`
2. Sube `database/backups/` a Google Drive/OneDrive
3. Mantén versiones históricas

---

## ⚠️ Buenas Prácticas

### ✅ HACER:
- Crear backup antes de actualizaciones
- Crear backup antes de migraciones
- Probar restauración periódicamente
- Mantener backups en múltiples ubicaciones
- Verificar que los backups no estén corruptos

### ❌ NO HACER:
- Confiar solo en backups locales
- Eliminar backups manualmente sin revisar
- Restaurar sin crear backup de seguridad
- Ignorar errores en el proceso de backup

---

## 🚨 Recuperación de Desastres

### Si perdiste datos:

1. **Detén el servidor** (`stop-pos.bat`)
2. **Ejecuta** `restore-database.bat`
3. **Selecciona** el backup más reciente
4. **Confirma** la restauración
5. **Inicia** el servidor (`start-pos.bat`)
6. **Verifica** que los datos estén correctos

### Si no hay backups:
- Los datos se perdieron permanentemente
- **Prevención:** Configura backups automáticos HOY

---

## 📊 Monitoreo de Backups

### Verificar último backup:
```batch
dir /o-d database\backups\backup_*.sqlite
```

### Ver tamaño de backups:
```batch
dir database\backups\backup_*.sqlite
```

---

## 🔐 Seguridad

### Proteger backups:
1. No compartas archivos `.sqlite` públicamente
2. Encripta backups externos (7-Zip con contraseña)
3. Mantén backups offline (USB desconectado)
4. Limita acceso a la carpeta `backups/`

---

## ❓ Preguntas Frecuentes

**P: ¿Cuánto espacio ocupan los backups?**  
R: Cada backup es ~500 KB - 5 MB. 10 backups = ~5-50 MB

**P: ¿Puedo hacer backup mientras el sistema está corriendo?**  
R: Sí, pero es mejor cerrar el sistema primero para evitar inconsistencias

**P: ¿Los backups incluyen imágenes de productos?**  
R: No, solo la base de datos. Respalda `storage/app/public/productos/` por separado

**P: ¿Qué pasa si restauro un backup antiguo?**  
R: Perderás todos los cambios posteriores a ese backup

---

## 📞 Soporte

Si tienes problemas con los backups:
1. Verifica que existe `database/database.sqlite`
2. Revisa permisos de la carpeta `database/`
3. Ejecuta los scripts como administrador si hay errores

---

**Última actualización:** 2025-12-04  
**Versión:** 1.0
