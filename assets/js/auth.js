import { firebaseConfig } from "./firebase-config.js"
import { initializeApp } from "https://www.gstatic.com/firebasejs/9.15.0/firebase-app.js"
import {
  getAuth,
  onAuthStateChanged,
  signInWithEmailAndPassword,
  createUserWithEmailAndPassword,
  signInWithPopup,
  GoogleAuthProvider,
  signOut,
  updateProfile,
} from "https://www.gstatic.com/firebasejs/9.15.0/firebase-auth.js"

// Initialize Firebase
const app = initializeApp(firebaseConfig)
const auth = getAuth(app)
const googleProvider = new GoogleAuthProvider()

// Global auth state
let currentUser = null

// Initialize authentication
document.addEventListener("DOMContentLoaded", () => {
  initializeAuth()
  setupAuthForms()
  setupLogoutButton()
})

/**
 * Initialize authentication state listener
 */
function initializeAuth() {
  onAuthStateChanged(auth, async (user) => {
    currentUser = user

    if (user) {
      await syncUserWithBackend(user)
      updateUIForLoggedInUser(user)
      updateCartCount()
    } else {
      updateUIForLoggedOutUser()
    }
  })
}

/**
 * Setup authentication forms
 */
function setupAuthForms() {
  // Login form
  const loginForm = document.getElementById("loginForm")
  if (loginForm) {
    loginForm.addEventListener("submit", handleLogin)
  }

  // Signup form
  const signupForm = document.getElementById("signupForm")
  if (signupForm) {
    signupForm.addEventListener("submit", handleSignup)
  }

  // Google auth buttons
  const googleLogin = document.getElementById("googleLogin")
  const googleSignup = document.getElementById("googleSignup")

  if (googleLogin) {
    googleLogin.addEventListener("click", handleGoogleAuth)
  }

  if (googleSignup) {
    googleSignup.addEventListener("click", handleGoogleAuth)
  }

  // Password toggle
  setupPasswordToggle()
}

/**
 * Handle email/password login
 */
async function handleLogin(e) {
  e.preventDefault()

  const email = document.getElementById("email").value
  const password = document.getElementById("password").value

  try {
    showLoading()
    await signInWithEmailAndPassword(auth, email, password)
    showToast("Sesión iniciada correctamente", "success")
    setTimeout(() => (window.location.href = "/"), 1000)
  } catch (error) {
    console.error("Login error:", error)
    showToast(getAuthErrorMessage(error.code), "error")
  } finally {
    hideLoading()
  }
}

/**
 * Handle email/password signup
 */
async function handleSignup(e) {
  e.preventDefault()

  const firstName = document.getElementById("firstName").value
  const lastName = document.getElementById("lastName").value
  const email = document.getElementById("email").value
  const password = document.getElementById("password").value
  const confirmPassword = document.getElementById("confirmPassword").value
  const termsAgree = document.getElementById("termsAgree").checked

  // Validation
  if (password !== confirmPassword) {
    showToast("Las contraseñas no coinciden", "error")
    return
  }

  if (!termsAgree) {
    showToast("Debes aceptar los términos y condiciones", "error")
    return
  }

  try {
    showLoading()
    const userCredential = await createUserWithEmailAndPassword(auth, email, password)

    // Update profile with name
    await updateProfile(userCredential.user, {
      displayName: `${firstName} ${lastName}`,
    })

    showToast("Cuenta creada correctamente", "success")
    setTimeout(() => (window.location.href = "/"), 1000)
  } catch (error) {
    console.error("Signup error:", error)
    showToast(getAuthErrorMessage(error.code), "error")
  } finally {
    hideLoading()
  }
}

/**
 * Handle Google authentication
 */
async function handleGoogleAuth() {
  try {
    showLoading()
    await signInWithPopup(auth, googleProvider)
    showToast("Sesión iniciada con Google", "success")
    setTimeout(() => (window.location.href = "/"), 1000)
  } catch (error) {
    console.error("Google auth error:", error)
    showToast("Error al iniciar sesión con Google", "error")
  } finally {
    hideLoading()
  }
}

/**
 * Setup logout button
 */
function setupLogoutButton() {
  const logoutBtn = document.getElementById("logoutBtn")
  const adminLogout = document.getElementById("adminLogout")

  if (logoutBtn) {
    logoutBtn.addEventListener("click", handleLogout)
  }

  if (adminLogout) {
    adminLogout.addEventListener("click", handleLogout)
  }
}

/**
 * Handle logout
 */
async function handleLogout() {
  try {
    await signOut(auth)
    await fetch("/api/auth.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ action: "logout" }),
    })
    window.location.href = "/"
  } catch (error) {
    console.error("Logout error:", error)
    showToast("Error al cerrar sesión", "error")
  }
}

/**
 * Sync Firebase user with backend
 */
async function syncUserWithBackend(user) {
  try {
    const response = await fetch("/api/auth.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        action: "login",
        firebase_uid: user.uid,
        email: user.email,
        name: user.displayName || user.email.split("@")[0],
      }),
    })

    if (!response.ok) {
      console.error("Failed to sync user with backend")
    }
  } catch (error) {
    console.error("Error syncing user:", error)
  }
}

/**
 * Update UI for logged in user
 */
function updateUIForLoggedInUser(user) {
  const authLinks = document.querySelectorAll('nav ul li a[href="login.php"]')
  authLinks.forEach((link) => {
    link.textContent = user.displayName || "Mi Cuenta"
    link.href = "profile.php"
  })

  const logoutBtn = document.getElementById("logoutBtn")
  if (logoutBtn) {
    logoutBtn.style.display = "flex"
  }
}

/**
 * Update UI for logged out user
 */
function updateUIForLoggedOutUser() {
  const authLinks = document.querySelectorAll('nav ul li a[href="profile.php"]')
  authLinks.forEach((link) => {
    link.textContent = "Iniciar Sesión"
    link.href = "login.php"
  })

  const logoutBtn = document.getElementById("logoutBtn")
  if (logoutBtn) {
    logoutBtn.style.display = "none"
  }

  const cartBadge = document.getElementById("cartBadge")
  if (cartBadge) {
    cartBadge.textContent = "0"
  }
}

/**
 * Setup password toggle functionality
 */
function setupPasswordToggle() {
  const toggleButtons = document.querySelectorAll(".toggle-password")

  toggleButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const input = this.parentElement.querySelector("input")
      const type = input.getAttribute("type") === "password" ? "text" : "password"
      input.setAttribute("type", type)

      this.classList.toggle("fa-eye")
      this.classList.toggle("fa-eye-slash")
    })
  })
}

/**
 * Update cart count
 */
async function updateCartCount() {
  if (!currentUser) return

  try {
    const response = await fetch("/api/cart.php?action=count")
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

/**
 * Get authentication error message
 */
function getAuthErrorMessage(errorCode) {
  const errorMessages = {
    "auth/user-not-found": "No existe una cuenta con este email",
    "auth/wrong-password": "Contraseña incorrecta",
    "auth/email-already-in-use": "Ya existe una cuenta con este email",
    "auth/weak-password": "La contraseña debe tener al menos 6 caracteres",
    "auth/invalid-email": "Email inválido",
    "auth/too-many-requests": "Demasiados intentos. Intenta más tarde",
  }

  return errorMessages[errorCode] || "Error de autenticación"
}

/**
 * Utility functions
 */
function showLoading() {
  const overlay = document.getElementById("loadingOverlay")
  if (overlay) overlay.classList.add("show")
}

function hideLoading() {
  const overlay = document.getElementById("loadingOverlay")
  if (overlay) overlay.classList.remove("show")
}

function showToast(message, type = "info") {
  const toast = document.createElement("div")
  toast.className = `toast ${type}`
  toast.textContent = message

  const colors = {
    success: "#2ecc71",
    error: "#e74c3c",
    warning: "#f39c12",
    info: "#56c6e2",
  }

  toast.style.cssText = `
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 15px 20px;
    border-radius: 5px;
    color: white;
    font-weight: 500;
    background-color: ${colors[type] || colors.info};
    z-index: 1500;
    animation: slideIn 0.3s ease-out;
  `

  document.body.appendChild(toast)

  setTimeout(() => toast.remove(), 3000)
}

// Export for other modules
export { currentUser, auth }
