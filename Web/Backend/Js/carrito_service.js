"use strict";

const ITBMS = 0.07;

function escapeHtml(valor) {
    return String(valor)
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#39;");
}

function guardarCarrito(items) {
    localStorage.setItem("carrito", JSON.stringify(items));
}

function leerCarrito() {
    const data = localStorage.getItem("carrito");
    if (!data) {
        return [];
    }

    try {
        const parsed = JSON.parse(data);
        if (!Array.isArray(parsed)) {
            return [];
        }

        return parsed.map((item) => ({
            idProducto: Number.parseInt(item.idProducto, 10),
            cantidad: Number.parseInt(item.cantidad, 10),
            nombre: typeof item.nombre === "string" && item.nombre.trim() !== ""
                ? item.nombre
                : "Producto #" + String(item.idProducto),
            precioUnitario: Number.isFinite(Number(item.precioUnitario))
                ? Number(item.precioUnitario)
                : 0,
            stock: Number.isInteger(Number(item.stock)) ? Number(item.stock) : 0,
        })).filter((item) => Number.isInteger(item.idProducto) && item.idProducto > 0 && Number.isInteger(item.cantidad) && item.cantidad > 0);
    } catch (_error) {
        return [];
    }
}

function actualizarCarritoProducto(idProducto, nuevaCantidad) {
    const cantidad = Number.parseInt(nuevaCantidad, 10);

    if (!Number.isInteger(cantidad) || cantidad < 1) {
        return;
    }

    const items = leerCarrito();
    const index = items.findIndex((item) => item.idProducto === Number.parseInt(idProducto, 10));

    if (index === -1) {
        return;
    }

    const item = items[index];
    const stockMaximo = item.stock > 0 ? item.stock : 999999;

    if (cantidad > stockMaximo) {
        return; // Evita que se actualice a una cantidad mayor al stock
    }

    items[index].cantidad = cantidad;
    guardarCarrito(items);
    renderizarCarrito();
}

function eliminarProductoCarrito(idProducto) {
    const id = Number.parseInt(idProducto, 10);
    const items = leerCarrito().filter((item) => item.idProducto !== id);

    guardarCarrito(items);
    renderizarCarrito();
}

function formatoDinero(valor) {
    return "$" + valor.toFixed(2);
}

function renderizarCarrito() {
    const items = leerCarrito();
    const contenedor = document.getElementById("carrito-lista");

    if (!contenedor) return; // Validación de seguridad

    if (items.length === 0) {
        contenedor.innerHTML = '<div class="carrito-empty">Tu carrito esta vacio.</div>';
    } else {
        contenedor.innerHTML = items.map((item) => {
            const subtotal = item.cantidad * item.precioUnitario;

            return `
                <article class="carrito-item-card">
                    <div class="item-col item-id">${item.idProducto}</div>
                    <div class="item-col item-nombre">${escapeHtml(item.nombre)}</div>
                    <div class="item-col item-cantidad">
                        <input type="number"
                               class="js-item-quantity"
                               data-producto-id="${item.idProducto}"
                               value="${item.cantidad}"
                               min="1"
                               max="${item.stock > 0 ? item.stock : 999999}"
                               step="1"
                               inputmode="numeric"
                               onkeydown="if(typeof validarCantidad === 'function') validarCantidad(event)"
                               onpaste="event.preventDefault()"
                               aria-label="Cantidad del producto ${escapeHtml(item.nombre)}">
                    </div>
                    <div class="item-col item-precio">${formatoDinero(item.precioUnitario)}</div>
                    <div class="item-col item-subtotal">${formatoDinero(subtotal)}</div>
                    <div class="item-col item-remove">
                        <button class="btn btn-ghost btn-sm js-remove-item" data-producto-id="${item.idProducto}" type="button">Eliminar</button>
                    </div>
                </article>
            `;
        }).join("");
    }

    const subtotal = items.reduce((sum, item) => sum + (item.cantidad * item.precioUnitario), 0);
    const itbms = subtotal * ITBMS;
    const total = subtotal + itbms;
    const unidades = items.reduce((sum, item) => sum + item.cantidad, 0);

    // Actualizar resumen
    document.getElementById("resumen-productos").textContent = String(items.length);
    document.getElementById("resumen-unidades").textContent = String(unidades);
    document.getElementById("resumen-subtotal").textContent = formatoDinero(subtotal);
    document.getElementById("resumen-itbms").textContent = formatoDinero(itbms);
    document.getElementById("resumen-total").textContent = formatoDinero(total);
}

function manejarAccionesCarrito(event) {
    const botonEliminar = event.target.closest(".js-remove-item");
    if (botonEliminar) {
        eliminarProductoCarrito(botonEliminar.getAttribute("data-producto-id"));
        return;
    }

    const inputCantidad = event.target.closest(".js-item-quantity");
    if (inputCantidad) {
        actualizarCarritoProducto(inputCantidad.getAttribute("data-producto-id"), inputCantidad.value);
    }
}

function vaciarCarrito() {
    guardarCarrito([]);
    renderizarCarrito();
}

// Inicialización de Eventos del DOM
document.addEventListener("DOMContentLoaded", () => {
    
    const btnVaciar = document.getElementById("btn-vaciar");
    const carritoLista = document.getElementById("carrito-lista");
    
    if (btnVaciar) btnVaciar.addEventListener("click", vaciarCarrito);
    if (carritoLista) {
        carritoLista.addEventListener("click", manejarAccionesCarrito);
        carritoLista.addEventListener("change", manejarAccionesCarrito);
    }

    // Manejadores del modal de pago
    const modalPago = document.getElementById("modal-pago");
    const btnFinalizarCompra = document.getElementById("btn-finalizar-compra");
    const btnCerrarModal = document.getElementById("btn-cerrar-modal");
    const btnCancelarModal = document.getElementById("btn-cancelar-modal");

    function abrirModalPago() {
        const items = leerCarrito();
        
        if (items.length === 0) {
            alert("Tu carrito está vacío. Agrega productos antes de finalizar la compra.");
            return;
        }

        const subtotal = items.reduce((sum, item) => sum + (item.cantidad * item.precioUnitario), 0);
        const itbms = subtotal * ITBMS;
        const total = subtotal + itbms;

        document.getElementById("modal-subtotal").textContent = formatoDinero(subtotal);
        document.getElementById("modal-itbms").textContent = formatoDinero(itbms);
        document.getElementById("modal-total").textContent = formatoDinero(total);

        if (modalPago) modalPago.showModal();
    }

    function cerrarModalPago() {
        if (modalPago) modalPago.close();
    }

    if (btnFinalizarCompra) btnFinalizarCompra.addEventListener("click", abrirModalPago);
    if (btnCerrarModal) btnCerrarModal.addEventListener("click", cerrarModalPago);
    if (btnCancelarModal) btnCancelarModal.addEventListener("click", cerrarModalPago);

    // Cerrar modal al hacer click fuera del contenido
    if (modalPago) {
        modalPago.addEventListener("click", (event) => {
            if (event.target === modalPago) {
                cerrarModalPago();
            }
        });
    }

    // Renderizar al cargar la página
    renderizarCarrito();
});

// Escuchar cambios en otras pestañas
window.addEventListener("storage", (event) => {
    if (event.key === "carrito") {
        renderizarCarrito();
    }
});