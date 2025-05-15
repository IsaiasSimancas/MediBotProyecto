document.addEventListener("DOMContentLoaded", () => {
    // Variables globales
    const productosContainer = document.getElementById("productos-container")
    const paginacionContainer = document.getElementById("paginacion")
    const listaCategoriasContainer = document.getElementById("lista-categorias")
    const modal = document.getElementById("producto-modal")
    const modalContent = document.getElementById("modal-producto-detalle")
    const closeModal = document.querySelector(".close-modal")
    const menuToggle = document.getElementById("menu-toggle")
    const navMenu = document.getElementById("nav-menu")
  
    // Parámetros de URL
    // const urlParams = new URLSearchParams(window.location.search)
    // const paginaActual = Number.parseInt(urlParams.get("pagina")) || 1
    // const categoria = urlParams.get("categoria") || ""
    // const busqueda = urlParams.get("busqueda") || ""
  
    // Variables de estado
    let currentPage = 1
    let totalPages = 1
    let currentCategoria = ""
    let currentBusqueda = ""
  
    // Obtener parámetros de la URL
    const urlParams = new URLSearchParams(window.location.search)
    if (urlParams.has("pagina")) {
      currentPage = Number.parseInt(urlParams.get("pagina"))
    }
    if (urlParams.has("categoria")) {
      currentCategoria = urlParams.get("categoria")
    }
    if (urlParams.has("busqueda")) {
      currentBusqueda = urlParams.get("busqueda")
    }
  
    const searchForm = document.getElementById("search-form")
  
    // Inicializar la página
    cargarCategorias()
    cargarProductos(currentPage, currentCategoria, currentBusqueda)
  
    // Event listeners
    if (closeModal) {
      closeModal.addEventListener("click", cerrarModal)
    }
  
    window.addEventListener("click", (event) => {
      if (event.target === modal) {
        cerrarModal()
      }
    })
  
    if (menuToggle) {
      menuToggle.addEventListener("click", () => {
        navMenu.classList.toggle("active")
      })
    }
  
    if (searchForm) {
      searchForm.addEventListener("submit", function (e) {
        e.preventDefault()
        const busquedaInput = this.querySelector('input[name="busqueda"]')
        currentBusqueda = busquedaInput.value
        currentPage = 1
        cargarProductos(currentPage, currentCategoria, currentBusqueda)
        actualizarURL()
      })
    }
  
    // Funciones
    function cargarCategorias() {
      fetch("api/get-categorias.php")
        .then((response) => response.json())
        .then((data) => {
          if (data.success && data.categorias.length > 0) {
            // Eliminar el elemento de carga
            const loadingItem = listaCategoriasContainer.querySelector(".loading-item")
            if (loadingItem) {
              loadingItem.remove()
            }
  
            // Agregar categorías
            data.categorias.forEach((categoria) => {
              const li = document.createElement("li")
              const a = document.createElement("a")
              a.href = `productos.php?categoria=${encodeURIComponent(categoria.nombre)}`
              a.textContent = categoria.nombre
  
              if (currentCategoria === categoria.nombre) {
                a.classList.add("active")
              }
  
              a.addEventListener("click", function (e) {
                e.preventDefault()
                currentCategoria = categoria.nombre
                currentPage = 1
                cargarProductos(currentPage, currentCategoria, currentBusqueda)
  
                // Actualizar clases active
                document.querySelectorAll("#lista-categorias a").forEach((el) => {
                  el.classList.remove("active")
                })
                this.classList.add("active")
  
                actualizarURL()
              })
  
              li.appendChild(a)
              listaCategoriasContainer.appendChild(li)
            })
          } else {
            console.error("Error al cargar categorías o no hay categorías disponibles")
          }
        })
        .catch((error) => {
          console.error("Error en la solicitud de categorías:", error)
        })
    }
  
    function mostrarCategorias(categorias) {
      // Eliminar el elemento de carga
      const loadingItem = listaCategoriasContainer.querySelector(".loading-item")
      if (loadingItem) {
        loadingItem.remove()
      }
  
      // Agregar cada categoría a la lista
      categorias.forEach((cat) => {
        const li = document.createElement("li")
        const a = document.createElement("a")
        a.href = `productos.php?categoria=${encodeURIComponent(cat.nombre)}`
        a.textContent = cat.nombre
  
        // Marcar como activa si es la categoría actual
        if (currentCategoria === cat.nombre) {
          a.classList.add("active")
        }
  
        li.appendChild(a)
        listaCategoriasContainer.appendChild(li)
      })
    }
  
    function cargarProductos(page, categoria = "", busqueda = "") {
      // Mostrar indicador de carga
      productosContainer.innerHTML = `
              <div class="loading-container">
                  <div class="loading-spinner"></div>
                  <p>Cargando productos...</p>
              </div>
          `
  
      // Construir URL con parámetros
      let url = `api/get-medicamentos.php?page=${page}&limit=8`
      if (categoria) {
        url += `&categoria=${encodeURIComponent(categoria)}`
      }
      if (busqueda) {
        url += `&busqueda=${encodeURIComponent(busqueda)}`
      }
  
      fetch(url)
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            totalPages = data.totalPages
            currentPage = data.currentPage
  
            if (data.medicamentos.length > 0) {
              // Limpiar contenedor
              productosContainer.innerHTML = ""
  
              // Mostrar productos
              data.medicamentos.forEach((producto) => {
                const productoCard = crearProductoCard(producto)
                productosContainer.appendChild(productoCard)
              })
  
              // Actualizar paginación
              actualizarPaginacion()
            } else {
              productosContainer.innerHTML = `
                              <div class="no-results">
                                  <i class="fas fa-search"></i>
                                  <p>No se encontraron productos que coincidan con tu búsqueda.</p>
                                  <button class="btn-reset" onclick="window.location.href='productos.php'">Ver todos los productos</button>
                              </div>
                          `
              paginacionContainer.innerHTML = ""
            }
          } else {
            productosContainer.innerHTML = `
                          <div class="error-message">
                              <i class="fas fa-exclamation-circle"></i>
                              <p>Error al cargar productos: ${data.message}</p>
                              <button class="btn-retry" onclick="window.location.reload()">Reintentar</button>
                          </div>
                      `
          }
        })
        .catch((error) => {
          console.error("Error en la solicitud:", error)
          productosContainer.innerHTML = `
                      <div class="error-message">
                          <i class="fas fa-exclamation-circle"></i>
                          <p>Error de conexión. Por favor, verifica tu conexión a internet e intenta nuevamente.</p>
                          <button class="btn-retry" onclick="window.location.reload()">Reintentar</button>
                      </div>
                  `
        })
    }
  
    function mostrarProductos(productos) {
      // Limpiar el contenedor
      productosContainer.innerHTML = ""
  
      if (productos.length === 0) {
        mostrarNoResultados()
        return
      }
  
      // Crear tarjeta para cada producto
      productos.forEach((producto) => {
        const card = crearTarjetaProducto(producto)
        productosContainer.appendChild(card)
      })
    }
  
    function crearProductoCard(producto) {
      const card = document.createElement("div")
      card.className = "producto-card"
  
      // Formatear precio
      const precioFormateado = new Intl.NumberFormat("es-MX", {
        style: "currency",
        currency: "MXN",
      }).format(producto.precio)
  
      // Determinar disponibilidad
      const disponible = producto.stock > 0
      const disponibilidadClass = disponible ? "disponible" : "no-disponible"
      const disponibilidadTexto = disponible ? "Disponible" : "Agotado"
  
      // Imagen por defecto si no hay imagen
      const imagenUrl = producto.imagen ? producto.imagen : "img/placeholder.jpg"
  
      card.innerHTML = `
              <div class="producto-imagen">
                  <img src="${imagenUrl}" alt="${producto.nombre}" onerror="this.src='img/placeholder.jpg'">
                  <span class="categoria-badge">${producto.categoria}</span>
              </div>
              <div class="producto-info">
                  <h3 class="producto-nombre">${producto.nombre}</h3>
                  <p class="producto-descripcion">${producto.descripcion.substring(0, 80)}${
                    producto.descripcion.length > 80 ? "..." : ""
                  }</p>
                  <div class="producto-footer">
                      <div class="producto-precio-stock">
                          <span class="producto-precio">${precioFormateado}</span>
                          <span class="producto-stock ${disponibilidadClass}">
                              <i class="fas fa-circle"></i> ${disponibilidadTexto}
                          </span>
                      </div>
                      <div class="producto-acciones">
                          <button class="btn-detalles" data-id="${producto.id}">
                              <i class="fas fa-eye"></i> Ver detalles
                          </button>
                          ${
                            disponible
                              ? `
                              <button class="btn-agregar-carrito" data-id="${producto.id}">
                                  <i class="fas fa-cart-plus"></i> Agregar
                              </button>
                          `
                              : `
                              <button class="btn-agregar-carrito disabled" disabled>
                                  <i class="fas fa-cart-plus"></i> Agotado
                              </button>
                          `
                          }
                      </div>
                  </div>
              </div>
          `
  
      // Event listener para el botón de detalles
      const btnDetalles = card.querySelector(".btn-detalles")
      btnDetalles.addEventListener("click", () => {
        mostrarDetallesProducto(producto.id)
      })
  
      // Event listener para el botón de agregar al carrito
      if (disponible) {
        const btnAgregarCarrito = card.querySelector(".btn-agregar-carrito")
        btnAgregarCarrito.addEventListener("click", () => {
          agregarAlCarrito(producto.id)
        })
      }
  
      return card
    }
  
    function crearTarjetaProducto(producto) {
      const card = document.createElement("div")
      card.className = "producto-card"
  
      // Determinar estado del stock
      let stockClass = "stock-disponible"
      let stockText = "En stock"
  
      if (producto.stock <= 0) {
        stockClass = "stock-agotado"
        stockText = "Agotado"
      } else if (producto.stock < 10) {
        stockClass = "stock-bajo"
        stockText = `Solo ${producto.stock} disponibles`
      }
  
      // Formatear precio
      const precioFormateado = new Intl.NumberFormat("es-CO", {
        style: "currency",
        currency: "COP",
      }).format(producto.precio)
  
      // Crear HTML de la tarjeta
      card.innerHTML = `
              <div class="producto-imagen">
                  <img src="${producto.imagen || "img/placeholder.jpg"}" alt="${producto.nombre}">
              </div>
              <div class="producto-info">
                  <h3 class="producto-nombre">${producto.nombre}</h3>
                  <span class="producto-categoria">${producto.categoria}</span>
                  <p class="producto-descripcion">${producto.descripcion}</p>
                  <div class="producto-precio">${precioFormateado}</div>
                  <div class="producto-stock ${stockClass}">${stockText}</div>
                  <div class="producto-acciones">
                      <button class="btn-detalles" data-id="${producto.id}">Ver detalles</button>
                      <button class="btn-agregar ${producto.stock <= 0 ? "disabled" : ""}" 
                              data-id="${producto.id}" 
                              ${producto.stock <= 0 ? "disabled" : ""}>
                          <i class="fas fa-cart-plus"></i> Agregar
                      </button>
                  </div>
              </div>
          `
  
      // Agregar event listeners
      const btnDetalles = card.querySelector(".btn-detalles")
      const btnAgregar = card.querySelector(".btn-agregar")
  
      btnDetalles.addEventListener("click", () => mostrarDetalleProducto(producto.id))
  
      if (producto.stock > 0) {
        btnAgregar.addEventListener("click", () => agregarAlCarrito(producto.id))
      }
  
      return card
    }
  
    function mostrarDetallesProducto(id) {
      // Mostrar indicador de carga en el modal
      modalContent.innerHTML = `
              <div class="loading-container">
                  <div class="loading-spinner"></div>
                  <p>Cargando detalles...</p>
              </div>
          `
  
      // Mostrar el modal
      modal.style.display = "block"
  
      // Obtener detalles del producto
      fetch(`api/get-medicamento.php?id=${id}`)
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            const producto = data.medicamento
  
            // Formatear precio
            const precioFormateado = new Intl.NumberFormat("es-MX", {
              style: "currency",
              currency: "MXN",
            }).format(producto.precio)
  
            // Determinar disponibilidad
            const disponible = producto.stock > 0
            const disponibilidadClass = disponible ? "disponible" : "no-disponible"
            const disponibilidadTexto = disponible ? "Disponible" : "Agotado"
  
            // Imagen por defecto si no hay imagen
            const imagenUrl = producto.imagen ? producto.imagen : "img/placeholder.jpg"
  
            modalContent.innerHTML = `
                          <div class="producto-detalle">
                              <div class="producto-detalle-imagen">
                                  <img src="${imagenUrl}" alt="${producto.nombre}" onerror="this.src='img/placeholder.jpg'">
                              </div>
                              <div class="producto-detalle-info">
                                  <h2>${producto.nombre}</h2>
                                  <span class="categoria-badge">${producto.categoria}</span>
                                  <p class="descripcion">${producto.descripcion}</p>
                                  <div class="precio-stock">
                                      <span class="precio">${precioFormateado}</span>
                                      <span class="stock ${disponibilidadClass}">
                                          <i class="fas fa-circle"></i> ${disponibilidadTexto}
                                      </span>
                                  </div>
                                  ${
                                    disponible
                                      ? `
                                      <div class="cantidad-container">
                                          <label for="cantidad">Cantidad:</label>
                                          <div class="cantidad-control">
                                              <button class="btn-cantidad" id="btn-menos">-</button>
                                              <input type="number" id="cantidad" value="1" min="1" max="${producto.stock}">
                                              <button class="btn-cantidad" id="btn-mas">+</button>
                                          </div>
                                          <span class="stock-disponible">${producto.stock} unidades disponibles</span>
                                      </div>
                                      <button class="btn-agregar-carrito-detalle" data-id="${producto.id}">
                                          <i class="fas fa-cart-plus"></i> Agregar al carrito
                                      </button>
                                  `
                                      : `
                                      <button class="btn-agregar-carrito-detalle disabled" disabled>
                                          <i class="fas fa-cart-plus"></i> Producto agotado
                                      </button>
                                  `
                                  }
                              </div>
                          </div>
                      `
  
            // Event listeners para los controles de cantidad
            if (disponible) {
              const btnMenos = document.getElementById("btn-menos")
              const btnMas = document.getElementById("btn-mas")
              const inputCantidad = document.getElementById("cantidad")
              const maxStock = producto.stock
  
              btnMenos.addEventListener("click", () => {
                const cantidad = Number.parseInt(inputCantidad.value)
                if (cantidad > 1) {
                  inputCantidad.value = cantidad - 1
                }
              })
  
              btnMas.addEventListener("click", () => {
                const cantidad = Number.parseInt(inputCantidad.value)
                if (cantidad < maxStock) {
                  inputCantidad.value = cantidad + 1
                }
              })
  
              inputCantidad.addEventListener("change", function () {
                const cantidad = Number.parseInt(this.value)
                if (isNaN(cantidad) || cantidad < 1) {
                  this.value = 1
                } else if (cantidad > maxStock) {
                  this.value = maxStock
                }
              })
  
              // Event listener para agregar al carrito desde el modal
              const btnAgregarCarritoDetalle = document.querySelector(".btn-agregar-carrito-detalle")
              btnAgregarCarritoDetalle.addEventListener("click", () => {
                const cantidad = Number.parseInt(inputCantidad.value)
                agregarAlCarrito(producto.id, cantidad)
              })
            }
          } else {
            modalContent.innerHTML = `
                          <div class="error-message">
                              <i class="fas fa-exclamation-circle"></i>
                              <p>Error al cargar detalles del producto: ${data.message}</p>
                          </div>
                      `
          }
        })
        .catch((error) => {
          console.error("Error en la solicitud de detalles:", error)
          modalContent.innerHTML = `
                      <div class="error-message">
                          <i class="fas fa-exclamation-circle"></i>
                          <p>Error de conexión. Por favor, verifica tu conexión a internet e intenta nuevamente.</p>
                      </div>
                  `
        })
    }
  
    function mostrarDetalleProducto(id) {
      fetch(`api/get-medicamento.php?id=${id}`)
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            mostrarModalProducto(data.medicamento)
          } else {
            console.error("Error al cargar detalles del producto:", data.message)
          }
        })
        .catch((error) => {
          console.error("Error en la solicitud de detalles:", error)
        })
    }
  
    function mostrarModalProducto(producto) {
      // Determinar estado del stock
      let stockClass = "stock-disponible"
      let stockText = "En stock"
  
      if (producto.stock <= 0) {
        stockClass = "stock-agotado"
        stockText = "Agotado"
      } else if (producto.stock < 10) {
        stockClass = "stock-bajo"
        stockText = `Solo ${producto.stock} disponibles`
      }
  
      // Formatear precio
      const precioFormateado = new Intl.NumberFormat("es-CO", {
        style: "currency",
        currency: "COP",
      }).format(producto.precio)
  
      // Crear HTML del modal
      modalContent.innerHTML = `
              <div class="producto-detalle">
                  <div class="producto-detalle-imagen">
                      <img src="${producto.imagen || "img/placeholder.jpg"}" alt="${producto.nombre}">
                  </div>
                  <div class="producto-detalle-info">
                      <h2 class="producto-detalle-nombre">${producto.nombre}</h2>
                      <span class="producto-detalle-categoria">${producto.categoria}</span>
                      <p class="producto-detalle-descripcion">${producto.descripcion}</p>
                      <div class="producto-detalle-precio">${precioFormateado}</div>
                      <div class="producto-detalle-stock ${stockClass}">${stockText}</div>
                      <div class="producto-detalle-acciones">
                          <button class="btn-agregar ${producto.stock <= 0 ? "disabled" : ""}" 
                                  data-id="${producto.id}" 
                                  ${producto.stock <= 0 ? "disabled" : ""}>
                              <i class="fas fa-cart-plus"></i> Agregar al carrito
                          </button>
                      </div>
                  </div>
              </div>
          `
  
      // Agregar event listener al botón de agregar
      const btnAgregar = modalContent.querySelector(".btn-agregar")
      if (producto.stock > 0) {
        btnAgregar.addEventListener("click", () => agregarAlCarrito(producto.id))
      }
  
      // Mostrar modal
      modal.style.display = "block"
      document.body.style.overflow = "hidden" // Evitar scroll en el fondo
    }
  
    function cerrarModal() {
      modal.style.display = "none"
      document.body.style.overflow = "auto" // Restaurar scroll
    }
  
    function agregarAlCarrito(id, cantidad = 1) {
      // Verificar si el usuario está logueado
      fetch("api/check-session.php")
        .then((response) => response.json())
        .then((data) => {
          if (data.logueado) {
            // Usuario logueado, agregar al carrito
            return fetch("api/add-to-cart.php", {
              method: "POST",
              headers: {
                "Content-Type": "application/json",
              },
              body: JSON.stringify({
                producto_id: id,
                cantidad: cantidad,
              }),
            })
          } else {
            // Usuario no logueado, redirigir a login
            alert("Debes iniciar sesión para agregar productos al carrito")
            window.location.href = "login.php?redirect=productos.php"
            throw new Error("Usuario no logueado")
          }
        })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            // Mostrar mensaje de éxito
            alert("Producto agregado al carrito")
  
            // Cerrar modal si está abierto
            if (modal.style.display === "block") {
              modal.style.display = "none"
            }
  
            // Opcional: actualizar contador del carrito si existe
            const cartCounter = document.querySelector(".cart-counter")
            if (cartCounter && data.cartCount) {
              cartCounter.textContent = data.cartCount
            }
          } else {
            alert("Error al agregar al carrito: " + data.message)
          }
        })
        .catch((error) => {
          if (error.message !== "Usuario no logueado") {
            console.error("Error al agregar al carrito:", error)
            alert("Error al agregar al carrito")
          }
        })
    }
  
    function generarPaginacion(paginaActual, totalPaginas) {
      paginacionContainer.innerHTML = ""
  
      if (totalPaginas <= 1) {
        return
      }
  
      // Construir base URL para paginación
      let baseUrl = "productos.php?"
      if (currentCategoria) {
        baseUrl += `categoria=${encodeURIComponent(currentCategoria)}&`
      }
      if (currentBusqueda) {
        baseUrl += `busqueda=${encodeURIComponent(currentBusqueda)}&`
      }
  
      // Botón anterior
      const btnAnterior = document.createElement("a")
      btnAnterior.href = paginaActual > 1 ? `${baseUrl}pagina=${paginaActual - 1}` : "#"
      btnAnterior.className = `paginacion-btn ${paginaActual <= 1 ? "disabled" : ""}`
      btnAnterior.innerHTML = "&laquo; Anterior"
      if (paginaActual <= 1) {
        btnAnterior.addEventListener("click", (e) => e.preventDefault())
      }
      paginacionContainer.appendChild(btnAnterior)
  
      // Determinar rango de páginas a mostrar
      let inicio = Math.max(1, paginaActual - 2)
      const fin = Math.min(totalPaginas, inicio + 4)
  
      // Ajustar inicio si estamos cerca del final
      if (fin === totalPaginas) {
        inicio = Math.max(1, fin - 4)
      }
  
      // Botones de páginas
      for (let i = inicio; i <= fin; i++) {
        const btnPagina = document.createElement("a")
        btnPagina.href = `${baseUrl}pagina=${i}`
        btnPagina.className = `paginacion-btn ${i === paginaActual ? "active" : ""}`
        btnPagina.textContent = i
        paginacionContainer.appendChild(btnPagina)
      }
  
      // Botón siguiente
      const btnSiguiente = document.createElement("a")
      btnSiguiente.href = paginaActual < totalPaginas ? `${baseUrl}pagina=${paginaActual + 1}` : "#"
      btnSiguiente.className = `paginacion-btn ${paginaActual >= totalPaginas ? "disabled" : ""}`
      btnSiguiente.innerHTML = "Siguiente &raquo;"
      if (paginaActual >= totalPaginas) {
        btnSiguiente.addEventListener("click", (e) => e.preventDefault())
      }
      paginacionContainer.appendChild(btnSiguiente)
    }
  
    function actualizarPaginacion() {
      paginacionContainer.innerHTML = ""
  
      if (totalPages <= 1) {
        return
      }
  
      const ul = document.createElement("ul")
      ul.className = "pagination"
  
      // Botón anterior
      const liPrev = document.createElement("li")
      const aPrev = document.createElement("a")
      aPrev.href = "#"
      aPrev.innerHTML = '<i class="fas fa-chevron-left"></i>'
      aPrev.className = currentPage === 1 ? "disabled" : ""
  
      if (currentPage > 1) {
        aPrev.addEventListener("click", (e) => {
          e.preventDefault()
          currentPage--
          cargarProductos(currentPage, currentCategoria, currentBusqueda)
          actualizarURL()
        })
      }
  
      liPrev.appendChild(aPrev)
      ul.appendChild(liPrev)
  
      // Determinar rango de páginas a mostrar
      let startPage = Math.max(1, currentPage - 2)
      const endPage = Math.min(totalPages, startPage + 4)
  
      if (endPage - startPage < 4) {
        startPage = Math.max(1, endPage - 4)
      }
  
      // Primera página
      if (startPage > 1) {
        const liFirst = document.createElement("li")
        const aFirst = document.createElement("a")
        aFirst.href = "#"
        aFirst.textContent = "1"
  
        aFirst.addEventListener("click", (e) => {
          e.preventDefault()
          currentPage = 1
          cargarProductos(currentPage, currentCategoria, currentBusqueda)
          actualizarURL()
        })
  
        liFirst.appendChild(aFirst)
        ul.appendChild(liFirst)
  
        // Puntos suspensivos
        if (startPage > 2) {
          const liDots = document.createElement("li")
          const aDots = document.createElement("a")
          aDots.className = "dots"
          aDots.textContent = "..."
          liDots.appendChild(aDots)
          ul.appendChild(liDots)
        }
      }
  
      // Páginas numeradas
      for (let i = startPage; i <= endPage; i++) {
        const li = document.createElement("li")
        const a = document.createElement("a")
        a.href = "#"
        a.textContent = i
  
        if (i === currentPage) {
          a.className = "active"
        }
  
        a.addEventListener("click", (e) => {
          e.preventDefault()
          currentPage = i
          cargarProductos(currentPage, currentCategoria, currentBusqueda)
          actualizarURL()
        })
  
        li.appendChild(a)
        ul.appendChild(li)
      }
  
      // Última página
      if (endPage < totalPages) {
        // Puntos suspensivos
        if (endPage < totalPages - 1) {
          const liDots = document.createElement("li")
          const aDots = document.createElement("a")
          aDots.className = "dots"
          aDots.textContent = "..."
          liDots.appendChild(aDots)
          ul.appendChild(liDots)
        }
  
        const liLast = document.createElement("li")
        const aLast = document.createElement("a")
        aLast.href = "#"
        aLast.textContent = totalPages
  
        aLast.addEventListener("click", (e) => {
          e.preventDefault()
          currentPage = totalPages
          cargarProductos(currentPage, currentCategoria, currentBusqueda)
          actualizarURL()
        })
  
        liLast.appendChild(aLast)
        ul.appendChild(liLast)
      }
  
      // Botón siguiente
      const liNext = document.createElement("li")
      const aNext = document.createElement("a")
      aNext.href = "#"
      aNext.innerHTML = '<i class="fas fa-chevron-right"></i>'
      aNext.className = currentPage === totalPages ? "disabled" : ""
  
      if (currentPage < totalPages) {
        aNext.addEventListener("click", (e) => {
          e.preventDefault()
          currentPage++
          cargarProductos(currentPage, currentCategoria, currentBusqueda)
          actualizarURL()
        })
      }
  
      liNext.appendChild(aNext)
      ul.appendChild(liNext)
  
      paginacionContainer.appendChild(ul)
    }
  
    function actualizarURL() {
      let url = "productos.php"
      const params = []
  
      if (currentPage > 1) {
        params.push(`pagina=${currentPage}`)
      }
  
      if (currentCategoria) {
        params.push(`categoria=${encodeURIComponent(currentCategoria)}`)
      }
  
      if (currentBusqueda) {
        params.push(`busqueda=${encodeURIComponent(currentBusqueda)}`)
      }
  
      if (params.length > 0) {
        url += "?" + params.join("&")
      }
  
      // Actualizar URL sin recargar la página
      window.history.pushState({}, "", url)
    }
  
    function mostrarNoResultados() {
      productosContainer.innerHTML = `
              <div class="no-resultados">
                  <i class="fas fa-search" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                  <h3>No se encontraron productos</h3>
                  <p>Intenta con otra búsqueda o categoría</p>
              </div>
          `
    }
  
    function mostrarError(mensaje) {
      productosContainer.innerHTML = `
              <div class="no-resultados">
                  <i class="fas fa-exclamation-circle" style="font-size: 3rem; color: #e74c3c; margin-bottom: 1rem;"></i>
                  <h3>Error</h3>
                  <p>${mensaje}</p>
              </div>
          `
    }
  })
  