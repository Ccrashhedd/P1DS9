package com.example.proyecto1

import android.Manifest
import android.content.Intent
import android.content.pm.PackageManager
import android.os.Bundle
import android.widget.Toast
import androidx.activity.ComponentActivity
import androidx.activity.compose.rememberLauncherForActivityResult
import androidx.activity.compose.setContent
import androidx.activity.result.contract.ActivityResultContracts
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.automirrored.filled.Logout
import androidx.compose.material.icons.filled.Add
import androidx.compose.material.icons.filled.QrCodeScanner
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.core.content.ContextCompat
import com.example.proyecto1.model.*
import com.example.proyecto1.network.RetrofitClient
import com.example.proyecto1.network.SessionManager
import com.example.proyecto1.scanner.CameraPreview
import com.example.proyecto1.ui.theme.Proyecto1Theme
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response

class AdminScannerActivity : ComponentActivity() {
    @OptIn(ExperimentalMaterial3Api::class)
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContent {
            Proyecto1Theme {
                val context = LocalContext.current
                val sessionManager = remember { SessionManager(context) }
                
                var hasCameraPermission by remember {
                    mutableStateOf(
                        ContextCompat.checkSelfPermission(
                            context,
                            Manifest.permission.CAMERA
                        ) == PackageManager.PERMISSION_GRANTED
                    )
                }

                val launcher = rememberLauncherForActivityResult(
                    contract = ActivityResultContracts.RequestPermission(),
                    onResult = { granted -> hasCameraPermission = granted }
                )

                LaunchedEffect(Unit) {
                    if (!hasCameraPermission) {
                        launcher.launch(Manifest.permission.CAMERA)
                    }
                }

                var scannResult by remember { mutableStateOf<String?>(null) }
                var showForm by remember { mutableStateOf(false) }
                var selectedProducto by remember { mutableStateOf<Producto?>(null) }
                var isScanning by remember { mutableStateOf(true) }

                Scaffold(
                    topBar = {
                        TopAppBar(
                            title = { Text("Panel Administrador", color = Color.White) },
                            colors = TopAppBarDefaults.topAppBarColors(containerColor = Color(0xFF1E293B)),
                            navigationIcon = {
                                IconButton(onClick = {
                                    startActivity(Intent(context, MainActivity::class.java))
                                    finish()
                                }) {
                                    Icon(Icons.AutoMirrored.Filled.ArrowBack, "Volver", tint = Color.White)
                                }
                            },
                            actions = {
                                IconButton(onClick = {
                                    sessionManager.logout()
                                    startActivity(Intent(context, MainActivity::class.java))
                                    finish()
                                }) {
                                    Icon(Icons.AutoMirrored.Filled.Logout, "Cerrar Sesión", tint = Color.White)
                                }
                            }
                        )
                    }
                ) { padding ->
                    Surface(
                        modifier = Modifier
                            .fillMaxSize()
                            .padding(padding),
                        color = Color(0xFFF8FAFC)
                    ) {
                        Column(
                            modifier = Modifier
                                .fillMaxSize()
                                .padding(16.dp)
                                .verticalScroll(rememberScrollState()),
                            horizontalAlignment = Alignment.CenterHorizontally
                        ) {
                            if (hasCameraPermission) {
                                if (isScanning) {
                                    Card(
                                        modifier = Modifier
                                            .fillMaxWidth()
                                            .height(350.dp),
                                        elevation = CardDefaults.cardElevation(8.dp),
                                        shape = RoundedCornerShape(16.dp)
                                    ) {
                                        Box(modifier = Modifier.fillMaxSize()) {
                                            CameraPreview { code ->
                                                isScanning = false
                                                scannResult = code
                                                analizarCodigo(code) { producto ->
                                                    selectedProducto = producto
                                                    showForm = true
                                                }
                                            }
                                        }
                                    }
                                    
                                    Spacer(modifier = Modifier.height(24.dp))
                                    
                                    Icon(
                                        Icons.Default.QrCodeScanner,
                                        contentDescription = null,
                                        modifier = Modifier.size(48.dp),
                                        tint = Color(0xFF64748B)
                                    )
                                    Text(
                                        "Escanea el código de barras del producto",
                                        style = MaterialTheme.typography.bodyLarge,
                                        color = Color(0xFF64748B),
                                        modifier = Modifier.padding(top = 8.dp)
                                    )
                                }
                            } else {
                                Box(Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
                                    Column(horizontalAlignment = Alignment.CenterHorizontally) {
                                        Text("Se requiere permiso de cámara para escanear.")
                                        Spacer(Modifier.height(8.dp))
                                        Button(onClick = { launcher.launch(Manifest.permission.CAMERA) }) {
                                            Text("Conceder Permiso")
                                        }
                                    }
                                }
                            }

                            if (showForm) {
                                Spacer(modifier = Modifier.height(16.dp))
                                ProductForm(
                                    producto = selectedProducto,
                                    codigoDefault = scannResult ?: "",
                                    onSaved = { 
                                        showForm = false
                                        selectedProducto = null
                                        isScanning = true
                                        scannResult = null
                                    },
                                    onCancel = {
                                        showForm = false
                                        selectedProducto = null
                                        isScanning = true
                                        scannResult = null
                                    }
                                )
                            }
                        }
                    }
                }
            }
        }
    }

    private fun analizarCodigo(codigo: String, onResult: (Producto?) -> Unit) {
        val id = codigo.toLongOrNull() ?: 0L
        RetrofitClient.instance.buscarProducto(id).enqueue(object : Callback<ProductoResponse> {
            override fun onResponse(call: Call<ProductoResponse>, response: Response<ProductoResponse>) {
                if (response.isSuccessful && response.body()?.ok == true) {
                    onResult(response.body()?.datos)
                } else {
                    onResult(null) // Producto nuevo
                }
            }
            override fun onFailure(call: Call<ProductoResponse>, t: Throwable) {
                Toast.makeText(this@AdminScannerActivity, "Error de red", Toast.LENGTH_SHORT).show()
                onResult(null)
            }
        })
    }
}

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun ProductForm(
    producto: Producto?, 
    codigoDefault: String, 
    onSaved: () -> Unit,
    onCancel: () -> Unit
) {
    var idProducto by remember { mutableStateOf(producto?.idProducto ?: codigoDefault) }
    var nombre by remember { mutableStateOf(producto?.nombre ?: "") }
    var descripcion by remember { mutableStateOf(producto?.descripcion ?: "") }
    var precio by remember { mutableStateOf(producto?.precioVenta?.toString() ?: "") }
    var stock by remember { mutableStateOf(producto?.stock?.toString() ?: "") }
    var imagen by remember { mutableStateOf(producto?.imagen ?: "") }
    
    // Categorías y Marcas dinámicas
    var categorias by remember { mutableStateOf<List<Categoria>>(emptyList()) }
    var marcas by remember { mutableStateOf<List<Marca>>(emptyList()) }
    var selectedCatId by remember { mutableStateOf(producto?.idCategoria ?: 1) }
    var selectedMarcId by remember { mutableStateOf(producto?.idMarca ?: 1) }
    
    var expandedCat by remember { mutableStateOf(false) }
    var expandedMarc by remember { mutableStateOf(false) }

    var showAddCatDialog by remember { mutableStateOf(false) }
    var showAddMarcDialog by remember { mutableStateOf(false) }
    var newValue by remember { mutableStateOf("") }

    val context = LocalContext.current

    val refreshData = {
        RetrofitClient.instance.getCategorias().enqueue(object : Callback<List<Categoria>> {
            override fun onResponse(call: Call<List<Categoria>>, response: Response<List<Categoria>>) {
                categorias = response.body() ?: emptyList()
            }
            override fun onFailure(call: Call<List<Categoria>>, t: Throwable) {}
        })
        RetrofitClient.instance.getMarcas().enqueue(object : Callback<List<Marca>> {
            override fun onResponse(call: Call<List<Marca>>, response: Response<List<Marca>>) {
                marcas = response.body() ?: emptyList()
            }
            override fun onFailure(call: Call<List<Marca>>, t: Throwable) {}
        })
    }

    LaunchedEffect(Unit) {
        refreshData()
    }

    if (showAddCatDialog || showAddMarcDialog) {
        AlertDialog(
            onDismissRequest = { 
                showAddCatDialog = false
                showAddMarcDialog = false
                newValue = ""
            },
            title = { Text(if (showAddCatDialog) "Nueva Categoría" else "Nueva Marca") },
            text = {
                OutlinedTextField(
                    value = newValue,
                    onValueChange = { newValue = it },
                    label = { Text("Nombre") }
                )
            },
            confirmButton = {
                Button(onClick = {
                    if (newValue.isNotBlank()) {
                        val call = if (showAddCatDialog) {
                            RetrofitClient.instance.guardarCategoria(newValue)
                        } else {
                            RetrofitClient.instance.guardarMarca(newValue)
                        }
                        
                        call.enqueue(object : Callback<BaseResponse> {
                            override fun onResponse(call: Call<BaseResponse>, response: Response<BaseResponse>) {
                                if (response.isSuccessful && response.body()?.ok == true) {
                                    Toast.makeText(context, "¡Agregado!", Toast.LENGTH_SHORT).show()
                                    refreshData()
                                }
                                showAddCatDialog = false
                                showAddMarcDialog = false
                                newValue = ""
                            }
                            override fun onFailure(call: Call<BaseResponse>, t: Throwable) {
                                showAddCatDialog = false
                                showAddMarcDialog = false
                                newValue = ""
                            }
                        })
                    }
                }) {
                    Text("Guardar")
                }
            },
            dismissButton = {
                TextButton(onClick = { 
                    showAddCatDialog = false
                    showAddMarcDialog = false
                    newValue = ""
                }) {
                    Text("Cancelar")
                }
            }
        )
    }

    Column(
        modifier = Modifier
            .fillMaxWidth()
            .background(Color.White, RoundedCornerShape(12.dp))
            .padding(16.dp)
            .verticalScroll(rememberScrollState()),
        verticalArrangement = Arrangement.spacedBy(12.dp)
    ) {
        Text(
            text = if (producto == null) "Registrar Nuevo Producto" else "Editar Producto",
            style = MaterialTheme.typography.titleLarge,
            fontWeight = FontWeight.Bold,
            color = Color(0xFF1E293B)
        )
        
        OutlinedTextField(
            value = idProducto,
            onValueChange = { if(producto == null) idProducto = it },
            label = { Text("Código de Producto") },
            modifier = Modifier.fillMaxWidth(),
            shape = RoundedCornerShape(12.dp),
            enabled = producto == null
        )
        
        OutlinedTextField(
            value = nombre,
            onValueChange = { nombre = it },
            label = { Text("Nombre del Producto") },
            modifier = Modifier.fillMaxWidth(),
            shape = RoundedCornerShape(12.dp)
        )

        // Select de Categoría
        Row(
            modifier = Modifier.fillMaxWidth(),
            verticalAlignment = Alignment.CenterVertically,
            horizontalArrangement = Arrangement.spacedBy(8.dp)
        ) {
            ExposedDropdownMenuBox(
                expanded = expandedCat,
                onExpandedChange = { expandedCat = !expandedCat },
                modifier = Modifier.weight(1f)
            ) {
                OutlinedTextField(
                    value = categorias.find { it.idCategoria == selectedCatId }?.nombreCat ?: "Seleccionar Categoría",
                    onValueChange = {},
                    readOnly = true,
                    label = { Text("Categoría") },
                    trailingIcon = { ExposedDropdownMenuDefaults.TrailingIcon(expanded = expandedCat) },
                    modifier = Modifier.menuAnchor().fillMaxWidth(),
                    shape = RoundedCornerShape(12.dp)
                )
                ExposedDropdownMenu(
                    expanded = expandedCat,
                    onDismissRequest = { expandedCat = false }
                ) {
                    categorias.forEach { cat ->
                        DropdownMenuItem(
                            text = { Text(cat.nombreCat) },
                            onClick = {
                                selectedCatId = cat.idCategoria
                                expandedCat = false
                            }
                        )
                    }
                }
            }
            IconButton(onClick = { showAddCatDialog = true }) {
                Icon(imageVector = Icons.Default.Add, contentDescription = "Añadir Categoría")
            }
        }

        // Select de Marca
        Row(
            modifier = Modifier.fillMaxWidth(),
            verticalAlignment = Alignment.CenterVertically,
            horizontalArrangement = Arrangement.spacedBy(8.dp)
        ) {
            ExposedDropdownMenuBox(
                expanded = expandedMarc,
                onExpandedChange = { expandedMarc = !expandedMarc },
                modifier = Modifier.weight(1f)
            ) {
                OutlinedTextField(
                    value = marcas.find { it.idMarca == selectedMarcId }?.nombreMarc ?: "Seleccionar Marca",
                    onValueChange = {},
                    readOnly = true,
                    label = { Text("Marca") },
                    trailingIcon = { ExposedDropdownMenuDefaults.TrailingIcon(expanded = expandedMarc) },
                    modifier = Modifier.menuAnchor().fillMaxWidth(),
                    shape = RoundedCornerShape(12.dp)
                )
                ExposedDropdownMenu(
                    expanded = expandedMarc,
                    onDismissRequest = { expandedMarc = false }
                ) {
                    marcas.forEach { m ->
                        DropdownMenuItem(
                            text = { Text(m.nombreMarc) },
                            onClick = {
                                selectedMarcId = m.idMarca
                                expandedMarc = false
                            }
                        )
                    }
                }
            }
            IconButton(onClick = { showAddMarcDialog = true }) {
                Icon(imageVector = Icons.Default.Add, contentDescription = "Añadir Marca")
            }
        }

        OutlinedTextField(
            value = descripcion,
            onValueChange = { descripcion = it },
            label = { Text("Descripción") },
            modifier = Modifier.fillMaxWidth(),
            shape = RoundedCornerShape(12.dp),
            minLines = 2
        )
        
        Row(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
            OutlinedTextField(
                value = precio,
                onValueChange = { precio = it },
                label = { Text("Precio Venta") },
                modifier = Modifier.weight(1f),
                shape = RoundedCornerShape(12.dp),
                prefix = { Text("$") }
            )
            OutlinedTextField(
                value = stock,
                onValueChange = { stock = it },
                label = { Text("Stock") },
                modifier = Modifier.weight(1f),
                shape = RoundedCornerShape(12.dp)
            )
        }

        OutlinedTextField(
            value = imagen,
            onValueChange = { imagen = it },
            label = { Text("Nombre de la Imagen") },
            modifier = Modifier.fillMaxWidth(),
            shape = RoundedCornerShape(12.dp)
        )
        
        Spacer(modifier = Modifier.height(16.dp))
        
        Button(
            modifier = Modifier.fillMaxWidth().height(50.dp),
            shape = RoundedCornerShape(12.dp),
            colors = ButtonDefaults.buttonColors(containerColor = Color(0xFF2563EB)),
            onClick = {
                if (idProducto.isBlank() || nombre.isBlank() || precio.isBlank() || stock.isBlank()) {
                    Toast.makeText(context, "Por favor completa los campos obligatorios", Toast.LENGTH_SHORT).show()
                    return@Button
                }
                
                val p = Producto(
                    idProducto = idProducto,
                    nombre = nombre,
                    unidad = producto?.unidad ?: "Unidad",
                    descripcion = descripcion,
                    stock = stock.toIntOrNull() ?: 0,
                    precioCosto = producto?.precioCosto ?: 0.0,
                    precioVenta = precio.toDoubleOrNull() ?: 0.0,
                    imagen = if(imagen.isBlank()) null else imagen,
                    idCategoria = selectedCatId,
                    idMarca = selectedMarcId
                )
                
                RetrofitClient.instance.guardarProducto(p).enqueue(object : Callback<BaseResponse> {
                    override fun onResponse(call: Call<BaseResponse>, response: Response<BaseResponse>) {
                        if (response.isSuccessful && response.body()?.ok == true) {
                            Toast.makeText(context, "¡Guardado exitosamente!", Toast.LENGTH_SHORT).show()
                            onSaved()
                        } else {
                            Toast.makeText(context, "Error: ${response.body()?.mensaje ?: "No se pudo guardar"}", Toast.LENGTH_SHORT).show()
                        }
                    }
                    override fun onFailure(call: Call<BaseResponse>, t: Throwable) {
                        Toast.makeText(context, "Error de red: ${t.message}", Toast.LENGTH_SHORT).show()
                    }
                })
            }
        ) {
            Text("Guardar Cambios", fontWeight = FontWeight.Bold)
        }
        
        TextButton(
            onClick = onCancel,
            modifier = Modifier.fillMaxWidth()
        ) {
            Text("Cancelar y volver", color = Color.Gray)
        }
    }
}

