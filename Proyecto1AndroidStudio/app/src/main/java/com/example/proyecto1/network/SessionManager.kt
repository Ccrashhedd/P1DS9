package com.example.proyecto1.network

import android.content.Context
import android.content.SharedPreferences

class SessionManager(context: Context) {
    private val prefs: SharedPreferences = context.getSharedPreferences("user_session", Context.MODE_PRIVATE)

    fun saveSession(rol: Int, nombre: String) {
        prefs.edit().apply {
            putInt("USER_ROL", rol)
            putString("USER_NAME", nombre)
            putBoolean("IS_LOGGED", true)
            apply()
        }
    }

    fun getRol(): Int = prefs.getInt("USER_ROL", -1)
    
    fun getNombre(): String = prefs.getString("USER_NAME", "Usuario") ?: "Usuario"
    
    fun isLogged(): Boolean = prefs.getBoolean("IS_LOGGED", false)
    
    fun logout() {
        prefs.edit().clear().apply()
    }
}
