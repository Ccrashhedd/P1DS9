package com.example.proyecto1.network

import com.example.proyecto1.model.*
import retrofit2.Call
import retrofit2.http.*

interface ApiService {
    @GET("get_productos.php")
    fun getCatalogo(): Call<List<Producto>>

    @POST("login_empleado.php")
    fun login(@Body request: LoginRequest): Call<LoginResponse>

    @POST("procesar_factura.php")
    fun procesarPago(@Body request: PagoRequest): Call<BaseResponse>

    @GET("buscar_producto.php")
    fun buscarProducto(@Query("idProducto") id: Long): Call<ProductoResponse>

    @POST("guardar_producto.php")
    fun guardarProducto(@Body producto: Producto): Call<BaseResponse>

    // Nuevos endpoints para el Dashboard
    @GET("get_facturas.php")
    fun getHistorialFacturas(): Call<List<Factura>>

    @POST("registrar_empleado.php")
    fun registrarEmpleado(@Body request: RegistroEmpleadoRequest): Call<BaseResponse>

    @GET("catalogos.php")
    fun getCategorias(@Query("action") action: String = "getCategorias"): Call<List<Categoria>>

    @GET("catalogos.php")
    fun getMarcas(@Query("action") action: String = "getMarcas"): Call<List<Marca>>

    @FormUrlEncoded
    @POST("catalogos.php")
    fun guardarCategoria(
        @Field("nombre") nombre: String,
        @Query("action") action: String = "addCategoria"
    ): Call<BaseResponse>

    @FormUrlEncoded
    @POST("catalogos.php")
    fun guardarMarca(
        @Field("nombre") nombre: String,
        @Query("action") action: String = "addMarca"
    ): Call<BaseResponse>
}
