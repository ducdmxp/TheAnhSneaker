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
  const tabLinks = document.querySelectorAll(".tab-link");
  const productCards = document.querySelectorAll(".product-card");
  const loadMoreBtn = document.getElementById("load-more-btn");
  const itemsPerLoad = 8;
  let currentFilter = "all";

  function filterAndShowProducts() {
    let visibleItems = 0;
    productCards.forEach((card) => {
      const category = card.getAttribute("data-category");
      const matchesFilter =
        currentFilter === "all" || category === currentFilter;
      card.style.display = "none";
      card.classList.remove("show");
      if (matchesFilter) {
        if (visibleItems < itemsPerLoad) {
          card.style.display = "block";
          card.classList.add("show");
          visibleItems++;
        }
      }
    });
    updateLoadMoreButton();
  }

  function loadMoreProducts() {
    let newlyShownItems = 0;
    productCards.forEach((card) => {
      const category = card.getAttribute("data-category");
      const matchesFilter =
        currentFilter === "all" || category === currentFilter;
      if (card.style.display === "none" && matchesFilter) {
        if (newlyShownItems < itemsPerLoad) {
          card.style.display = "block";
          card.classList.add("show");
          newlyShownItems++;
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
    loadMoreBtn.style.display = hiddenCount > 0 ? "block" : "none";
  }

  tabLinks.forEach((tab) => {
    tab.addEventListener("click", () => {
      tabLinks.forEach((link) => link.classList.remove("active"));
      tab.classList.add("active");
      currentFilter = tab.getAttribute("data-filter");
      filterAndShowProducts();
    });
  });

  loadMoreBtn.addEventListener("click", loadMoreProducts);
  filterAndShowProducts();
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
