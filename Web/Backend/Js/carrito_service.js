console.log("CARGANDO carrito_service.js");

"use strict";

const ITBMS = 0.07;

/* =========================
   CARRITO LOCAL STORAGE
========================= */

function guardarCarrito(items) {
    localStorage.setItem("carrito", JSON.stringify(items));
}

function leerCarrito() {
    try {
        const data = JSON.parse(localStorage.getItem("carrito"));
        return Array.isArray(data) ? data : [];
    } catch (e) {
        return [];
    }
}

/* =========================
   UTILIDADES
========================= */

function formatoDinero(valor) {
    return "$" + Number(valor).toFixed(2);
}

function escaparHtml(texto) {
    return String(texto).replace(/[&<>"']/g, function (m) {
        return ({
            "&": "&amp;",
            "<": "&lt;",
            ">": "&gt;",
            '"': "&quot;",
            "'": "&#039;"
        })[m];
    });
}

/* =========================
   RENDER DEL CARRITO
========================= */

function renderizarCarrito() {

    const items = leerCarrito();

    const lista = document.getElementById("carrito-lista");

    if (!lista) {
        return;
    }

    if (items.length === 0) {

        lista.innerHTML = `
            <div class="carrito-empty">
                Tu carrito esta vacio.
            </div>
        `;

    } else {

        lista.innerHTML = items.map(item => {

            const subtotal = item.cantidad * item.precioUnitario;

            return `
                <article class="carrito-item-card">

                    <div class="item-col item-id">
                        ${item.idProducto}
                    </div>

                    <div class="item-col item-nombre">
                        ${escaparHtml(item.nombre)}
                    </div>

                    <div class="item-col item-cantidad">
                        <input
                            type="number"
                            class="js-item-quantity"
                            data-producto-id="${item.idProducto}"
                            value="${item.cantidad}"
                            min="1"
                            max="${item.stock || 999999}"
                        >
                    </div>

                    <div class="item-col item-precio">
                        ${formatoDinero(item.precioUnitario)}
                    </div>

                    <div class="item-col item-subtotal">
                        ${formatoDinero(subtotal)}
                    </div>

                    <div class="item-col item-remove">
                        <button
                            type="button"
                            class="btn btn-ghost btn-sm js-remove-item"
                            data-producto-id="${item.idProducto}"
                        >
                            Eliminar
                        </button>
                    </div>

                </article>
            `;

        }).join("");
    }

    const subtotal = items.reduce((s, i) => {
        return s + (i.cantidad * i.precioUnitario);
    }, 0);

    const itbms = subtotal * ITBMS;

    const total = subtotal + itbms;

    document.getElementById("resumen-productos").textContent =
        items.length;

    document.getElementById("resumen-unidades").textContent =
        items.reduce((s, i) => s + i.cantidad, 0);

    document.getElementById("resumen-subtotal").textContent =
        formatoDinero(subtotal);

    document.getElementById("resumen-itbms").textContent =
        formatoDinero(itbms);

    document.getElementById("resumen-total").textContent =
        formatoDinero(total);
}

/* =========================
   ACTUALIZAR CANTIDAD
========================= */

function actualizarCantidad(idProducto, cantidad) {

    cantidad = parseInt(cantidad);

    if (isNaN(cantidad) || cantidad < 1) {
        return;
    }

    const items = leerCarrito();

    const item = items.find(i => {
        return Number(i.idProducto) === Number(idProducto);
    });

    if (!item) {
        return;
    }

    if (item.stock && cantidad > item.stock) {
        cantidad = item.stock;
    }

    item.cantidad = cantidad;

    guardarCarrito(items);

    renderizarCarrito();
}

/* =========================
   ELIMINAR PRODUCTO
========================= */

function eliminarProducto(idProducto) {

    const items = leerCarrito().filter(item => {
        return Number(item.idProducto) !== Number(idProducto);
    });

    guardarCarrito(items);

    renderizarCarrito();
}

/* =========================
   VACIAR CARRITO
========================= */

function vaciarCarrito() {
    guardarCarrito([]);
    renderizarCarrito();
}

/* =========================
   MASCARA FECHA MM/AA
========================= */

function aplicarMascaraFecha(input) {

    input.addEventListener("input", function () {

        let valor = input.value.replace(/\D/g, "");

        if (valor.length > 4) {
            valor = valor.substring(0, 4);
        }

        if (valor.length >= 3) {
            valor =
                valor.substring(0, 2) +
                "/" +
                valor.substring(2);
        }

        input.value = valor;
    });
}

/* =========================
   INICIALIZAR
========================= */

function inicializarVistaCarrito() {

    console.log("INICIALIZANDO CARRITO");

    renderizarCarrito();

    /* ========= BOTON VACIAR ========= */

    const btnVaciar = document.getElementById("btn-vaciar");

    if (btnVaciar) {
        btnVaciar.addEventListener("click", vaciarCarrito);
    }

    /* ========= LISTA ========= */

    const carritoLista = document.getElementById("carrito-lista");

    if (carritoLista) {

        carritoLista.addEventListener("click", function (event) {

            const btnEliminar =
                event.target.closest(".js-remove-item");

            if (btnEliminar) {

                eliminarProducto(
                    btnEliminar.dataset.productoId
                );
            }
        });

        carritoLista.addEventListener("change", function (event) {

            const input =
                event.target.closest(".js-item-quantity");

            if (input) {

                actualizarCantidad(
                    input.dataset.productoId,
                    input.value
                );
            }
        });
    }

    /* ========= MODAL ========= */

    const modal = document.getElementById("modal-pago");
const btnFinalizar = document.getElementById("btn-finalizar-compra");

if (btnFinalizar && modal) {

    btnFinalizar.addEventListener("click", () => {

        const items = leerCarrito();

        if (items.length === 0) {
            alert("Tu carrito está vacío.");
            return;
        }

        const subtotal = items.reduce((s, i) => {
            return s + (i.cantidad * i.precioUnitario);
        }, 0);

        const itbms = subtotal * ITBMS;

        document.getElementById("modal-subtotal").textContent =
            formatoDinero(subtotal);

        document.getElementById("modal-itbms").textContent =
            formatoDinero(itbms);

        document.getElementById("modal-total").textContent =
            formatoDinero(subtotal + itbms);

        modal.showModal();
    });
}

    document.getElementById("btn-cerrar-modal")
        ?.addEventListener("click", () => modal.close());

    document.getElementById("btn-cancelar-modal")
        ?.addEventListener("click", () => modal.close());

    /* ========= INPUTS TARJETA ========= */

    const inputDigitos = document.getElementById("input-digitos");
    const inputCvv = document.getElementById("input-cvv");
    const inputFecha = document.getElementById("input-fecha");

    /* SOLO NUMEROS */

    [inputDigitos, inputCvv].forEach(input => {

        if (!input) return;

        input.addEventListener("input", function () {
            input.value = input.value.replace(/\D/g, "");
        });
    });

    /* MASCARA FECHA */

    if (inputFecha) {

        aplicarMascaraFecha(inputFecha);

        inputFecha.addEventListener("input", function () {
            inputFecha.value =
                inputFecha.value.replace(/[^\d/]/g, "");
        });
    }

    /* ========= LIMPIAR ========= */

    document.getElementById("btn-limpiar-tarjeta")
        ?.addEventListener("click", function () {

            if (inputDigitos) inputDigitos.value = "";
            if (inputCvv) inputCvv.value = "";
            if (inputFecha) inputFecha.value = "";
        });

    /* ========= BOTON USAR TARJETA ========= */

    document.getElementById("btn-llenar-digitos")
        ?.addEventListener("click", function () {

            const digitos = inputDigitos.value.trim();

            if (digitos === "") {
                alert("Debes ingresar los dígitos.");
                return;
            }

            alert("Tarjeta preparada correctamente.");
        });

    /* ========= FORMULARIO PAGO ========= */

    const formPago = document.getElementById("form-pago");

if (formPago) {

    
    formPago.addEventListener("submit", (event) => {

        console.log("SUBMIT FUNCIONANDO");


        event.preventDefault();

        console.log("SUBMIT DETECTADO");

        const inputDigitos = document.getElementById("input-digitos");
        const inputCvv = document.getElementById("input-cvv");
        const inputFecha = document.getElementById("input-fecha");

        const digitos = inputDigitos ? inputDigitos.value.trim() : "";
        const cvv = inputCvv ? inputCvv.value.trim() : "";
        const fecha = inputFecha ? inputFecha.value.trim() : "";

        console.log("DIGITOS:", digitos);
        console.log("CVV:", cvv);
        console.log("FECHA:", fecha);

        if (digitos === "") {
            alert("Debes ingresar los dígitos.");
            return;
        }

        const items = leerCarrito();

        if (items.length === 0) {
            alert("El carrito está vacío.");
            return;
        }

        const subtotal = items.reduce((s, i) => {
            return s + (i.cantidad * i.precioUnitario);
        }, 0);

        const itbms = subtotal * ITBMS;
        const total = subtotal + itbms;

        document.getElementById("input-subtotal").value =
            subtotal.toFixed(2);

        document.getElementById("input-itbms").value =
            itbms.toFixed(2);

        document.getElementById("input-total").value =
            total.toFixed(2);

        document.getElementById("input-productos").value =
            JSON.stringify(
                items.map(item => ({
                    idProducto: Number(item.idProducto),
                    cantidad: Number(item.cantidad)
                }))
            );

        const hiddenDigitos = document.createElement("input");
        hiddenDigitos.type = "hidden";
        hiddenDigitos.name = "digitos";
        hiddenDigitos.value = digitos;
        formPago.appendChild(hiddenDigitos);

        const hiddenCvv = document.createElement("input");
        hiddenCvv.type = "hidden";
        hiddenCvv.name = "cvv";
        hiddenCvv.value = cvv;
        formPago.appendChild(hiddenCvv);

        const hiddenFecha = document.createElement("input");
        hiddenFecha.type = "hidden";
        hiddenFecha.name = "fecha";
        hiddenFecha.value = fecha;
        formPago.appendChild(hiddenFecha);

        console.log("FORMULARIO ENVIADO");

        formPago.submit();
    });
}
}

/* =========================
   DOM READY
========================= */

document.addEventListener(
    "DOMContentLoaded",
    inicializarVistaCarrito
);