document.addEventListener("DOMContentLoaded", () => {
  // Crear elementos del chatbot
  createChatbotElements()

  // Variables globales
  const chatbotButton = document.querySelector(".chatbot-button")
  const chatbotContainer = document.querySelector(".chatbot-container")
  const chatbotClose = document.querySelector(".chatbot-close")
  const chatbotMessages = document.querySelector(".chatbot-messages")
  const chatbotInput = document.querySelector(".chatbot-input")
  const chatbotSend = document.querySelector(".chatbot-send")
  const typingIndicator = document.querySelector(".typing-indicator")

  // Estado del chatbot
  const chatbotState = {
    active: false,
    currentStep: 0,
    responses: [],
    waitingForResponse: false,
  }

  // Event listeners
  chatbotButton.addEventListener("click", toggleChatbot)
  chatbotClose.addEventListener("click", toggleChatbot)
  chatbotSend.addEventListener("click", sendMessage)
  chatbotInput.addEventListener("keypress", (e) => {
    if (e.key === "Enter") {
      sendMessage()
    }
  })

  // Iniciar chatbot
  function initChatbot() {
    chatbotState.currentStep = 0
    chatbotState.responses = []
    chatbotState.waitingForResponse = false
    chatbotMessages.innerHTML = ""

    // Mensaje de bienvenida después de un breve retraso
    setTimeout(() => {
      getNextQuestion()
    }, 500)
  }

  // Alternar visibilidad del chatbot
  function toggleChatbot() {
    chatbotState.active = !chatbotState.active
    chatbotContainer.classList.toggle("active", chatbotState.active)

    if (chatbotState.active && chatbotMessages.children.length === 0) {
      initChatbot()
    }
  }

  // Enviar mensaje del usuario
  function sendMessage() {
    if (chatbotState.waitingForResponse || !chatbotInput.value.trim()) {
      return
    }

    const userMessage = chatbotInput.value.trim()
    addMessage(userMessage, "user")
    chatbotInput.value = ""

    // Guardar respuesta
    chatbotState.responses.push(userMessage)
    chatbotState.waitingForResponse = true

    // Mostrar indicador de escritura
    showTypingIndicator()

    // Obtener siguiente pregunta o recomendación
    setTimeout(() => {
      hideTypingIndicator()
      getNextQuestion()
    }, 1000)
  }

  // Obtener siguiente pregunta o recomendación
  function getNextQuestion() {
    chatbotState.currentStep++

    fetch("api/chatbot.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        step: chatbotState.currentStep,
        responses: chatbotState.responses,
      }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          if (data.recommendations) {
            // Mostrar recomendaciones
            addMessage(data.message, "bot")
            showRecommendations(data.recommendations)

            // Agregar mensaje de cierre
            setTimeout(() => {
              addMessage(
                "¿Hay algo más en lo que pueda ayudarte? Puedes iniciar una nueva consulta cuando quieras.",
                "bot",
              )
              chatbotState.waitingForResponse = false
            }, 1000)
          } else {
            // Mostrar siguiente pregunta
            addMessage(data.question, "bot")
            chatbotState.waitingForResponse = false
          }
        } else {
          // Mostrar error
          addMessage("Lo siento, ha ocurrido un error. Por favor, intenta nuevamente.", "bot")
          chatbotState.waitingForResponse = false
        }
      })
      .catch((error) => {
        console.error("Error:", error)
        addMessage("Lo siento, ha ocurrido un error de conexión. Por favor, intenta nuevamente.", "bot")
        chatbotState.waitingForResponse = false
      })
  }

  // Agregar mensaje al chat
  function addMessage(text, sender) {
    const messageDiv = document.createElement("div")
    messageDiv.className = `message message-${sender}`

    const messageText = document.createElement("div")
    messageText.className = "message-text"
    messageText.textContent = text

    const messageTime = document.createElement("div")
    messageTime.className = "message-time"

    const now = new Date()
    const hours = now.getHours().toString().padStart(2, "0")
    const minutes = now.getMinutes().toString().padStart(2, "0")
    messageTime.textContent = `${hours}:${minutes}`

    messageDiv.appendChild(messageText)
    messageDiv.appendChild(messageTime)
    chatbotMessages.appendChild(messageDiv)

    // Scroll al final
    chatbotMessages.scrollTop = chatbotMessages.scrollHeight
  }

  // Mostrar recomendaciones
  function showRecommendations(recommendations) {
    recommendations.forEach((med) => {
      const recDiv = document.createElement("div")
      recDiv.className = "recommendation-card"

      // Formatear precio
      const precio = Number.parseFloat(med.precio)
      const precioFormateado = new Intl.NumberFormat("es-CO", {
        style: "currency",
        currency: "COP",
      }).format(precio)

      recDiv.innerHTML = `
                <h4>${med.nombre}</h4>
                <p>${med.descripcion.substring(0, 100)}${med.descripcion.length > 100 ? "..." : ""}</p>
                <p class="price">${precioFormateado}</p>
                <a href="productos.php?id=${med.id}" class="view-btn">Ver detalles</a>
            `

      chatbotMessages.appendChild(recDiv)
    })

    // Scroll al final
    chatbotMessages.scrollTop = chatbotMessages.scrollHeight
  }

  // Mostrar indicador de escritura
  function showTypingIndicator() {
    typingIndicator.classList.add("active")
    chatbotMessages.appendChild(typingIndicator)
    chatbotMessages.scrollTop = chatbotMessages.scrollHeight
  }

  // Ocultar indicador de escritura
  function hideTypingIndicator() {
    typingIndicator.classList.remove("active")
  }

  // Crear elementos del chatbot
  function createChatbotElements() {
    // Verificar si ya existen
    if (document.querySelector(".chatbot-button")) {
      return
    }

    // Crear botón flotante
    const chatbotButton = document.createElement("div")
    chatbotButton.className = "chatbot-button"
    chatbotButton.innerHTML = '<i class="fas fa-robot"></i>'
    document.body.appendChild(chatbotButton)

    // Crear contenedor del chatbot
    const chatbotContainer = document.createElement("div")
    chatbotContainer.className = "chatbot-container"

    // Estructura del chatbot
    chatbotContainer.innerHTML = `
            <div class="chatbot-header">
                <h3><i class="fas fa-robot"></i> MediBot</h3>
                <button class="chatbot-close"><i class="fas fa-times"></i></button>
            </div>
            <div class="chatbot-body">
                <div class="chatbot-messages"></div>
                <div class="typing-indicator">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
            <div class="chatbot-footer">
                <div class="chatbot-input-container">
                    <input type="text" class="chatbot-input" placeholder="Escribe tu mensaje...">
                    <button class="chatbot-send"><i class="fas fa-paper-plane"></i></button>
                </div>
            </div>
        `

    document.body.appendChild(chatbotContainer)

    // Agregar estilos
    const link = document.createElement("link")
    link.rel = "stylesheet"
    link.href = "css/chatbot.css"
    document.head.appendChild(link)
  }
})
