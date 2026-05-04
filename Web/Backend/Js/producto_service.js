"use strict";

function cargarCarrito() {
    const data = localStorage.getItem("carrito");

    if (!data) {
        return [];
    }

    try {
        const carritoParseado = JSON.parse(data);
        if (!Array.isArray(carritoParseado)) {
            return [];
        }

        return carritoParseado
            .map((item) => ({
                idProducto: Number.parseInt(item.idProducto, 10),
                cantidad: Number.parseInt(item.cantidad, 10),
                nombre: typeof item.nombre === "string" ? item.nombre : "Producto #" + String(item.idProducto),
                precioUnitario: Number.isFinite(Number(item.precioUnitario)) ? Number(item.precioUnitario) : 0,
            }))
            .filter((item) => Number.isInteger(item.idProducto) && item.idProducto > 0 && Number.isInteger(item.cantidad) && item.cantidad > 0);
    } catch (_error) {
        return [];
    }
}

let carrito = cargarCarrito();

function guardarCarrito() {
    localStorage.setItem("carrito", JSON.stringify(carrito));
}

function obtenerCarrito() {
    return [...carrito];
}

function obtenerCantidadTotalCarrito() {
    return carrito.reduce((acumulado, item) => acumulado + item.cantidad, 0);
}

function agregarProductoCarrito(productoId, cantidad, extras) {
    const id = Number.parseInt(productoId, 10);
    const cant = Number.parseInt(cantidad, 10);
    const nombre = extras && typeof extras.nombre === "string" && extras.nombre.trim() !== ""
        ? extras.nombre.trim()
        : "Producto #" + id;
    const precioUnitario = extras && Number.isFinite(Number(extras.precioUnitario))
        ? Number(extras.precioUnitario)
        : 0;

    if (!Number.isInteger(id) || id <= 0) {
        throw new Error("productoId invalido");
    }

    if (!Number.isInteger(cant) || cant <= 0) {
        throw new Error("cantidad invalida");
    }

    const existente = carrito.find((item) => item.idProducto === id);

    if (existente) {
        existente.cantidad += cant;
        if (precioUnitario > 0) {
            existente.precioUnitario = precioUnitario;
        }
        if (nombre) {
            existente.nombre = nombre;
        }
    } else {
        carrito.push({
            idProducto: id,
            cantidad: cant,
            nombre,
            precioUnitario,
        });
    }

    guardarCarrito();
    return obtenerCarrito();
}

function actualizarBadgeCarritoFlotante() {
    const badge = document.getElementById("cart-floating-count");
    const total = obtenerCantidadTotalCarrito();

    if (badge) {
        badge.textContent = String(total);
        badge.hidden = total <= 0;
    }

    const label = document.getElementById("cart-floating-label");
    if (label) {
        label.textContent = total === 1 ? "1 producto" : total + " productos";
    }
}

function obtenerCantidadDesdeInput(productoId) {
    const input = document.getElementById("cantidadProducto-" + productoId);
    if (!input) {
        return "1";
    }

    return input.value;
}

function mostrarConfirmacionProducto(nombreProducto, cantidad) {
    const dialog = document.querySelector("dialog");
    if (!dialog || typeof dialog.showModal !== "function") {
        return;
    }

    const mensaje = dialog.querySelector("p");
    if (mensaje) {
        const qty = Number.parseInt(cantidad, 10) || 1;
        mensaje.textContent = "Se agrego " + qty + " x " + nombreProducto + " al carrito.";
    }

    dialog.showModal();
}

function addToCart(productoId, nombreProducto, precioUnitario) {
    const cantidad = obtenerCantidadDesdeInput(productoId);

    try {
        agregarProductoCarrito(productoId, cantidad, {
            nombre: nombreProducto,
            precioUnitario,
        });
        actualizarBadgeCarritoFlotante();
        mostrarConfirmacionProducto(nombreProducto, cantidad);
    } catch (error) {
        const mensaje = error instanceof Error ? error.message : "No se pudo agregar el producto.";
        window.alert(mensaje);
    }
}

function inicializarCarritoFlotante() {
    actualizarBadgeCarritoFlotante();

    window.addEventListener("storage", (event) => {
        if (event.key === "carrito") {
            carrito = cargarCarrito();
            actualizarBadgeCarritoFlotante();
        }
    });
}

function agregarProductoCarritoDesdeEvento(event) {
    const boton = event.currentTarget || event.target;
    const productoId = boton ? boton.getAttribute("data-producto-id") : null;
    const inputCantidad = document.getElementById("cantidad");
    const cantidad = inputCantidad ? inputCantidad.value : "1";

    return agregarProductoCarrito(productoId, cantidad);
}

function limpiarCarrito() {
    carrito = [];
    guardarCarrito();
}

if (typeof module !== "undefined" && module.exports) {
    module.exports = {
        obtenerCarrito,
        obtenerCantidadTotalCarrito,
        agregarProductoCarrito,
        agregarProductoCarritoDesdeEvento,
        limpiarCarrito,
    };
}

if (typeof window !== "undefined") {
    window.obtenerCarrito = obtenerCarrito;
    window.obtenerCantidadTotalCarrito = obtenerCantidadTotalCarrito;
    window.agregarProductoCarrito = agregarProductoCarrito;
    window.agregarProductoCarritoDesdeEvento = agregarProductoCarritoDesdeEvento;
    window.limpiarCarrito = limpiarCarrito;
    window.addToCart = addToCart;

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", inicializarCarritoFlotante);
    } else {
        inicializarCarritoFlotante();
    }
}