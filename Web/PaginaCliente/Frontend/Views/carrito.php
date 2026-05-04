<section>
    <div class="container">
        <h2>Carrito de compras</h2>
        <p>Aqui veras tus productos en una lista con scroll y el resumen total a la derecha.</p>

        <div class="carrito-layout">
            <div class="carrito-productos panel-card">
                <div class="carrito-head">
                    <h3>Productos agregados</h3>
                    <button class="btn btn-ghost" id="btn-vaciar" type="button">Vaciar carrito</button>
                </div>

                <div class="carrito-lista-scroll" id="carrito-lista"></div>
            </div>

            <aside class="carrito-resumen panel-card">
                <h3>Resumen</h3>
                <div class="resumen-linea">
                    <span>Productos</span>
                    <strong id="resumen-productos">0</strong>
                </div>
                <div class="resumen-linea">
                    <span>Unidades</span>
                    <strong id="resumen-unidades">0</strong>
                </div>
                <div class="resumen-linea">
                    <span>Subtotal</span>
                    <strong id="resumen-subtotal">$0.00</strong>
                </div>
                <div class="resumen-linea">
                    <span>ITBMS (7%)</span>
                    <strong id="resumen-itbms">$0.00</strong>
                </div>
                <hr>
                <div class="resumen-linea total">
                    <span>Total</span>
                    <strong id="resumen-total">$0.00</strong>
                </div>

                <button class="btn btn-primary btn-full" type="button">Finalizar compra</button>
                <button class="btn btn-light btn-full" type="button">Ver tarjetas</button>
            </aside>
        </div>
    </div>
</section>

<script>
"use strict";

const ITBMS = 0.07;

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
        })).filter((item) => Number.isInteger(item.idProducto) && item.idProducto > 0 && Number.isInteger(item.cantidad) && item.cantidad > 0);
    } catch (_error) {
        return [];
    }
}

function formatoDinero(valor) {
    return "$" + valor.toFixed(2);
}

function renderizarCarrito() {
    const items = leerCarrito();
    const contenedor = document.getElementById("carrito-lista");

    if (items.length === 0) {
        contenedor.innerHTML = '<div class="carrito-empty">Tu carrito esta vacio.</div>';
    } else {
        contenedor.innerHTML = items.map((item) => {
            const subtotal = item.cantidad * item.precioUnitario;

            return `
                <article class="carrito-item-card">
                    <div class="item-col item-id">#${item.idProducto}</div>
                    <div class="item-col item-nombre">${item.nombre}</div>
                    <div class="item-col item-cantidad">x${item.cantidad}</div>
                    <div class="item-col item-precio">${formatoDinero(item.precioUnitario)}</div>
                    <div class="item-col item-subtotal">${formatoDinero(subtotal)}</div>
                </article>
            `;
        }).join("");
    }

    const subtotal = items.reduce((sum, item) => sum + (item.cantidad * item.precioUnitario), 0);
    const itbms = subtotal * ITBMS;
    const total = subtotal + itbms;
    const unidades = items.reduce((sum, item) => sum + item.cantidad, 0);

    document.getElementById("resumen-productos").textContent = String(items.length);
    document.getElementById("resumen-unidades").textContent = String(unidades);
    document.getElementById("resumen-subtotal").textContent = formatoDinero(subtotal);
    document.getElementById("resumen-itbms").textContent = formatoDinero(itbms);
    document.getElementById("resumen-total").textContent = formatoDinero(total);
}

function vaciarCarrito() {
    localStorage.setItem("carrito", JSON.stringify([]));
    renderizarCarrito();
}

document.getElementById("btn-vaciar").addEventListener("click", vaciarCarrito);
window.addEventListener("storage", (event) => {
    if (event.key === "carrito") {
        renderizarCarrito();
    }
});

renderizarCarrito();
</script>
