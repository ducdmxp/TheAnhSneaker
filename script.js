document.addEventListener("DOMContentLoaded", () => {
  // ---------- Header: đổ bóng khi cuộn + menu mobile ----------
  const header = document.querySelector(".header");
  const nav = document.getElementById("main-nav");
  const navToggle = document.querySelector(".nav-toggle");
  const toTopBtn = document.getElementById("scrollToTopBtn");

  const onScroll = () => {
    const y = window.scrollY;
    header?.classList.toggle("scrolled", y > 10);
    if (toTopBtn) toTopBtn.hidden = y < 600;
  };
  window.addEventListener("scroll", onScroll, { passive: true });
  onScroll();

  const setNav = (open) => {
    nav?.classList.toggle("is-open", open);
    navToggle?.setAttribute("aria-expanded", String(open));
    navToggle?.setAttribute("aria-label", open ? "Đóng menu" : "Mở menu");
  };
  navToggle?.addEventListener("click", () => setNav(!nav.classList.contains("is-open")));
  nav?.querySelectorAll("a").forEach((a) => a.addEventListener("click", () => setNav(false)));
  document.addEventListener("keydown", (e) => e.key === "Escape" && setNav(false));

  toTopBtn?.addEventListener("click", () => window.scrollTo({ top: 0, behavior: "smooth" }));

  // ---------- Hiệu ứng xuất hiện khi cuộn ----------
  const revealEls = document.querySelectorAll(".reveal");
  if ("IntersectionObserver" in window) {
    const io = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add("visible");
            io.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.12, rootMargin: "0px 0px -40px 0px" }
    );
    revealEls.forEach((el) => io.observe(el));
  } else {
    revealEls.forEach((el) => el.classList.add("visible"));
  }

  // ---------- Lọc sản phẩm & Xem thêm ----------
  // Sản phẩm đã được PHP render sẵn trong HTML (tốt cho SEO); JS chỉ bật/tắt class "show".
  const grid = document.querySelector(".product-grid");
  const tabs = document.querySelectorAll(".tab-link");
  const loadMoreBtn = document.getElementById("load-more-btn");
  const perLoad = Number(grid?.dataset.perLoad) || 8;
  let filter = "all";
  let limit = perLoad;

  const cards = grid ? Array.from(grid.querySelectorAll(".product-card")) : [];

  const setEmpty = (text) => {
    let msg = grid.querySelector(".product-empty");
    if (!text) return msg?.remove();
    if (!msg) {
      msg = document.createElement("p");
      msg.className = "product-empty";
      grid.appendChild(msg);
    }
    msg.textContent = text;
  };

  const render = () => {
    const matched = cards.filter((c) => filter === "all" || c.dataset.category === filter);
    cards.forEach((c) => c.classList.remove("show"));
    matched.slice(0, limit).forEach((c) => c.classList.add("show"));
    if (cards.length) {
      setEmpty(matched.length ? "" : "Danh mục này đang về hàng. Nhắn Zalo để shop báo mẫu mới nhé!");
    }
    if (loadMoreBtn) loadMoreBtn.hidden = matched.length <= limit;
  };

  if (grid) {
    tabs.forEach((tab) =>
      tab.addEventListener("click", () => {
        tabs.forEach((t) => {
          t.classList.toggle("active", t === tab);
          t.setAttribute("aria-pressed", String(t === tab));
        });
        filter = tab.dataset.filter;
        limit = perLoad;
        render();
      })
    );
    loadMoreBtn?.addEventListener("click", () => {
      limit += perLoad;
      render();
    });
    render();
  }

  // ---------- Toast ----------
  const toast = document.querySelector(".toast");
  let toastTimer;
  const showToast = (text) => {
    if (!toast) return;
    toast.textContent = text;
    toast.classList.add("visible");
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toast.classList.remove("visible"), 3500);
  };

  // ---------- Đo lường quảng cáo (Facebook / TikTok / Google) ----------
  const T = window.TAS_TRACKING || {};
  const newEventId = () =>
    (window.crypto && crypto.randomUUID ? crypto.randomUUID() : Date.now() + "-" + Math.random().toString(16).slice(2));

  // Gửi thêm sự kiện từ server (Facebook Conversions API) với cùng event_id để Facebook khử trùng.
  const sendServerEvent = (eventName, eventId, custom) => {
    if (!T.capi || !navigator.sendBeacon) return;
    try {
      const body = JSON.stringify({ event_name: eventName, event_id: eventId, url: location.href, custom: custom || {} });
      navigator.sendBeacon(T.endpoint, new Blob([body], { type: "application/json" }));
    } catch (e) {}
  };

  // PageView server-side khớp với PageView của Pixel trong <head>
  if (T.pageViewId) sendServerEvent("PageView", T.pageViewId);

  // ---------- Ghi nhận khách đến từ quảng cáo nào (lưu 7 ngày, đọc/ghi an toàn) ----------
  const adSource = (() => {
    const q = new URLSearchParams(location.search);
    const names = { facebook: "Facebook", fb: "Facebook", ig: "Instagram", instagram: "Instagram", tiktok: "TikTok", google: "Google", zalo: "Zalo" };
    let src = null;
    if (q.get("utm_source")) {
      const raw = q.get("utm_source").toLowerCase();
      src = { name: names[raw] || q.get("utm_source"), campaign: q.get("utm_campaign") || "" };
    } else if (q.get("fbclid")) src = { name: "Facebook", campaign: "" };
    else if (q.get("ttclid")) src = { name: "TikTok", campaign: "" };
    else if (q.get("gclid") || q.get("gbraid") || q.get("wbraid")) src = { name: "Google", campaign: "" };
    try {
      if (src) {
        localStorage.setItem("tas_src", JSON.stringify({ ...src, t: Date.now() }));
        return src;
      }
      const saved = JSON.parse(localStorage.getItem("tas_src") || "null");
      if (saved && Date.now() - saved.t < 7 * 864e5) return saved;
    } catch (e) {}
    return src;
  })();

  // ---------- ViewContent: khách xem trang chi tiết sản phẩm ----------
  const viewData = (() => {
    try {
      return JSON.parse(document.body.dataset.viewContent || "null");
    } catch (e) {
      return null;
    }
  })();
  if (viewData) {
    const eventId = newEventId();
    const fbData = {
      content_ids: [viewData.id],
      content_name: viewData.name,
      content_type: "product",
      content_category: viewData.category,
      value: viewData.price,
      currency: "VND",
    };
    try {
      if (window.fbq) window.fbq("track", "ViewContent", fbData, { eventID: eventId });
    } catch (e) {}
    try {
      if (window.ttq) {
        window.ttq.track("ViewContent", {
          content_id: viewData.id, content_name: viewData.name, content_type: "product", value: viewData.price, currency: "VND",
        }, { event_id: eventId });
      }
    } catch (e) {}
    try {
      if (window.gtag) {
        window.gtag("event", "view_item", {
          currency: "VND",
          value: viewData.price,
          items: [{ item_id: viewData.id, item_name: viewData.name, item_category: viewData.category, price: viewData.price }],
        });
      }
    } catch (e) {}
    sendServerEvent("ViewContent", eventId, fbData);
  }

  // ---------- Contact: khách bấm Zalo / Gọi ----------
  const trackContact = (channel, product) => {
    const eventId = newEventId();
    const data = { content_category: channel };
    if (product) {
      Object.assign(data, {
        content_name: product.name,
        content_ids: [product.id],
        content_type: "product",
        value: product.price,
        currency: "VND",
      });
    }
    try {
      if (window.fbq) window.fbq("track", "Contact", data, { eventID: eventId });
    } catch (e) {}
    try {
      if (window.ttq) {
        window.ttq.track("Contact", product
          ? { content_id: product.id, content_name: product.name, content_type: "product", value: product.price, currency: "VND" }
          : {}, { event_id: eventId });
      }
    } catch (e) {}
    try {
      if (window.gtag) {
        window.gtag("event", "generate_lead", {
          method: channel,
          item_name: product?.name,
          value: product?.price,
          currency: "VND",
          ad_source: adSource?.name,
        });
        if (T.adsSendTo) window.gtag("event", "conversion", { send_to: T.adsSendTo });
      }
    } catch (e) {}
    sendServerEvent("Contact", eventId, data);
  };

  document.addEventListener("click", (e) => {
    const link = e.target.closest("[data-track]");
    if (!link) return;
    const channel = link.dataset.track;
    const product = link.dataset.product
      ? { name: link.dataset.product, id: link.dataset.productId, price: Number(link.dataset.price) || 0 }
      : null;

    trackContact(channel, product);

    // Tự sao chép tin nhắn đặt hàng (kèm nguồn quảng cáo nếu có) để khách dán vào Zalo.
    if (channel !== "zalo" || !navigator.clipboard || (!product && !adSource)) return;
    const from = adSource
      ? ` mình xem quảng cáo trên ${adSource.name}${adSource.campaign ? " [" + adSource.campaign + "]" : ""},`
      : "";
    let msg;
    if (product) {
      const price = product.price ? new Intl.NumberFormat("vi-VN").format(product.price) + "đ" : "";
      msg = `Shop ơi,${from} muốn đặt mẫu: ${product.name}${price ? " (" + price + ")" : ""}. Size: `;
    } else {
      msg = `Shop ơi,${from} cần tư vấn chọn giày ạ.`;
    }
    navigator.clipboard
      .writeText(msg)
      .then(() => showToast(product
        ? "Đã sao chép tên mẫu, dán vào Zalo kèm size để đặt nhanh nhé!"
        : "Đã sao chép lời nhắn, dán vào Zalo để shop tư vấn nhanh nhé!"))
      .catch(() => {});
  });
});
