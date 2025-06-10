/**
 * Cart Page Functionality
 */
class CartPage {
  constructor() {
    this.cartItems = []
    this.subtotal = 0
    this.discount = 0
    this.total = 0
    this.promoCode = null

    this.initializeElements()
    this.setupEventListeners()
    this.loadCartData()
  }

  initializeElements() {
    this.cartItemsContainer = document.getElementById("cartItems")
    this.cartSummary = document.getElementById("cartSummary")
    this.emptyCart = document.getElementById("emptyCart")
    this.cartBadge = document.getElementById("cartBadge")
    this.subtotalElement = document.getElementById("subtotal")
    this.discountElement = document.getElementById("discount")
    this.totalElement = document.getElementById("total")
    this.promoInput = document.getElementById("promoInput")
    this.applyPromoBtn = document.getElementById("applyPromo")
    this.clearCartBtn = document.getElementById("clearCart")
    this.checkoutBtn = document.getElementById("proceedCheckout")
    this.loadingOverlay = document.getElementById("loadingOverlay")
    this.logoutBtn = document.getElementById("logoutBtn")
  }

  setupEventListeners() {
    // Aplicar código promocional
    this.applyPromoBtn?.addEventListener("click", () => this.applyPromoCode())
    this.promoInput?.addEventListener("keypress", (e) => {
      if (e.key === "Enter") this.applyPromoCode()
    })

    // Vaciar carrito
    this.clearCartBtn?.addEventListener("click", () => this.clearCart())

    // Proceder al checkout
    this.checkoutBtn?.addEventListener("click", () => this.proceedToCheckout())

    // Logout
    this.logoutBtn?.addEventListener("click", () => this.handleLogout())

    // Delegación de eventos para items del carrito
    this.cartItemsContainer?.addEventListener("click", (e) => this.handleCartItemClick(e))
    this.cartItemsContainer?.addEventListener("change", (e) => this.handleQuantityChange(e))
  }

  async loadCartData() {
    try {
      this.showLoading(true)

      const response = await fetch("api/cart.php")
      const data = await response.json()

      if (data.success) {
        this.cartItems = data.items || []
        this.subtotal = Number.parseFloat(data.total || 0)
        this.updateCartDisplay()
        this.updateCartBadge(data.count || 0)

        if (this.cartItems.length === 0) {
          this.showEmptyCart()
        } else {
          this.loadRecommendedCourses()
        }
      } else {
        this.showToast("Error al cargar el carrito: " + data.message, "error")
        this.showEmptyCart()
      }
    } catch (error) {
      console.error("Error loading cart:", error)
      this.showToast("Error al cargar el carrito", "error")
      this.showEmptyCart()
    } finally {
      this.showLoading(false)
    }
  }

  updateCartDisplay() {
    if (this.cartItems.length === 0) {
      this.showEmptyCart()
      return
    }

    this.emptyCart.style.display = "none"
    this.cartSummary.style.display = "block"

    // Renderizar items del carrito
    this.cartItemsContainer.innerHTML = this.cartItems.map((item) => this.renderCartItem(item)).join("")

    // Actualizar resumen
    this.calculateTotals()
    this.updateSummary()
  }

  renderCartItem(item) {
    const imageUrl = item.image_url || "/placeholder.svg?height=120&width=120"
    const levelClass = item.level ? item.level.toLowerCase() : "a1"

    return `
            <div class="cart-item" data-course-id="${item.course_id}">
                <div class="item-image">
                    <img src="${imageUrl}" alt="${item.title}" onerror="this.src='/placeholder.svg?height=120&width=120'">
                    <div class="level-badge ${levelClass}">${item.level || "A1"}</div>
                </div>
                <div class="item-details">
                    <h3 class="item-title">${item.title}</h3>
                    <p class="item-description">${item.description ? item.description.substring(0, 100) + "..." : ""}</p>
                    <div class="item-level">Nivel: ${item.level || "A1"}</div>
                </div>
                <div class="item-quantity">
                    <label>Cantidad:</label>
                    <div class="quantity-controls">
                        <button type="button" class="quantity-btn minus" data-action="decrease">
                            <i class="fas fa-minus"></i>
                        </button>
                        <input type="number" class="quantity-input" value="${item.quantity}" min="1" max="10">
                        <button type="button" class="quantity-btn plus" data-action="increase">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="item-price">
                    <div class="unit-price">$${Number.parseFloat(item.price).toFixed(2)} c/u</div>
                    <div class="total-price">$${(Number.parseFloat(item.price) * Number.parseInt(item.quantity)).toFixed(2)}</div>
                </div>
                <div class="item-actions">
                    <button type="button" class="remove-item" data-course-id="${item.course_id}">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `
  }

  handleCartItemClick(e) {
    const courseId = e.target.closest("[data-course-id]")?.dataset.courseId
    if (!courseId) return

    if (e.target.closest(".remove-item")) {
      this.removeItem(courseId)
    } else if (e.target.closest(".quantity-btn")) {
      const action = e.target.closest(".quantity-btn").dataset.action
      const quantityInput = e.target.closest(".cart-item").querySelector(".quantity-input")
      const currentQuantity = Number.parseInt(quantityInput.value)

      if (action === "increase") {
        this.updateQuantity(courseId, currentQuantity + 1)
      } else if (action === "decrease" && currentQuantity > 1) {
        this.updateQuantity(courseId, currentQuantity - 1)
      }
    }
  }

  handleQuantityChange(e) {
    if (e.target.classList.contains("quantity-input")) {
      const courseId = e.target.closest("[data-course-id]").dataset.courseId
      const newQuantity = Number.parseInt(e.target.value)

      if (newQuantity >= 1 && newQuantity <= 10) {
        this.updateQuantity(courseId, newQuantity)
      } else {
        e.target.value = 1
        this.updateQuantity(courseId, 1)
      }
    }
  }

  async updateQuantity(courseId, quantity) {
    try {
      this.showLoading(true)

      const response = await fetch("api/cart.php?action=update", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          course_id: Number.parseInt(courseId),
          quantity: quantity,
        }),
      })

      const data = await response.json()

      if (data.success) {
        this.showToast("Cantidad actualizada", "success")
        this.loadCartData() // Recargar datos
      } else {
        this.showToast("Error al actualizar: " + data.message, "error")
      }
    } catch (error) {
      console.error("Error updating quantity:", error)
      this.showToast("Error al actualizar la cantidad", "error")
    } finally {
      this.showLoading(false)
    }
  }

  async removeItem(courseId) {
    if (!confirm("¿Estás seguro de que quieres eliminar este curso del carrito?")) {
      return
    }

    try {
      this.showLoading(true)

      const response = await fetch("api/cart.php?action=remove", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          course_id: Number.parseInt(courseId),
        }),
      })

      const data = await response.json()

      if (data.success) {
        this.showToast("Curso eliminado del carrito", "success")
        this.loadCartData() // Recargar datos
      } else {
        this.showToast("Error al eliminar: " + data.message, "error")
      }
    } catch (error) {
      console.error("Error removing item:", error)
      this.showToast("Error al eliminar el curso", "error")
    } finally {
      this.showLoading(false)
    }
  }

  async clearCart() {
    if (!confirm("¿Estás seguro de que quieres vaciar todo el carrito?")) {
      return
    }

    try {
      this.showLoading(true)

      const response = await fetch("api/cart.php?action=clear", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
      })

      const data = await response.json()

      if (data.success) {
        this.showToast("Carrito vaciado", "success")
        this.cartItems = []
        this.showEmptyCart()
        this.updateCartBadge(0)
      } else {
        this.showToast("Error al vaciar el carrito: " + data.message, "error")
      }
    } catch (error) {
      console.error("Error clearing cart:", error)
      this.showToast("Error al vaciar el carrito", "error")
    } finally {
      this.showLoading(false)
    }
  }

  applyPromoCode() {
    const code = this.promoInput.value.trim().toUpperCase()

    if (!code) {
      this.showToast("Ingresa un código promocional", "error")
      return
    }

    // Códigos promocionales de ejemplo
    const promoCodes = {
      DESCUENTO10: 0.1,
      NUEVO20: 0.2,
      ESTUDIANTE15: 0.15,
      VERANO25: 0.25,
    }

    if (promoCodes[code]) {
      this.discount = this.subtotal * promoCodes[code]
      this.promoCode = code
      this.calculateTotals()
      this.updateSummary()
      this.showToast(`¡Código aplicado! Descuento del ${promoCodes[code] * 100}%`, "success")
      this.promoInput.value = ""
    } else {
      this.showToast("Código promocional inválido", "error")
    }
  }

  calculateTotals() {
    this.subtotal = this.cartItems.reduce((sum, item) => {
      return sum + Number.parseFloat(item.price) * Number.parseInt(item.quantity)
    }, 0)

    // Recalcular descuento si hay código aplicado
    if (this.promoCode) {
      const promoCodes = {
        DESCUENTO10: 0.1,
        NUEVO20: 0.2,
        ESTUDIANTE15: 0.15,
        VERANO25: 0.25,
      }
      this.discount = this.subtotal * (promoCodes[this.promoCode] || 0)
    }

    this.total = this.subtotal - this.discount
  }

  updateSummary() {
    this.subtotalElement.textContent = `$${this.subtotal.toFixed(2)}`
    this.discountElement.textContent = `-$${this.discount.toFixed(2)}`
    this.totalElement.textContent = `$${this.total.toFixed(2)}`

    // Mostrar/ocultar descuento
    const discountRow = this.discountElement.closest(".summary-row")
    if (this.discount > 0) {
      discountRow.style.display = "flex"
    } else {
      discountRow.style.display = "none"
    }
  }

  showEmptyCart() {
    this.cartItemsContainer.innerHTML = ""
    this.cartSummary.style.display = "none"
    this.emptyCart.style.display = "block"
    document.getElementById("recommendedCourses").style.display = "none"
  }

  updateCartBadge(count) {
    if (this.cartBadge) {
      this.cartBadge.textContent = count.toString()
    }
  }

  async loadRecommendedCourses() {
    try {
      // Simular carga de cursos recomendados
      const recommendedGrid = document.getElementById("recommendedGrid")
      if (recommendedGrid) {
        recommendedGrid.innerHTML = `
                    <div class="loading-recommended">
                        <p>Cargando cursos recomendados...</p>
                    </div>
                `
      }
    } catch (error) {
      console.error("Error loading recommended courses:", error)
    }
  }

  proceedToCheckout() {
    if (this.cartItems.length === 0) {
      this.showToast("Tu carrito está vacío", "error")
      return
    }

    // Guardar datos del carrito en sessionStorage para el checkout
    sessionStorage.setItem(
      "checkoutData",
      JSON.stringify({
        items: this.cartItems,
        subtotal: this.subtotal,
        discount: this.discount,
        total: this.total,
        promoCode: this.promoCode,
      }),
    )

    // Redirigir al checkout
    window.location.href = "checkout.php"
  }

  async handleLogout() {
    const firebase = window.firebase // Declare firebase variable here
    try {
      await firebase.auth().signOut()

      const response = await fetch("api/auth.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          action: "logout",
        }),
      })

      this.showToast("Sesión cerrada correctamente", "success")
      setTimeout(() => {
        window.location.href = "index.php"
      }, 1000)
    } catch (error) {
      console.error("Error al cerrar sesión:", error)
      this.showToast("Error al cerrar sesión", "error")
    }
  }

  showLoading(show) {
    if (this.loadingOverlay) {
      if (show) {
        this.loadingOverlay.classList.add("show")
      } else {
        this.loadingOverlay.classList.remove("show")
      }
    }
  }

  showToast(message, type = "info") {
    const toastContainer = document.getElementById("toastContainer")
    if (!toastContainer) return

    const toast = document.createElement("div")
    toast.className = `toast ${type}`
    toast.innerHTML = `
            <div class="toast-content">
                <i class="fas fa-${type === "success" ? "check-circle" : type === "error" ? "exclamation-circle" : "info-circle"}"></i>
                <span>${message}</span>
            </div>
        `

    toastContainer.appendChild(toast)

    setTimeout(() => {
      toast.classList.add("fade-out")
      setTimeout(() => {
        if (toast.parentNode) {
          toast.parentNode.removeChild(toast)
        }
      }, 300)
    }, 3000)
  }
}

// Inicializar cuando el DOM esté listo
document.addEventListener("DOMContentLoaded", () => {
  // Verificar autenticación de Firebase
  const firebase = window.firebase // Declare firebase variable here
  firebase.auth().onAuthStateChanged((user) => {
    if (user) {
      // Usuario autenticado, inicializar carrito
      new CartPage()
    } else {
      // Usuario no autenticado, redirigir
      window.location.href = "login.php"
    }
  })
})
