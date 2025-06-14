<?php
// Asegúrate de que la sesión esté iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Las variables $cart_items y $totals se pasan desde el CartController::checkout()
?>

<!-- Checkout Section -->
<section class="checkout">
    <div class="container">
        <h1 class="checkout-title">Finalizar Compra</h1>
        
        <div class="checkout-container">
            <!-- Formulario de Checkout -->
            <div class="checkout-form">
                <!-- Información Personal -->
                <div class="checkout-section">
                    <h2>Información Personal</h2>
                    <form id="checkoutForm">
                        <div class="form-row">
                            <input type="text" name="first_name" placeholder="Nombre" required>
                            <input type="text" name="last_name" placeholder="Apellido" required>
                        </div>
                        <div class="form-row">
                            <input type="email" name="email" placeholder="Correo Electrónico" required>
                            <input type="tel" name="phone" placeholder="Teléfono" required>
                        </div>
                    </form>
                </div>

                <!-- Información de Facturación -->
                <div class="checkout-section">
                    <h2>Información de Facturación</h2>
                    <div class="form-row">
                        <input type="text" name="address" placeholder="Dirección" required>
                        <input type="text" name="city" placeholder="Ciudad" required>
                    </div>
                    <div class="form-row">
                        <input type="text" name="state" placeholder="Estado/Provincia" required>
                        <input type="text" name="zip_code" placeholder="Código Postal" required>
                    </div>
                    <div class="form-row">
                        <select name="country" required>
                            <option value="">Seleccionar País</option>
                            <option value="CO">Colombia</option>
                            <option value="MX">México</option>
                            <option value="AR">Argentina</option>
                            <option value="ES">España</option>
                            <option value="US">Estados Unidos</option>
                            <option value="other">Otro</option>
                        </select>
                    </div>
                </div>

                <!-- Método de Pago -->
                <div class="checkout-section">
                    <h2>Método de Pago</h2>
                    <div class="payment-methods">
                        <label class="payment-method">
                            <input type="radio" name="payment_method" value="credit_card" checked>
                            <span class="radio-custom"></span>
                            <div class="payment-label">
                                <i class="fas fa-credit-card"></i>
                                Tarjeta de Crédito/Débito
                            </div>
                        </label>
                        
                        <label class="payment-method">
                            <input type="radio" name="payment_method" value="paypal">
                            <span class="radio-custom"></span>
                            <div class="payment-label">
                                <i class="fab fa-paypal"></i>
                                PayPal
                            </div>
                        </label>
                        
                        <label class="payment-method">
                            <input type="radio" name="payment_method" value="bank_transfer">
                            <span class="radio-custom"></span>
                            <div class="payment-label">
                                <i class="fas fa-university"></i>
                                Transferencia Bancaria
                            </div>
                        </label>
                    </div>

                    <!-- Formulario de Tarjeta de Crédito -->
                    <div class="credit-card-form" id="creditCardForm">
                        <div class="form-row">
                            <input type="text" name="card_number" placeholder="Número de Tarjeta" maxlength="19">
                            <input type="text" name="card_name" placeholder="Nombre en la Tarjeta">
                        </div>
                        <div class="form-row">
                            <input type="text" name="expiry_date" placeholder="MM/AA" maxlength="5">
                            <input type="text" name="cvv" placeholder="CVV" maxlength="4">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resumen del Pedido -->
            <div class="order-summary">
                <div class="summary-header">
                    <h2>Resumen del Pedido</h2>
                    <span class="items-count"><?php echo count($cart_items); ?></span>
                </div>
                
                <div class="cart-items">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item">
                            <div class="item-image">
                                <?php if (!empty($item['cover_image'])): ?>
                                    <img src="<?php echo htmlspecialchars($item['cover_image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                <?php else: ?>
                                    <img src="https://i.imgur.com/xdbHo4E.png" alt="Imagen por defecto" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                <?php endif; ?>
                            </div>
                            <div class="item-details">
                                <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                <p>Acceso Digital</p>
                            </div>
                            <div class="item-price">$<?php echo htmlspecialchars(number_format($item['price'], 2)); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="summary-totals">
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span>$<?php echo htmlspecialchars(number_format($totals['subtotal'], 2)); ?></span>
                    </div>
                    
                    <?php if ($totals['discount'] > 0): ?>
                        <div class="summary-row">
                            <span>Descuento 
                                <?php if ($totals['promo_code_applied']): ?>
                                    (<?php echo htmlspecialchars($totals['promo_code_applied']); ?>)
                                <?php endif; ?>
                            </span>
                            <span style="color: var(--orange-color);">-$<?php echo htmlspecialchars(number_format($totals['discount'], 2)); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="summary-row">
                        <span>Impuesto</span>
                        <span>$<?php echo htmlspecialchars(number_format($totals['tax'], 2)); ?></span>
                    </div>
                    
                    <div class="summary-row total">
                        <span>Total</span>
                        <span>$<?php echo htmlspecialchars(number_format($totals['total'], 2)); ?></span>
                    </div>
                </div>

                <button type="submit" form="checkoutForm" class="btn-checkout">
                    <i class="fas fa-lock"></i>
                    Completar Compra
                </button>

                <div class="secure-checkout">
                    <i class="fas fa-shield-alt"></i>
                    <span>Pago 100% Seguro</span>
                </div>

                <div class="payment-icons">
                    <i class="fab fa-cc-visa"></i>
                    <i class="fab fa-cc-mastercard"></i>
                    <i class="fab fa-cc-paypal"></i>
                    <i class="fab fa-cc-amex"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Back to Shopping Link -->
<div class="back-to-shopping">
    <a href="index.php?controller=cart&action=view">
        <i class="fas fa-arrow-left"></i>
        Volver al Carrito
    </a>
</div>

<script>
    // Script para mostrar/ocultar formulario de tarjeta de crédito
    document.addEventListener('DOMContentLoaded', function() {
        const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
        const creditCardForm = document.getElementById('creditCardForm');

        paymentMethods.forEach(method => {
            method.addEventListener('change', function() {
                if (this.value === 'credit_card') {
                    creditCardForm.style.display = 'block';
                } else {
                    creditCardForm.style.display = 'none';
                }
            });
        });

        // Formatear número de tarjeta
        const cardNumberInput = document.querySelector('input[name="card_number"]');
        if (cardNumberInput) {
            cardNumberInput.addEventListener('input', function() {
                let value = this.value.replace(/\s/g, '').replace(/[^0-9]/gi, '');
                let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
                this.value = formattedValue;
            });
        }

        // Formatear fecha de expiración
        const expiryInput = document.querySelector('input[name="expiry_date"]');
        if (expiryInput) {
            expiryInput.addEventListener('input', function() {
                let value = this.value.replace(/\D/g, '');
                if (value.length >= 2) {
                    value = value.substring(0, 2) + '/' + value.substring(2, 4);
                }
                this.value = value;
            });
        }
    });
</script>
