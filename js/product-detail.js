document.addEventListener("DOMContentLoaded", () => {
    // Set current year in footer
    document.getElementById("current-year").textContent = new Date().getFullYear()
  
    // Get product ID from URL
    const urlParams = new URLSearchParams(window.location.search)
    const productId = Number.parseInt(urlParams.get("id"))
  
    // Sample data for medications
    const medications = [
      {
        id: 1,
        name: "Paracetamol 500mg",
        description:
          "Analgésico y antipirético para aliviar el dolor y reducir la fiebre. Indicado para el tratamiento sintomático del dolor leve a moderado y estados febriles. Cada tableta contiene 500mg de paracetamol como ingrediente activo.",
        price: 5.99,
        image: "img/placeholder.jpg",
        category: "Analgésicos",
        stock: 150,
        dosage: "1-2 tabletas cada 6-8 horas según sea necesario. No exceder 8 tabletas en 24 horas.",
        activeIngredient: "Paracetamol (Acetaminofén)",
        presentation: "Caja con 20 tabletas",
        laboratory: "Farmacéutica Nacional",
      },
      {
        id: 2,
        name: "Ibuprofeno 400mg",
        description:
          "Antiinflamatorio no esteroideo (AINE) para aliviar el dolor, reducir la inflamación y bajar la fiebre. Indicado para dolores musculares, articulares, menstruales, de cabeza y dentales. Cada tableta contiene 400mg de ibuprofeno como ingrediente activo.",
        price: 7.5,
        image: "img/placeholder.jpg",
        category: "Antiinflamatorios",
        stock: 120,
        dosage: "1 tableta cada 6-8 horas según sea necesario. No exceder 3 tabletas en 24 horas.",
        activeIngredient: "Ibuprofeno",
        presentation: "Caja con 30 tabletas",
        laboratory: "Laboratorios Médicos",
      },
      {
        id: 3,
        name: "Omeprazol 20mg",
        description:
          "Inhibidor de la bomba de protones que reduce la producción de ácido en el estómago. Indicado para el tratamiento de úlceras gástricas y duodenales, reflujo gastroesofágico y síndrome de Zollinger-Ellison. Cada cápsula contiene 20mg de omeprazol como ingrediente activo.",
        price: 12.75,
        image: "img/placeholder.jpg",
        category: "Gastrointestinal",
        stock: 80,
        dosage: "1 cápsula diaria, preferiblemente antes del desayuno.",
        activeIngredient: "Omeprazol",
        presentation: "Caja con 14 cápsulas",
        laboratory: "Farmacéutica Global",
      },
      {
        id: 4,
        name: "Loratadina 10mg",
        description:
          "Antihistamínico de segunda generación que alivia los síntomas de alergias como rinitis alérgica y urticaria. No causa somnolencia significativa. Cada tableta contiene 10mg de loratadina como ingrediente activo.",
        price: 8.99,
        image: "img/placeholder.jpg",
        category: "Antialérgicos",
        stock: 100,
        dosage: "1 tableta diaria.",
        activeIngredient: "Loratadina",
        presentation: "Caja con 10 tabletas",
        laboratory: "Laboratorios Alergia",
      },
      {
        id: 5,
        name: "Amoxicilina 500mg",
        description:
          "Antibiótico de amplio espectro del grupo de las penicilinas. Indicado para el tratamiento de infecciones bacterianas del tracto respiratorio, urinario, piel y tejidos blandos. Cada cápsula contiene 500mg de amoxicilina como ingrediente activo.",
        price: 15.5,
        image: "img/placeholder.jpg",
        category: "Antibióticos",
        stock: 60,
        dosage: "1 cápsula cada 8 horas durante 7-10 días o según prescripción médica.",
        activeIngredient: "Amoxicilina",
        presentation: "Caja con 21 cápsulas",
        laboratory: "Laboratorios Antibióticos",
      },
      {
        id: 6,
        name: "Vitamina C 1000mg",
        description:
          "Suplemento vitamínico que ayuda a reforzar el sistema inmunológico y contribuye a la formación de colágeno. Indicado para prevenir deficiencias de vitamina C y como complemento en tratamientos de resfriados. Cada tableta contiene 1000mg de ácido ascórbico como ingrediente activo.",
        price: 9.25,
        image: "img/placeholder.jpg",
        category: "Vitaminas",
        stock: 200,
        dosage: "1 tableta diaria.",
        activeIngredient: "Ácido Ascórbico",
        presentation: "Frasco con 30 tabletas",
        laboratory: "Laboratorios Nutricionales",
      },
    ]
  
    // Find product by ID
    const product = medications.find((med) => med.id === productId)
    const productDetailContainer = document.getElementById("product-detail")
  
    // If product not found
    if (!product) {
      productDetailContainer.innerHTML = `
              <div class="card">
                  <div class="card-body" style="text-align: center; padding: 2rem;">
                      <h1 style="font-size: 1.5rem; font-weight: bold; color: #1f2937; margin-bottom: 1rem;">Producto no encontrado</h1>
                      <p style="color: #6b7280; margin-bottom: 1.5rem;">El producto que estás buscando no existe o ha sido removido.</p>
                      <a href="catalogo.html" class="btn btn-primary">Volver al catálogo</a>
                  </div>
              </div>
          `
      return
    }
  
    // Render product details
    productDetailContainer.innerHTML = `
          <div class="product-detail">
              <div class="product-detail-image">
                  <img src="${product.image}" alt="${product.name}">
                  <span class="product-detail-category">${product.category}</span>
              </div>
              
              <div class="product-detail-info">
                  <h1 class="product-detail-title">${product.name}</h1>
                  <p class="product-detail-description">${product.description}</p>
                  
                  <div class="product-detail-price-stock">
                      <div class="price-container">
                          <span class="price-label">Precio</span>
                          <span class="product-detail-price">$${product.price.toFixed(2)}</span>
                      </div>
                      <div class="stock-container">
                          <span class="stock-label">Disponibilidad</span>
                          <span class="product-detail-stock ${product.stock > 0 ? "stock-available" : "stock-unavailable"}">
                              ${product.stock > 0 ? `${product.stock} en stock` : "Agotado"}
                          </span>
                      </div>
                  </div>
                  
                  <div class="product-detail-actions">
                      <button class="btn btn-primary">
                          <i class="fas fa-shopping-cart"></i> Añadir al carrito
                      </button>
                      <button class="btn btn-outline">
                          <i class="fas fa-heart"></i>
                      </button>
                      <button class="btn btn-outline">
                          <i class="fas fa-share-alt"></i>
                      </button>
                  </div>
              </div>
          </div>
          
          <div class="product-details-section">
              <h2>Detalles del producto</h2>
              <div class="product-details-grid">
                  <div>
                      <div class="product-detail-item">
                          <p class="detail-label">Principio activo</p>
                          <p>${product.activeIngredient}</p>
                      </div>
                      <div class="product-detail-item">
                          <p class="detail-label">Presentación</p>
                          <p>${product.presentation}</p>
                      </div>
                      <div class="product-detail-item">
                          <p class="detail-label">Laboratorio</p>
                          <p>${product.laboratory}</p>
                      </div>
                  </div>
                  <div>
                      <div class="product-detail-item">
                          <p class="detail-label">Dosificación</p>
                          <p>${product.dosage}</p>
                      </div>
                  </div>
              </div>
          </div>
      `
  
    // Add event listener to Add to Cart button
    const addToCartBtn = productDetailContainer.querySelector(".btn-primary")
    addToCartBtn.addEventListener("click", () => {
      // In a real app, this would add the product to cart
      // For now, redirect to login
      window.location.href = "index.html"
    })
  })
  