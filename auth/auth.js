// Variables globales (se inicializan en firebase-config.js)
let auth, googleProvider

// Esperar a que Firebase esté listo
document.addEventListener("DOMContentLoaded", () => {
  // Obtener las instancias globales
  auth = window.auth
  googleProvider = window.googleProvider

  if (!auth || !googleProvider) {
    console.error("Firebase no está inicializado correctamente")
    return
  }

  console.log("Auth.js cargado correctamente")

  // Configurar eventos
  setupAuthEvents()
  setupAuthStateListener()
})

// Función para mostrar/ocultar loading
function showLoading() {
  const loading = document.getElementById("loadingOverlay")
  if (loading) {
    loading.style.display = "flex"
  }
}

function hideLoading() {
  const loading = document.getElementById("loadingOverlay")
  if (loading) {
    loading.style.display = "none"
  }
}

// Función para mostrar mensajes
function showMessage(message, type = "success") {
  // Remover toasts existentes
  const existingToasts = document.querySelectorAll(".toast")
  existingToasts.forEach((toast) => toast.remove())

  const toast = document.createElement("div")
  toast.className = `toast ${type}`
  toast.textContent = message
  document.body.appendChild(toast)

  setTimeout(() => {
    if (toast.parentNode) {
      toast.parentNode.removeChild(toast)
    }
  }, 3000)
}

// Función para determinar si es admin
function isAdmin(user) {
  const adminEmails = ["admin@ecommerce.com", "admin@elprofehernan.com"]
  return user && adminEmails.includes(user.email)
}

// Configurar el listener del estado de autenticación
function setupAuthStateListener() {
  auth.onAuthStateChanged(async (user) => {
    console.log("Estado de auth cambió:", user ? `Usuario: ${user.email}` : "No logueado")

    if (user) {
      const currentPage = window.location.pathname

      // Si estamos en login o signup, redirigir
      if (currentPage.includes("login.html") || currentPage.includes("signup.html")) {
        try {
          const idToken = await user.getIdToken()
          console.log("Redirigiendo con token...")
          window.location.href = `auth_callback.php?idToken=${idToken}`
        } catch (error) {
          console.error("Error obteniendo token:", error)
          showMessage("Error de autenticación", "error")
        }
      } else {
        // Actualizar UI en otras páginas
        updateUI(user)
      }
    } else {
      // Usuario no logueado
      const currentPage = window.location.pathname
      if (currentPage.includes("courses.php")) {
        window.location.href = "../../login.html"
      }
      updateUI(null)
    }
  })
}

// Configurar eventos de los formularios
function setupAuthEvents() {
  // Login form
  const loginForm = document.getElementById("loginForm")
  if (loginForm) {
    console.log("Configurando formulario de login")

    loginForm.addEventListener("submit", async (e) => {
      e.preventDefault()
      const email = document.getElementById("email").value
      const password = document.getElementById("password").value

      console.log("Intentando login con email:", email)
      showLoading()

      try {
        await auth.signInWithEmailAndPassword(email, password)
        showMessage("Login exitoso", "success")
      } catch (error) {
        console.error("Error en login:", error)
        hideLoading()
        showMessage("Error: " + error.message, "error")
      }
    })
  }

  // Google login button
  const googleLoginBtn = document.getElementById("googleLogin")
  if (googleLoginBtn) {
    console.log("Configurando botón de Google login")

    googleLoginBtn.addEventListener("click", async (e) => {
      e.preventDefault()
      console.log("Click en Google login")
      showLoading()

      try {
        console.log("Intentando popup de Google...")
        const result = await auth.signInWithPopup(googleProvider)
        console.log("Login con Google exitoso:", result.user.email)
        showMessage("Login con Google exitoso", "success")
      } catch (error) {
        console.error("Error en Google login:", error)
        hideLoading()

        if (error.code === "auth/popup-closed-by-user") {
          console.log("Usuario cerró el popup")
          return
        }

        showMessage("Error con Google: " + error.message, "error")
      }
    })
  } else {
    console.log("Botón de Google no encontrado")
  }

  // Signup form
  const signupForm = document.getElementById("signupForm")
  if (signupForm) {
    console.log("Configurando formulario de signup")

    signupForm.addEventListener("submit", async (e) => {
      e.preventDefault()

      const firstName = document.getElementById("firstName").value
      const lastName = document.getElementById("lastName").value
      const email = document.getElementById("email").value
      const password = document.getElementById("password").value
      const confirmPassword = document.getElementById("confirmPassword").value

      if (password !== confirmPassword) {
        showMessage("Las contraseñas no coinciden", "error")
        return
      }

      console.log("Intentando signup con email:", email)
      showLoading()

      try {
        const userCredential = await auth.createUserWithEmailAndPassword(email, password)
        await userCredential.user.updateProfile({
          displayName: `${firstName} ${lastName}`,
        })
        showMessage("Registro exitoso", "success")
      } catch (error) {
        console.error("Error en signup:", error)
        hideLoading()
        showMessage("Error: " + error.message, "error")
      }
    })
  }

  // Google signup button
  const googleSignupBtn = document.getElementById("googleSignup")
  if (googleSignupBtn) {
    console.log("Configurando botón de Google signup")

    googleSignupBtn.addEventListener("click", async (e) => {
      e.preventDefault()
      console.log("Click en Google signup")
      showLoading()

      try {
        const result = await auth.signInWithPopup(googleProvider)
        console.log("Signup con Google exitoso:", result.user.email)
        showMessage("Registro con Google exitoso", "success")
      } catch (error) {
        console.error("Error en Google signup:", error)
        hideLoading()

        if (error.code === "auth/popup-closed-by-user") {
          console.log("Usuario cerró el popup")
          return
        }

        showMessage("Error con Google: " + error.message, "error")
      }
    })
  }

  // Logout button
  const logoutBtn = document.getElementById("logoutBtn")
  if (logoutBtn) {
    console.log("Configurando botón de logout")

    logoutBtn.addEventListener("click", async (e) => {
      e.preventDefault()
      console.log("Click en logout")
      showLoading()

      try {
        await auth.signOut()
        window.location.href = "logout.php"
      } catch (error) {
        console.error("Error en logout:", error)
        hideLoading()
        showMessage("Error al cerrar sesión", "error")
      }
    })
  }
}

// Función para actualizar UI (puede ser sobrescrita desde otras páginas)
function updateUI(user) {
  console.log("Actualizando UI para:", user ? user.email : "usuario no logueado")

  // Esta función puede ser personalizada en cada página
  if (typeof window.updateUI === "function" && window.updateUI !== updateUI) {
    window.updateUI(user)
  }
}
