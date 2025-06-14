// Import Firebase (adjust the path if necessary)
import { initializeApp } from "https://www.gstatic.com/firebasejs/9.2.0/firebase-app.js"
import {
  getAuth,
  GoogleAuthProvider,
  FacebookAuthProvider,
  signInWithEmailAndPassword,
  createUserWithEmailAndPassword,
  updateProfile,
  sendEmailVerification,
  signInWithPopup,
  signOut,
  sendPasswordResetEmail,
  setPersistence,
  browserSessionPersistence,
  browserLocalPersistence,
  onAuthStateChanged,
} from "https://www.gstatic.com/firebasejs/9.2.0/firebase-auth.js"

// Firebase configuration (replace with your actual configuration)
const firebaseConfig = {
  apiKey: "YOUR_API_KEY",
  authDomain: "YOUR_AUTH_DOMAIN",
  projectId: "YOUR_PROJECT_ID",
  storageBucket: "YOUR_STORAGE_BUCKET",
  messagingSenderId: "YOUR_MESSAGING_SENDER_ID",
  appId: "YOUR_APP_ID",
}

// Initialize Firebase
const app = initializeApp(firebaseConfig)

// Obtener instancia de autenticación
const auth = getAuth(app)

// Proveedores
const googleProvider = new GoogleAuthProvider()
const facebookProvider = new FacebookAuthProvider()

// Elementos del DOM
const loadingOverlay = document.getElementById("loadingOverlay")

// Verificar si el usuario ya está logueado
document.addEventListener("DOMContentLoaded", () => {
  onAuthStateChanged(auth, async (user) => {
    if (user) {
      // Si el usuario está logueado, obtener el ID token y enviarlo al backend PHP
      try {
        const idToken = await user.getIdToken()
        const currentPage = window.location.pathname

        // Redirigir a auth_callback.php para que el backend establezca la sesión PHP
        if (currentPage.includes("login.html") || currentPage.includes("signup.html")) {
          window.location.href = `auth_callback.php?idToken=${idToken}`
        }
      } catch (error) {
        console.error("Error al obtener el ID token:", error)
        showToast("Error de autenticación. Por favor, intenta de nuevo.", "error")
        // Opcional: forzar logout si hay un error grave con el token
        signOut(auth)
      }
    } else {
      // Si el usuario no está logueado, y está en una página protegida, redirigir a login
      const currentPage = window.location.pathname
      if (currentPage.includes("courses.php")) {
        window.location.href = "login.html"
      }
    }
  })
})

// Mostrar/ocultar contraseña
document.querySelectorAll(".toggle-password").forEach((icon) => {
  icon.addEventListener("click", () => {
    const input = icon.previousElementSibling
    if (input.type === "password") {
      input.type = "text"
      icon.classList.remove("fa-eye")
      icon.classList.add("fa-eye-slash")
    } else {
      input.type = "password"
      icon.classList.remove("fa-eye-slash")
      icon.classList.add("fa-eye")
    }
  })
})

// Función para notificaciones toast
function showToast(message, type = "success") {
  const existingToasts = document.querySelectorAll(".toast")
  existingToasts.forEach((toast) => toast.remove())

  const toast = document.createElement("div")
  toast.className = `toast ${type}`
  toast.textContent = message
  document.body.appendChild(toast)

  setTimeout(() => {
    toast.remove()
  }, 3000)
}

// Mostrar overlay de carga
function showLoading() {
  if (loadingOverlay) {
    loadingOverlay.classList.add("show")
  }
}

// Ocultar overlay de carga
function hideLoading() {
  if (loadingOverlay) {
    loadingOverlay.classList.remove("show")
  }
}

// Manejar errores de autenticación
function handleAuthError(error) {
  hideLoading()
  let errorMessage = "Ocurrió un error. Por favor, intenta de nuevo."

  switch (error.code) {
    case "auth/email-already-in-use":
      errorMessage = "Este correo ya está registrado. Usa otro o inicia sesión."
      break
    case "auth/invalid-email":
      errorMessage = "Por favor, ingresa un correo válido."
      break
    case "auth/weak-password":
      errorMessage = "La contraseña debe tener al menos 6 caracteres."
      break
    case "auth/user-not-found":
    case "auth/wrong-password":
      errorMessage = "Correo o contraseña inválidos. Intenta de nuevo."
      break
    case "auth/too-many-requests":
      errorMessage = "Demasiados intentos fallidos. Intenta de nuevo más tarde."
      break
    case "auth/account-exists-with-different-credential":
      errorMessage = "Ya existe una cuenta con este correo pero con otras credenciales."
      break
    case "auth/popup-closed-by-user":
      return // No mostrar error si el usuario cierra el popup
    default:
      console.error("Error de autenticación:", error)
  }

  showToast(errorMessage, "error")
}

// Funcionalidad de Login
if (document.getElementById("loginForm")) {
  const loginForm = document.getElementById("loginForm")
  const emailInput = document.getElementById("email")
  const passwordInput = document.getElementById("password")
  const emailError = document.getElementById("emailError")
  const passwordError = document.getElementById("passwordError")
  const rememberMe = document.getElementById("rememberMe")
  const googleLoginBtn = document.getElementById("googleLogin")
  const facebookLoginBtn = document.getElementById("facebookLogin")
  const forgotPasswordLink = document.getElementById("forgotPassword")
  const passwordResetModal = document.getElementById("passwordResetModal")
  const resetPasswordForm = document.getElementById("resetPasswordForm")
  const resetEmailInput = document.getElementById("resetEmail")
  const resetEmailError = document.getElementById("resetEmailError")
  const closeModalBtn = document.querySelector(".close-modal")

  // Validación de correo
  function validateEmail(email) {
    const re =
      /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
    return re.test(String(email).toLowerCase())
  }

  // Enviar formulario de login
  loginForm.addEventListener("submit", async (e) => {
    e.preventDefault()

    emailError.textContent = ""
    passwordError.textContent = ""

    const email = emailInput.value.trim()
    const password = passwordInput.value
    let isValid = true

    if (!email) {
      emailError.textContent = "El correo es requerido"
      isValid = false
    } else if (!validateEmail(email)) {
      emailError.textContent = "Por favor, ingresa un correo válido"
      isValid = false
    }

    if (!password) {
      passwordError.textContent = "La contraseña es requerida"
      isValid = false
    }

    if (isValid) {
      showLoading()
      try {
        const persistence = rememberMe.checked ? browserLocalPersistence : browserSessionPersistence
        await setPersistence(auth, persistence)
        const userCredential = await signInWithEmailAndPassword(auth, email, password)
        const idToken = await userCredential.user.getIdToken()
        window.location.href = `auth_callback.php?idToken=${idToken}`
      } catch (error) {
        handleAuthError(error)
      }
    }
  })

  // Login con Google
  googleLoginBtn.addEventListener("click", async () => {
    showLoading()
    try {
      const persistence = rememberMe.checked ? browserLocalPersistence : browserSessionPersistence
      await setPersistence(auth, persistence)
      const userCredential = await signInWithPopup(auth, googleProvider)
      const idToken = await userCredential.user.getIdToken()
      window.location.href = `auth_callback.php?idToken=${idToken}`
    } catch (error) {
      handleAuthError(error)
    }
  })

  // Login con Facebook
  facebookLoginBtn.addEventListener("click", async () => {
    showLoading()
    try {
      const persistence = rememberMe.checked ? browserLocalPersistence : browserSessionPersistence
      await setPersistence(auth, persistence)
      const userCredential = await signInWithPopup(auth, facebookProvider)
      const idToken = await userCredential.user.getIdToken()
      window.location.href = `auth_callback.php?idToken=${idToken}`
    } catch (error) {
      handleAuthError(error)
    }
  })

  // Modal de restablecimiento de contraseña
  forgotPasswordLink.addEventListener("click", (e) => {
    e.preventDefault()
    passwordResetModal.classList.add("show")
  })

  closeModalBtn.addEventListener("click", () => {
    passwordResetModal.classList.remove("show")
  })

  passwordResetModal.addEventListener("click", (e) => {
    if (e.target === passwordResetModal) {
      passwordResetModal.classList.remove("show")
    }
  })

  resetPasswordForm.addEventListener("submit", async (e) => {
    e.preventDefault()

    resetEmailError.textContent = ""
    const email = resetEmailInput.value.trim()

    if (!email) {
      resetEmailError.textContent = "El correo es requerido"
      return
    } else if (!validateEmail(email)) {
      resetEmailError.textContent = "Por favor, ingresa un correo válido"
      return
    }

    showLoading()
    try {
      await sendPasswordResetEmail(auth, email)
      hideLoading()
      passwordResetModal.classList.remove("show")
      showToast("Correo de restablecimiento enviado. Revisa tu bandeja.", "success")
    } catch (error) {
      handleAuthError(error)
    }
  })
}

// Funcionalidad de Signup
if (document.getElementById("signupForm")) {
  const signupForm = document.getElementById("signupForm")
  const firstNameInput = document.getElementById("firstName")
  const lastNameInput = document.getElementById("lastName")
  const emailInput = document.getElementById("email")
  const passwordInput = document.getElementById("password")
  const confirmPasswordInput = document.getElementById("confirmPassword")
  const termsAgree = document.getElementById("termsAgree")
  const firstNameError = document.getElementById("firstNameError")
  const lastNameError = document.getElementById("lastNameError")
  const emailError = document.getElementById("emailError")
  const passwordError = document.getElementById("passwordError")
  const confirmPasswordError = document.getElementById("confirmPasswordError")
  const termsAgreeError = document.getElementById("termsAgreeError")
  const passwordStrength = document.getElementById("passwordStrength")
  const strengthSegments = passwordStrength.querySelectorAll(".strength-segment")
  const strengthText = passwordStrength.querySelector(".strength-text")
  const googleSignupBtn = document.getElementById("googleSignup")
  const facebookSignupBtn = document.getElementById("facebookSignup")

  // Validación de correo
  function validateEmail(email) {
    const re =
      /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
    return re.test(String(email).toLowerCase())
  }

  // Verificador de fuerza de contraseña
  function checkPasswordStrength(password) {
    let strength = 0

    if (password.length >= 8) strength += 1
    if (/[a-z]/.test(password)) strength += 1
    if (/[A-Z]/.test(password)) strength += 1
    if (/[0-9]/.test(password)) strength += 1
    if (/[^a-zA-Z0-9]/.test(password)) strength += 1

    strengthSegments.forEach((segment, index) => {
      segment.className = "strength-segment"
      if (index < strength) {
        if (strength <= 2) {
          segment.classList.add("weak")
        } else if (strength <= 3) {
          segment.classList.add("medium")
        } else {
          segment.classList.add("strong")
        }
      }
    })

    if (password.length === 0) {
      strengthText.textContent = "Fuerza de la contraseña"
    } else if (strength <= 2) {
      strengthText.textContent = "Contraseña débil"
    } else if (strength <= 3) {
      strengthText.textContent = "Contraseña media"
    } else {
      strengthText.textContent = "Contraseña fuerte"
    }

    return strength
  }

  passwordInput.addEventListener("input", () => {
    checkPasswordStrength(passwordInput.value)
  })

  // Enviar formulario de signup
  signupForm.addEventListener("submit", async (e) => {
    e.preventDefault()

    firstNameError.textContent = ""
    lastNameError.textContent = ""
    emailError.textContent = ""
    passwordError.textContent = ""
    confirmPasswordError.textContent = ""
    termsAgreeError.textContent = ""

    const firstName = firstNameInput.value.trim()
    const lastName = lastNameInput.value.trim()
    const email = emailInput.value.trim()
    const password = passwordInput.value
    const confirmPassword = confirmPasswordInput.value
    let isValid = true

    if (!firstName) {
      firstNameError.textContent = "El nombre es requerido"
      isValid = false
    }

    if (!lastName) {
      lastNameError.textContent = "El apellido es requerido"
      isValid = false
    }

    if (!email) {
      emailError.textContent = "El correo es requerido"
      isValid = false
    } else if (!validateEmail(email)) {
      emailError.textContent = "Por favor, ingresa un correo válido"
      isValid = false
    }

    if (!password) {
      passwordError.textContent = "La contraseña es requerida"
      isValid = false
    } else if (password.length < 6) {
      passwordError.textContent = "La contraseña debe tener al menos 6 caracteres"
      isValid = false
    } else if (checkPasswordStrength(password) <= 2) {
      passwordError.textContent = "Por favor, elige una contraseña más fuerte"
      isValid = false
    }

    if (!confirmPassword) {
      confirmPasswordError.textContent = "Confirma tu contraseña"
      isValid = false
    } else if (password !== confirmPassword) {
      confirmPasswordError.textContent = "Las contraseñas no coinciden"
      isValid = false
    }

    if (!termsAgree.checked) {
      termsAgreeError.textContent = "Debes aceptar los términos"
      isValid = false
    }

    if (isValid) {
      showLoading()
      try {
        const userCredential = await createUserWithEmailAndPassword(auth, email, password)
        const user = userCredential.user

        await updateProfile(user, {
          displayName: `${firstName} ${lastName}`,
        })

        await sendEmailVerification(user)
        const idToken = await user.getIdToken()
        window.location.href = `auth_callback.php?idToken=${idToken}`
      } catch (error) {
        handleAuthError(error)
      }
    }
  })

  // Signup con Google
  googleSignupBtn.addEventListener("click", async () => {
    showLoading()
    try {
      const userCredential = await signInWithPopup(auth, googleProvider)
      const idToken = await userCredential.user.getIdToken()
      window.location.href = `auth_callback.php?idToken=${idToken}`
    } catch (error) {
      handleAuthError(error)
    }
  })

  // Signup con Facebook
  facebookSignupBtn.addEventListener("click", async () => {
    showLoading()
    try {
      const userCredential = await signInWithPopup(auth, facebookProvider)
      const idToken = await userCredential.user.getIdToken()
      window.location.href = `auth_callback.php?idToken=${idToken}`
    } catch (error) {
      handleAuthError(error)
    }
  })
}

// Funcionalidad de Logout
const logoutBtn = document.getElementById("logoutBtn")
if (logoutBtn) {
  logoutBtn.addEventListener("click", async (e) => {
    e.preventDefault()
    showLoading()
    try {
      await signOut(auth)
      // Redirigir a un script PHP para limpiar la sesión del servidor
      window.location.href = "logout.php"
    } catch (error) {
      console.error("Error al cerrar sesión:", error)
      showToast("Error al cerrar sesión. Por favor, intenta de nuevo.", "error")
      hideLoading()
    }
  })
}
