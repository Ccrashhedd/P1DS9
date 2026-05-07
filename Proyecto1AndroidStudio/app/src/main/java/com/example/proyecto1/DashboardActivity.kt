package com.example.proyecto1

import android.content.Intent
import android.os.Bundle
import android.widget.Toast
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.grid.GridCells
import androidx.compose.foundation.lazy.grid.LazyVerticalGrid
import androidx.compose.foundation.lazy.grid.items
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.Logout
import androidx.compose.material.icons.filled.*
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import coil.compose.AsyncImage
import com.example.proyecto1.model.*
import com.example.proyecto1.network.RetrofitClient
import com.example.proyecto1.network.SessionManager
import com.example.proyecto1.ui.theme.Proyecto1Theme
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response

class DashboardActivity : ComponentActivity() {
    @OptIn(ExperimentalMaterial3Api::class)
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContent {
            Proyecto1Theme {
                val context = LocalContext.current
                val session = remember { SessionManager(context) }
                val rol = session.getRol()
                val nombreRol = if(rol == 1) "Administrador" else "Empleado"

                Scaffold(
                    topBar = {
                        TopAppBar(
                            title = { Text("Panel de Control - $nombreRol", color = Color.White) },
                            colors = TopAppBarDefaults.topAppBarColors(containerColor = Color(0xFF1E293B)),
                            navigationIcon = {
                                IconButton(onClick = {
                                    startActivity(Intent(context, MainActivity::class.java))
                                    finish()
                                }) {
                                    Icon(Icons.Default.ArrowBack, "Volver al Catálogo", tint = Color.White)
                                }
                            },
                            actions = {
                                IconButton(onClick = {
                                    session.logout()
                                    startActivity(Intent(context, MainActivity::class.java))
                                    finish()
                                }) {
                                    Icon(Icons.AutoMirrored.Filled.Logout, "Salir", tint = Color.White)
                                }
                            }
                        )
                    }
                ) { padding ->
                    Box(modifier = Modifier.padding(padding).fillMaxSize().background(Color(0xFFF1F5F9))) {
                        if (rol == 1) {
                            AdminDashboardView()
                        } else {
                            EmployeeDashboardView()
                        }
                    }
                }
            }
        }
    }
}

@Composable
fun AdminDashboardView() {
    val context = LocalContext.current
    var currentScreen by remember { mutableStateOf("Menu") }

    when (currentScreen) {
        "Menu" -> {
            LazyVerticalGrid(
                columns = GridCells.Fixed(1),
                contentPadding = PaddingValues(16.dp),
                verticalArrangement = Arrangement.spacedBy(16.dp)
            ) {
                item {
                    DashboardCard("Gestión de Inventario", "Ver, editar y agregar productos", Icons.Default.Inventory, Color(0xFF2563EB)) {
                        currentScreen = "Inventario"
                    }
                }
                item {
                    DashboardCard("Categorías y Marcas", "Gestionar tipos de productos", Icons.Default.Category, Color(0xFF8B5CF6)) {
                        currentScreen = "CategoriasMarcas"
                    }
                }
                item {
                    DashboardCard("Historial de Ventas", "Ver facturas y detalles", Icons.Default.ReceiptLong, Color(0xFF059669)) {
                        currentScreen = "Facturas"
                    }
                }
                item {
                    DashboardCard("Gestión de Empleados", "Registrar nuevo personal", Icons.Default.People, Color(0xFFD97706)) {
                        currentScreen = "Empleados"
                    }
                }
            }
        }
        "Inventario" -> GestionInventarioAdminView { currentScreen = "Menu" }
        "CategoriasMarcas" -> GestionCategoriasMarcasView { currentScreen = "Menu" }
        "Facturas" -> HistorialFacturasView { currentScreen = "Menu" }
        "Empleados" -> RegistroEmpleadoView { currentScreen = "Menu" }
    }
}

@Composable
fun GestionCategoriasMarcasView(onBack: () -> Unit) {
    var tabIndex by remember { mutableStateOf(0) }
    val tabs = listOf("Categorías", "Marcas")

    Column(Modifier.fillMaxSize()) {
        Row(verticalAlignment = Alignment.CenterVertically, modifier = Modifier.padding(8.dp)) {
            IconButton(onClick = onBack) { Icon(Icons.Default.ArrowBack, null) }
            Text("Gestión de Tipos", style = MaterialTheme.typography.titleLarge)
        }
        
        TabRow(selectedTabIndex = tabIndex) {
            tabs.forEachIndexed { index, title ->
                Tab(selected = tabIndex == index, onClick = { tabIndex = index }, text = { Text(title) })
            }
        }

        when (tabIndex) {
            0 -> CategoriaListView()
            1 -> MarcaListView()
        }
    }
}

@Composable
fun CategoriaListView() {
    var categorias by remember { mutableStateOf<List<Categoria>>(emptyList()) }
    var nuevaCat by remember { mutableStateOf("") }
    val context = LocalContext.current

    val cargar = {
        RetrofitClient.instance.getCategorias().enqueue(object : Callback<List<Categoria>> {
            override fun onResponse(call: Call<List<Categoria>>, response: Response<List<Categoria>>) {
                categorias = response.body() ?: emptyList()
            }
            override fun onFailure(call: Call<List<Categoria>>, t: Throwable) {}
        })
    }

    LaunchedEffect(Unit) { cargar() }

    Column(Modifier.padding(16.dp)) {
        Row(verticalAlignment = Alignment.CenterVertically) {
            OutlinedTextField(value = nuevaCat, onValueChange = { nuevaCat = it }, label = { Text("Nueva Categoría") }, modifier = Modifier.weight(1f))
            IconButton(onClick = {
                if(nuevaCat.isNotBlank()) {
                    RetrofitClient.instance.guardarCategoria(nuevaCat).enqueue(object : Callback<BaseResponse> {
                        override fun onResponse(call: Call<BaseResponse>, response: Response<BaseResponse>) {
                            if(response.isSuccessful) {
                                nuevaCat = ""
                                cargar()
                                Toast.makeText(context, "Guardado", Toast.LENGTH_SHORT).show()
                            }
                        }
                        override fun onFailure(call: Call<BaseResponse>, t: Throwable) {}
                    })
                }
            }) { Icon(Icons.Default.Add, null) }
        }
        Spacer(Modifier.height(16.dp))
        LazyColumn {
            items(categorias) { cat ->
                Card(Modifier.fillMaxWidth().padding(vertical = 4.dp)) {
                    Text(cat.nombreCat, Modifier.padding(16.dp))
                }
            }
        }
    }
}

@Composable
fun MarcaListView() {
    var marcas by remember { mutableStateOf<List<Marca>>(emptyList()) }
    var nuevaMarca by remember { mutableStateOf("") }
    val context = LocalContext.current

    val cargar = {
        RetrofitClient.instance.getMarcas().enqueue(object : Callback<List<Marca>> {
            override fun onResponse(call: Call<List<Marca>>, response: Response<List<Marca>>) {
                marcas = response.body() ?: emptyList()
            }
            override fun onFailure(call: Call<List<Marca>>, t: Throwable) {}
        })
    }

    LaunchedEffect(Unit) { cargar() }

    Column(Modifier.padding(16.dp)) {
        Row(verticalAlignment = Alignment.CenterVertically) {
            OutlinedTextField(value = nuevaMarca, onValueChange = { nuevaMarca = it }, label = { Text("Nueva Marca") }, modifier = Modifier.weight(1f))
            IconButton(onClick = {
                if(nuevaMarca.isNotBlank()) {
                    RetrofitClient.instance.guardarMarca(nuevaMarca).enqueue(object : Callback<BaseResponse> {
                        override fun onResponse(call: Call<BaseResponse>, response: Response<BaseResponse>) {
                            if(response.isSuccessful) {
                                nuevaMarca = ""
                                cargar()
                                Toast.makeText(context, "Guardado", Toast.LENGTH_SHORT).show()
                            }
                        }
                        override fun onFailure(call: Call<BaseResponse>, t: Throwable) {}
                    })
                }
            }) { Icon(Icons.Default.Add, null) }
        }
        Spacer(Modifier.height(16.dp))
        LazyColumn {
            items(marcas) { m ->
                Card(Modifier.fillMaxWidth().padding(vertical = 4.dp)) {
                    Text(m.nombreMarc, Modifier.padding(16.dp))
                }
            }
        }
    }
}

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun GestionInventarioAdminView(onBack: () -> Unit) {
    var productosOriginales by remember { mutableStateOf<List<Producto>>(emptyList()) }
    var productosFiltrados by remember { mutableStateOf<List<Producto>>(emptyList()) }
    var isLoading by remember { mutableStateOf(true) }
    
    // Estados para Filtros
    var searchQuery by remember { mutableStateOf("") }
    var selectedCategoria by remember { mutableStateOf("Todas") }
    var selectedMarca by remember { mutableStateOf("Todas") }
    var currentSort by remember { mutableStateOf("Ninguno") }
    var showFilterSheet by remember { mutableStateOf(false) }

    var productoAEditar by remember { mutableStateOf<Producto?>(null) }
    var mostrarFormularioNuevo by remember { mutableStateOf(false) }

    val cargarProductos = {
        isLoading = true
        RetrofitClient.instance.getCatalogo().enqueue(object : Callback<List<Producto>> {
            override fun onResponse(call: Call<List<Producto>>, response: Response<List<Producto>>) {
                productosOriginales = response.body() ?: emptyList()
                isLoading = false
            }
            override fun onFailure(call: Call<List<Producto>>, t: Throwable) { isLoading = false }
        })
    }

    LaunchedEffect(Unit) { cargarProductos() }

    // Lógica de Filtrado (Igual al catálogo pero incluye stock 0)
    LaunchedEffect(searchQuery, selectedCategoria, selectedMarca, currentSort, productosOriginales) {
        val nombresCategorias = mapOf("1" to "discos duros", "2" to "fuentes de poder", "3" to "memorias ram", "4" to "procesadores", "5" to "tarjetas madres")
        val nombresMarcas = mapOf("1" to "KINGSTON", "2" to "CRUCIAL", "3" to "XYZ", "4" to "G-SKILL", "5" to "INTEL", "6" to "AMD", "7" to "GIGABYTE", "8" to "MSI")

        var temp = productosOriginales.filter { prod ->
            val matchesSearch = if (searchQuery.isBlank()) true else {
                val nameMatch = prod.nombre?.contains(searchQuery, ignoreCase = true) ?: false
                val descMatch = prod.descripcion?.contains(searchQuery, ignoreCase = true) ?: false
                val catMatch = (nombresCategorias[prod.idCategoria.toString()] ?: "").contains(searchQuery, ignoreCase = true)
                val marcMatch = (nombresMarcas[prod.idMarca.toString()] ?: "").contains(searchQuery, ignoreCase = true)
                nameMatch || descMatch || catMatch || marcMatch
            }
            val matchesCat = selectedCategoria == "Todas" || prod.idCategoria.toString() == selectedCategoria
            val matchesMarc = selectedMarca == "Todas" || prod.idMarca.toString() == selectedMarca
            matchesSearch && matchesCat && matchesMarc
        }

        temp = when (currentSort) {
            "Precio: Menor a Mayor" -> temp.sortedBy { it.precioVenta }
            "Precio: Mayor a Menor" -> temp.sortedByDescending { it.precioVenta }
            "Nombre: A-Z" -> temp.sortedBy { it.nombre }
            "Nombre: Z-A" -> temp.sortedByDescending { it.nombre }
            else -> temp
        }
        productosFiltrados = temp
    }

    Column(Modifier.fillMaxSize()) {
        Column(Modifier.background(Color.White).padding(8.dp)) {
            Row(verticalAlignment = Alignment.CenterVertically, horizontalArrangement = Arrangement.SpaceBetween, modifier = Modifier.fillMaxWidth()) {
                Row(verticalAlignment = Alignment.CenterVertically) {
                    IconButton(onClick = onBack) { Icon(Icons.Default.ArrowBack, null) }
                    Text("Gestión Inventario", style = MaterialTheme.typography.titleLarge)
                }
                Button(onClick = { mostrarFormularioNuevo = true }) {
                    Icon(Icons.Default.Add, null)
                    Text("Nuevo")
                }
            }
            TextField(
                value = searchQuery,
                onValueChange = { searchQuery = it },
                modifier = Modifier.fillMaxWidth().padding(top = 8.dp),
                placeholder = { Text("Buscar producto...") },
                leadingIcon = { Icon(Icons.Default.Search, null) },
                trailingIcon = {
                    IconButton(onClick = { showFilterSheet = true }) {
                        Icon(Icons.Default.FilterList, contentDescription = "Filtros")
                    }
                },
                colors = TextFieldDefaults.colors(focusedContainerColor = Color(0xFFF1F5F9), unfocusedContainerColor = Color(0xFFF1F5F9)),
                singleLine = true
            )
        }

        if (isLoading) {
            Box(Modifier.fillMaxSize(), contentAlignment = Alignment.Center) { CircularProgressIndicator() }
        } else {
            LazyColumn(contentPadding = PaddingValues(16.dp), verticalArrangement = Arrangement.spacedBy(8.dp)) {
                items(productosFiltrados) { prod ->
                    Card(modifier = Modifier.fillMaxWidth(), colors = CardDefaults.cardColors(containerColor = if((prod.stock ?: 0) == 0) Color(0xFFFFEBEE) else Color.White)) {
                        Row(Modifier.padding(12.dp), verticalAlignment = Alignment.CenterVertically) {
                            AsyncImage(
                                model = "http://10.0.2.2/DS92026/P1DS9/APP/img/${prod.imagen}",
                                contentDescription = null,
                                modifier = Modifier.size(60.dp),
                                contentScale = ContentScale.Fit
                            )
                            Spacer(Modifier.width(12.dp))
                            Column(Modifier.weight(1f)) {
                                Text(prod.nombre ?: "", fontWeight = FontWeight.Bold)
                                Text("Stock: ${prod.stock} | $${prod.precioVenta}", fontSize = 14.sp, color = if((prod.stock ?: 0) == 0) Color.Red else Color.Unspecified)
                            }
                            IconButton(onClick = { productoAEditar = prod }) {
                                Icon(Icons.Default.Edit, "Editar", tint = Color(0xFF2563EB))
                            }
                        }
                    }
                }
            }
        }
    }

    if (showFilterSheet) {
        FilterDialog(
            selectedCat = selectedCategoria,
            selectedMarc = selectedMarca,
            selectedSort = currentSort,
            onDismiss = { showFilterSheet = false },
            onApply = { cat, marc, sort ->
                selectedCategoria = cat
                selectedMarca = marc
                currentSort = sort
                showFilterSheet = false
            }
        )
    }

    if (productoAEditar != null || mostrarFormularioNuevo) {
        AlertDialog(onDismissRequest = { productoAEditar = null; mostrarFormularioNuevo = false }, confirmButton = {}, text = {
            ProductForm(producto = productoAEditar, codigoDefault = productoAEditar?.idProducto ?: "", onSaved = { productoAEditar = null; mostrarFormularioNuevo = false; cargarProductos() }, onCancel = { productoAEditar = null; mostrarFormularioNuevo = false })
        })
    }
}

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun EmployeeDashboardView() {
    var productosOriginales by remember { mutableStateOf<List<Producto>>(emptyList()) }
    var productosFiltrados by remember { mutableStateOf<List<Producto>>(emptyList()) }
    var isLoading by remember { mutableStateOf(true) }

    var searchQuery by remember { mutableStateOf("") }
    var selectedCategoria by remember { mutableStateOf("Todas") }
    var selectedMarca by remember { mutableStateOf("Todas") }
    var currentSort by remember { mutableStateOf("Ninguno") }
    var showFilterSheet by remember { mutableStateOf(false) }

    LaunchedEffect(Unit) {
        RetrofitClient.instance.getCatalogo().enqueue(object : Callback<List<Producto>> {
            override fun onResponse(call: Call<List<Producto>>, response: Response<List<Producto>>) {
                productosOriginales = response.body() ?: emptyList()
                isLoading = false
            }
            override fun onFailure(call: Call<List<Producto>>, t: Throwable) { isLoading = false }
        })
    }

    LaunchedEffect(searchQuery, selectedCategoria, selectedMarca, currentSort, productosOriginales) {
        val nombresCategorias = mapOf("1" to "discos duros", "2" to "fuentes de poder", "3" to "memorias ram", "4" to "procesadores", "5" to "tarjetas madres")
        val nombresMarcas = mapOf("1" to "KINGSTON", "2" to "CRUCIAL", "3" to "XYZ", "4" to "G-SKILL", "5" to "INTEL", "6" to "AMD", "7" to "GIGABYTE", "8" to "MSI")

        var temp = productosOriginales.filter { prod ->
            val matchesSearch = if (searchQuery.isBlank()) true else {
                val nameMatch = prod.nombre?.contains(searchQuery, ignoreCase = true) ?: false
                val catMatch = (nombresCategorias[prod.idCategoria.toString()] ?: "").contains(searchQuery, ignoreCase = true)
                val marcMatch = (nombresMarcas[prod.idMarca.toString()] ?: "").contains(searchQuery, ignoreCase = true)
                nameMatch || catMatch || marcMatch
            }
            val matchesCat = selectedCategoria == "Todas" || prod.idCategoria.toString() == selectedCategoria
            val matchesMarc = selectedMarca == "Todas" || prod.idMarca.toString() == selectedMarca
            matchesSearch && matchesCat && matchesMarc
        }

        temp = when (currentSort) {
            "Precio: Menor a Mayor" -> temp.sortedBy { it.precioVenta }
            "Precio: Mayor a Menor" -> temp.sortedByDescending { it.precioVenta }
            "Nombre: A-Z" -> temp.sortedBy { it.nombre }
            "Nombre: Z-A" -> temp.sortedByDescending { it.nombre }
            else -> temp
        }
        productosFiltrados = temp
    }

    Column(Modifier.fillMaxSize()) {
        Column(Modifier.background(Color.White).padding(8.dp)) {
            TextField(
                value = searchQuery,
                onValueChange = { searchQuery = it },
                modifier = Modifier.fillMaxWidth(),
                placeholder = { Text("Buscar en inventario...") },
                leadingIcon = { Icon(Icons.Default.Search, null) },
                trailingIcon = {
                    IconButton(onClick = { showFilterSheet = true }) {
                        Icon(Icons.Default.FilterList, contentDescription = "Filtros")
                    }
                },
                colors = TextFieldDefaults.colors(focusedContainerColor = Color(0xFFF1F5F9), unfocusedContainerColor = Color(0xFFF1F5F9)),
                singleLine = true
            )
        }

        if (isLoading) {
            Box(Modifier.fillMaxSize(), contentAlignment = Alignment.Center) { CircularProgressIndicator() }
        } else {
            LazyColumn(contentPadding = PaddingValues(16.dp), verticalArrangement = Arrangement.spacedBy(8.dp)) {
                items(productosFiltrados) { prod ->
                    Card(modifier = Modifier.fillMaxWidth(), colors = CardDefaults.cardColors(containerColor = if((prod.stock ?: 0) == 0) Color(0xFFFFEBEE) else Color.White)) {
                        Row(Modifier.padding(16.dp), verticalAlignment = Alignment.CenterVertically) {
                            AsyncImage(
                                model = "http://10.0.2.2/DS92026/P1DS9/APP/img/${prod.imagen}",
                                contentDescription = null,
                                modifier = Modifier.size(80.dp),
                                contentScale = ContentScale.Fit
                            )
                            Spacer(Modifier.width(16.dp))
                            Column {
                                Text(prod.nombre ?: "", fontWeight = FontWeight.Bold, fontSize = 16.sp)
                                Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                                    Text("Stock: ${prod.stock}", color = if((prod.stock ?: 0) == 0) Color.Red else Color.Black, fontWeight = if((prod.stock ?: 0) == 0) FontWeight.Bold else FontWeight.Normal)
                                    Text("Precio: $${prod.precioVenta}", fontWeight = FontWeight.ExtraBold, color = Color(0xFF2563EB))
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    if (showFilterSheet) {
        FilterDialog(
            selectedCat = selectedCategoria,
            selectedMarc = selectedMarca,
            selectedSort = currentSort,
            onDismiss = { showFilterSheet = false },
            onApply = { cat, marc, sort ->
                selectedCategoria = cat
                selectedMarca = marc
                currentSort = sort
                showFilterSheet = false
            }
        )
    }
}

@Composable
fun DashboardCard(title: String, subtitle: String, icon: ImageVector, color: Color, onClick: () -> Unit) {
    Card(
        onClick = onClick,
        modifier = Modifier.fillMaxWidth(),
        shape = RoundedCornerShape(16.dp),
        colors = CardDefaults.cardColors(containerColor = Color.White),
        elevation = CardDefaults.cardElevation(4.dp)
    ) {
        Row(Modifier.padding(24.dp), verticalAlignment = Alignment.CenterVertically) {
            Surface(color = color.copy(alpha = 0.1f), shape = RoundedCornerShape(12.dp)) {
                Icon(icon, null, modifier = Modifier.padding(12.dp).size(32.dp), tint = color)
            }
            Spacer(Modifier.width(20.dp))
            Column(Modifier.weight(1f)) {
                Text(title, fontWeight = FontWeight.Bold, fontSize = 18.sp)
                Text(subtitle, fontSize = 14.sp, color = Color.Gray)
            }
        }
    }
}

@Composable
fun RegistroEmpleadoView(onBack: () -> Unit) {
    var usuario by remember { mutableStateOf("") }
    var nombre by remember { mutableStateOf("") }
    var apellido by remember { mutableStateOf("") }
    var pass by remember { mutableStateOf("") }
    val context = LocalContext.current

    Column(Modifier.padding(16.dp).verticalScroll(rememberScrollState()), verticalArrangement = Arrangement.spacedBy(12.dp)) {
        Row(verticalAlignment = Alignment.CenterVertically) {
            IconButton(onClick = onBack) { Icon(Icons.Default.ArrowBack, null) }
            Text("Registrar Empleado", style = MaterialTheme.typography.titleLarge)
        }
        OutlinedTextField(value = usuario, onValueChange = { usuario = it }, label = { Text("Usuario") }, modifier = Modifier.fillMaxWidth())
        OutlinedTextField(value = nombre, onValueChange = { nombre = it }, label = { Text("Nombre") }, modifier = Modifier.fillMaxWidth())
        OutlinedTextField(value = apellido, onValueChange = { apellido = it }, label = { Text("Apellido") }, modifier = Modifier.fillMaxWidth())
        OutlinedTextField(value = pass, onValueChange = { pass = it }, label = { Text("Contraseña") }, modifier = Modifier.fillMaxWidth())
        
        Button(
            modifier = Modifier.fillMaxWidth().height(50.dp),
            onClick = {
                if (usuario.isBlank() || nombre.isBlank() || apellido.isBlank() || pass.isBlank()) {
                    Toast.makeText(context, "Por favor complete todos los campos", Toast.LENGTH_SHORT).show()
                    return@Button
                }

                val req = RegistroEmpleadoRequest(usuario, nombre, apellido, pass)
                RetrofitClient.instance.registrarEmpleado(req).enqueue(object : Callback<BaseResponse> {
                    override fun onResponse(call: Call<BaseResponse>, response: Response<BaseResponse>) {
                        if (response.isSuccessful && response.body()?.ok == true) {
                            Toast.makeText(context, "¡Empleado registrado con éxito!", Toast.LENGTH_LONG).show()
                            onBack()
                        } else {
                            val msg = response.body()?.mensaje ?: "Error en el servidor"
                            Toast.makeText(context, "Error: $msg", Toast.LENGTH_LONG).show()
                        }
                    }
                    override fun onFailure(call: Call<BaseResponse>, t: Throwable) {
                        Toast.makeText(context, "Fallo de red: ${t.message}", Toast.LENGTH_LONG).show()
                    }
                })
            }
        ) {
            Text("Guardar Empleado")
        }
    }
}

@Composable
fun HistorialFacturasView(onBack: () -> Unit) {
    var facturas by remember { mutableStateOf<List<Factura>>(emptyList()) }
    var isLoading by remember { mutableStateOf(true) }

    LaunchedEffect(Unit) {
        RetrofitClient.instance.getHistorialFacturas().enqueue(object : Callback<List<Factura>> {
            override fun onResponse(call: Call<List<Factura>>, response: Response<List<Factura>>) {
                facturas = response.body() ?: emptyList()
                isLoading = false
            }
            override fun onFailure(call: Call<List<Factura>>, t: Throwable) { isLoading = false }
        })
    }

    Column(Modifier.padding(16.dp)) {
        Row(verticalAlignment = Alignment.CenterVertically) {
            IconButton(onClick = onBack) { Icon(Icons.Default.ArrowBack, null) }
            Text("Historial de Ventas", style = MaterialTheme.typography.titleLarge)
        }
        if (isLoading) {
            Box(Modifier.fillMaxSize(), contentAlignment = Alignment.Center) { CircularProgressIndicator() }
        } else {
            LazyColumn(verticalArrangement = Arrangement.spacedBy(12.dp)) {
                items(facturas) { fac ->
                    Card(
                        modifier = Modifier.fillMaxWidth(),
                        colors = CardDefaults.cardColors(containerColor = Color.White),
                        elevation = CardDefaults.cardElevation(2.dp)
                    ) {
                        Column(Modifier.padding(16.dp)) {
                            Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                                Text("Factura #${fac.idFactura}", fontWeight = FontWeight.Bold, fontSize = 16.sp)
                                Text(fac.fecha ?: "", color = Color.Gray, fontSize = 12.sp)
                            }
                            Spacer(Modifier.height(8.dp))
                            
                            // Mostrar detalles si existen
                            fac.detalles?.forEach { det ->
                                Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                                    Text("${det.nombreProducto ?: "Producto ${det.idProducto}"} x${det.cantidad}", fontSize = 14.sp)
                                    Text("$${String.format("%.2f", det.precio_unitario * det.cantidad)}", fontSize = 14.sp)
                                }
                            }
                            
                            HorizontalDivider(Modifier.padding(vertical = 8.dp))
                            
                            Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                                Text("Subtotal:", fontSize = 12.sp)
                                Text("$${String.format("%.2f", fac.subtotal)}", fontSize = 12.sp)
                            }
                            Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                                Text("ITBMS (7%):", fontSize = 12.sp)
                                Text("$${String.format("%.2f", fac.itbms)}", fontSize = 12.sp)
                            }
                            Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                                Text("TOTAL:", fontWeight = FontWeight.ExtraBold, color = Color(0xFF2563EB), fontSize = 16.sp)
                                Text("$${String.format("%.2f", fac.total)}", fontWeight = FontWeight.ExtraBold, color = Color(0xFF2563EB), fontSize = 16.sp)
                            }
                        }
                    }
                }
            }
        }
    }
}
