<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../services/catalog_service.php';
require_once __DIR__ . '/../services/product_service.php';

/**
 * Maneja una solicitud HTTP completa (GET o POST).
 * Delega al manejador de POST si aplica y construye el estado de la página.
 * 
 * @return array Estado de la página con vista, datos y mensajes
 */
function handleRequest(): array
{
    try {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            handlePostAction();
        }

        return buildPageState();
    } catch (Throwable $e) {
        setFlash('flash_error', 'Ocurrió un problema al procesar la solicitud.');

        return buildPageState();
    }
}

/**
 * Procesa acciones POST basadas en el parámetro _action.
 * Delega a funciones específicas según la acción solicitada.
 * 
 * @return void
 */
function handlePostAction(): void
{
    $action = (string) ($_POST['_action'] ?? '');

    switch ($action) {
        case 'go_catalogo':
            setCurrentView('catalogo');
            redirectToIndex();
        case 'go_login':
            setCurrentView('login');
            redirectToIndex();
        case 'go_dashboard':
            if (!isLoggedIn()) {
                setFlash('flash_error', 'Debes iniciar sesión para acceder al panel.');
                setCurrentView('login');
            } else {
                setCurrentView('dashboard');
            }
            redirectToIndex();
        case 'go_productos':
            if (!isLoggedIn()) {
                setFlash('flash_error', 'Debes iniciar sesión para administrar productos.');
                setCurrentView('login');
            } else {
                setCurrentView('productos_panel');
            }
            redirectToIndex();
        case 'catalog_filter':
            setCatalogFilters([
                'q' => trim((string) ($_POST['q'] ?? '')),
                'categoria' => max(0, (int) ($_POST['categoria'] ?? 0)),
                'marca' => max(0, (int) ($_POST['marca'] ?? 0)),
                'orden' => (string) ($_POST['orden'] ?? 'nombre_asc'),
            ]);
            setCurrentView('catalogo');
            redirectToIndex();
        case 'catalog_reset':
            resetCatalogFilters();
            setCurrentView('catalogo');
            redirectToIndex();
        case 'login':
            processLogin();
            redirectToIndex();
        case 'logout':
            processLogout();
            redirectToIndex();
        case 'guardar_producto':
            processGuardarProducto();
            redirectToIndex();
        case 'actualizar_stock':
            processActualizarStock();
            redirectToIndex();
        default:
            redirectToIndex();
    }
}

/**
 * Procesa el inicio de sesión del usuario.
 * Valida credenciales y establece la sesión.
 * 
 * @return void
 */
function processLogin(): void
{
    $usuarioIngresado = trim((string) ($_POST['usuario'] ?? ''));
    $contrasenaIngresada = (string) ($_POST['contrasena'] ?? '');

    if ($usuarioIngresado === '' || $contrasenaIngresada === '') {
        setFlash('flash_error', 'Debes completar usuario y contraseña.');
        setCurrentView('login');
        return;
    }

    try {
        $empleado = authenticate($usuarioIngresado, $contrasenaIngresada);

        if (!$empleado) {
            setFlash('flash_error', 'Usuario o contraseña incorrectos.');
            setCurrentView('login');
            return;
        }

        loginUser($empleado);
        setFlash('flash_success', 'Bienvenido, has iniciado sesión correctamente.');
        setCurrentView('dashboard');
    } catch (Throwable $e) {
        setFlash('flash_error', 'No se pudo procesar el inicio de sesión. Revisa la conexión con la base de datos.');
        setCurrentView('login');
    }
}

/**
 * Procesa el cierre de sesión del usuario.
 * Destruye la sesión y redirige al catálogo.
 * 
 * @return void
 */
function processLogout(): void
{
    logoutUser();
    setFlash('flash_success', 'La sesión se cerró correctamente.');
    setCurrentView('catalogo');
}

/**
 * Procesa la creación de un nuevo producto.
 * Valida que el usuario sea administrador.
 * 
 * @return void
 */
function processGuardarProducto(): void
{
    if (!hasRole([1])) {
        setFlash('flash_error', 'Solo el administrador puede agregar productos.');
        setCurrentView(isLoggedIn() ? 'dashboard' : 'login');
        return;
    }

    $data = [
        'idProducto' => trim((string) ($_POST['idProducto'] ?? '')),
        'nombre' => trim((string) ($_POST['nombre'] ?? '')),
        'unidad' => trim((string) ($_POST['unidad'] ?? '')),
        'descripcion' => trim((string) ($_POST['descripcion'] ?? '')),
        'stock' => filter_var($_POST['stock'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]),
        'precioCosto' => filter_var($_POST['precioCosto'] ?? null, FILTER_VALIDATE_FLOAT),
        'precioVenta' => filter_var($_POST['precioVenta'] ?? null, FILTER_VALIDATE_FLOAT),
        'imagen' => trim((string) ($_POST['imagen'] ?? '')),
        'idCategoria' => filter_var($_POST['idCategoria'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]),
        'idMarca' => filter_var($_POST['idMarca'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]),
    ];

    if (
        $data['idProducto'] === '' ||
        !ctype_digit($data['idProducto']) ||
        $data['nombre'] === '' ||
        $data['unidad'] === '' ||
        $data['descripcion'] === '' ||
        $data['stock'] === false ||
        $data['precioCosto'] === false || (float) $data['precioCosto'] < 0 ||
        $data['precioVenta'] === false || (float) $data['precioVenta'] < 0 ||
        $data['idCategoria'] === false ||
        $data['idMarca'] === false
    ) {
        setFlash('flash_error', 'Revisa los datos del producto. Hay campos vacíos o inválidos.');
        setCurrentView('productos_panel');
        return;
    }

    try {
        guardarProducto([
            'idProducto' => $data['idProducto'],
            'nombre' => $data['nombre'],
            'unidad' => $data['unidad'],
            'descripcion' => $data['descripcion'],
            'stock' => (int) $data['stock'],
            'precioCosto' => (float) $data['precioCosto'],
            'precioVenta' => (float) $data['precioVenta'],
            'imagen' => $data['imagen'],
            'idCategoria' => (int) $data['idCategoria'],
            'idMarca' => (int) $data['idMarca'],
        ]);
        setFlash('flash_success', 'Producto agregado correctamente.');
    } catch (Throwable $e) {
        setFlash('flash_error', $e instanceof RuntimeException ? $e->getMessage() : 'No se pudo guardar el producto. Verifica la base de datos.');
    }

    setCurrentView('productos_panel');
}

/**
 * Procesa la actualización de stock de un producto.
 * Valida permisos de administrador o empleado.
 * 
 * @return void
 */
function processActualizarStock(): void
{
    if (!hasRole([1, 2])) {
        setFlash('flash_error', 'Debes iniciar sesión para actualizar stock.');
        setCurrentView(isLoggedIn() ? 'dashboard' : 'login');
        return;
    }

    $idProducto = trim((string) ($_POST['idProducto'] ?? ''));
    $stock = filter_var($_POST['stock'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

    if ($idProducto === '' || !ctype_digit($idProducto) || $stock === false) {
        setFlash('flash_error', 'Los datos para actualizar el stock no son válidos.');
        setCurrentView('productos_panel');
        return;
    }

    try {
        actualizarStockProducto($idProducto, (int) $stock);
        setFlash('flash_success', 'Stock actualizado correctamente.');
    } catch (Throwable $e) {
        setFlash('flash_error', 'No se pudo actualizar el stock. Revisa la base de datos.');
    }

    setCurrentView('productos_panel');
}

/**
 * Construye el estado completo de la página.
 * Incluye información de usuario, catálogo, panel administrativo y mensajes flash.
 * 
 * @return array Estado de la página con toda la información requerida
 */
function buildPageState(): array
{
    $errorConexion = null;
    $view = currentView();

    if (!$errorConexion) {
        try {
            db();
        } catch (Throwable $e) {
            $errorConexion = 'No se pudo conectar o consultar la base de datos. Revisa las credenciales, que XAMPP/MySQL esté encendido y que la base de datos ds9p1 exista.';
        }
    }

    if (!isLoggedIn() && in_array($view, ['dashboard', 'productos_panel'], true)) {
        $view = 'login';
        setCurrentView('login');
    }

    $state = [
        'view' => $view,
        'errorConexion' => $errorConexion,
        'flashSuccess' => flash('flash_success'),
        'flashError' => flash('flash_error'),
        'currentUser' => currentUser(),
        'catalog' => [
            'filters' => getCatalogFilters(),
            'categorias' => [],
            'marcas' => [],
            'productos' => [],
        ],
        'panel' => [
            'categorias' => [],
            'marcas' => [],
            'productos' => [],
        ],
    ];

    if ($errorConexion === null) {
        try {
            $state['catalog']['categorias'] = getCategorias();
            $state['catalog']['marcas'] = getMarcas();
            $state['catalog']['productos'] = getProductosCatalogo($state['catalog']['filters']);

            if (isLoggedIn()) {
                $state['panel']['categorias'] = $state['catalog']['categorias'];
                $state['panel']['marcas'] = $state['catalog']['marcas'];
                $state['panel']['productos'] = getProductosPanel();
            }
        } catch (Throwable $e) {
            $state['errorConexion'] = 'No se pudo cargar la información desde la base de datos.';
        }
    }

    return $state;
}
