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

  // --- PRODUCT FILTERING & LOAD MORE ---
  // Sản phẩm được PHP render sẵn trong HTML (tốt cho SEO). JS chỉ lọc / phân trang
  // bằng cách bật tắt class "show". Nếu HTML chưa có sản phẩm (VD: lỗi DB lúc render)
  // thì mới gọi API để tải bổ sung.
  const productApiUrl = "api/fetch_products.php";
  const productGrid = document.querySelector(".product-grid");
  const tabLinks = document.querySelectorAll(".tab-link");
  const loadMoreBtn = document.getElementById("load-more-btn");
  const itemsPerLoad = Number(productGrid?.dataset.perLoad) || 8;

  let currentFilter = "all";
  let visibleLimit = itemsPerLoad;

  const escapeHTML = (value) =>
    String(value ?? "").replace(/[&<>"']/g, (c) => ({
      "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;",
    })[c]);

  const formatPrice = (price) => {
    if (!price || isNaN(price)) return "";
    return new Intl.NumberFormat("vi-VN", { style: "currency", currency: "VND" }).format(price);
  };

  function buildCard(product) {
    const card = document.createElement("article");
    card.className = "product-card";
    card.dataset.category = product.category;
    const soldCount = Number(product.sold_count) > 0
      ? `<div class="product-sold-count"><i class="fa-solid fa-fire" aria-hidden="true"></i><span>Đã bán ${Number(product.sold_count)}</span></div>`
      : "";
    const oldPrice = formatPrice(product.price_old)
      ? `<span class="old-price">${formatPrice(product.price_old)}</span>`
      : "";
    const tag = product.tag ? `<span class="product-tag">${escapeHTML(product.tag)}</span>` : "";
    card.innerHTML = `
      <div class="product-image">
        <img src="${escapeHTML(product.image_url)}" alt="Giày ${escapeHTML(product.name)} - TheAnhSneaker" width="400" height="400" loading="lazy" decoding="async" />
        ${tag}
      </div>
      <div class="product-info">
        <h3>${escapeHTML(product.name)}</h3>
        <div class="product-rating" aria-hidden="true">
          <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star-half-alt"></i>
        </div>
        ${soldCount}
        <div class="product-price"><span class="new-price">${formatPrice(product.price_new)}</span>${oldPrice}</div>
        <a href="https://zalo.me/0981936021" class="buy-button" target="_blank" rel="nofollow noopener">Mua Ngay Qua Zalo</a>
      </div>`;
    return card;
  }

  const getCards = () => Array.from(productGrid.querySelectorAll(".product-card"));

  function setEmptyMessage(text) {
    let msg = productGrid.querySelector(".product-empty");
    if (!text) {
      if (msg) msg.remove();
      return;
    }
    if (!msg) {
      msg = document.createElement("p");
      msg.className = "product-empty";
      msg.style.cssText = "text-align: center; width: 100%;";
      productGrid.appendChild(msg);
    }
    msg.textContent = text;
  }

  function renderProducts() {
    const cards = getCards();
    const matched = cards.filter(
      (card) => currentFilter === "all" || card.dataset.category === currentFilter
    );
    cards.forEach((card) => card.classList.remove("show"));
    matched.slice(0, visibleLimit).forEach((card) => card.classList.add("show"));

    if (cards.length === 0) {
      setEmptyMessage("Hiện chưa có sản phẩm nào.");
    } else if (matched.length === 0) {
      setEmptyMessage("Không có sản phẩm nào trong danh mục này.");
    } else {
      setEmptyMessage("");
    }
    if (loadMoreBtn) {
      loadMoreBtn.style.display = matched.length > visibleLimit ? "block" : "none";
    }
  }

  async function fetchProductsFallback() {
    try {
      const response = await fetch(productApiUrl);
      if (!response.ok) throw new Error("Lỗi mạng khi tải sản phẩm.");
      const products = await response.json();
      if (Array.isArray(products)) {
        products.forEach((product) => productGrid.appendChild(buildCard(product)));
      }
      renderProducts();
    } catch (error) {
      console.error("Lỗi khi tải sản phẩm:", error);
      setEmptyMessage("Không thể tải danh sách sản phẩm. Vui lòng thử lại sau.");
    }
  }

  if (productGrid) {
    tabLinks.forEach((tab) => {
      tab.addEventListener("click", () => {
        tabLinks.forEach((link) => link.classList.remove("active"));
        tab.classList.add("active");
        currentFilter = tab.getAttribute("data-filter");
        visibleLimit = itemsPerLoad;
        renderProducts();
      });
    });

    if (loadMoreBtn) {
      loadMoreBtn.addEventListener("click", () => {
        visibleLimit += itemsPerLoad;
        renderProducts();
      });
    }

    if (getCards().length > 0) {
      renderProducts();
    } else {
      fetchProductsFallback();
    }
  }

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
        dot.setAttribute("aria-label", `Xem nhóm đánh giá ${i + 1}`);
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
