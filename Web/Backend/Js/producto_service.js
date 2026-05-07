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
                stock: Number.isInteger(Number(item.stock)) ? Number(item.stock) : 0,
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
    const stock = extras && Number.isInteger(Number(extras.stock))
        ? Number(extras.stock)
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
        if (stock > 0) {
            existente.stock = stock;
        }
    } else {
        carrito.push({
            idProducto: id,
            cantidad: cant,
            nombre,
            precioUnitario,
            stock,
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

function addToCart(productoId, nombreProducto, precioUnitario, stockDisponible) {
    // 1. Obtener la cantidad que el usuario escribió en el input
    const cantidadInput = Number.parseInt(obtenerCantidadDesdeInput(productoId), 10) || 1;
    const id = Number.parseInt(productoId, 10);
    const stock = Number.parseInt(stockDisponible, 10);
    const tieneStockValido = Number.isInteger(stock) && stock >= 0;

    // 2. Revisar si el producto ya está en el carrito para saber cuántos hay
    const existente = carrito.find((item) => item.idProducto === id);
    const cantidadEnCarrito = existente ? existente.cantidad : 0;

    // 3. Validar: Lo que ya hay + lo que quiere agregar ¿supera el stock?
    if (tieneStockValido && (cantidadEnCarrito + cantidadInput) > stock) {
        let mensaje = "No hay suficiente stock. ";
        if (cantidadEnCarrito > 0) {
            mensaje += "Ya tienes " + cantidadEnCarrito + " unidad(es) de este producto en tu carrito y solo quedan " + stock + " disponibles en total.";
        } else {
            mensaje += "Solo quedan " + stock + " unidad(es) disponibles.";
        }
        
        window.alert(mensaje);
        return; // Detenemos la ejecución aquí para que no se agregue
    }

    // 4. Si pasa la validación, procedemos a agregar como siempre
    try {
        agregarProductoCarrito(productoId, cantidadInput, {
            nombre: nombreProducto,
            precioUnitario,
            stock: stock,
        });
        actualizarBadgeCarritoFlotante();
        mostrarConfirmacionProducto(nombreProducto, cantidadInput);
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

function inicializarBotonesAgregarCarrito() {
    const botones = document.querySelectorAll(".js-add-to-cart");

    botones.forEach((boton) => {
        boton.addEventListener("click", () => {
            const productoId = boton.getAttribute("data-producto-id");
            const nombreProducto = boton.getAttribute("data-producto-nombre") || "Producto";
            const precioUnitario = boton.getAttribute("data-producto-precio") || "0";
            const stockDisponible = boton.getAttribute("data-producto-stock") || "0";

            try {
                addToCart(productoId, nombreProducto, precioUnitario, stockDisponible);
            } catch (error) {
                const mensaje = error instanceof Error ? error.message : "No se pudo agregar el producto.";
                window.alert(mensaje);
            }
        });
    });
}

function inicializarDialogCarrito() {
    const dialog = document.querySelector("dialog");
    if (!dialog) {
        return;
    }

    const botonesDialog = dialog.querySelectorAll("button");
    botonesDialog.forEach((boton) => {
        boton.addEventListener("click", () => {
            const formaction = boton.getAttribute("formaction");
            if (formaction) {
                setTimeout(() => {
                    window.location.href = formaction;
                }, 0);
            }
        });
    });
}

document.addEventListener("DOMContentLoaded", () => {
    inicializarCarritoFlotante();
    inicializarBotonesAgregarCarrito();
    inicializarDialogCarrito();
});

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
    window.actualizarBadgeCarritoFlotante = actualizarBadgeCarritoFlotante;

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", inicializarCarritoFlotante);
        document.addEventListener("DOMContentLoaded", inicializarBotonesAgregarCarrito);
    } else {
        inicializarCarritoFlotante();
        inicializarBotonesAgregarCarrito();
    }
}

function validarSaldoTarjeta(saldoDisponible, saldoMaximo, montoCompra) {
    const saldo = Number(saldoDisponible + saldoMaximo);
    const monto = Number(montoCompra);
    if (!Number.isFinite(saldo) || saldo < 0) {
        throw new Error("Saldo disponible inválido");
    }

    if (!Number.isFinite(monto) || monto <= 0) {
        throw new Error("Monto de compra inválido");
    }

}