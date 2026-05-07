package com.example.proyecto1

import android.content.Intent
import android.os.Bundle
import android.widget.Toast
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.compose.foundation.background
import androidx.compose.foundation.horizontalScroll
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.grid.GridCells
import androidx.compose.foundation.lazy.grid.LazyVerticalGrid
import androidx.compose.foundation.lazy.grid.items
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.verticalScroll
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.material.icons.filled.Add
import androidx.compose.material.icons.filled.Close
import androidx.compose.material.icons.filled.Dashboard
import androidx.compose.material.icons.filled.Delete
import androidx.compose.material.icons.filled.FilterList
import androidx.compose.material.icons.filled.Person
import androidx.compose.material.icons.filled.Remove
import androidx.compose.material.icons.filled.Search
import androidx.compose.material.icons.filled.ShoppingCart
import androidx.compose.material.icons.Icons
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.TextRange
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.input.TextFieldValue
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

class MainActivity : ComponentActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContent {
            Proyecto1Theme {
                MainScreen()
            }
        }
    }
}

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun MainScreen() {
    val context = LocalContext.current
    var productosOriginales by remember { mutableStateOf<List<Producto>>(emptyList()) }
    var productosFiltrados by remember { mutableStateOf<List<Producto>>(emptyList()) }
    var isLoading by remember { mutableStateOf(true) }
    
    // Estados para Filtros
    var searchQuery by remember { mutableStateOf("") }
    var selectedCategoria by remember { mutableStateOf("Todas") }
    var selectedMarca by remember { mutableStateOf("Todas") }
    var currentSort by remember { mutableStateOf("Ninguno") }
    
    var showFilterSheet by remember { mutableStateOf(false) }
    
    // Detalle de Producto
    var productoSeleccionado by remember { mutableStateOf<Producto?>(null) }
    
    // Carrito
    var carritoItems by remember { mutableStateOf<Map<String, Int>>(emptyMap()) }
    var showCartDialog by remember { mutableStateOf(false) }
    var showPaymentDialog by remember { mutableStateOf(false) }
    
    // Perfil / Sesión
    val sessionManager = remember { SessionManager(context) }
    var showProfileDialog by remember { mutableStateOf(false) }

    val azulWeb = Color(0xFF2563EB)

    LaunchedEffect(Unit) {
        RetrofitClient.instance.getCatalogo().enqueue(object : Callback<List<Producto>> {
            override fun onResponse(call: Call<List<Producto>>, response: Response<List<Producto>>) {
                if (response.isSuccessful) {
                    val lista = response.body() ?: emptyList()
                    productosOriginales = lista
                    productosFiltrados = lista
                }
                isLoading = false
            }
            override fun onFailure(call: Call<List<Producto>>, t: Throwable) {
                isLoading = false
                Toast.makeText(context, "Fallo: ${t.message}", Toast.LENGTH_LONG).show()
            }
        })
    }

    // Lógica de Filtrado y Ordenamiento
    LaunchedEffect(searchQuery, selectedCategoria, selectedMarca, currentSort, productosOriginales) {
        val nombresCategorias = mapOf(
            "1" to "discos duros",
            "2" to "fuentes de poder",
            "3" to "memorias ram",
            "4" to "procesadores",
            "5" to "tarjetas madres"
        )
        val nombresMarcas = mapOf(
            "1" to "KINGSTON",
            "2" to "CRUCIAL",
            "3" to "XYZ",
            "4" to "G-SKILL",
            "5" to "INTEL",
            "6" to "AMD",
            "7" to "GIGABYTE",
            "8" to "MSI"
        )

        var temp = productosOriginales.filter { prod ->
            // Filtro de stock: Solo mostrar si stock > 0
            if ((prod.stock ?: 0) <= 0) return@filter false

            val matchesSearch = if (searchQuery.isBlank()) true else {
                val nameMatch = prod.nombre?.contains(searchQuery, ignoreCase = true) ?: false
                val descMatch = prod.descripcion?.contains(searchQuery, ignoreCase = true) ?: false
                
                // Buscar por nombre de categoría o marca si no hay coincidencia en nombre/descripción
                val catName = nombresCategorias[prod.idCategoria.toString()] ?: ""
                val marcName = nombresMarcas[prod.idMarca.toString()] ?: ""
                val catMatch = catName.contains(searchQuery, ignoreCase = true)
                val marcMatch = marcName.contains(searchQuery, ignoreCase = true)

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

    Scaffold(
        topBar = {
            Column {
                TopAppBar(
                    title = { Text("Elvis Tech", color = Color.White, fontWeight = FontWeight.Bold) },
                    colors = TopAppBarDefaults.topAppBarColors(containerColor = azulWeb),
                    actions = {
                        val totalItems = carritoItems.values.sum()
                        BadgedBox(badge = { if(totalItems > 0) Badge { Text("$totalItems") } }) {
                            IconButton(onClick = { showCartDialog = true }) {
                                Icon(Icons.Default.ShoppingCart, "Carrito", tint = Color.White)
                            }
                        }
                        IconButton(onClick = {
                            if (sessionManager.isLogged()) {
                                showProfileDialog = true
                            } else {
                                context.startActivity(Intent(context, LoginActivity::class.java))
                            }
                        }) {
                            Icon(Icons.Default.Person, "Perfil", tint = Color.White)
                        }
                    }
                )
                // Barra de Búsqueda
                TextField(
                    value = searchQuery,
                    onValueChange = { searchQuery = it },
                    modifier = Modifier.fillMaxWidth().padding(8.dp),
                    placeholder = { Text("Buscar producto...") },
                    leadingIcon = { Icon(Icons.Default.Search, contentDescription = null) },
                    trailingIcon = {
                        IconButton(onClick = { showFilterSheet = true }) {
                            Icon(Icons.Default.FilterList, contentDescription = "Filtros")
                        }
                    },
                    colors = TextFieldDefaults.colors(
                        focusedContainerColor = Color.White,
                        unfocusedContainerColor = Color.White
                    ),
                    singleLine = true
                )
            }
        }
    ) { paddingValues ->
        Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(paddingValues)
                .background(Color(0xFFF1F5F9))
        ) {
            if (isLoading) {
                Box(Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
                    CircularProgressIndicator(color = azulWeb)
                }
            } else {
                Text(
                    "Resultados (${productosFiltrados.size})",
                    modifier = Modifier.padding(horizontal = 16.dp, vertical = 8.dp),
                    style = MaterialTheme.typography.labelLarge,
                    color = Color.Gray
                )

                LazyVerticalGrid(
                    columns = GridCells.Fixed(2),
                    contentPadding = PaddingValues(8.dp),
                    modifier = Modifier.fillMaxSize()
                ) {
                    items(productosFiltrados) { producto ->
                        ProductoCard(
                            producto = producto,
                            onCardClick = { productoSeleccionado = producto },
                            onAdd = {
                                val actualEnCarrito = carritoItems[producto.idProducto] ?: 0
                                if (actualEnCarrito < (producto.stock ?: 0)) {
                                    carritoItems = carritoItems + (producto.idProducto to actualEnCarrito + 1)
                                    Toast.makeText(context, "Agregado: ${producto.nombre}", Toast.LENGTH_SHORT).show()
                                } else {
                                    Toast.makeText(context, "Stock máximo alcanzado", Toast.LENGTH_SHORT).show()
                                }
                            }
                        )
                    }
                }
            }
        }

        if (productoSeleccionado != null) {
            ProductDetailDialog(
                producto = productoSeleccionado!!,
                onDismiss = { productoSeleccionado = null },
                onAdd = {
                    val actualEnCarrito = carritoItems[productoSeleccionado!!.idProducto] ?: 0
                    if (actualEnCarrito < (productoSeleccionado!!.stock ?: 0)) {
                        carritoItems = carritoItems + (productoSeleccionado!!.idProducto to actualEnCarrito + 1)
                        Toast.makeText(context, "Agregado: ${productoSeleccionado!!.nombre}", Toast.LENGTH_SHORT).show()
                        productoSeleccionado = null
                    } else {
                        Toast.makeText(context, "Stock máximo alcanzado", Toast.LENGTH_SHORT).show()
                    }
                }
            )
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

        if (showCartDialog) {
            CarritoDialog(
                itemsEnCarrito = carritoItems,
                catalogoCompleto = productosOriginales,
                onDismiss = { showCartDialog = false },
                onUpdateQuantity = { id, nuevaCant ->
                    if (nuevaCant <= 0) {
                        carritoItems = carritoItems - id
                    } else {
                        carritoItems = carritoItems + (id to nuevaCant)
                    }
                },
                onGoToPayment = {
                    showCartDialog = false
                    showPaymentDialog = true
                }
            )
        }

        if (showPaymentDialog) {
            PagoDialog(
                itemsEnCarrito = carritoItems,
                catalogoCompleto = productosOriginales,
                onDismiss = { showPaymentDialog = false },
                onPaymentSuccess = {
                    carritoItems = emptyMap()
                    showPaymentDialog = false
                }
            )
        }

        if (showProfileDialog) {
            ProfileDialog(
                sessionManager = sessionManager,
                onDismiss = { showProfileDialog = false },
                onLogout = {
                    sessionManager.logout()
                    showProfileDialog = false
                    Toast.makeText(context, "Sesión cerrada", Toast.LENGTH_SHORT).show()
                },
                onGoToDashboard = {
                    showProfileDialog = false
                    context.startActivity(Intent(context, DashboardActivity::class.java))
                }
            )
        }
    }
}

@Composable
fun ProfileDialog(
    sessionManager: SessionManager,
    onDismiss: () -> Unit,
    onLogout: () -> Unit,
    onGoToDashboard: () -> Unit
) {
    AlertDialog(
        onDismissRequest = onDismiss,
        title = { Text("Mi Perfil", fontWeight = FontWeight.Bold) },
        text = {
            Column(modifier = Modifier.fillMaxWidth(), horizontalAlignment = Alignment.CenterHorizontally) {
                Icon(
                    Icons.Default.Person,
                    contentDescription = null,
                    modifier = Modifier.size(64.dp),
                    tint = Color(0xFF2563EB)
                )
                Spacer(Modifier.height(16.dp))
                Text("Bienvenido,", style = MaterialTheme.typography.bodyMedium)
                Text(
                    sessionManager.getNombre(), 
                    style = MaterialTheme.typography.titleLarge, 
                    fontWeight = FontWeight.Bold
                )
                Text(
                    if(sessionManager.getRol() == 1) "Administrador" else "Empleado",
                    style = MaterialTheme.typography.bodySmall,
                    color = Color.Gray
                )
                
                Spacer(Modifier.height(24.dp))
                
                Button(
                    onClick = onGoToDashboard,
                    modifier = Modifier.fillMaxWidth(),
                    colors = ButtonDefaults.buttonColors(containerColor = Color(0xFF2563EB))
                ) {
                    Icon(Icons.Default.Dashboard, null)
                    Spacer(Modifier.width(8.dp))
                    Text("Ir al Panel de Control")
                }
            }
        },
        confirmButton = {
            TextButton(onClick = onLogout) {
                Text("Cerrar Sesión", color = Color.Red)
            }
        },
        dismissButton = {
            TextButton(onClick = onDismiss) {
                Text("Cerrar")
            }
        }
    )
}

@Composable
fun FilterDialog(
    selectedCat: String,
    selectedMarc: String,
    selectedSort: String,
    onDismiss: () -> Unit,
    onApply: (String, String, String) -> Unit
) {
    var tempCat by remember { mutableStateOf(selectedCat) }
    var tempMarc by remember { mutableStateOf(selectedMarc) }
    var tempSort by remember { mutableStateOf(selectedSort) }

    val categorias = listOf("Todas", "1", "2", "3", "4", "5") // IDs de tu DB
    val marcas = listOf("Todas", "1", "2", "3", "4", "5", "6", "7", "8") // IDs de tu DB
    val opcionesSort = listOf("Ninguno", "Precio: Menor a Mayor", "Precio: Mayor a Menor", "Nombre: A-Z", "Nombre: Z-A")

    val nombresCategorias = mapOf(
        "1" to "discos duros",
        "2" to "fuentes de poder",
        "3" to "memorias ram",
        "4" to "procesadores",
        "5" to "tarjetas madres"
    )
    val nombresMarcas = mapOf(
        "1" to "KINGSTON",
        "2" to "CRUCIAL",
        "3" to "XYZ",
        "4" to "G-SKILL",
        "5" to "INTEL",
        "6" to "AMD",
        "7" to "GIGABYTE",
        "8" to "MSI"
    )

    AlertDialog(
        onDismissRequest = onDismiss,
        title = { Text("Filtros y Ordenamiento") },
        text = {
            Column(verticalArrangement = Arrangement.spacedBy(12.dp)) {
                Text("Ordenar por:", fontWeight = FontWeight.Bold)
                opcionesSort.forEach { opcion ->
                    Row(verticalAlignment = Alignment.CenterVertically) {
                        RadioButton(selected = tempSort == opcion, onClick = { tempSort = opcion })
                        Text(opcion, fontSize = 14.sp)
                    }
                }
                
                Text("Categoría:", fontWeight = FontWeight.Bold)
                // Aquí podrías usar un DropdownMenu, pero por simplicidad usamos chips o Row
                Row(modifier = Modifier.horizontalScroll(rememberScrollState())) {
                    categorias.forEach { cat ->
                        FilterChip(
                            selected = tempCat == cat,
                            onClick = { tempCat = cat },
                            label = { Text(if(cat == "Todas") "Todas" else nombresCategorias[cat] ?: "Cat $cat") },
                            modifier = Modifier.padding(end = 4.dp)
                        )
                    }
                }

                Text("Marca:", fontWeight = FontWeight.Bold)
                Row(modifier = Modifier.horizontalScroll(rememberScrollState())) {
                    marcas.forEach { marc ->
                        FilterChip(
                            selected = tempMarc == marc,
                            onClick = { tempMarc = marc },
                            label = { Text(if(marc == "Todas") "Todas" else nombresMarcas[marc] ?: "Marca $marc") },
                            modifier = Modifier.padding(end = 4.dp)
                        )
                    }
                }
            }
        },
        confirmButton = {
            Button(onClick = { onApply(tempCat, tempMarc, tempSort) }) { Text("Aplicar") }
        },
        dismissButton = {
            TextButton(onClick = onDismiss) { Text("Cancelar") }
        }
    )
}

@Composable
fun CarritoDialog(
    itemsEnCarrito: Map<String, Int>,
    catalogoCompleto: List<Producto>,
    onDismiss: () -> Unit,
    onUpdateQuantity: (String, Int) -> Unit,
    onGoToPayment: () -> Unit
) {
    var itemAEliminar by remember { mutableStateOf<Producto?>(null) }

    val productosSeleccionados = catalogoCompleto.filter { itemsEnCarrito.containsKey(it.idProducto) }
    
    val subtotal = productosSeleccionados.sumOf { (it.precioVenta ?: 0.0) * (itemsEnCarrito[it.idProducto] ?: 0) }
    val itbms = subtotal * 0.07
    val total = subtotal + itbms

    AlertDialog(
        onDismissRequest = onDismiss,
        title = { Text("Resumen del Carrito", fontWeight = FontWeight.Bold) },
        text = {
            Column(modifier = Modifier.fillMaxWidth().verticalScroll(rememberScrollState())) {
                if (itemsEnCarrito.isEmpty()) {
                    Text("El carrito está vacío")
                } else {
                    productosSeleccionados.forEach { prod ->
                        val cant = itemsEnCarrito[prod.idProducto] ?: 0
                        Column(Modifier.fillMaxWidth().padding(vertical = 8.dp)) {
                            Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween, verticalAlignment = Alignment.CenterVertically) {
                                Text(prod.nombre ?: "", modifier = Modifier.weight(1f), fontSize = 14.sp, fontWeight = FontWeight.Bold)
                                Text("$${String.format(java.util.Locale.US, "%.2f", (prod.precioVenta ?: 0.0) * cant)}", fontSize = 14.sp)
                            }
                            Row(verticalAlignment = Alignment.CenterVertically) {
                                IconButton(onClick = {
                                    if (cant > 1) {
                                        onUpdateQuantity(prod.idProducto, cant - 1)
                                    } else {
                                        itemAEliminar = prod
                                    }
                                }) {
                                    Icon(Icons.Default.Remove, "Menos", tint = Color.Red)
                                }
                                Text("$cant", fontSize = 16.sp, modifier = Modifier.padding(horizontal = 8.dp))
                                IconButton(onClick = {
                                    if (cant < (prod.stock ?: 0)) {
                                        onUpdateQuantity(prod.idProducto, cant + 1)
                                    }
                                }) {
                                    Icon(Icons.Default.Add, "Mas", tint = Color(0xFF2563EB))
                                }
                                Spacer(Modifier.weight(1f))
                                IconButton(onClick = { itemAEliminar = prod }) {
                                    Icon(Icons.Default.Delete, "Eliminar", tint = Color.Gray)
                                }
                            }
                        }
                    }
                    HorizontalDivider(Modifier.padding(vertical = 8.dp))
                    Text("Subtotal: $${String.format(java.util.Locale.US, "%.2f", subtotal)}", fontSize = 14.sp)
                    Text("ITBMS (7%): $${String.format(java.util.Locale.US, "%.2f", itbms)}", fontSize = 14.sp)
                    Text("Total: $${String.format(java.util.Locale.US, "%.2f", total)}", fontWeight = FontWeight.Bold, fontSize = 16.sp, color = Color(0xFF2563EB))
                }
            }
        },
        confirmButton = {
            Button(
                onClick = onGoToPayment,
                enabled = itemsEnCarrito.isNotEmpty(),
                colors = ButtonDefaults.buttonColors(containerColor = Color(0xFF2563EB))
            ) {
                Text("Comprar")
            }
        },
        dismissButton = {
            TextButton(onClick = onDismiss) { Text("Cerrar") }
        }
    )

    if (itemAEliminar != null) {
        AlertDialog(
            onDismissRequest = { itemAEliminar = null },
            title = { Text("¿Deseas eliminar este producto del carrito de compras?") },
            text = { Text("Esta acción quitará el producto del resumen.") },
            confirmButton = {
                Button(
                    onClick = {
                        onUpdateQuantity(itemAEliminar!!.idProducto, 0)
                        itemAEliminar = null
                    },
                    colors = ButtonDefaults.buttonColors(containerColor = Color.Red)
                ) {
                    Text("Eliminar")
                }
            },
            dismissButton = {
                Button(
                    onClick = { itemAEliminar = null },
                    colors = ButtonDefaults.buttonColors(containerColor = Color(0xFF2563EB))
                ) {
                    Text("Cancelar")
                }
            }
        )
    }
}

@Composable
fun PagoDialog(
    itemsEnCarrito: Map<String, Int>,
    catalogoCompleto: List<Producto>,
    onDismiss: () -> Unit,
    onPaymentSuccess: () -> Unit
) {
    val context = LocalContext.current
    var numTarjeta by remember { mutableStateOf(TextFieldValue("")) }
    var fechaVence by remember { mutableStateOf(TextFieldValue("")) }
    var codSeguridad by remember { mutableStateOf("") }
    var isPaying by remember { mutableStateOf(false) }

    val productosSeleccionados = catalogoCompleto.filter { itemsEnCarrito.containsKey(it.idProducto) }
    
    val subtotal = productosSeleccionados.sumOf { (it.precioVenta ?: 0.0) * (itemsEnCarrito[it.idProducto] ?: 0) }
    val itbms = subtotal * 0.07
    val total = subtotal + itbms

    AlertDialog(
        onDismissRequest = onDismiss,
        title = { Text("Finalizar Pago", fontWeight = FontWeight.Bold) },
        text = {
            Column(modifier = Modifier.fillMaxWidth().verticalScroll(rememberScrollState())) {
                Text("Resumen:", fontWeight = FontWeight.Bold)
                Text("Subtotal: $${String.format(java.util.Locale.US, "%.2f", subtotal)}")
                Text("ITBMS (7%): $${String.format(java.util.Locale.US, "%.2f", itbms)}")
                Text("Total a pagar: $${String.format(java.util.Locale.US, "%.2f", total)}", 
                     color = Color(0xFF2563EB), fontWeight = FontWeight.Bold, fontSize = 18.sp)
                
                Spacer(Modifier.height(16.dp))
                
                OutlinedTextField(
                    value = numTarjeta,
                    onValueChange = { input ->
                        val clean = input.text.filter { it.isDigit() }
                        if (clean.length <= 16) {
                            val formatted = clean.chunked(4).joinToString(" ")
                            numTarjeta = TextFieldValue(
                                text = formatted,
                                selection = TextRange(formatted.length)
                            )
                        }
                    },
                    label = { Text("Número de Tarjeta (16 dígitos)") },
                    modifier = Modifier.fillMaxWidth(),
                    singleLine = true,
                    keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Number)
                )
                Spacer(Modifier.height(8.dp))
                Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                    OutlinedTextField(
                        value = fechaVence,
                        onValueChange = { input ->
                            val clean = input.text.filter { it.isDigit() }
                            if (clean.length <= 4) {
                                val formatted = when {
                                    clean.length > 2 -> "${clean.substring(0, 2)}/${clean.substring(2)}"
                                    else -> clean
                                }
                                // Mantenemos el cursor siempre al final
                                fechaVence = TextFieldValue(
                                    text = formatted,
                                    selection = TextRange(formatted.length)
                                )
                            }
                        },
                        label = { Text("Fecha Vence (MM/YY)") },
                        modifier = Modifier.weight(1f),
                        singleLine = true,
                        placeholder = { Text("05/28") },
                        keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Number)
                    )
                    OutlinedTextField(
                        value = codSeguridad,
                        onValueChange = { 
                            val clean = it.filter { char -> char.isDigit() }
                            if (clean.length <= 4) codSeguridad = clean 
                        },
                        label = { Text("CVV") },
                        modifier = Modifier.weight(1f),
                        singleLine = true,
                        keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Number)
                    )
                }
            }
        },
        confirmButton = {
            Button(
                onClick = {
                    val cleanNum = numTarjeta.text.replace(" ", "")
                    val cleanFecha = fechaVence.text
                    if (cleanNum.length < 16 || cleanFecha.length < 5 || codSeguridad.isEmpty()) {
                        Toast.makeText(context, "Por favor complete todos los datos correctamente", Toast.LENGTH_SHORT).show()
                        return@Button
                    }
                    
                    // Convertir MM/YY a YYYY-MM-01 para la BD (el día no importa)
                    val partes = cleanFecha.split("/")
                    val mes = partes[0]
                    val anio = "20${partes[1]}" // Asumimos siglo 21 (20xx)
                    val fechaParaBD = "$anio-$mes-01"

                    isPaying = true
                    
                    val itemsRequest = productosSeleccionados.map { 
                        ItemCarrito(it.idProducto, itemsEnCarrito[it.idProducto] ?: 0, it.precioVenta ?: 0.0)
                    }
                    
                    val request = PagoRequest(cleanNum, fechaParaBD, codSeguridad, itemsRequest, subtotal, itbms, total)
                    
                    RetrofitClient.instance.procesarPago(request).enqueue(object : Callback<BaseResponse> {
                        override fun onResponse(call: Call<BaseResponse>, response: Response<BaseResponse>) {
                            isPaying = false
                            if (response.isSuccessful && response.body()?.ok == true) {
                                Toast.makeText(context, "¡Pago Exitoso! Factura generada.", Toast.LENGTH_LONG).show()
                                onPaymentSuccess()
                            } else {
                                Toast.makeText(context, "Error: ${response.body()?.mensaje ?: "Error desconocido"}", Toast.LENGTH_SHORT).show()
                            }
                        }
                        override fun onFailure(call: Call<BaseResponse>, t: Throwable) {
                            isPaying = false
                            Toast.makeText(context, "Fallo de red: ${t.message}", Toast.LENGTH_SHORT).show()
                        }
                    })
                },
                enabled = !isPaying,
                colors = ButtonDefaults.buttonColors(containerColor = Color(0xFF2563EB))
            ) {
                if (isPaying) CircularProgressIndicator(color = Color.White, modifier = Modifier.size(20.dp))
                else Text("Pagar")
            }
        },
        dismissButton = {
            TextButton(onClick = onDismiss) { Text("Cerrar") }
        }
    )
}

@Composable
fun ProductDetailDialog(
    producto: Producto,
    onDismiss: () -> Unit,
    onAdd: () -> Unit
) {
    AlertDialog(
        onDismissRequest = onDismiss,
        title = {
            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.SpaceBetween,
                verticalAlignment = Alignment.CenterVertically
            ) {
                Text(text = "Detalles del Producto", fontWeight = FontWeight.Bold, fontSize = 18.sp)
                IconButton(onClick = onDismiss) {
                    Icon(Icons.Default.Close, contentDescription = "Cerrar")
                }
            }
        },
        text = {
            Column(
                modifier = Modifier.fillMaxWidth().verticalScroll(rememberScrollState()),
                horizontalAlignment = Alignment.CenterHorizontally
            ) {
                val imageUrl = "http://10.0.2.2/DS92026/P1DS9/APP/img/${producto.imagen}"
                AsyncImage(
                    model = imageUrl,
                    contentDescription = null,
                    modifier = Modifier.height(150.dp).fillMaxWidth(),
                    contentScale = ContentScale.Fit
                )
                
                Spacer(Modifier.height(16.dp))
                
                Text(
                    text = producto.nombre ?: "Sin nombre",
                    style = MaterialTheme.typography.titleLarge,
                    fontWeight = FontWeight.Bold
                )
                
                Spacer(Modifier.height(8.dp))
                
                val precioFormateado = String.format(java.util.Locale.US, "%.2f", producto.precioVenta ?: 0.0)
                Text(
                    text = "$$precioFormateado",
                    style = MaterialTheme.typography.headlineMedium,
                    color = Color(0xFF2563EB),
                    fontWeight = FontWeight.ExtraBold
                )

                Text(
                    text = "Stock disponible: ${producto.stock}",
                    style = MaterialTheme.typography.bodyMedium,
                    color = Color.Gray
                )

                HorizontalDivider(Modifier.padding(vertical = 12.dp))
                
                Text(
                    text = "Descripción:",
                    modifier = Modifier.fillMaxWidth(),
                    style = MaterialTheme.typography.labelLarge,
                    fontWeight = FontWeight.Bold
                )
                Text(
                    text = producto.descripcion ?: "Sin descripción disponible.",
                    modifier = Modifier.fillMaxWidth(),
                    style = MaterialTheme.typography.bodyMedium
                )
            }
        },
        confirmButton = {
            Button(
                onClick = onAdd,
                modifier = Modifier.fillMaxWidth(),
                colors = ButtonDefaults.buttonColors(containerColor = Color(0xFF2563EB))
            ) {
                Text("Añadir al carrito")
            }
        }
    )
}

@Composable
fun ProductoCard(producto: Producto, onCardClick: () -> Unit, onAdd: () -> Unit) {
    Card(
        modifier = Modifier
            .padding(8.dp)
            .fillMaxWidth(),
        onClick = onCardClick,
        colors = CardDefaults.cardColors(containerColor = Color.White),
        elevation = CardDefaults.cardElevation(4.dp)
    ) {
        Column(horizontalAlignment = Alignment.CenterHorizontally, modifier = Modifier.padding(8.dp)) {
            // URL DE IMAGEN (Apunta a tu carpeta de imágenes en XAMPP)
            val imageUrl = "http://10.0.2.2/DS92026/P1DS9/APP/img/${producto.imagen}"

            AsyncImage(
                model = imageUrl,
                contentDescription = null,
                modifier = Modifier.height(100.dp).fillMaxWidth(),
                contentScale = ContentScale.Fit
            )

            Text(producto.nombre ?: "Sin nombre", maxLines = 1, fontWeight = FontWeight.Bold, fontSize = 14.sp)
            
            // Formatear precio a 2 decimales (usando punto decimal siempre)
            val precioFormateado = String.format(java.util.Locale.US, "%.2f", producto.precioVenta ?: 0.0)
            Text("$$precioFormateado", color = Color(0xFF2563EB), fontWeight = FontWeight.ExtraBold)

            Text("Stock: ${producto.stock}", fontSize = 10.sp, color = Color.Gray)

            Button(
                onClick = onAdd,
                modifier = Modifier.fillMaxWidth().padding(top = 8.dp),
                colors = ButtonDefaults.buttonColors(containerColor = Color(0xFF2563EB)),
                contentPadding = PaddingValues(0.dp)
            ) {
                Text("Comprar", fontSize = 12.sp)
            }
        }
    }
}
