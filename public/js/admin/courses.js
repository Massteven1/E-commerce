// Gestión de cursos en el panel de administración

let currentCourses = []
let filteredCourses = []

document.addEventListener("DOMContentLoaded", () => {
  initializeCoursesPage()
  setupEventListeners()
})

function initializeCoursesPage() {
  loadCoursesFromGrid()
  setupFilters()
}

function setupEventListeners() {
  const searchInput = document.getElementById("courseSearch")
  if (searchInput) {
    searchInput.addEventListener("input", debounce(filterCourses, 300))
  }

  const levelFilter = document.getElementById("levelFilter")
  const priceFilter = document.getElementById("priceFilter")

  if (levelFilter) {
    levelFilter.addEventListener("change", filterCourses)
  }

  if (priceFilter) {
    priceFilter.addEventListener("change", filterCourses)
  }
}

function loadCoursesFromGrid() {
  const courseCards = document.querySelectorAll(".course-card")
  currentCourses = Array.from(courseCards).map((card) => {
    const title = card.querySelector("h3")?.textContent || ""
    const level = card.querySelector(".course-level")?.textContent || ""
    const priceElement = card.querySelector(".course-price")
    const isFree = priceElement?.querySelector(".free-badge") !== null

    return {
      id: card.dataset.courseId,
      title: title,
      level: level,
      isFree: isFree,
      element: card,
    }
  })

  filteredCourses = [...currentCourses]
}

function filterCourses() {
  const searchTerm = document.getElementById("courseSearch")?.value.toLowerCase() || ""
  const levelFilter = document.getElementById("levelFilter")?.value || ""
  const priceFilter = document.getElementById("priceFilter")?.value || ""

  filteredCourses = currentCourses.filter((course) => {
    const matchesSearch = course.title.toLowerCase().includes(searchTerm)
    const matchesLevel = !levelFilter || course.level === levelFilter
    const matchesPrice =
      !priceFilter || (priceFilter === "free" && course.isFree) || (priceFilter === "paid" && !course.isFree)

    return matchesSearch && matchesLevel && matchesPrice
  })

  updateCoursesDisplay()
}

function updateCoursesDisplay() {
  currentCourses.forEach((course) => {
    const isVisible = filteredCourses.includes(course)
    course.element.style.display = isVisible ? "" : "none"
  })
}

function editCourse(courseId) {
  window.location.href = `index.php?action=edit_course&id=${courseId}`
}

function deleteCourse(courseId) {
  if (!confirm("¿Estás seguro de que quieres eliminar este curso?")) {
    return
  }

  const formData = new FormData()
  formData.append("action", "delete_course")
  formData.append("course_id", courseId)

  fetch(window.location.href, {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showNotification("Curso eliminado correctamente", "success")
        // Remover el elemento del DOM
        const courseCard = document.querySelector(`[data-course-id="${courseId}"]`)
        if (courseCard) {
          courseCard.remove()
        }
        // Actualizar arrays
        currentCourses = currentCourses.filter((c) => c.id != courseId)
        filteredCourses = filteredCourses.filter((c) => c.id != courseId)
      } else {
        showNotification("Error al eliminar el curso", "error")
      }
    })
    .catch((error) => {
      console.error("Error:", error)
      showNotification("Error de conexión", "error")
    })
}

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

  document.body.appendChild(notification)

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
  // Placeholder for setupFilters function
  // This function should be implemented to initialize filters if needed
}
