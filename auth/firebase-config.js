// Import the Firebase SDK
import firebase from "firebase/app"
import "firebase/auth"
import "firebase/firestore"

// Firebase configuration - usando variables globales simples
const firebaseConfig = {
  apiKey: "AIzaSyAtCjRAp58m3IewqHWgvwLuxxdIb5026kg",
  authDomain: "e-commerce-elprofehernan.firebaseapp.com",
  databaseURL: "https://e-commerce-elprofehernan-default-rtdb.firebaseio.com",
  projectId: "e-commerce-elprofehernan",
  storageBucket: "e-commerce-elprofehernan.firebasestorage.app",
  messagingSenderId: "769275191194",
  appId: "1:769275191194:web:5546d2aed7bd9e60f56423",
  measurementId: "G-3RGDE75FEY",
}

// Inicializar Firebase
if (!firebase.apps.length) {
  firebase.initializeApp(firebaseConfig)
}

// Variables globales para usar en otros scripts
window.auth = firebase.auth()
window.googleProvider = new firebase.auth.GoogleAuthProvider()

console.log("Firebase inicializado correctamente")
