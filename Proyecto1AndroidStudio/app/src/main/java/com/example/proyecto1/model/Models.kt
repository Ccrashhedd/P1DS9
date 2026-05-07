package com.example.proyecto1.model

import com.google.gson.annotations.SerializedName

data class Producto(
    @SerializedName("idProducto") val idProducto: String, // Cambiado a String por seguridad
    @SerializedName("nombre") val nombre: String? = "Sin nombre",
    @SerializedName("unidad") val unidad: String? = "Unidad",
    @SerializedName("descripcion") val descripcion: String? = "",
    @SerializedName("stock") val stock: Int? = 0,
    @SerializedName("precioCosto") val precioCosto: Double? = 0.0,
    @SerializedName("precioVenta") val precioVenta: Double? = 0.0,
    @SerializedName("imagen") val imagen: String? = null,
    @SerializedName("idCategoria") val idCategoria: Int? = 1,
    @SerializedName("idMarca") val idMarca: Int? = 1
)

data class Empleado(
    val usuario: String,
    val nombre: String,
    val apellido: String,
    val rol: Int,
    val contrasena: String
)

data class LoginRequest(
    val usuario: String,
    val contrasena: String
)

data class LoginResponse(
    val ok: Boolean,
    val mensaje: String,
    val empleado: Empleado? = null
)

data class PagoRequest(
    val digitosTarjeta: String,
    val fechaVence: String,
    val codSeguridad: String,
    val items: List<ItemCarrito>,
    val subtotal: Double,
    val itbms: Double,
    val total: Double
)

data class ItemCarrito(
    val idProducto: String,
    val cantidad: Int,
    val precioUnitario: Double
)

data class BaseResponse(
    val ok: Boolean,
    val mensaje: String
)

data class ProductoResponse(
    val ok: Boolean,
    val datos: Producto? = null,
    val mensaje: String? = null
)

// Nuevos Modelos para Dashboard
data class Factura(
    val idFactura: Int,
    val idTarjeta: Int,
    val subtotal: Double,
    val itbms: Double,
    val total: Double,
    val fecha: String? = null,
    val detalles: List<FacturaDetalle>? = null
)

data class FacturaDetalle(
    val idFacDet: Int,
    val idFactura: Int,
    val idProducto: String,
    val cantidad: Int,
    val precio_unitario: Double,
    val nombreProducto: String? = null // Para mostrar en el historial
)

data class RegistroEmpleadoRequest(
    val usuario: String,
    val nombre: String,
    val apellido: String,
    val contrasena: String,
    val rol: Int = 2 // Por defecto rol empleado
)

data class Categoria(
    val idCategoria: Int,
    val nombreCat: String
)

data class Marca(
    val idMarca: Int,
    val nombreMarc: String
)

