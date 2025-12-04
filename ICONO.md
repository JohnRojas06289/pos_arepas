# 🎨 Cómo Usar el Ícono del Sistema

## 📁 Archivos de Ícono

El proyecto incluye un ícono personalizado:

- **Archivo PNG:** `pos_arepas_icon.png` (512x512px)
- **Ubicación:** Raíz del proyecto

![Ícono POS Arepas](pos_arepas_icon.png)

---

## 🔗 Aplicar Ícono al Acceso Directo

### Opción 1: Automática (Recomendada)
1. Ejecuta el script `crear-acceso-directo.bat`
2. El ícono se aplicará automáticamente

### Opción 2: Manual
1. Haz clic derecho en el acceso directo "POS Arepas"
2. Selecciona **Propiedades**
3. En la pestaña **Acceso directo**, haz clic en **Cambiar icono...**
4. Haz clic en **Examinar...**
5. Navega a la carpeta del proyecto
6. Selecciona `pos_arepas_icon.png`
7. Haz clic en **Aceptar**

---

## 🖼️ Convertir a ICO (Opcional)

Windows funciona mejor con archivos `.ico`. Para convertir el PNG a ICO:

### Usando una herramienta online:
1. Ve a https://convertio.co/es/png-ico/
2. Sube `pos_arepas_icon.png`
3. Descarga el archivo `.ico`
4. Guárdalo como `pos_arepas_icon.ico` en la raíz del proyecto

### Usando PowerShell (requiere .NET):
```powershell
# Este script convierte PNG a ICO
Add-Type -AssemblyName System.Drawing
$img = [System.Drawing.Image]::FromFile("$PWD\pos_arepas_icon.png")
$icon = [System.Drawing.Icon]::FromHandle($img.GetHicon())
$stream = [System.IO.File]::Create("$PWD\pos_arepas_icon.ico")
$icon.Save($stream)
$stream.Close()
```

---

## 🎯 Usar en la Aplicación Web

Para usar el ícono como favicon en el navegador:

1. Copia el ícono a `public/`:
   ```bash
   copy pos_arepas_icon.png public\favicon.png
   ```

2. Edita `resources\views\layouts\app.blade.php` y agrega:
   ```html
   <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
   ```

---

## 📝 Características del Ícono

- **Diseño:** Arepa estilizada + caja registradora
- **Colores:** Dorado, naranja, marrón (colores cálidos)
- **Estilo:** Flat design con gradientes sutiles
- **Tamaño:** 512x512px (alta resolución)
- **Formato:** PNG con transparencia

---

## 🔄 Regenerar el Acceso Directo con Ícono

Si ya creaste el acceso directo antes, simplemente:

1. Elimina el acceso directo actual del escritorio
2. Ejecuta nuevamente `crear-acceso-directo.bat`
3. El nuevo acceso directo tendrá el ícono aplicado

---

**Nota:** Windows puede tardar unos segundos en actualizar el ícono en el escritorio. Si no lo ves inmediatamente, presiona F5 para refrescar el escritorio.
