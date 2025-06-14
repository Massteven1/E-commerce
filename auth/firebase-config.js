// Import the Firebase SDK
import firebase from "firebase/app"
import "firebase/auth"
import "firebase/firestore"

// Firebase configuration
const firebaseConfig = {
  apiKey: "AIzaSyAtCjRAp58m3IewqHWgvwLuxxdIb5026kg",
  authDomain: "e-commerce-elprofehernan.firebaseapp.com",
  databaseURL: "https://e-commerce-elprofehernan-default-rtdb.firebaseio.com",
  projectId: "e-commerce-elprofehernan",
  storageBucket: "e-commerce-elprofehernan.firebasestorage.app",
  messagingSenderId: "769275191194",
  appId: "1:769275191194:web:cb88de78f4ed9da5f56423",
  measurementId: "G-F2Q4QC6BKW",
}

// Initialize Firebase
if (!firebase.apps.length) {
  firebase.initializeApp(firebaseConfig)
}

export default firebase
