import { firebaseConfig } from "./firebase-config.js"
import { initializeApp } from "https://www.gstatic.com/firebasejs/9.15.0/firebase-app.js"
import { getAuth, onAuthStateChanged, signOut } from "https://www.gstatic.com/firebasejs/9.15.0/firebase-auth.js"

// Initialize Firebase
const app = initializeApp(firebaseConfig)
const auth = getAuth(app)

// Global auth state
let currentUser = null

// Initialize authentication
document.addEventListener("DOMContentLoaded", () => {
  initializeAuth()
  setupLogoutButton()
})

/**
 * Initialize authentication state listener
 */
function initializeAuth() {
  onAuthStateChanged(auth, async (user) => {
    currentUser = user

    if (user) {
      // User is signed in
      await syncUserWithBackend(user)
      updateUIForLoggedInUser(user)
      updateCartCount()
    } else {
      // User is signed out
      updateUIForLoggedOutUser()
    }
  })
}

/**
 * Sync Firebase user with backend
 */
async function syncUserWithBackend(user) {
  try {
    const idToken = await user.getIdToken()

    const response = await fetch("/api/auth/sync", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${idToken}`,
      },
      body: JSON.stringify({
        uid: user.uid,
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
  const authLink = document.getElementById("authLink")
  const logoutBtn = document.getElementById("logoutBtn")

  if (authLink) {
    authLink.innerHTML = `<a href="/profile">${user.displayName || "Mi Cuenta"}</a>`
  }

  if (logoutBtn) {
    logoutBtn.style.display = "flex"
  }
}

/**
 * Update UI for logged out user
 */
function updateUIForLoggedOutUser() {
  const authLink = document.getElementById("authLink")
  const logoutBtn = document.getElementById("logoutBtn")
  const cartBadge = document.getElementById("cartBadge")

  if (authLink) {
    authLink.innerHTML = '<a href="/login">Iniciar Sesión</a>'
  }

  if (logoutBtn) {
    logoutBtn.style.display = "none"
  }

  if (cartBadge) {
    cartBadge.textContent = "0"
  }
}

/**
 * Setup logout button
 */
function setupLogoutButton() {
  const logoutBtn = document.getElementById("logoutBtn")

  if (logoutBtn) {
    logoutBtn.addEventListener("click", async () => {
      try {
        await signOut(auth)
        window.location.href = "/"
      } catch (error) {
        console.error("Error signing out:", error)
        showToast("Error al cerrar sesión", "error")
      }
    })
  }
}

/**
 * Update cart count
 */
async function updateCartCount() {
  if (!currentUser) return

  try {
    const idToken = await currentUser.getIdToken()
    const response = await fetch("/api/cart/count", {
      headers: {
        Authorization: `Bearer ${idToken}`,
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

/**
 * Get current user
 */
export function getCurrentUser() {
  return currentUser
}

/**
 * Get user ID token
 */
export async function getUserToken() {
  if (!currentUser) return null
  return await currentUser.getIdToken()
}

/**
 * Check if user is authenticated
 */
export function isAuthenticated() {
  return currentUser !== null
}

/**
 * Show toast notification
 */
export function showToast(message, type = "info") {
  const toastContainer = document.getElementById("toastContainer")
  if (!toastContainer) return

  const toast = document.createElement("div")
  toast.className = `toast ${type}`
  toast.textContent = message

  toastContainer.appendChild(toast)

  // Remove toast after 3 seconds
  setTimeout(() => {
    toast.remove()
  }, 3000)
}

// Export auth instance for other modules
export { auth }
