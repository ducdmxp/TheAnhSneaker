document.addEventListener("DOMContentLoaded", function () {
  // --- HEADER SCROLL & MOBILE NAV ---
  const header = document.querySelector(".header");
  const mobileNavToggle = document.querySelector(".mobile-nav-toggle");
  const navWrapper = document.querySelector(".nav-wrapper");

  // Hiệu ứng Header khi cuộn
  window.addEventListener("scroll", function () {
    if (window.scrollY > 50) {
      header.classList.add("scrolled");
    } else {
      header.classList.remove("scrolled");
    }
  });

  // Mở/Đóng menu di động
  mobileNavToggle.addEventListener("click", function () {
    navWrapper.classList.toggle("is-open");
    this.classList.toggle("is-open");
    document.body.classList.toggle("nav-open");
  });

  // Đóng menu khi nhấp vào một link
  document.querySelectorAll(".main-nav a").forEach((link) => {
    link.addEventListener("click", () => {
      navWrapper.classList.remove("is-open");
      mobileNavToggle.classList.remove("is-open");
      document.body.classList.remove("nav-open");
    });
  });

  // --- SCROLL TO TOP BUTTON ---
  const scrollToTopBtn = document.getElementById("scrollToTopBtn");
  window.onscroll = function () {
    if (
      document.body.scrollTop > 300 ||
      document.documentElement.scrollTop > 300
    ) {
      scrollToTopBtn.style.display = "block";
    } else {
      scrollToTopBtn.style.display = "none";
    }
  };
  scrollToTopBtn.addEventListener("click", () =>
    window.scrollTo({ top: 0, behavior: "smooth" })
  );

  // --- REVEAL ON SCROLL ANIMATION ---
  const revealElements = document.querySelectorAll(".reveal");
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add("visible");
          observer.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.1 }
  );
  revealElements.forEach((elem) => observer.observe(elem));

  // --- PRODUCT FILTERING & DYNAMIC LOADING LOGIC ---

  // THAY ĐỔI QUAN TRỌNG 1: Trỏ đến file PHP API thay vì Google Sheets
  const productApiUrl = "api/fetch_products.php";

  const productGrid = document.querySelector(".product-grid");
  const tabLinks = document.querySelectorAll(".tab-link");
  const loadMoreBtn = document.getElementById("load-more-btn");

  let allProducts = [];
  let currentFilter = "all";
  let itemsShown = 0;
  const itemsPerLoad = 8;

  // THAY ĐỔI QUAN TRỌNG 2: Hàm fetchProducts được viết lại để xử lý JSON từ PHP
  async function fetchProducts() {
    if (!productGrid) return;

    try {
      const response = await fetch(productApiUrl);
      if (!response.ok) {
        throw new Error("Lỗi mạng khi tải sản phẩm.");
      }

      allProducts = await response.json(); // Dữ liệu trả về là JSON

      if (allProducts.length === 0) {
        productGrid.innerHTML =
          '<p style="text-align: center; width: 100%;">Hiện chưa có sản phẩm nào.</p>';
        if (loadMoreBtn) loadMoreBtn.style.display = "none";
        return;
      }

      filterAndShowProducts();
    } catch (error) {
      console.error("Lỗi khi tải sản phẩm:", error);
      productGrid.innerHTML =
        '<p style="text-align: center; width: 100%;">Không thể tải danh sách sản phẩm. Vui lòng thử lại sau.</p>';
    }
  }

  // Hàm hiển thị sản phẩm (Không thay đổi nhiều, chỉ cần đảm bảo các tên thuộc tính khớp)
  function displayProducts(productsToDisplay) {
    productGrid.innerHTML = ""; // Xóa sản phẩm cũ
    productsToDisplay.forEach((product) => {
      const productCard = document.createElement("div");
      productCard.className = "product-card show";
      productCard.setAttribute("data-category", product.category);

      const formatPrice = (price) => {
        if (!price || isNaN(price)) return "";
        return new Intl.NumberFormat("vi-VN", {
          style: "currency",
          currency: "VND",
        }).format(price);
      };

      let soldCountHTML = "";
      if (product.sold_count && Number(product.sold_count) > 0) {
        soldCountHTML = `
            <div class="product-sold-count">
                <i class="fa-solid fa-fire"></i>
                <span>Đã bán ${product.sold_count}</span>
            </div>
        `;
      }

      const oldPriceHTML = product.price_old
        ? `<span class="old-price">${formatPrice(product.price_old)}</span>`
        : "";
      const tagHTML = product.tag
        ? `<span class="product-tag">${product.tag}</span>`
        : "";

      productCard.innerHTML = `
          <div class="product-image">
              <img src="${product.image_url}" alt="${product.name}" />
              ${tagHTML}
          </div>
          <div class="product-info">
              <h3>${product.name}</h3>
              <div class="product-rating">
                  <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star-half-alt"></i>
              </div>
              ${soldCountHTML}
              <div class="product-price">
                  <span class="new-price">${formatPrice(
                    product.price_new
                  )}</span>
                  ${oldPriceHTML}
              </div>
              <a href="https://zalo.me/0981936021" class="buy-button">Mua Ngay Qua Zalo</a>
          </div>
      `;
      productGrid.appendChild(productCard);
    });
  }

  // Hàm lọc và reset hiển thị
  function filterAndShowProducts() {
    const filteredProducts = allProducts.filter(
      (p) => currentFilter === "all" || p.category === currentFilter
    );
    itemsShown = itemsPerLoad;
    const productsToDisplay = filteredProducts.slice(0, itemsShown);

    if (productsToDisplay.length > 0) {
      displayProducts(productsToDisplay);
    } else {
      productGrid.innerHTML = `<p style="text-align: center; width: 100%;">Không có sản phẩm nào trong danh mục này.</p>`;
    }

    updateLoadMoreButton(filteredProducts);
  }

  // Hàm tải thêm sản phẩm
  function loadMoreProducts() {
    const filteredProducts = allProducts.filter(
      (p) => currentFilter === "all" || p.category === currentFilter
    );
    const currentlyShowing =
      productGrid.querySelectorAll(".product-card").length;
    const nextItemsToShow = filteredProducts.slice(
      currentlyShowing,
      currentlyShowing + itemsPerLoad
    );

    // Nối sản phẩm mới vào lưới, không vẽ lại từ đầu
    nextItemsToShow.forEach((product) => {
      // (Code tạo productCard giống hệt trong hàm displayProducts)
      const productCard = document.createElement("div");
      productCard.className = "product-card show";
      productCard.setAttribute("data-category", product.category);
      const formatPrice = (price) => {
        if (!price || isNaN(price)) return "";
        return new Intl.NumberFormat("vi-VN", {
          style: "currency",
          currency: "VND",
        }).format(price);
      };
      let soldCountHTML =
        product.sold_count && Number(product.sold_count) > 0
          ? `<div class="product-sold-count"><i class="fa-solid fa-fire"></i><span>Đã bán ${product.sold_count}</span></div>`
          : "";
      const oldPriceHTML = product.price_old
        ? `<span class="old-price">${formatPrice(product.price_old)}</span>`
        : "";
      const tagHTML = product.tag
        ? `<span class="product-tag">${product.tag}</span>`
        : "";
      productCard.innerHTML = `<div class="product-image"><img src="${
        product.image_url
      }" alt="${
        product.name
      }" />${tagHTML}</div><div class="product-info"><h3>${
        product.name
      }</h3><div class="product-rating"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star-half-alt"></i></div>${soldCountHTML}<div class="product-price"><span class="new-price">${formatPrice(
        product.price_new
      )}</span>${oldPriceHTML}</div><a href="https://zalo.me/0981936021" class="buy-button">Mua Ngay Qua Zalo</a></div>`;
      productGrid.appendChild(productCard);
    });

    updateLoadMoreButton(filteredProducts);
  }

  // Hàm cập nhật nút "Xem thêm"
  function updateLoadMoreButton(filteredProducts) {
    const currentlyShowing =
      productGrid.querySelectorAll(".product-card").length;
    if (loadMoreBtn) {
      if (currentlyShowing >= filteredProducts.length) {
        loadMoreBtn.style.display = "none";
      } else {
        loadMoreBtn.style.display = "block";
      }
    }
  }

  // Gắn sự kiện cho các tab filter
  tabLinks.forEach((tab) => {
    tab.addEventListener("click", () => {
      tabLinks.forEach((link) => link.classList.remove("active"));
      tab.classList.add("active");
      currentFilter = tab.getAttribute("data-filter");
      filterAndShowProducts();
    });
  });

  // Gắn sự kiện cho nút "Xem thêm"
  if (loadMoreBtn) {
    loadMoreBtn.addEventListener("click", loadMoreProducts);
  }

  // Bắt đầu chạy
  fetchProducts();

  // --- TESTIMONIALS SLIDER LOGIC --- (Giữ nguyên)
  const sliderContainer = document.querySelector(".testimonial-slider");
  if (sliderContainer) {
    const track = sliderContainer.querySelector(".testimonial-track");
    const slides = Array.from(track.children);
    const nextButton = document.querySelector(".next-btn");
    const prevButton = document.querySelector(".prev-btn");
    const dotsNav = document.querySelector(".slider-dots");
    let slidesPerPage;
    let currentIndex = 0;
    let slideCount;

    const setupSlider = () => {
      slidesPerPage = window.innerWidth >= 768 ? 2 : 1;
      slideCount = Math.ceil(slides.length / slidesPerPage);
      currentIndex = currentIndex >= slideCount ? slideCount - 1 : currentIndex;
      updateSliderUI();
      createDots();
    };

    const createDots = () => {
      dotsNav.innerHTML = "";
      for (let i = 0; i < slideCount; i++) {
        const dot = document.createElement("button");
        dot.classList.add("dot");
        if (i === currentIndex) dot.classList.add("active");
        dotsNav.appendChild(dot);
        dot.addEventListener("click", () => {
          currentIndex = i;
          updateSliderUI();
        });
      }
    };

    const updateSliderUI = () => {
      const amountToMove = currentIndex * sliderContainer.clientWidth;
      track.style.transform = `translateX(-${amountToMove}px)`;
      const dots = Array.from(dotsNav.children);
      dots.forEach((dot, index) => {
        dot.classList.toggle("active", index === currentIndex);
      });
    };

    nextButton.addEventListener("click", () => {
      currentIndex = (currentIndex + 1) % slideCount;
      updateSliderUI();
    });

    prevButton.addEventListener("click", () => {
      currentIndex = (currentIndex - 1 + slideCount) % slideCount;
      updateSliderUI();
    });

    setupSlider();
    window.addEventListener("resize", setupSlider);
  }
});
