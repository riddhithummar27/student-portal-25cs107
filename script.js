/* =========================================================
   StudentHub Portal — shared script.js
   Handles: active nav link, hamburger menu, dark/light theme
   (localStorage), notification banner, login form, notices
   accordion, payment modal, contact form, profile form.
   ========================================================= */

function setActiveNav() {
  const links = document.querySelectorAll(".nav-links a");
  const current = window.location.pathname.split("/").pop() || "dashboard.html";
  links.forEach((link) => {
    if (link.getAttribute("href") === current) {
      link.classList.add("active");
    }
  });
}

function initTheme() {
  const btn = document.getElementById("themeToggle");
  const saved = localStorage.getItem("studenthub-theme");
  if (saved === "dark") {
    document.documentElement.setAttribute("data-theme", "dark");
  }
  if (btn) {
    btn.textContent = saved === "dark" ? "☀️" : "🌙";
    btn.addEventListener("click", () => {
      const isDark = document.documentElement.getAttribute("data-theme") === "dark";
      if (isDark) {
        document.documentElement.removeAttribute("data-theme");
        localStorage.setItem("studenthub-theme", "light");
        btn.textContent = "🌙";
      } else {
        document.documentElement.setAttribute("data-theme", "dark");
        localStorage.setItem("studenthub-theme", "dark");
        btn.textContent = "☀️";
      }
    });
  }
}

function initHamburger() {
  const btn = document.getElementById("hamburger");
  const nav = document.getElementById("navLinks");
  if (btn && nav) {
    btn.addEventListener("click", () => {
      const isOpen = nav.classList.toggle("open");
      btn.classList.toggle("active");
      btn.setAttribute("aria-expanded", String(isOpen));
    });
  }
}

function initBanner() {
  const banner = document.getElementById("notifBanner");
  const closeBtn = document.getElementById("bannerClose");
  if (banner && closeBtn) {
    if (localStorage.getItem("studenthub-banner-dismissed") === "true") {
      banner.style.display = "none";
    }
    closeBtn.addEventListener("click", () => {
      banner.style.display = "none";
      localStorage.setItem("studenthub-banner-dismissed", "true");
    });
  }
}

function initLoginForm() {
  const form = document.getElementById("loginForm");
  if (form) {
    form.addEventListener("submit", (e) => {
      e.preventDefault();
      const user = document.getElementById("username").value.trim();
      const pass = document.getElementById("password").value.trim();
      const error = document.getElementById("loginError");
      if (!user || !pass) {
        error.textContent = "Please enter both username and password.";
        error.style.display = "block";
        return;
      }
      error.style.display = "none";
      window.location.href = "dashboard.html";
    });
  }
}

function initAccordion() {
  const headers = document.querySelectorAll(".accordion-header");
  headers.forEach((header) => {
    header.addEventListener("click", () => {
      const item = header.parentElement;
      const wasOpen = item.classList.contains("open");
      document.querySelectorAll(".accordion-item").forEach((i) => i.classList.remove("open"));
      if (!wasOpen) item.classList.add("open");
    });
  });
}

function initPaymentModal() {
  const modal = document.getElementById("paymentModal");
  if (!modal) return;
  const openBtns = document.querySelectorAll(".pay-now-btn");
  const closeBtn = document.getElementById("modalClose");
  const confirmBtn = document.getElementById("modalConfirm");
  const modalAmount = document.getElementById("modalAmount");

  openBtns.forEach((btn) => {
    btn.addEventListener("click", () => {
      if (modalAmount) modalAmount.textContent = btn.getAttribute("data-amount") || "";
      modal.classList.add("show");
    });
  });
  if (closeBtn) closeBtn.addEventListener("click", () => modal.classList.remove("show"));
  modal.addEventListener("click", (e) => {
    if (e.target === modal) modal.classList.remove("show");
  });
  if (confirmBtn) {
    confirmBtn.addEventListener("click", () => {
      modal.classList.remove("show");
      alert("Payment recorded successfully (demo only — no real transaction).");
    });
  }
}

function initContactForm() {
  const form = document.getElementById("contactForm");
  const success = document.getElementById("contactSuccess");
  if (form) {
    form.addEventListener("submit", (e) => {
      e.preventDefault();
      success.style.display = "block";
      form.reset();
      setTimeout(() => (success.style.display = "none"), 4000);
    });
  }
}

function initProfileForm() {
  const form = document.getElementById("profileForm");
  const success = document.getElementById("profileSuccess");
  if (form) {
    form.addEventListener("submit", (e) => {
      e.preventDefault();
      success.style.display = "block";
      setTimeout(() => (success.style.display = "none"), 3000);
    });
  }
}

document.addEventListener("DOMContentLoaded", () => {
  initTheme();
  initHamburger();
  setActiveNav();
  initBanner();
  initLoginForm();
  initAccordion();
  initPaymentModal();
  initContactForm();
  initProfileForm();
});