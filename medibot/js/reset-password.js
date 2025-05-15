document.addEventListener("DOMContentLoaded", () => {
    // Reset password form submission
    const resetForm = document.getElementById("reset-form")
    const resetBtn = document.getElementById("reset-btn")
    const resetError = document.getElementById("reset-error")
    const resetErrorMessage = document.getElementById("reset-error-message")
    const resetSuccess = document.getElementById("reset-success")
  
    resetForm.addEventListener("submit", (e) => {
      e.preventDefault()
  
      // Reset messages
      resetError.style.display = "none"
      resetSuccess.style.display = "none"
  
      // Get form value
      const email = document.getElementById("reset-email").value
  
      // Basic validation
      if (!email) {
        resetErrorMessage.textContent = "Por favor ingrese su correo electrónico"
        resetError.style.display = "flex"
        return
      }
  
      // Show loading state
      resetBtn.textContent = "Enviando..."
      resetBtn.disabled = true
  
      // Simulate sending reset email (this would connect to a backend in a real app)
      setTimeout(() => {
        // Simulate successful email sending
        resetSuccess.style.display = "flex"
        resetBtn.textContent = "Enviar Enlace de Recuperación"
        resetBtn.disabled = true
  
        // In case of error, you would do:
        // resetErrorMessage.textContent = 'Error al enviar el correo. Inténtelo nuevamente.';
        // resetError.style.display = 'flex';
        // resetBtn.textContent = 'Enviar Enlace de Recuperación';
        // resetBtn.disabled = false;
      }, 1500)
    })
  })
  