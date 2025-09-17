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
  });

  // Đóng menu khi nhấp vào một link
  document.querySelectorAll(".main-nav a").forEach((link) => {
    link.addEventListener("click", () => {
      navWrapper.classList.remove("is-open");
      mobileNavToggle.classList.remove("is-open");
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

  // --- SMOOTH SCROLL FOR NAV LINKS ---
  document
    .querySelectorAll('.main-nav a[href^="#"], .cta-button[href^="#"]')
    .forEach((anchor) => {
      anchor.addEventListener("click", function (e) {
        e.preventDefault();
        document.querySelector(this.getAttribute("href")).scrollIntoView({
          behavior: "smooth",
        });
      });
    });

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

  // --- PRODUCT FILTERING & LOAD MORE LOGIC ---
  // --- PRODUCT FILTERING & DYNAMIC LOADING LOGIC ---
  const sheetURL =
    "https://docs.google.com/spreadsheets/d/e/2PACX-1vSIA-8yO4hU4V5zBRYvv28TRJNW6pEWRd1WAkXCb2nzM4b5JPEHlqISvvDix6mfRzm4GrcApaXeZjpn/pub?gid=0&single=true&output=csv"; // <--- PASTE THE GOOGLE SHEET CSV URL HERE

  const productGrid = document.querySelector(".product-grid");
  const tabLinks = document.querySelectorAll(".tab-link");
  const loadMoreBtn = document.getElementById("load-more-btn");

  let allProducts = []; // Mảng để lưu tất cả sản phẩm
  let currentFilter = "all";
  let itemsShown = 0;
  const itemsPerLoad = 8;

  // Hàm chính: Tải và xử lý dữ liệu từ Google Sheet
  async function fetchProducts() {
    if (!productGrid) return; // Dừng lại nếu không tìm thấy product-grid

    try {
      const response = await fetch(sheetURL);
      if (!response.ok) {
        throw new Error("Network response was not ok");
      }
      const csvText = await response.text();

      // Chuyển đổi CSV thành mảng các đối tượng sản phẩm
      const lines = csvText.trim().split("\n");
      const headers = lines[0].trim().split(",");
      allProducts = lines
        .slice(1)
        .map((line) => {
          const values = line.trim().split(",");
          let product = {};
          headers.forEach((header, index) => {
            product[header.trim()] = values[index] ? values[index].trim() : "";
          });
          return product;
        })
        .filter((p) => p.id); // Lọc ra những dòng có id để tránh dòng trống

      filterAndShowProducts();
    } catch (error) {
      console.error("Lỗi khi tải sản phẩm:", error);
      productGrid.innerHTML =
        '<p style="text-align: center;">Không thể tải danh sách sản phẩm. Vui lòng kiểm tra lại đường link Google Sheets.</p>';
    }
  }

  // Hàm hiển thị sản phẩm ra HTML
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
    displayProducts(filteredProducts.slice(0, itemsShown));
    updateLoadMoreButton(filteredProducts);
  }

  // Hàm tải thêm sản phẩm
  function loadMoreProducts() {
    const filteredProducts = allProducts.filter(
      (p) => currentFilter === "all" || p.category === currentFilter
    );
    itemsShown += itemsPerLoad;
    displayProducts(filteredProducts.slice(0, itemsShown));
    updateLoadMoreButton(filteredProducts);
  }

  // Hàm cập nhật nút "Xem thêm"
  function updateLoadMoreButton(filteredProducts) {
    if (itemsShown >= filteredProducts.length) {
      loadMoreBtn.style.display = "none";
    } else {
      loadMoreBtn.style.display = "block";
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

  // --- TESTIMONIALS SLIDER LOGIC (Giữ nguyên) ---
  // ... (Toàn bộ code của slider giữ nguyên như file cũ của bạn) ...
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
      currentIndex = 0;
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

// --- TESTIMONIALS SLIDER LOGIC (REFACTORED FOR RESPONSIVENESS) ---
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
    currentIndex = 0;
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
