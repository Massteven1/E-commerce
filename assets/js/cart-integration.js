/**
 * Cart Integration for all pages
 */
class CartIntegration {
  constructor() {
    this.setupEventListeners()
    this.updateCartCount()
  }

  setupEventListeners() {
    // Delegación de eventos para botones "Añadir al carrito"
    document.addEventListener("click", (e) => {
      if (e.target.closest(".add-to-cart-btn")) {
        e.preventDefault()
        this.handleAddToCart(e)
      }
    })

    // Click en el icono del carrito
    const cartIcon = document.getElementById("cartIcon")
    if (cartIcon) {
      cartIcon.addEventListener("click", () => {
        window.location.href = "cart.php"
      })
    }
  }

  async handleAddToCart(event) {
    const button = event.target.closest(".add-to-cart-btn")
    const courseId = button.dataset.courseId
    const originalContent = button.innerHTML // Declare originalContent here

    if (!courseId) {
      this.showToast("Error: ID del curso no encontrado", "error")
      return
    }

    // Verificar autenticación
    const firebase = window.firebase // Declare firebase here
    const user = firebase.auth().currentUser
    if (!user) {
      this.showToast("Debes iniciar sesión para añadir productos al carrito", "error")
      setTimeout(() => {
        window.location.href = "login.php"
      }, 2000)
      return
    }

    try {
      // Mostrar estado de carga
      button.disabled = true
      button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Añadiendo...'

      const response = await fetch("api/cart.php?action=add", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          course_id: Number.parseInt(courseId),
          quantity: 1,
        }),
      })

      const data = await response.json()

      if (data.success) {
        this.showToast("¡Curso añadido al carrito!", "success")
        this.updateCartCount()

        // Cambiar botón temporalmente
        button.innerHTML = '<i class="fas fa-check"></i> ¡Añadido!'
        button.classList.add("added")

        setTimeout(() => {
          button.innerHTML = originalContent
          button.classList.remove("added")
          button.disabled = false
        }, 2000)
      } else {
        this.showToast(data.message || "Error al añadir al carrito", "error")
        button.innerHTML = originalContent
        button.disabled = false
      }
    } catch (error) {
      console.error("Error adding to cart:", error)
      this.showToast("Error al añadir al carrito", "error")
      button.innerHTML = originalContent
      button.disabled = false
    }
  }

  async updateCartCount() {
    const firebase = window.firebase // Declare firebase here
    const user = firebase.auth().currentUser
    if (!user) {
      this.setCartBadge(0)
      return
    }

    try {
      const response = await fetch("api/cart.php?action=count")
      const data = await response.json()

      if (data.success) {
        this.setCartBadge(data.count || 0)
      }
    } catch (error) {
      console.error("Error updating cart count:", error)
    }
  }

  setCartBadge(count) {
    const cartBadge = document.getElementById("cartBadge")
    if (cartBadge) {
      cartBadge.textContent = count.toString()

      // Animación cuando cambia el número
      cartBadge.style.transform = "scale(1.2)"
      setTimeout(() => {
        cartBadge.style.transform = "scale(1)"
      }, 200)
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

// Inicializar cuando Firebase esté listo
document.addEventListener("DOMContentLoaded", () => {
  window.firebase.auth().onAuthStateChanged((user) => {
    // Use window.firebase here
    if (user) {
      new CartIntegration()
    }
  })
})
