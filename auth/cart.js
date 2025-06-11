import { getUserToken, isAuthenticated, showToast } from "./auth.js"

/**
 * Cart management functionality
 */
class CartManager {
  constructor() {
    this.initializeEventListeners()
  }

  /**
   * Initialize event listeners
   */
  initializeEventListeners() {
    // Add to cart buttons
    document.addEventListener("click", (e) => {
      if (e.target.classList.contains("add-to-cart-btn") || e.target.closest(".add-to-cart-btn")) {
        e.preventDefault()
        this.handleAddToCart(e)
      }
    })

    // Cart icon click
    const cartIcon = document.getElementById("cartIcon")
    if (cartIcon) {
      cartIcon.addEventListener("click", () => {
        window.location.href = "/cart"
      })
    }
  }

  /**
   * Handle add to cart button click
   */
  async handleAddToCart(event) {
    if (!isAuthenticated()) {
      showToast("Debes iniciar sesión para añadir productos al carrito", "warning")
      window.location.href = "/login"
      return
    }

    const button = event.target.closest(".add-to-cart-btn")
    const courseId = button.dataset.courseId

    if (!courseId) {
      showToast("Error: ID del curso no encontrado", "error")
      return
    }

    try {
      // Show loading state
      button.disabled = true
      button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Añadiendo...'

      const success = await this.addToCart(courseId)

      if (success) {
        showToast("Curso añadido al carrito", "success")
        this.updateCartCount()
      }
    } catch (error) {
      console.error("Error adding to cart:", error)
      showToast("Error al añadir al carrito", "error")
    } finally {
      // Restore button state
      button.disabled = false
      button.innerHTML = '<i class="fas fa-cart-plus"></i> Añadir al Carrito'
    }
  }

  /**
   * Add item to cart
   */
  async addToCart(courseId, quantity = 1) {
    try {
      const token = await getUserToken()

      const response = await fetch("/api/cart/add", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify({
          course_id: courseId,
          quantity: quantity,
        }),
      })

      if (!response.ok) {
        throw new Error("Failed to add item to cart")
      }

      const data = await response.json()
      return data.success
    } catch (error) {
      console.error("Error adding to cart:", error)
      return false
    }
  }

  /**
   * Remove item from cart
   */
  async removeFromCart(courseId) {
    try {
      const token = await getUserToken()

      const response = await fetch("/api/cart/remove", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify({
          course_id: courseId,
        }),
      })

      if (!response.ok) {
        throw new Error("Failed to remove item from cart")
      }

      const data = await response.json()
      return data
    } catch (error) {
      console.error("Error removing from cart:", error)
      return { success: false }
    }
  }

  /**
   * Update item quantity in cart
   */
  async updateQuantity(courseId, quantity) {
    try {
      const token = await getUserToken()

      const response = await fetch("/api/cart/update", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify({
          course_id: courseId,
          quantity: quantity,
        }),
      })

      if (!response.ok) {
        throw new Error("Failed to update cart item")
      }

      const data = await response.json()
      return data
    } catch (error) {
      console.error("Error updating cart:", error)
      return { success: false }
    }
  }

  /**
   * Update cart count in UI
   */
  async updateCartCount() {
    if (!isAuthenticated()) return

    try {
      const token = await getUserToken()
      const response = await fetch("/api/cart/count", {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      })

      if (response.ok) {
        const data = await response.json()
        const cartBadge = document.getElementById("cartBadge")
        if (cartBadge) {
          cartBadge.textContent = data.count || "0"
        }
      }
    } catch (error) {
      console.error("Error updating cart count:", error)
    }
  }
}

// Initialize cart manager when DOM is loaded
document.addEventListener("DOMContentLoaded", () => {
  new CartManager()
})

export default CartManager
