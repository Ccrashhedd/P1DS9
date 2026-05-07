# P1DS9 - Sistema de Gestión de Tienda de Componentes Informáticos

## Descripción General

**P1DS9** es una solución completa de e-commerce para una tienda de componentes informáticos. Incluye:

- **Aplicación Android** (Kotlin + Jetpack Compose): App mobile para consultar productos, realizar compras y gestionar carrito
- **Backend PHP**: API REST que sirve datos a la aplicación Android y web
- **Frontend Web**: Interfaz web con gestión de catálogo, carrito de compras y administración
- **Base de Datos MySQL**: Sistema relacional con tablas de productos, categorías, empleados, facturas y transacciones

---

## 📋 Requisitos Previos

### Para el Backend y Frontend Web:
- **XAMPP** (versión 7.4 o superior con PHP 8.2+)
  - Descargar desde: https://www.apachefriends.org/
- **phpMyAdmin** (incluido en XAMPP)

### Para la Aplicación Android:
- **Android Studio** (Ladybug o superior recomendado)
- **Emulador de Android** con API 33 (Tiramisu) - Recomendado para evitar bloqueos de red

### Navegador Web:
- Chrome, Firefox, Edge o cualquier navegador moderno

---

## 🚀 Instalación y Configuración

### Paso 1: Preparar la Estructura de Carpetas

1. Navega a `C:\xampp\htdocs\`
2. Verifica que exista la siguiente estructura:
   ```
   C:\xampp\htdocs\DS92026\P1DS9\
   ```
3. Si no existe, créala manualmente

### Paso 2: Configurar la Base de Datos

1. **Inicia XAMPP**:
   - Abre el Panel de Control de XAMPP
   - Inicia los módulos `Apache` y `MySQL`

2. **Accede a phpMyAdmin**:
   - Abre tu navegador en: `http://localhost/phpmyadmin`
   - Las credenciales por defecto son: 
     - Usuario: `root`
     - Contraseña: (vacía)

3. **Crea la base de datos**:
   - Haz clic en "New" en el panel izquierdo
   - Nombre de base de datos: `ds9p1`
   - Collation: `utf8mb4_general_ci`
   - Haz clic en "Create"

4. **Importa el esquema SQL**:
   - En phpMyAdmin, selecciona la base de datos `ds9p1`
   - Ve a la pestaña "Import"
   - Selecciona el archivo `DB/ds9p1.sql` del proyecto
   - Haz clic en "Import"

**Resultado esperado**: La base de datos se importará con todas las tablas (categoría, marca, productos, empleado, factura, factura_detalle, tarjeta)

### Paso 3: Configurar Carpeta de Imágenes

1. Navega a `C:\xampp\htdocs\DS92026\P1DS9\APP\`
2. Crea una carpeta llamada `img` si no existe
3. Descarga o coloca las imágenes de los productos en esta carpeta
   - Las imágenes deben coincidir con los nombres registrados en la tabla `productos` (columna `imagen`)

### Paso 4: Verificar Configuración PHP

La configuración de base de datos está en `Web/Backend/PHP/config/app.php`:

```php
const DB_HOST = '127.0.0.1';
const DB_USER = 'root';
const DB_PASS = '';  // Contraseña vacía por defecto en XAMPP
const DB_NAME = 'ds9p1';
```

Si has configurado una contraseña para MySQL, edita este archivo y actualiza la constante `DB_PASS`.

---

## 💻 Ejecutar la Aplicación

### Opción 1: Frontend Web (Recomendado para comenzar)

1. **Inicia XAMPP**:
   - Panel de Control de XAMPP → Inicia `Apache` y `MySQL`

2. **Accede a la aplicación web**:
   - Abre tu navegador en: `http://localhost/DS92026/P1DS9/Web/PaginaCliente/Frontend/HTML/index.php`

3. **Funcionalidades disponibles**:
   - **Catálogo**: Visualiza todos los productos disponibles
   - **Búsqueda**: Busca productos por nombre
   - **Carrito**: Añade productos al carrito (almacenado localmente en localStorage)
   - **Login**: Accede como empleado con credenciales:
     - Usuario: `admin` / Contraseña: `admin123` (rol: Administrador)
     - Usuario: `empleado` / Contraseña: `empleado123` (rol: Empleado)
   - **Panel Administrativo**: Solo para usuarios con rol admin

**Credenciales de prueba incluidas en la base de datos**:
- Admin: `admin` / `admin123`
- Empleado: `empleado` / `empleado123`

---

### Opción 2: Aplicación Android

#### Requisitos adicionales:
- Android Studio (Ladybug o superior)
- Emulador con **API 33 (Tiramisu)** o dispositivo físico conectado

#### Pasos:

1. **Configura XAMPP y la base de datos** (Sigue los pasos 1-3 anteriores)

2. **Abre el proyecto Android en Android Studio**:
   - Abre Android Studio
   - Selecciona `File > Open`
   - Navega a la carpeta raíz del proyecto P1DS9
   - Espera a que se sincronice Gradle

3. **Configura la IP del servidor**:
   - Abre el archivo: `com.example.proyecto1.network.RetrofitClient`
   - Verifica que la URL base sea:
     ```kotlin
     private const val BASE_URL = "http://10.0.2.2/DS92026/P1DS9/APP/"
     ```
   - Esta IP (10.0.2.2) es obligatoria para comunicarse con el servidor local desde un emulador

4. **Ejecuta la aplicación**:
   - Selecciona el emulador con API 33 en la barra superior
   - Haz clic en el botón verde "Run" (Play icon)
   - La app se compilará e instalará en el emulador

#### Funcionalidades de la app:
- Visualización de catálogo de productos
- Carrito de compras
- Login con validación de roles
- Procesamiento de pagos simulados
- Validación de fondos en tarjetas

---

### Opción 3: Backend PHP (API REST)

El backend está disponible en: `http://localhost/DS92026/P1DS9/APP/`

**Endpoints principales**:
- `get_productos.php` - Obtiene lista de productos
- `get_facturas.php` - Obtiene historial de facturas
- `login_empleado.php` - Autenticación de empleados
- `procesar_factura.php` - Procesa transacciones de compra
- `guardar_producto.php` - Crud de productos (admin)

---

## 🔧 Solución de Problemas

### Error: "No se puede conectar a la base de datos"
**Solución**:
1. Verifica que XAMPP está ejecutando MySQL (Panel de Control → MySQL debe estar en verde)
2. Comprueba que la base de datos `ds9p1` existe en phpMyAdmin
3. Verifica las credenciales en `Web/Backend/PHP/config/app.php`
4. Intenta crear una conexión de prueba directamente en phpMyAdmin

### Error: "SocketTimeoutException" en la app Android
**Solución**:
1. Asegúrate de usar un emulador con API 33 (Tiramisu)
2. Las API 34+ bloquean el tráfico HTTP en redes locales
3. Verifica que `network_security_config.xml` está correctamente vinculado en `AndroidManifest.xml`
4. Confirma que el servidor está corriendo: `http://localhost/DS92026/P1DS9/APP/get_productos.php`

### Error: "Malformed JSON" en la app
**Solución**:
1. Abre directamente en el navegador: `http://localhost/DS92026/P1DS9/APP/get_productos.php`
2. Si ves errores PHP, significa hay un error en el backend
3. Revisa la consola de errores de PHP en el servidor

### La app no se conecta con dispositivo físico
**Solución**:
1. Conecta el teléfono a la misma red Wi-Fi que tu computadora
2. Obtén la IPv4 de tu PC (ejecuta `ipconfig` en CMD y busca "IPv4 Address")
3. En `RetrofitClient`, cambia `10.0.2.2` por tu IPv4 (ej: `192.168.1.15`)
4. Recompila e instala la app

### Página web en blanco o error 404
**Solución**:
1. Verifica que Apache está corriendo en XAMPP
2. Asegúrate que la URL es exacta: `http://localhost/DS92026/P1DS9/Web/PaginaCliente/Frontend/HTML/index.php`
3. Revisa la consola del navegador (F12) para ver errores JavaScript
4. Verifica que la conexión a la base de datos funciona

---

## 📂 Estructura del Proyecto

```
P1DS9/
├── APP/                           # Backend PHP para app Android
│   ├── *.php                      # Endpoints de la API
│   └── img/                       # Imágenes de productos
├── DB/
│   └── ds9p1.sql                  # Esquema de la base de datos
├── Web/                           # Aplicación web
│   ├── Backend/
│   │   ├── Js/                    # Servicios JavaScript
│   │   └── PHP/
│   │       ├── config/            # Configuración de BD
│   │       ├── controllers/       # Enrutador principal
│   │       ├── includes/          # Funciones compartidas
│   │       └── services/          # Lógica de negocios
│   ├── PaginaCliente/
│   │   ├── Backend/               # Backend específico cliente
│   │   └── Frontend/
│   │       ├── HTML/              # Vistas PHP
│   │       ├── Views/             # Componentes dinámicos
│   │       └── partials/          # Partes reutilizables
│   └── Styles/                    # CSS de la aplicación
└── README.md                      # Este archivo
```

---

## 👤 Usuarios de Prueba

| Usuario | Contraseña | Rol | Descripción |
|---------|-----------|-----|-------------|
| admin | admin123 | Administrador | Acceso completo a funciones administrativas |
| empleado | empleado123 | Empleado | Acceso limitado a lectura de catálogo |

---

## 📞 Notas Importantes

- **Seguridad**: Las contraseñas en la BD de prueba son de demostración. En producción, usar hash bcrypt o similar
- **SSL/HTTPS**: El sistema usa HTTP local. Para producción, implementar certificados SSL
- **Validación de Tarjetas**: Los datos de tarjetas son simulados para desarrollo
- **Stock**: Los productos tienen stock inicial de 10 unidades. Ajusta según necesites en phpMyAdmin

---

## ✅ Checklist de Instalación

- [ ] XAMPP instalado y ejecutándose
- [ ] Base de datos `ds9p1` creada e importada
- [ ] Carpeta `APP/img/` creada con imágenes
- [ ] Configuración PHP verificada en `Web/Backend/PHP/config/app.php`
- [ ] Web accesible en `http://localhost/DS92026/P1DS9/Web/PaginaCliente/Frontend/HTML/index.php`
- [ ] App Android sincronizada en Android Studio
- [ ] Emulador API 33 disponible (para app Android)

---

## 📖 Más Información

Para detalles adicionales sobre la configuración de la app Android, consulta el archivo `APP/Guía de Instalación y Ejecución APP android.txt`
