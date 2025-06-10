// Admin panel functionality
document.addEventListener("DOMContentLoaded", () => {
  initializeAdminPanel()
})

/**
 * Initialize admin panel
 */
function initializeAdminPanel() {
  setupModals()
  setupDeleteButtons()
  setupForms()
  setupSidebar()
}

/**
 * Setup modal functionality
 */
function setupModals() {
  // New course button
  const newCourseBtn = document.getElementById("newCourseBtn")
  if (newCourseBtn) {
    newCourseBtn.addEventListener("click", () => {
      showModal("courseModal")
    })
  }

  // Close modal buttons
  const closeButtons = document.querySelectorAll(".close-modal")
  closeButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const modal = this.closest(".modal")
      hideModal(modal.id)
    })
  })

  // Cancel buttons
  const cancelButtons = document.querySelectorAll("#cancelBtn")
  cancelButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const modal = this.closest(".modal")
      hideModal(modal.id)
    })
  })

  // Click outside modal to close
  const modals = document.querySelectorAll(".modal")
  modals.forEach((modal) => {
    modal.addEventListener("click", function (e) {
      if (e.target === this) {
        hideModal(this.id)
      }
    })
  })
}

/**
 * Setup delete buttons
 */
function setupDeleteButtons() {
  const deleteButtons = document.querySelectorAll(".delete-course")
  deleteButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const courseId = this.dataset.id
      if (confirm("¿Estás seguro de que quieres eliminar este curso?")) {
        deleteCourse(courseId)
      }
    })
  })
}

/**
 * Setup forms
 */
function setupForms() {
  // Course form
  const courseForm = document.getElementById("courseForm")
  if (courseForm) {
    courseForm.addEventListener("submit", (e) => {
      if (!validateCourseForm()) {
        e.preventDefault()
      }
    })
  }
}

/**
 * Setup sidebar for mobile
 */
function setupSidebar() {
  // Add mobile menu toggle if needed
  const menuToggle = document.getElementById("menuToggle")
  if (menuToggle) {
    menuToggle.addEventListener("click", () => {
      const sidebar = document.querySelector(".admin-sidebar")
      sidebar.classList.toggle("show")
    })
  }
}

/**
 * Show modal
 */
function showModal(modalId) {
  const modal = document.getElementById(modalId)
  if (modal) {
    modal.classList.add("show")
    document.body.style.overflow = "hidden"
  }
}

/**
 * Hide modal
 */
function hideModal(modalId) {
  const modal = document.getElementById(modalId)
  if (modal) {
    modal.classList.remove("show")
    document.body.style.overflow = "auto"

    // Reset form if exists
    const form = modal.querySelector("form")
    if (form) {
      form.reset()
    }
  }
}

/**
 * Validate course form
 */
function validateCourseForm() {
  const title = document.getElementById("title").value.trim()
  const description = document.getElementById("description").value.trim()
  const price = document.getElementById("price").value
  const level = document.getElementById("level").value

  if (!title) {
    showAlert("El título es requerido", "error")
    return false
  }

  if (!description) {
    showAlert("La descripción es requerida", "error")
    return false
  }

  if (!price || price <= 0) {
    showAlert("El precio debe ser mayor a 0", "error")
    return false
  }

  if (!level) {
    showAlert("El nivel es requerido", "error")
    return false
  }

  return true
}

/**
 * Delete course
 */
async function deleteCourse(courseId) {
  try {
    const formData = new FormData()
    formData.append("action", "delete")
    formData.append("id", courseId)

    const response = await fetch("courses.php", {
      method: "POST",
      body: formData,
    })

    if (response.ok) {
      location.reload()
    } else {
      showAlert("Error al eliminar el curso", "error")
    }
  } catch (error) {
    console.error("Error:", error)
    showAlert("Error al eliminar el curso", "error")
  }
}

/**
 * Show alert
 */
function showAlert(message, type = "info") {
  const alert = document.createElement("div")
  alert.className = `alert alert-${type}`
  alert.textContent = message
  alert.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 2500;
        min-width: 300px;
        animation: slideIn 0.3s ease-out;
    `

  document.body.appendChild(alert)

  setTimeout(() => {
    alert.remove()
  }, 5000)
}

/**
 * Format currency
 */
function formatCurrency(amount) {
  return new Intl.NumberFormat("es-CO", {
    style: "currency",
    currency: "USD",
  }).format(amount)
}

/**
 * Format date
 */
function formatDate(date) {
  return new Intl.DateTimeFormat("es-ES", {
    year: "numeric",
    month: "short",
    day: "numeric",
  }).format(new Date(date))
}
