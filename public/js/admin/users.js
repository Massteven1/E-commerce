// Gestión de usuarios en el panel de administración

// Variables globales
let currentUsers = []
let filteredUsers = []

// Inicialización
document.addEventListener("DOMContentLoaded", () => {
  initializeUsersPage()
  setupEventListeners()
})

function initializeUsersPage() {
  // Cargar usuarios desde la tabla
  loadUsersFromTable()

  // Configurar filtros
  setupFilters()

  // Configurar búsqueda
  setupSearch()
}

function setupEventListeners() {
  // Búsqueda en tiempo real
  const searchInput = document.getElementById("userSearch")
  if (searchInput) {
    searchInput.addEventListener("input", debounce(filterUsers, 300))
  }

  // Filtros
  const roleFilter = document.getElementById("roleFilter")
  const statusFilter = document.getElementById("statusFilter")

  if (roleFilter) {
    roleFilter.addEventListener("change", filterUsers)
  }

  if (statusFilter) {
    statusFilter.addEventListener("change", filterUsers)
  }
}

function loadUsersFromTable() {
  const table = document.getElementById("usersTable")
  if (!table) return

  const rows = table.querySelectorAll("tbody tr")
  currentUsers = Array.from(rows)
    .map((row) => {
      if (row.cells.length < 2) return null

      return {
        id: row.dataset.userId,
        name: row.cells[1].textContent.trim(),
        email: row.cells[2].textContent.trim(),
        role: row.cells[3].textContent.trim().toLowerCase(),
        status: row.querySelector('input[type="checkbox"]')?.checked ? "active" : "inactive",
        element: row,
      }
    })
    .filter((user) => user !== null)

  filteredUsers = [...currentUsers]
}

function filterUsers() {
  const searchTerm = document.getElementById("userSearch")?.value.toLowerCase() || ""
  const roleFilter = document.getElementById("roleFilter")?.value || ""
  const statusFilter = document.getElementById("statusFilter")?.value || ""

  filteredUsers = currentUsers.filter((user) => {
    const matchesSearch = user.name.toLowerCase().includes(searchTerm) || user.email.toLowerCase().includes(searchTerm)
    const matchesRole = !roleFilter || user.role === roleFilter
    const matchesStatus =
      !statusFilter ||
      (statusFilter === "1" && user.status === "active") ||
      (statusFilter === "0" && user.status === "inactive")

    return matchesSearch && matchesRole && matchesStatus
  })

  updateTableDisplay()
}

function updateTableDisplay() {
  currentUsers.forEach((user) => {
    const isVisible = filteredUsers.includes(user)
    user.element.style.display = isVisible ? "" : "none"
  })
}

function toggleUserStatus(userId, isActive) {
  const formData = new FormData()
  formData.append("action", "toggle_status")
  formData.append("user_id", userId)
  formData.append("status", isActive ? "1" : "0")

  fetch(window.location.href, {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showNotification("Estado del usuario actualizado correctamente", "success")
        // Actualizar el estado local
        const user = currentUsers.find((u) => u.id == userId)
        if (user) {
          user.status = isActive ? "active" : "inactive"
        }
      } else {
        showNotification("Error al actualizar el estado del usuario", "error")
        // Revertir el checkbox
        const checkbox = document.querySelector(`input[onchange*="${userId}"]`)
        if (checkbox) {
          checkbox.checked = !isActive
        }
      }
    })
    .catch((error) => {
      console.error("Error:", error)
      showNotification("Error de conexión", "error")
      // Revertir el checkbox
      const checkbox = document.querySelector(`input[onchange*="${userId}"]`)
      if (checkbox) {
        checkbox.checked = !isActive
      }
    })
}

function viewUser(userId) {
  // Encontrar el usuario
  const user = currentUsers.find((u) => u.id == userId)
  if (!user) return

  // Crear contenido del modal
  const modalBody = document.getElementById("userModalBody")
  if (!modalBody) return

  modalBody.innerHTML = `
        <div class="user-details">
            <div class="user-header">
                <div class="user-avatar-large">
                    ${user.name.charAt(0).toUpperCase()}
                </div>
                <div class="user-info">
                    <h3>${user.name}</h3>
                    <p>${user.email}</p>
                    <span class="role-badge role-${user.role}">${user.role}</span>
                </div>
            </div>
            <div class="user-stats">
                <div class="stat-item">
                    <div class="stat-label">Estado</div>
                    <div class="stat-value">${user.status === "active" ? "Activo" : "Inactivo"}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Cursos Comprados</div>
                    <div class="stat-value">0</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Total Gastado</div>
                    <div class="stat-value">$0.00</div>
                </div>
            </div>
        </div>
    `

  // Mostrar modal
  const modal = document.getElementById("userModal")
  if (modal) {
    modal.style.display = "block"
  }
}

function editUser(userId) {
  // Implementar edición de usuario
  showNotification("Función de edición en desarrollo", "info")
}

function deleteUser(userId) {
  if (!confirm("¿Estás seguro de que quieres desactivar este usuario?")) {
    return
  }

  const formData = new FormData()
  formData.append("action", "delete_user")
  formData.append("user_id", userId)

  fetch(window.location.href, {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showNotification("Usuario desactivado correctamente", "success")
        // Recargar la página después de un breve delay
        setTimeout(() => {
          window.location.reload()
        }, 1000)
      } else {
        showNotification("Error al desactivar el usuario", "error")
      }
    })
    .catch((error) => {
      console.error("Error:", error)
      showNotification("Error de conexión", "error")
    })
}

function exportUsers() {
  // Implementar exportación de usuarios
  showNotification("Función de exportación en desarrollo", "info")
}

function closeModal() {
  const modal = document.getElementById("userModal")
  if (modal) {
    modal.style.display = "none"
  }
}

// Cerrar modal al hacer clic fuera
window.onclick = (event) => {
  const modal = document.getElementById("userModal")
  if (event.target === modal) {
    modal.style.display = "none"
  }
}

// Utilidades
function debounce(func, wait) {
  let timeout
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout)
      func(...args)
    }
    clearTimeout(timeout)
    timeout = setTimeout(later, wait)
  }
}

function showNotification(message, type = "info") {
  // Crear elemento de notificación
  const notification = document.createElement("div")
  notification.className = `notification notification-${type}`
  notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${getNotificationIcon(type)}"></i>
            <span>${message}</span>
        </div>
        <button class="notification-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `

  // Agregar al DOM
  document.body.appendChild(notification)

  // Auto-remover después de 5 segundos
  setTimeout(() => {
    if (notification.parentElement) {
      notification.remove()
    }
  }, 5000)
}

function getNotificationIcon(type) {
  const icons = {
    success: "check-circle",
    error: "exclamation-circle",
    warning: "exclamation-triangle",
    info: "info-circle",
  }
  return icons[type] || "info-circle"
}

function setupFilters() {
  // Configuración adicional de filtros si es necesaria
  console.log("Filtros configurados")
}

function setupSearch() {
  // Configuración adicional de búsqueda si es necesaria
  console.log("Búsqueda configurada")
}
