document.addEventListener("DOMContentLoaded", function () {
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
  const tabLinks = document.querySelectorAll(".tab-link");
  const productCards = document.querySelectorAll(".product-card");
  const loadMoreBtn = document.getElementById("load-more-btn");
  const itemsPerLoad = 8;
  let currentFilter = "all";
  let visibleItems = 0;

  function filterAndShowProducts() {
    visibleItems = 0;
    productCards.forEach((card) => {
      const category = card.getAttribute("data-category");
      const matchesFilter =
        currentFilter === "all" || category === currentFilter;

      if (matchesFilter && visibleItems < itemsPerLoad) {
        card.style.display = "block";
        card.classList.add("show");
        visibleItems++;
      } else {
        card.style.display = "none";
        card.classList.remove("show");
      }
    });
    updateLoadMoreButton();
  }

  function loadMoreProducts() {
    let newVisibleItems = 0;
    productCards.forEach((card) => {
      if (card.style.display === "none") {
        // Chỉ xét những card đang ẩn
        const category = card.getAttribute("data-category");
        const matchesFilter =
          currentFilter === "all" || category === currentFilter;
        if (matchesFilter && newVisibleItems < itemsPerLoad) {
          card.style.display = "block";
          card.classList.add("show");
          newVisibleItems++;
        }
      }
    });
    updateLoadMoreButton();
  }

  function updateLoadMoreButton() {
    let hiddenCount = 0;
    productCards.forEach((card) => {
      const category = card.getAttribute("data-category");
      const matchesFilter =
        currentFilter === "all" || category === currentFilter;
      if (matchesFilter && card.style.display === "none") {
        hiddenCount++;
      }
    });

    if (hiddenCount > 0) {
      loadMoreBtn.style.display = "block";
    } else {
      loadMoreBtn.style.display = "none";
    }
  }

  tabLinks.forEach((tab) => {
    tab.addEventListener("click", () => {
      // Cập nhật trạng thái active
      tabLinks.forEach((link) => link.classList.remove("active"));
      tab.classList.add("active");

      // Lọc sản phẩm
      currentFilter = tab.getAttribute("data-filter");
      filterAndShowProducts();
    });
  });

  loadMoreBtn.addEventListener("click", loadMoreProducts);

  // Hiển thị sản phẩm ban đầu
  filterAndShowProducts();
});

// --- TESTIMONIALS SLIDER LOGIC ---
const track = document.querySelector(".testimonial-track");
const slides = Array.from(track.children);
const nextButton = document.querySelector(".next-btn");
const prevButton = document.querySelector(".prev-btn");
const dotsNav = document.querySelector(".slider-dots");

if (track) {
  // Chỉ chạy code nếu có slider
  const slideWidth = slides[0].getBoundingClientRect().width;
  let slidesPerPage = window.innerWidth >= 768 ? 2 : 1;
  let currentIndex = 0;

  // Sắp xếp các slide cạnh nhau
  const setSlidePosition = (slide, index) => {
    // không cần set vị trí vì đã dùng flexbox
  };
  slides.forEach(setSlidePosition);

  // Tạo các dấu chấm điều hướng
  slides.forEach((slide, index) => {
    if (index % slidesPerPage === 0) {
      const dot = document.createElement("button");
      dot.classList.add("dot");
      if (index === 0) dot.classList.add("active");
      dotsNav.appendChild(dot);

      dot.addEventListener("click", (e) => {
        currentIndex = index;
        updateSlider();
      });
    }
  });
  const dots = Array.from(dotsNav.children);

  const updateSlider = () => {
    const amountToMove = (currentIndex / slidesPerPage) * track.clientWidth;
    track.style.transform = "translateX(-" + amountToMove + "px)";
    dots.forEach((dot) => dot.classList.remove("active"));
    dots[currentIndex / slidesPerPage].classList.add("active");
  };

  // Bấm nút qua phải
  nextButton.addEventListener("click", (e) => {
    currentIndex += slidesPerPage;
    if (currentIndex >= slides.length) {
      currentIndex = 0;
    }
    updateSlider();
  });

  // Bấm nút qua trái
  prevButton.addEventListener("click", (e) => {
    currentIndex -= slidesPerPage;
    if (currentIndex < 0) {
      currentIndex = slides.length - slidesPerPage;
    }
    updateSlider();
  });

  // Cập nhật slider khi thay đổi kích thước cửa sổ
  window.addEventListener("resize", () => {
    slidesPerPage = window.innerWidth >= 768 ? 2 : 1;
    // Cần tính toán và cập nhật lại dots + vị trí
    // Để đơn giản, ta chỉ cập nhật vị trí
    currentIndex = 0; // Reset về slide đầu
    updateSlider();
  });
}
