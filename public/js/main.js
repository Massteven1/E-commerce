import { showToast } from "./auth.js"

/**
 * Main application functionality
 */
class App {
  constructor() {
    this.initializeEventListeners()
    this.initializeBackToTop()
    this.initializeSearch()
  }

  /**
   * Initialize global event listeners
   */
  initializeEventListeners() {
    // Contact form
    const contactForm = document.getElementById("contactForm")
    if (contactForm) {
      contactForm.addEventListener("submit", this.handleContactForm.bind(this))
    }

    // Course level cards
    document.addEventListener("click", (e) => {
      if (e.target.closest(".course-card[data-level]")) {
        const level = e.target.closest(".course-card").dataset.level
        window.location.href = `/courses?level=${level}`
      }
    })

    // Smooth scrolling for anchor links
    document.addEventListener("click", (e) => {
      if (e.target.matches('a[href^="#"]')) {
        e.preventDefault()
        const target = document.querySelector(e.target.getAttribute("href"))
        if (target) {
          target.scrollIntoView({
            behavior: "smooth",
            block: "start",
          })
        }
      }
    })
  }

  /**
   * Handle contact form submission
   */
  async handleContactForm(event) {
    event.preventDefault()

    const formData = new FormData(event.target)
    const data = {
      name: formData.get("name"),
      email: formData.get("email"),
      message: formData.get("message"),
    }

    // Basic validation
    if (!data.name || !data.email || !data.message) {
      showToast("Por favor, completa todos los campos", "warning")
      return
    }

    try {
      // Show loading state
      const submitBtn = event.target.querySelector('button[type="submit"]')
      const originalText = submitBtn.textContent
      submitBtn.disabled = true
      submitBtn.textContent = "Enviando..."

      // Simulate form submission (replace with actual endpoint)
      await new Promise((resolve) => setTimeout(resolve, 1000))

      showToast("Mensaje enviado correctamente. Te contactaremos pronto.", "success")
      event.target.reset()

      // Restore button
      submitBtn.disabled = false
      submitBtn.textContent = originalText
    } catch (error) {
      console.error("Error sending contact form:", error)
      showToast("Error al enviar el mensaje. Inténtalo de nuevo.", "error")
    }
  }

  /**
   * Initialize back to top button
   */
  initializeBackToTop() {
    const backToTopBtn = document.querySelector(".back-to-top a")

    if (backToTopBtn) {
      backToTopBtn.addEventListener("click", (e) => {
        e.preventDefault()
        window.scrollTo({
          top: 0,
          behavior: "smooth",
        })
      })

      // Show/hide based on scroll position
      window.addEventListener("scroll", () => {
        const backToTop = document.querySelector(".back-to-top")
        if (window.pageYOffset > 300) {
          backToTop.style.opacity = "1"
          backToTop.style.visibility = "visible"
        } else {
          backToTop.style.opacity = "0"
          backToTop.style.visibility = "hidden"
        }
      })
    }
  }

  /**
   * Initialize search functionality
   */
  initializeSearch() {
    const searchInput = document.getElementById("searchInput")

    if (searchInput) {
      let searchTimeout

      searchInput.addEventListener("input", (e) => {
        clearTimeout(searchTimeout)
        const query = e.target.value.trim()

        if (query.length >= 3) {
          searchTimeout = setTimeout(() => {
            this.performSearch(query)
          }, 300)
        }
      })

      searchInput.addEventListener("keypress", (e) => {
        if (e.key === "Enter") {
          e.preventDefault()
          const query = e.target.value.trim()
          if (query) {
            window.location.href = `/courses?search=${encodeURIComponent(query)}`
          }
        }
      })
    }
  }

  /**
   * Perform search (could be enhanced with autocomplete)
   */
  async performSearch(query) {
    try {
      // This could be enhanced to show search suggestions
      console.log("Searching for:", query)
    } catch (error) {
      console.error("Search error:", error)
    }
  }

  /**
   * Show loading overlay
   */
  static showLoading() {
    const overlay = document.getElementById("loadingOverlay")
    if (overlay) {
      overlay.classList.add("show")
    }
  }

  /**
   * Hide loading overlay
   */
  static hideLoading() {
    const overlay = document.getElementById("loadingOverlay")
    if (overlay) {
      overlay.classList.remove("show")
    }
  }

  /**
   * Format currency
   */
  static formatCurrency(amount) {
    return new Intl.NumberFormat("es-CO", {
      style: "currency",
      currency: "USD",
    }).format(amount)
  }

  /**
   * Format date
   */
  static formatDate(date) {
    return new Intl.DateTimeFormat("es-ES", {
      year: "numeric",
      month: "long",
      day: "numeric",
    }).format(new Date(date))
  }
}

// Initialize app when DOM is loaded
document.addEventListener("DOMContentLoaded", () => {
  new App()
})

// Export utilities
export { App }
