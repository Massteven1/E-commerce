// Import Firebase modules
import firebase from "firebase/app"
import "firebase/auth"
import "firebase/database"
import "firebase/storage"
import "firebase/analytics"

// Configuración de Firebase
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
firebase.initializeApp(firebaseConfig)

// Configurar persistencia de autenticación
firebase.auth().setPersistence(firebase.auth.Auth.Persistence.LOCAL)

console.log("Firebase inicializado correctamente")
