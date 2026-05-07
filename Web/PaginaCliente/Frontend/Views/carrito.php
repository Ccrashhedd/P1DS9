<?php ?>

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

                <button class="btn btn-primary btn-full" id="btn-finalizar-compra" type="button">Finalizar compra</button>

            </aside>
        </div>
    </div>
</section>

<dialog class="moduloPago" id="modal-pago">
    <div class="moduloContainer">
        <div class="modal-header">
            <h3>Finalizar compra</h3>
            <button class="btn-close" id="btn-cerrar-modal" type="button" aria-label="Cerrar">×</button>
        </div>

        <div class="containerTarjetas">
            <aside class="seccionTarjetas">
                <h5>Ingrese tarjeta de pago</h5>
                <form id="formulario-tarjeta" onsubmit="return false;">
                    <label for="input-digitos">Dígitos:</label>
                    <input 
                        type="text"
                        id="input-digitos"
                        name="digitos"
                        maxlength="16"
                        placeholder="Últimos dígitos"
                        inputmode="numeric"
                        pattern="\d{2,8}"
                        >
                    <label for="input-cvv">CVV</label>
                    <input
                        type="text"
                        id="input-cvv"
                        name="cvv"
                        maxlength="4"
                        placeholder="CVV"
                        >
                    <label for="input-fecha">Fecha de vencimiento</label>
                    <input
                        type="text"
                        id="input-fecha"
                        name="fecha"
                        maxlength="5"
                        placeholder="MM/AA"
                        inputmode="numeric"
                    >
                    <div>
                        <button class="btn btn-primary btn-sm" type="button" id="btn-llenar-digitos">Usar tarjeta ingresada</button>
                        <button class="btn btn-ghost btn-sm" type="button" id="btn-limpiar-tarjeta">Limpiar</button>
                    </div>
                </form>
            </aside>

            <div class="seccionPago">
                <form id="form-pago" method="post">
                    <input type="hidden" name="_action" value="pago_carrito">
                    <input type="hidden" name="idTarjeta" id="input-id-tarjeta" value="">
                    <input type="hidden" name="subtotal" id="input-subtotal" value="0">
                    <input type="hidden" name="itbms" id="input-itbms" value="0">
                    <input type="hidden" name="total" id="input-total" value="0">
                    <input type="hidden" name="productos" id="input-productos" value="[]">
                    <h4>Resumen del precio</h4>
                    <div class="resumen-linea-modal">
                        <span>Subtotal:</span>
                        <strong id="modal-subtotal">$0.00</strong>
                    </div>
                    <div class="resumen-linea-modal">
                        <span>ITBMS (7%):</span>
                        <strong id="modal-itbms">$0.00</strong>
                    </div>
                    <hr>
                    <div class="resumen-linea-modal total">
                        <span>Total:</span>
                        <strong id="modal-total">$0.00</strong>
                    </div>
                    <button class="btn btn-primary btn-full" type="submit">Pagar</button>
                    <button class="btn btn-ghost btn-full" id="btn-cancelar-modal" type="button">Cancelar</button>
                </form>
            </div>
        </div>
    </div>
</dialog>

<script src="../../../Backend/Js/input_service.js" defer></script>
<script src="../../../Backend/Js/carrito_service.js"></script>
