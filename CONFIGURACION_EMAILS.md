# Sistema de Emails - Ofertópolis

## Estado Actual del Sistema

### Arquitectura
Tu aplicación usa **PHPMailer** (instalado via Composer) para enviar emails. Este es un sistema robusto y ampliamente usado.

### Configuración Dual (Localhost vs Producción)

Actualmente el código tiene una **detección automática de entorno**:

```php
if ($_SERVER['SERVER_NAME'] == 'localhost') {
    // LOCALHOST: MailHog (servidor de pruebas)
    $mail->isSMTP();
    $mail->Host = 'localhost';
    $mail->Port = 1025;
    $mail->SMTPAuth = false;
    $mail->setFrom('no-reply@ofertopolis.com', 'Ofertópolis');
} else {
    // PRODUCCIÓN: Gmail
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'tuemail@gmail.com';
    $mail->Password = 'tu_clave_de_aplicacion';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;
    $mail->setFrom('notificaciones@ofertopolis.com', 'Ofertópolis');
}
```

---

## Funcionalidades que Envían Emails

### 1. **Creación de Promociones** (duenio/duenio.php)
- **Cuándo:** Dueño crea una nueva promoción
- **Destinatario:** Administrador
- **Asunto:** "Nueva promoción pendiente de aprobación"
- **Contenido:** Detalles de la promoción

### 2. **Aprobación/Rechazo de Promociones** (admin/promociones.php)
- **Cuándo:** Admin aprueba o rechaza una promoción
- **Destinatario:** Dueño del local
- **Asunto:** "Promoción aprobada" o "Promoción rechazada"
- **Contenido:** Estado y detalles

### 3. **Solicitudes de Promociones** (duenio/solicitudes.php)
- **Cuándo:** Dueño acepta solicitud de cliente
- **Destinatario:** Cliente
- **Asunto:** "Solicitud de promoción aceptada"
- **Contenido:** Detalles de la promoción aceptada

### 4. **Validación de Dueños** (admin/validar_duenios.php y admin/admin.php)
- **Cuándo:** Admin aprueba o deniega cuenta de dueño
- **Destinatario:** Dueño
- **Asuntos:** 
  - "Cuenta aprobada - Ofertópolis"
  - "Solicitud denegada - Ofertópolis"
- **Contenido:** Estado de cuenta y locales asignados

### 5. **Asignación de Locales Adicionales** (admin/admin.php)
- **Cuándo:** Admin asigna locales a un dueño existente
- **Destinatario:** Dueño
- **Asunto:** "Nuevos locales asignados - Ofertópolis"
- **Contenido:** Cantidad de locales asignados

---

## Configuración para InfinityFree + Gmail

### Paso 1: Crear una Cuenta de Gmail para el Sistema

**Opción A: Crear nueva cuenta Gmail**
1. Crear cuenta: `ofertopolis.notificaciones@gmail.com` (o similar)
2. Esta será SOLO para enviar emails automáticos
3. **NO uses tu cuenta personal**

**Opción B: Usar cuenta existente**
- Puede ser riesgoso si la cuenta se bloquea por spam

---

### Paso 2: Habilitar "Contraseñas de Aplicación" en Gmail

**IMPORTANTE:** Gmail ya no permite usar la contraseña normal para aplicaciones.

1. Ve a tu cuenta de Gmail
2. **Seguridad** → **Verificación en 2 pasos** (debes activarla primero)
3. **Seguridad** → **Contraseñas de aplicaciones**
4. Selecciona:
   - Aplicación: "Correo"
   - Dispositivo: "Otro (nombre personalizado)" → escribe "Ofertopolis"
5. Gmail generará una contraseña de 16 caracteres como: `abcd efgh ijkl mnop`
6. **GUARDA ESTA CONTRASEÑA** - la necesitarás

---

### Paso 3: Modificar el Código para Producción

#### Archivos a modificar (7 archivos con emails):
1. `duenio/duenio.php`
2. `duenio/solicitudes.php`
3. `admin/admin.php`
4. `admin/promociones.php`
5. `admin/validar_duenios.php`

En **CADA** archivo, cambia esta parte:

```php
} else {
    // Producción: SMTP real
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'tuemail@gmail.com';              // ← CAMBIAR
    $mail->Password = 'tu_clave_de_aplicacion';          // ← CAMBIAR
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;
    $mail->setFrom('notificaciones@ofertopolis.com', 'Ofertópolis'); // ← OPCIONAL
}
```

Por:

```php
} else {
    // Producción: SMTP real con Gmail
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'ofertopolis.notificaciones@gmail.com';  // ← TU EMAIL REAL
    $mail->Password = 'abcd efgh ijkl mnop';                    // ← CONTRASEÑA DE APLICACIÓN
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;
    $mail->CharSet = 'UTF-8';  // Para acentos
    $mail->setFrom('ofertopolis.notificaciones@gmail.com', 'Ofertópolis');
}
```

---

### Paso 4: Consideraciones para InfinityFree

#### Limitaciones de InfinityFree:
1. **Límite de emails:** 50-100 por hora (verificar con tu plan)
2. **Puerto 25 bloqueado:** Usar puerto 587 (TLS) - ya lo tienes configurado ✓
3. **Puerto 465 (SSL):** También funciona como alternativa

#### Alternativa si Gmail falla en InfinityFree:

**Usar el SMTP de InfinityFree:**
```php
$mail->isSMTP();
$mail->Host = 'smtp.infinityfree.net';  // O el que te den
$mail->SMTPAuth = true;
$mail->Username = 'tu_email_del_panel';
$mail->Password = 'tu_password_del_panel';
$mail->SMTPSecure = 'ssl';
$mail->Port = 465;
$mail->setFrom('noreply@tudominio.com', 'Ofertópolis');
```

---

### Paso 5: Mejor Práctica - Archivo de Configuración

**Te recomiendo crear un archivo centralizado:**

Crea: `includes/mail_config.php`

```php
<?php
// Configuración centralizada de emails

function configurarMail($mail) {
    if ($_SERVER['SERVER_NAME'] == 'localhost') {
        // Desarrollo local con MailHog
        $mail->isSMTP();
        $mail->Host = 'localhost';
        $mail->Port = 1025;
        $mail->SMTPAuth = false;
        $mail->setFrom('no-reply@ofertopolis.com', 'Ofertópolis');
    } else {
        // PRODUCCIÓN con Gmail
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'ofertopolis.notificaciones@gmail.com';  // ← CAMBIAR
        $mail->Password = 'abcd efgh ijkl mnop';                    // ← CAMBIAR
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';
        $mail->setFrom('ofertopolis.notificaciones@gmail.com', 'Ofertópolis');
    }
}
?>
```

Luego en cada archivo:
```php
require '../vendor/autoload.php';
require '../includes/mail_config.php';  // ← AGREGAR
use PHPMailer\PHPMailer\PHPMailer;

$mail = new PHPMailer(true);
configurarMail($mail);  // ← USAR FUNCIÓN
$mail->addAddress($destino['email']);
// ... resto del código
```

**VENTAJA:** Solo cambias las credenciales en UN archivo.

---

## Checklist de Migración

### Antes de Subir a InfinityFree:

- [ ] Crear cuenta Gmail para el sistema
- [ ] Activar verificación en 2 pasos en Gmail
- [ ] Generar contraseña de aplicación en Gmail
- [ ] Guardar la contraseña de aplicación en lugar seguro
- [ ] Cambiar `Username` en los 5 archivos PHP
- [ ] Cambiar `Password` en los 5 archivos PHP
- [ ] Verificar que `Port = 587` y `SMTPSecure = 'tls'`
- [ ] Agregar `CharSet = 'UTF-8'` para acentos
- [ ] Probar envío de email de prueba
- [ ] Verificar límites de envío del hosting

### Después de Subir:

1. **Probar cada funcionalidad:**
   - [ ] Crear promoción (email a admin)
   - [ ] Aprobar promoción (email a dueño)
   - [ ] Aceptar solicitud (email a cliente)
   - [ ] Aprobar dueño (email a dueño)

2. **Monitorear:**
   - Revisar bandeja de entrada de Gmail
   - Verificar que no lleguen a SPAM
   - Revisar logs de InfinityFree

---

## Solución de Problemas Comunes

### Error: "SMTP connect() failed"
- **Causa:** Puerto bloqueado o credenciales incorrectas
- **Solución:** 
  - Verificar puerto 587 abierto en hosting
  - Probar puerto 465 con `SMTPSecure = 'ssl'`
  - Verificar contraseña de aplicación (sin espacios)

### Error: "Invalid address"
- **Causa:** Email destino inválido
- **Solución:** Verificar que el email existe en BD

### Emails llegan a SPAM
- **Causa:** Gmail marca como sospechoso
- **Solución:**
  - Usar dominio real en `setFrom()` (no @ofertopolis.com)
  - Agregar registro SPF en DNS
  - No enviar emails masivos

### Límite de envíos excedido
- **Causa:** InfinityFree/Gmail limita envíos por hora
- **Solución:**
  - Implementar cola de emails (guardar en BD y enviar luego)
  - Reducir emails automáticos
  - Considerar servicio de emails dedicado (SendGrid, Mailgun)

---

## Recomendación Final

Para **producción seria**, considera usar servicios de email transaccional:
- **SendGrid** (100 emails/día gratis)
- **Mailgun** (5000 emails/mes gratis primeros 3 meses)
- **Amazon SES** (muy económico)

Estos servicios tienen:
- Mayor confiabilidad
- Menor probabilidad de SPAM
- Estadísticas de envío
- Mayor límite de emails

---

## Resumen Rápido

**AHORA (localhost):**
- MailHog en puerto 1025 (solo desarrollo)
- Emails NO se envían realmente

**DESPUÉS (InfinityFree):**
1. Crear Gmail: `ofertopolis.notificaciones@gmail.com`
2. Generar contraseña de aplicación
3. Cambiar 2 líneas en 5 archivos:
   - `Username = 'tu-gmail@gmail.com'`
   - `Password = 'tu-contraseña-de-aplicacion'`
4. Subir al servidor
5. Probar cada funcionalidad

**¿Necesitas que implemente la solución con archivo de configuración centralizado?**
