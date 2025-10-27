# Configuración de Email Centralizada - Ofertópolis

## 📧 Resumen

Se implementó un sistema de configuración de email **centralizado** para facilitar el mantenimiento y deployment del sistema Ofertópolis.

## 🎯 Objetivo

Antes de esta actualización, las credenciales de email estaban duplicadas en 5 archivos diferentes. Ahora están centralizadas en UN solo archivo, lo que facilita:

- ✅ Actualización de credenciales en un solo lugar
- ✅ Deployment más seguro y rápido
- ✅ Mantenimiento simplificado
- ✅ Menos posibilidad de errores

## 📁 Archivo de Configuración

**Ubicación:** `includes/mail_config.php`

### Función Principal: `configurarMail($mail)`

Esta función configura automáticamente el objeto PHPMailer según el entorno:

- **Localhost (Desarrollo):** Usa MailHog en puerto 1025
- **Producción:** Usa Gmail SMTP con credenciales reales

### Credenciales Actuales

```
Email: lusechi3@gmail.com
Contraseña: LasMasLindas
```

⚠️ **IMPORTANTE:** Estas son contraseñas de aplicación de Gmail, NO la contraseña normal de la cuenta.

## 📝 Archivos Actualizados

Los siguientes archivos ahora usan la configuración centralizada:

1. ✅ `duenio/duenio.php` - Creación de promociones por dueños
2. ✅ `duenio/solicitudes.php` - Aprobación de uso de promociones
3. ✅ `admin/admin.php` - Aprobación de dueños y asignación de locales
4. ✅ `admin/promociones.php` - Aprobación/rechazo de promociones
5. ✅ `admin/validar_duenios.php` - Página dedicada de validación de dueños

## 🔧 Cómo Usar en Nuevos Archivos

Si necesitas enviar emails en un nuevo archivo PHP:

```php
<?php
// 1. Incluir la configuración
include_once("../includes/mail_config.php");

// 2. Cargar PHPMailer
require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 3. Crear instancia de PHPMailer
$mail = new PHPMailer(true);

try {
    // 4. Aplicar configuración centralizada
    configurarMail($mail);
    
    // 5. Configurar destinatario y mensaje
    $mail->addAddress('destinatario@example.com', 'Nombre Destinatario');
    $mail->Subject = 'Asunto del email';
    $mail->Body = 'Contenido del mensaje...';
    
    // 6. Enviar
    $mail->send();
    echo "Email enviado exitosamente";
} catch (Exception $e) {
    echo "Error al enviar email: {$mail->ErrorInfo}";
}
?>
```

## 🌐 Detección de Entorno

El sistema detecta automáticamente el entorno mediante:

```php
$_SERVER['SERVER_NAME']
```

- Si es `'localhost'` → Usa MailHog (desarrollo)
- Si es otro valor → Usa Gmail SMTP (producción)

## 📨 Tipos de Emails Enviados

El sistema envía emails en las siguientes situaciones:

### Para Dueños:
1. **Promoción creada:** Notifica al administrador cuando un dueño crea una promoción
2. **Promoción aprobada:** Confirma que su promoción fue aprobada
3. **Promoción rechazada:** Informa que su promoción fue rechazada
4. **Cuenta aprobada:** Bienvenida al aprobar cuenta con locales asignados
5. **Cuenta denegada:** Informa que su solicitud fue rechazada
6. **Locales adicionales:** Notifica cuando se asignan locales adicionales

### Para Clientes:
1. **Solicitud aceptada:** Confirma que puede usar una promoción
2. **Solicitud rechazada:** Informa que su solicitud fue rechazada
3. **Upgrade de categoría:** Notifica cuando alcanza una nueva categoría

### Para Administradores:
1. **Nueva solicitud:** Alerta cuando un cliente solicita usar una promoción
2. **Nueva promoción:** Notifica cuando un dueño crea una promoción (pendiente aprobación)

## 🔐 Seguridad

- ✅ Las credenciales están en UN solo archivo
- ✅ El archivo está en la carpeta `includes/` (no accesible directamente desde web)
- ✅ Usa contraseña de aplicación de Gmail (no la contraseña principal)
- ✅ Conexión TLS encriptada en producción

## 🚀 Deployment a InfinityFree

Cuando despliegues a InfinityFree:

1. ✅ El sistema detectará automáticamente que NO es localhost
2. ✅ Usará automáticamente las credenciales de Gmail configuradas
3. ✅ No necesitas modificar ningún archivo
4. ✅ Solo asegúrate de que `includes/mail_config.php` esté presente

## 🔄 Cambiar Credenciales

Si necesitas cambiar las credenciales de email en el futuro:

1. Editar ÚNICAMENTE el archivo `includes/mail_config.php`
2. Modificar las líneas:
   ```php
   $mail->Username = 'NUEVO_EMAIL@gmail.com';
   $mail->Password = 'NUEVA_CONTRASEÑA_APLICACION';
   ```
3. Guardar el archivo
4. ✅ ¡Listo! Todos los archivos usarán las nuevas credenciales

## 📊 Validación

Todos los archivos fueron validados con PHP lint:

```
✅ includes/mail_config.php - No syntax errors
✅ duenio/duenio.php - No syntax errors
✅ duenio/solicitudes.php - No syntax errors
✅ admin/admin.php - No syntax errors
✅ admin/promociones.php - No syntax errors
✅ admin/validar_duenios.php - No syntax errors
```

## 📅 Fecha de Implementación

**Implementado:** 2025

**Versión:** 1.0

---

## 🆘 Soporte

Si tienes problemas con el envío de emails:

1. Verifica que `includes/mail_config.php` existe
2. Confirma que las credenciales de Gmail son correctas
3. En localhost, asegúrate de que MailHog esté corriendo en puerto 1025
4. En producción, verifica que el hosting permita conexiones SMTP salientes

**Para Gmail:**
- Usa una contraseña de aplicación, no la contraseña normal
- Habilita "Acceso de apps menos seguras" si es necesario
- Verifica que no haya límites de envío activos

---

*Documento generado automáticamente - Ofertópolis Email System v1.0*
