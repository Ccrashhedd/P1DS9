"use strict";

class InputService {
    constructor() {
        this._listeners = [];
    }

    addListener(listener) {
        if (typeof listener !== "function") {
            throw new TypeError("listener debe ser una funcion");
        }

        this._listeners.push(listener);
        return () => {
            this._listeners = this._listeners.filter((l) => l !== listener);
        };
    }

    trigger(event) {
        for (const listener of this._listeners) {
            listener(event);
        }
    }
}

function validarCantidad(event) {
    const teclasPermitidas = ["Backspace", "Delete", "Tab", "ArrowLeft", "ArrowRight", "Home", "End"];

    if (teclasPermitidas.includes(event.key)) {
        return;
    }

    if (!/^\d$/.test(event.key)) {
        event.preventDefault();
    }
}

function validarPrecio(event) {
    const teclasPermitidas = ["Backspace", "Delete", "Tab", "ArrowLeft", "ArrowRight", "Home", "End"];

    if (teclasPermitidas.includes(event.key)) {
        return;
    }

    if (event.key === "e" || event.key === "E" || event.key === "+" || event.key === "-" || event.key === ",") {
        event.preventDefault();
        return;
    }

    if (event.key === ".") {
        const valor = event.target.value || "";
        if (valor.length === 0 || valor.includes(".")) {
            event.preventDefault();
        }
        return;
    }

    if (!/^\d$/.test(event.key)) {
        event.preventDefault();
    }
}

function redondearNumero(num) {
    const numero = Number(num);

    if (Number.isNaN(numero)) {
        return 0;
    }

    return Math.round((numero + Number.EPSILON) * 100) / 100;
}

if (typeof module !== "undefined" && module.exports) {
    module.exports = {
        InputService,
        validarCantidad,
        validarPrecio,
        redondearNumero,
    };
}

if (typeof window !== "undefined") {
    window.InputService = InputService;
    window.validarCantidad = validarCantidad;
    window.validarPrecio = validarPrecio;
    window.redondearNumero = redondearNumero;
}

