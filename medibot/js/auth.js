document.addEventListener("DOMContentLoaded", () => {
  // Verificar si el usuario ya está autenticado
  checkSession()

  // Tab switching functionality
  const tabBtns = document.querySelectorAll(".tab-btn")
  const tabPanes = document.querySelectorAll(".tab-pane")

  tabBtns.forEach((btn) => {
    btn.addEventListener("click", function () {
      const tabId = this.getAttribute("data-tab")

      // Remove active class from all buttons and panes
      tabBtns.forEach((btn) => btn.classList.remove("active"))
      tabPanes.forEach((pane) => pane.classList.remove("active"))

      // Add active class to current button and pane
      this.classList.add("active")
      document.getElementById(tabId).classList.add("active")
    })
  })

  // Login form submission
  const loginForm = document.getElementById("login-form")
  const loginBtn = document.getElementById("login-btn")
  const loginError = document.getElementById("login-error")
  const loginErrorMessage = document.getElementById("login-error-message")

  loginForm.addEventListener("submit", async (e) => {
    e.preventDefault()

    // Reset error message
    loginError.style.display = "none"

    // Get form values
    const email = document.getElementById("email").value
    const password = document.getElementById("password").value
    const remember = document.getElementById("remember").checked

    // Basic validation
    if (!email || !password) {
      loginErrorMessage.textContent = "Por favor complete todos los campos"
      loginError.style.display = "flex"
      return
    }

    // Show loading state
    loginBtn.textContent = "Iniciando sesión..."
    loginBtn.disabled = true

    try {
      // Send login request to API
      const response = await fetch("api/login.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ email, password, remember }),
      })

      const data = await response.json()

      if (data.success) {
        // Redirect based on user role
        window.location.href = data.redirect
      } else {
        // Show error message
        loginErrorMessage.textContent = data.message
        loginError.style.display = "flex"
        loginBtn.textContent = "Iniciar Sesión"
        loginBtn.disabled = false
      }
    } catch (error) {
      console.error("Error:", error)
      loginErrorMessage.textContent = "Error de conexión. Inténtelo nuevamente."
      loginError.style.display = "flex"
      loginBtn.textContent = "Iniciar Sesión"
      loginBtn.disabled = false
    }
  })

  // Register form submission
  const registerForm = document.getElementById("register-form")
  const registerBtn = document.getElementById("register-btn")
  const registerError = document.getElementById("register-error")
  const registerErrorMessage = document.getElementById("register-error-message")

  registerForm.addEventListener("submit", async (e) => {
    e.preventDefault()

    // Reset error message
    registerError.style.display = "none"

    // Get form values
    const name = document.getElementById("name").value
    const email = document.getElementById("register-email").value
    const password = document.getElementById("register-password").value
    const confirmPassword = document.getElementById("confirm-password").value

    // Basic validation
    if (!name || !email || !password || !confirmPassword) {
      registerErrorMessage.textContent = "Por favor complete todos los campos"
      registerError.style.display = "flex"
      return
    }

    if (password !== confirmPassword) {
      registerErrorMessage.textContent = "Las contraseñas no coinciden"
      registerError.style.display = "flex"
      return
    }

    if (password.length < 6) {
      registerErrorMessage.textContent = "La contraseña debe tener al menos 6 caracteres"
      registerError.style.display = "flex"
      return
    }

    // Show loading state
    registerBtn.textContent = "Registrando..."
    registerBtn.disabled = true

    try {
      // Send register request to API
      const response = await fetch("api/register.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ name, email, password, confirmPassword }),
      })

      const data = await response.json()

      if (data.success) {
        // Redirect to user interface using the redirect URL from the server
        if (data.redirect) {
          window.location.href = data.redirect
        } else {
          // Fallback to user interface if no specific redirect is provided
          window.location.href = "user/index.php"
        }
      } else {
        // Show error message
        registerErrorMessage.textContent = data.message
        registerError.style.display = "flex"
        registerBtn.textContent = "Crear Cuenta"
        registerBtn.disabled = false
      }
    } catch (error) {
      console.error("Error:", error)
      registerErrorMessage.textContent = "Error de conexión. Inténtelo nuevamente."
      registerError.style.display = "flex"
      registerBtn.textContent = "Crear Cuenta"
      registerBtn.disabled = false
    }
  })

  // Function to check if user is already logged in
  async function checkSession() {
    try {
      const response = await fetch("api/check-session.php")
      const data = await response.json()

      if (data.success && data.authenticated) {
        // Redirect based on user role
        if (data.user.role === "superusuario" || data.user.role === "administrador") {
          window.location.href = "admin/dashboard.php"
        } else {
          window.location.href = "user/index.php"
        }
      }
    } catch (error) {
      console.error("Error checking session:", error)
    }
  }
})
