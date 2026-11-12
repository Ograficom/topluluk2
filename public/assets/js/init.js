/*!
 * Repost — library setup
 * Version: 3.0
 * Developer: HideDev — https://hidedev.com
 * Support: https://t.me/hidedevsupport
 */

// Image gallery
function setMediaLoading(el, isLoading) {
  const media = el?.closest?.(".post__media, .post__media-video");

  if (!media) return;

  media.classList.toggle("is-media-loading", isLoading);
}

function setPostMediaSlideOrientation(carousel) {
  const images = carousel.querySelectorAll(".post__media-image");

  images.forEach((image) => {
    const setOrientation = () => {
      const slide = image.closest(".f-carousel__slide");

      if (!slide || !image.naturalWidth || !image.naturalHeight) return;

      const isVertical = image.naturalHeight > image.naturalWidth;
      slide.classList.toggle("is-vertical", isVertical);
      slide.classList.toggle("is-horizontal", !isVertical);

      if (carousel.hasAttribute("data-paired-carousel")) {
        // CSS owns the paired layout widths so desktop stays two-up and mobile stays swipeable.
        return;
      }

      if (isVertical) {
        const carouselHeight = carousel.getBoundingClientRect().height || 340;
        const slideWidth = Math.round(
          (carouselHeight * image.naturalWidth) / image.naturalHeight,
        );
        slide.style.setProperty("--f-carousel-slide-width", `${slideWidth}px`);
      } else {
        slide.style.setProperty("--f-carousel-slide-width", "100%");
      }
    };

    if (image.complete) {
      setOrientation();
    } else {
      image.addEventListener("load", setOrientation, { once: true });
    }
  });
}
function initPlayerSwipeGuards(carousel) {
  carousel.querySelectorAll(".post__media-player-slide").forEach((slide) => {
    slide.style.setProperty("--f-carousel-slide-width", "100%");
    slide.style.height = "100%";
    slide.style.minHeight = "0";
    slide.style.maxHeight = "none";

    if (slide.querySelector(".post__media-player-guard")) return;

    const guard = document.createElement("div");
    guard.className = "post__media-player-guard";
    guard.setAttribute("aria-hidden", "true");
    slide.appendChild(guard);

    let startX = 0;
    let startY = 0;
    let isDragging = false;

    guard.addEventListener("pointerdown", (event) => {
      startX = event.clientX;
      startY = event.clientY;
      isDragging = false;
      guard.classList.remove("is-hidden");
      guard.setPointerCapture?.(event.pointerId);
    });

    guard.addEventListener("pointermove", (event) => {
      if (
        Math.abs(event.clientX - startX) > 8 ||
        Math.abs(event.clientY - startY) > 8
      ) {
        isDragging = true;
      }
    });

    guard.addEventListener("click", (event) => {
      event.preventDefault();
      event.stopPropagation();

      if (isDragging) return;

      guard.classList.add("is-hidden");

      const target = document.elementFromPoint(event.clientX, event.clientY);
      target?.dispatchEvent(
        new MouseEvent("click", {
          bubbles: true,
          cancelable: true,
          clientX: event.clientX,
          clientY: event.clientY,
        }),
      );
    });
  });
}

function fitPlayerjsElement(el) {
  el.style.width = "100%";
  el.style.height = "100%";
  el.style.minHeight = "0";
  el.style.maxHeight = "none";

  el.querySelectorAll("iframe, video, object, embed").forEach((media) => {
    media.style.width = "100%";
    media.style.height = "100%";
    media.style.minHeight = "0";
    media.style.maxHeight = "none";
  });

  [...el.children].forEach((child) => {
    child.style.width = "100%";
    child.style.height = "100%";
    child.style.minHeight = "0";
    child.style.maxHeight = "none";
  });
}

function hideMediaLoader(el) {
  window.setTimeout(() => setMediaLoading(el, false), 250);
}

function resetPlayerSwipeGuards(carousel) {
  carousel.querySelectorAll(".post__media-player-guard").forEach((guard) => {
    guard.classList.remove("is-hidden");
  });
}

function initFancyCarousels() {
  if (typeof Carousel === "undefined") return;

  document.querySelectorAll("[data-fancy-carousel]").forEach((carousel) => {
    if (carousel.dataset.fancyCarouselInit) return;

    carousel.dataset.fancyCarouselInit = "pending";

    setPostMediaSlideOrientation(carousel);
    initPlayerSwipeGuards(carousel);

    const carouselInstance = Carousel(carousel, {
      center: false,
      fill: false,
      infinite: false,
      slidesPerPage: "auto",
      transition: "tween",
      tween: {
        friction: 20,
        tension: 180,
      },
    });

    carouselInstance.on("change", () => resetPlayerSwipeGuards(carousel));
    carouselInstance.init();

    // Repost media navigation controls are rendered in _media.blade.php.
    // Connect their explicit buttons to the Fancyapps Carousel instance.
    carousel.querySelectorAll("[data-carousel-go-prev]").forEach((button) => {
      if (button.dataset.carouselControlInit) return;
      button.addEventListener("click", (event) => {
        event.preventDefault();
        event.stopPropagation();
        carouselInstance.prev();
      });
      button.dataset.carouselControlInit = "true";
    });

    carousel.querySelectorAll("[data-carousel-go-next]").forEach((button) => {
      if (button.dataset.carouselControlInit) return;
      button.addEventListener("click", (event) => {
        event.preventDefault();
        event.stopPropagation();
        carouselInstance.next();
      });
      button.dataset.carouselControlInit = "true";
    });

    carousel.dataset.fancyCarouselInit = "true";

    if (!carousel.querySelector("[data-player-url]")) {
      hideMediaLoader(carousel);
    }
  });
}

function initFancybox() {
  if (typeof Fancybox === "undefined" || document.body.dataset.fancyboxInit)
    return;

  Fancybox.bind("[data-fancybox]");
  document.body.dataset.fancyboxInit = "true";
}

function initFancyapp() {
  initFancyCarousels();
  initFancybox();
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initFancyapp);
} else {
  initFancyapp();
}

// Bootstrap popovers
const popoverTriggerList = document.querySelectorAll(
  '[data-bs-toggle="popover"]',
);
const popoverList =
  typeof bootstrap !== "undefined" && bootstrap.Popover
    ? [...popoverTriggerList].map(
        (popoverTriggerEl) => new bootstrap.Popover(popoverTriggerEl),
      )
    : [];

// Bootstrap tooltips
const tooltipTriggerList = document.querySelectorAll(
  '[data-bs-toggle="tooltip"]',
);
const tooltipList =
  typeof bootstrap !== "undefined" && bootstrap.Tooltip
    ? [...tooltipTriggerList].map(
        (tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl),
      )
    : [];

// PlayerJS
let playerjsLoader;

function loadPlayerjs(src) {
  if (typeof Playerjs !== "undefined") {
    return Promise.resolve();
  }

  if (!src) {
    return Promise.reject(new Error("Playerjs script URL is not set"));
  }

  if (playerjsLoader) return playerjsLoader;

  playerjsLoader = new Promise((resolve, reject) => {
    const script = document.createElement("script");

    script.src = src;
    script.async = true;
    script.dataset.playerjsLoader = "";
    script.addEventListener(
      "load",
      () =>
        typeof Playerjs !== "undefined"
          ? resolve()
          : reject(new Error("Playerjs is unavailable")),
      { once: true },
    );
    script.addEventListener(
      "error",
      () => reject(new Error("Playerjs failed to load")),
      { once: true },
    );
    document.head.append(script);
  });

  return playerjsLoader;
}

async function initPlayerjs() {
  const players = Array.from(document.querySelectorAll("[data-player-url]"));

  if (!players.length) return;

  try {
    await loadPlayerjs(players[0].dataset.playerScript);
  } catch (error) {
    console.error(error);
    players.forEach((el) => hideMediaLoader(el));
    return;
  }

  players.forEach((el) => {
    const url = (el.dataset.playerUrl || "").trim();

    if (!url || el.dataset.playerInit) return;

    if (!el.id) {
      el.id = `playerjs-${Math.random().toString(36).slice(2)}`;
    }

    new Playerjs({ id: el.id, file: url, width: "100%", height: "100%" });
    fitPlayerjsElement(el);
    requestAnimationFrame(() => fitPlayerjsElement(el));
    window.setTimeout(() => {
      fitPlayerjsElement(el);
      hideMediaLoader(el);
    }, 300);
    el.dataset.playerInit = "true";
  });
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initPlayerjs);
} else {
  initPlayerjs();
}

// Select2
if (typeof jQuery !== "undefined" && jQuery.fn?.select2) {
  jQuery(".js-addpost-categories").each(function () {
    const $categorySelect = jQuery(this);

    if ($categorySelect.hasClass("select2-hidden-accessible")) return;

    $categorySelect.select2({
      width: "auto",
      multiple: true,
      maximumSelectionLength:
        Number($categorySelect.data("maximum-selection-length")) || 3,
      closeOnSelect: false,
      placeholder: "Select a channel",
      templateResult: formatCategory,
      templateSelection: formatCategorySelection,
      escapeMarkup: (markup) => markup,
      language: {
        noResults: () => "No channels found",
        searching: () => "Searching…",
        maximumSelected: ({ maximum }) =>
          `You can select up to ${maximum} categories`,
        removeItem: () => "Remove",
        removeAllItems: () => "Remove all",
      },
    });

    const syncCategorySearchWidth = () => {
      const container = $categorySelect.next(".select2-container")[0];
      const searchField = container?.querySelector(".select2-search__field");
      const searchWrapper = container?.querySelector(".select2-search--inline");
      const hasSelection = ($categorySelect.val() || []).length > 0;

      if (!searchField || !searchWrapper) return;

      searchField.style.setProperty(
        "width",
        hasSelection ? "0px" : "103px",
        "important",
      );
      searchField.style.setProperty(
        "min-width",
        hasSelection ? "0px" : "103px",
        "important",
      );
      searchWrapper.style.setProperty("width", "auto", "important");
      searchWrapper.style.setProperty("min-width", "0px", "important");
    };

    $categorySelect.on(
      "change.addpostCategories select2:open select2:close",
      () => {
        window.requestAnimationFrame(syncCategorySearchWidth);
      },
    );

    syncCategorySearchWidth();
  });
}

function formatCategory(data) {
  if (!data.id) return data.text;

  if (data.id === "0") {
    return `<div class="addpost__category-blog">My blog</div>`;
  }

  const $el = jQuery(data.element);
  const icon = $el.data("icon");
  const count = $el.data("count");
  const name = data.text;

  return `
<div class="addpost__category">
${
  icon
    ? `<img src="${icon}" alt="${name}">`
    : `<span class="addpost__category-placeholder" aria-hidden="true">
           <svg width="16" height="16">
               <use href="#image"></use>
           </svg>
       </span>`
}
<div class="addpost__category-info">
<span>${name}</span>
<p>${count} posts</p>
</div>
</div>`;
}

function formatCategorySelection(data) {
  if (data.id === "0") return "My blog";

  return data.text;
}

// Tagify
const tagsInput = document.querySelector("#tags");
if (tagsInput && typeof Tagify !== "undefined") {
  new Tagify(tagsInput, {
    delimiters: null,
    originalInputValueFormat: (valuesArr) =>
      valuesArr.map((item) => item.value).join(","),
  });
}
