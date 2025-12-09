# Guía de Accesibilidad - Ofertópolis

## ✅ Mejoras de Accesibilidad Implementadas

### 1. **Etiquetas ARIA (Accessible Rich Internet Applications)**
- Todos los elementos interactivos tienen `aria-label` descriptivos
- Navegación con `role="navigation"` y `aria-label`
- Secciones con `aria-labelledby` vinculadas a sus títulos
- Carruseles con `aria-live="polite"` para anunciar cambios
- Botones con `aria-expanded` para menús desplegables

### 2. **Estructura Semántica HTML5**
- Uso correcto de `<header>`, `<nav>`, `<main>`, `<section>`, `<footer>`
- Etiqueta `lang="es"` en todas las páginas
- Títulos jerárquicos correctos (h1, h2, h3, etc.)
- `role="main"` en contenido principal

### 3. **Skip Link (Saltar al Contenido)**
- Enlace invisible que aparece al presionar Tab
- Permite saltar directamente al contenido principal
- Útil para usuarios de teclado y lectores de pantalla

### 4. **Navegación por Teclado**
- Todos los elementos interactivos son accesibles con Tab
- Estados de foco visibles con `outline`
- Botones toggle con estados aria correctos

### 5. **Textos Descriptivos**
- Enlaces con descripciones claras de su destino
- Imágenes decorativas con `aria-hidden="true"`
- Emojis con `aria-label` para su lectura correcta

---

## 🎯 Cómo Probar con el Narrador de Windows

### **Activar el Narrador**

**Método 1 - Atajo de teclado:**
- Presioná `Ctrl + Windows + Enter`

**Método 2 - Desde Configuración:**
1. Presioná `Windows + I` para abrir Configuración
2. Ve a **Accesibilidad**
3. Seleccioná **Narrador**
4. Activá el interruptor

### **Comandos Básicos del Narrador**

| Comando | Acción |
|---------|--------|
| `Tab` | Navegar entre elementos interactivos |
| `Shift + Tab` | Navegar hacia atrás |
| `Enter` o `Espacio` | Activar botón/enlace |
| `H` | Saltar al siguiente encabezado |
| `K` | Saltar al siguiente enlace |
| `B` | Saltar al siguiente botón |
| `Ctrl` | Detener la lectura |
| `Bloq Mayús + F12` | Leer página completa |
| `Bloq Mayús + Flecha Arriba/Abajo` | Leer línea por línea |

### **Qué Probar en Ofertópolis**

#### 1. **Página de Inicio (index.php)**
- El Narrador debe anunciar: "Ofertópolis - Tu shopping con las mejores promociones"
- Al presionar Tab, debe aparecer el "Saltar al contenido principal"
- Los carruseles deben anunciarse como "Carrusel de promociones destacadas"
- Cada promoción debe leerse con título, local y descripción

#### 2. **Navegación**
- El menú debe anunciarse como "Navegación principal"
- Cada enlace debe tener descripción clara (ej: "Ir a página de contacto")

#### 3. **Footer**
- Debe anunciarse como "Información del sitio"
- Enlaces de redes sociales indican que se abren en nueva ventana
- Email y teléfono son enlaces funcionales

#### 4. **Formularios (Login, Registro, Contacto)**
- Cada campo tiene etiqueta clara
- Botones de mostrar/ocultar contraseña se anuncian correctamente
- Mensajes de error/éxito se leen automáticamente

#### 5. **Recuperar Contraseña**
- Enlace "¿Olvidaste tu contraseña?" claramente anunciado
- Flujo completo accesible por teclado

---

## 🔍 Verificación de Accesibilidad

### **Checklist de Pruebas**

✅ **Navegación por Teclado**
- [ ] Puedo navegar toda la página solo con Tab
- [ ] Los elementos tienen un orden lógico
- [ ] El foco es visible en todo momento

✅ **Lectores de Pantalla**
- [ ] Todos los enlaces tienen texto descriptivo
- [ ] Las imágenes decorativas están ocultas (aria-hidden)
- [ ] Los formularios tienen etiquetas claras
- [ ] Los mensajes de error/éxito se leen automáticamente

✅ **Contraste y Legibilidad**
- [ ] El texto tiene suficiente contraste con el fondo
- [ ] Los tamaños de fuente son legibles
- [ ] Los colores no son la única forma de transmitir información

✅ **Semántica HTML**
- [ ] Uso correcto de encabezados (h1-h6)
- [ ] Estructura lógica de la página
- [ ] Atributo lang="es" presente

---

## 📋 Páginas con Mejoras de Accesibilidad

### ✅ Archivos Actualizados:

1. **`includes/navbar.php`** - Navegación accesible
2. **`includes/footer.php`** - Footer con ARIA labels
3. **`includes/header.php`** - Header con skip link (ya existía)
4. **`index.php`** - Página principal accesible
5. **`css/utilities.css`** - Estilos para skip link
6. **`auth/login.php`** - Formulario con recuperación de contraseña
7. **`auth/recuperar_password.php`** - Nuevo formulario accesible
8. **`auth/restablecer_password.php`** - Nuevo formulario accesible

---

## 🎨 Estilos de Accesibilidad

### **Skip Link CSS**
```css
.skip-link {
  position: absolute;
  top: -40px;
  /* Aparece solo cuando recibe foco */
}

.skip-link:focus {
  top: 0;
  outline: 3px solid var(--color-secondary);
}
```

---

## 🚀 Próximos Pasos (Opcional)

Para mejorar aún más la accesibilidad:

1. **Validar con herramientas automáticas:**
   - WAVE (extensión de Chrome)
   - Lighthouse (DevTools de Chrome)
   - axe DevTools

2. **Agregar más landmarks ARIA:**
   - `<aside role="complementary">`
   - `<form role="search">` para búsquedas

3. **Modo de alto contraste:**
   - CSS adicional para Windows High Contrast Mode

4. **Tamaños de texto ajustables:**
   - Permitir zoom hasta 200% sin pérdida de funcionalidad

---

## 📞 Contacto

Para reportar problemas de accesibilidad:
- Email: info@ofertopolis.com
- Tel: 0800-OFERTAS (633-7827)

---

**Desarrollado con ❤️ por Alaniz & Giacomelli | UTN FRRO - Entornos Gráficos**
