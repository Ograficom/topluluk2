/*!
 * Repost — main interface scripts
 * Version: 3.0
 * Developer: HideDev — https://hidedev.com
 * Support: https://t.me/hidedevsupport
 */

// Theme
(() => {
  const STORAGE_KEY = "theme";
  const THEMES = {
    light: "light",
    dark: "dark",
  };

  const root = document.documentElement;
  const prefersDark = window.matchMedia?.("(prefers-color-scheme: dark)");

  function readStoredTheme() {
    try {
      const theme = localStorage.getItem(STORAGE_KEY);
      return theme === THEMES.dark || theme === THEMES.light ? theme : null;
    } catch {
      return null;
    }
  }

  function writeStoredTheme(theme) {
    try {
      localStorage.setItem(STORAGE_KEY, theme);
    } catch {}
  }

  function getSystemTheme() {
    return prefersDark?.matches ? THEMES.dark : THEMES.light;
  }

  function getCurrentTheme() {
    return root.dataset.theme === THEMES.dark ? THEMES.dark : THEMES.light;
  }

  function updateSwitchers(theme = getCurrentTheme()) {
    document.querySelectorAll("[data-theme-toggle]").forEach((button) => {
      const isDark = theme === THEMES.dark;

      button.classList.toggle("is-active", isDark);
      button.setAttribute("aria-pressed", String(isDark));
      button.dataset.themeCurrent = theme;
    });
  }

  function applyTheme(theme, shouldStore = false) {
    const nextTheme = theme === THEMES.dark ? THEMES.dark : THEMES.light;

    root.dataset.theme = nextTheme;
    root.dataset.bsTheme = nextTheme;
    root.style.colorScheme = nextTheme;

    if (shouldStore) {
      writeStoredTheme(nextTheme);
    }

    updateSwitchers(nextTheme);
    syncEditorTheme(nextTheme);
  }

  function syncEditorTheme(theme = getCurrentTheme()) {
    const editorStyles = document.getElementById("editor-theme-styles");

    document.querySelectorAll(".tox-edit-area iframe").forEach((iframe) => {
      try {
        const frameDocument = iframe.contentDocument;
        if (!frameDocument?.documentElement) return;

        frameDocument.documentElement.dataset.editorTheme = theme;

        if (
          editorStyles &&
          !frameDocument.getElementById("editor-theme-styles")
        ) {
          frameDocument.head?.append(editorStyles.cloneNode());
        }
      } catch {}
    });
  }

  function toggleTheme() {
    applyTheme(
      getCurrentTheme() === THEMES.dark ? THEMES.light : THEMES.dark,
      true,
    );
  }

  function initThemeSwitchers() {
    document.querySelectorAll("[data-theme-toggle]").forEach((button) => {
      button.addEventListener("click", toggleTheme);
    });

    updateSwitchers();
  }

  applyTheme(readStoredTheme() || getSystemTheme());

  prefersDark?.addEventListener?.("change", () => {
    if (!readStoredTheme()) {
      applyTheme(getSystemTheme());
    }
  });

  window.addEventListener("storage", (event) => {
    if (event.key === STORAGE_KEY) {
      applyTheme(readStoredTheme() || getSystemTheme());
    }
  });

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initThemeSwitchers);
  } else {
    initThemeSwitchers();
  }

  new MutationObserver(() => syncEditorTheme()).observe(document.body, {
    childList: true,
    subtree: true,
  });
})();

// Fix the icon on closed DLE spoilers.
(() => {
  const CLOSED_PATH =
    "M17.418 6.109c0.272-0.268 0.709-0.268 0.979 0s0.271 0.701 0 0.969l-7.908 7.83c-0.27 0.268-0.707 0.268-0.979 0l-7.908-7.83c-0.27-0.268-0.27-0.701 0-0.969s0.709-0.268 0.979 0l7.419 7.141 7.418-7.141z";

  function syncClosedSpoilers(root = document) {
    root.querySelectorAll?.(".dle-spoiler").forEach((spoiler) => {
      const content = spoiler.querySelector(".text_spoiler");
      const iconPath = spoiler.querySelector(".spoiler-btn-svg path");

      if (content && iconPath && getComputedStyle(content).display === "none") {
        iconPath.setAttribute("d", CLOSED_PATH);
      }
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", () => syncClosedSpoilers(), {
      once: true,
    });
  } else {
    syncClosedSpoilers();
  }
})();

// Keep Bootstrap modals above the backdrop.
document.addEventListener("show.bs.modal", (event) => {
  const modal = event.target;

  if (!(modal instanceof HTMLElement) || !modal.classList.contains("modal"))
    return;

  if (!modal.closest("form") && modal.parentElement !== document.body) {
    document.body.append(modal);
  }
});

// Fast search
(() => {
  const GAP = 10;
  let frame = 0;

  function getSearchForm() {
    return document.querySelector("#searchModal .search__form");
  }

  function getSuggestions() {
    return document.getElementById("searchsuggestions");
  }

  function alignSuggestions() {
    frame = 0;

    const form = getSearchForm();
    const suggestions = getSuggestions();

    if (!form || !suggestions) return;

    const rect = form.getBoundingClientRect();
    const width = Math.round(rect.width);
    const left = Math.round(rect.left);
    const top = Math.round(rect.bottom + GAP);
    const maxHeight = Math.max(160, Math.floor(window.innerHeight - top - 16));
    const isNotFound =
      suggestions.querySelector(".notfound") &&
      !suggestions.querySelector(".searchhit");

    suggestions.classList.toggle("is-notfound", Boolean(isNotFound));

    suggestions.style.setProperty("position", "fixed", "important");
    suggestions.style.setProperty("left", `${left}px`, "important");
    suggestions.style.setProperty("right", "auto", "important");
    suggestions.style.setProperty("top", `${top}px`, "important");
    suggestions.style.setProperty("width", `${width}px`, "important");
    suggestions.style.setProperty("max-width", `${width}px`, "important");
    suggestions.style.setProperty("max-height", `${maxHeight}px`, "important");
  }

  function scheduleAlign() {
    if (frame) {
      cancelAnimationFrame(frame);
    }

    frame = requestAnimationFrame(alignSuggestions);
  }

  function initFastSearchSuggestions() {
    const input = document.getElementById("story");
    const modal = document.getElementById("searchModal");
    const form = getSearchForm();

    if (!input || !modal || !form) return;

    const demoResults = document.querySelector("[data-demo-search-results]");
    const updateDemoResults = () => {
      if (!demoResults) return;

      demoResults.hidden = input.value.trim().length === 0;
      scheduleAlign();
    };

    input?.addEventListener("input", () => {
      updateDemoResults();
      scheduleAlign();
      window.setTimeout(scheduleAlign, 80);
      window.setTimeout(scheduleAlign, 220);
    });

    modal.addEventListener("modal:open", () => {
      input?.focus();
      updateDemoResults();
      scheduleAlign();
    });

    modal.addEventListener("modal:close", () => {
      if (demoResults) demoResults.hidden = true;
    });

    window.addEventListener("resize", scheduleAlign, { passive: true });
    window.addEventListener("scroll", scheduleAlign, { passive: true });

    if ("ResizeObserver" in window) {
      new ResizeObserver(scheduleAlign).observe(form);
    }

    new MutationObserver((mutations) => {
      const hasSearchSuggestionsChange = mutations.some((mutation) => {
        if (mutation.target?.id === "searchsuggestions") return true;

        return [...mutation.addedNodes].some((node) => {
          return (
            node.nodeType === 1 &&
            (node.id === "searchsuggestions" ||
              node.querySelector?.("#searchsuggestions"))
          );
        });
      });

      if (hasSearchSuggestionsChange) {
        scheduleAlign();
      }
    }).observe(document.body, {
      childList: true,
      subtree: true,
    });

    updateDemoResults();
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initFastSearchSuggestions);
  } else {
    initFastSearchSuggestions();
  }
})();

// Search controls
(() => {
  function initSearchControls() {
    const modal = document.getElementById("searchModal");
    const form = document.getElementById("q_search");
    const input = document.getElementById("story");
    const clearButton = modal?.querySelector("[data-search-clear]");
    const voiceButton = modal?.querySelector("[data-search-voice]");

    if (
      !modal ||
      !form ||
      !input ||
      !clearButton ||
      !voiceButton ||
      form.dataset.searchControlsInit
    )
      return;

    const SpeechRecognition =
      window.SpeechRecognition || window.webkitSpeechRecognition;
    let recognition = null;
    let initialValue = "";

    form.dataset.searchControlsInit = "true";

    const updateClearButton = () => {
      clearButton.hidden = input.value.length === 0;
    };

    const notifyInputChanged = () => {
      input.dispatchEvent(new Event("input", { bubbles: true }));
      input.dispatchEvent(new Event("change", { bubbles: true }));
      input.dispatchEvent(new KeyboardEvent("keyup", { bubbles: true }));
    };

    const clearSearch = (shouldFocus = false) => {
      form.reset();
      updateClearButton();
      notifyInputChanged();

      if (shouldFocus) input.focus();
    };

    const setListening = (isListening) => {
      voiceButton.classList.toggle("is-listening", isListening);
      voiceButton.setAttribute("aria-pressed", String(isListening));
      voiceButton.setAttribute(
        "aria-label",
        isListening ? "Sesli aramayı durdur" : "Sesli arama",
      );
    };

    const stopRecognition = (abort = false) => {
      if (!recognition || voiceButton.getAttribute("aria-pressed") !== "true")
        return;

      if (abort) {
        recognition.abort();
      } else {
        recognition.stop();
      }
    };

    input.addEventListener("input", updateClearButton);

    clearButton.addEventListener("click", () => {
      stopRecognition(true);
      clearSearch(true);
    });

    if (SpeechRecognition) {
      recognition = new SpeechRecognition();
      recognition.lang = "tr-TR";
      recognition.continuous = false;
      recognition.interimResults = true;

      recognition.addEventListener("start", () => {
        setListening(true);
      });

      recognition.addEventListener("result", (event) => {
        let transcript = "";
        let hasFinalResult = false;

        for (let index = 0; index < event.results.length; index++) {
          transcript += event.results[index][0].transcript;
          hasFinalResult ||= event.results[index].isFinal;
        }

        input.value = [initialValue, transcript.trim()]
          .filter(Boolean)
          .join(" ");
        updateClearButton();

        if (hasFinalResult) {
          notifyInputChanged();
          recognition.stop();
        }
      });

      recognition.addEventListener("end", () => setListening(false));
      recognition.addEventListener("error", () => setListening(false));

      voiceButton.addEventListener("click", () => {
        if (voiceButton.getAttribute("aria-pressed") === "true") {
          stopRecognition();
          return;
        }

        initialValue = input.value.trim();
        setListening(true);

        try {
          recognition.start();
        } catch {
          setListening(false);
        }
      });
    } else {
      voiceButton.hidden = true;
    }

    modal.addEventListener("modal:close", () => {
      stopRecognition(true);
      setListening(false);
      clearSearch();
    });

    updateClearButton();
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initSearchControls);
  } else {
    initSearchControls();
  }
})();

// Header tabs
(() => {
  function moveIndicator(tabs, link) {
    if (!tabs || !link) return;

    tabs.style.setProperty("--header-tabs-x", `${link.offsetLeft}px`);
    tabs.style.setProperty("--header-tabs-width", `${link.offsetWidth}px`);
    tabs.classList.add("is-ready");

    tabs.querySelectorAll("a.is-current").forEach((currentLink) => {
      currentLink.classList.remove("is-current");
    });
    link.classList.add("is-current");
  }

  function initHeaderTabs() {
    document.querySelectorAll(".header__tabs").forEach((tabs) => {
      const links = Array.from(tabs.querySelectorAll("a"));

      if (!links.length) return;

      const getActiveLink = () => tabs.querySelector("a.active") || links[0];
      const resetIndicator = () => moveIndicator(tabs, getActiveLink());

      moveIndicator(tabs, getActiveLink());

      links.forEach((link) => {
        link.addEventListener("pointerenter", () => moveIndicator(tabs, link));
        link.addEventListener("focus", () => moveIndicator(tabs, link));
      });

      tabs.addEventListener("pointerleave", resetIndicator);
      tabs.addEventListener("focusout", () => {
        window.setTimeout(() => {
          if (!tabs.contains(document.activeElement)) resetIndicator();
        }, 0);
      });

      window.addEventListener("resize", () =>
        moveIndicator(tabs, getActiveLink()),
      );
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initHeaderTabs);
  } else {
    initHeaderTabs();
  }
})();

// Profile links
(() => {
  const PROFILE_LINK_SELECTOR = 'a[onclick*="ShowProfile"]';

  function makeProfileLinksDirect(root = document) {
    if (
      root.nodeType === Node.ELEMENT_NODE &&
      root.matches(PROFILE_LINK_SELECTOR)
    ) {
      root.removeAttribute("onclick");
    }

    root.querySelectorAll?.(PROFILE_LINK_SELECTOR).forEach((link) => {
      link.removeAttribute("onclick");
    });
  }

  function initDirectProfileLinks() {
    makeProfileLinksDirect();

    new MutationObserver((mutations) => {
      mutations.forEach((mutation) => {
        mutation.addedNodes.forEach(makeProfileLinksDirect);
      });
    }).observe(document.body, {
      childList: true,
      subtree: true,
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initDirectProfileLinks);
  } else {
    initDirectProfileLinks();
  }
})();

// Header loader
(() => {
  const STANDARD_LOADER_SELECTOR = "#loading-layer, #loader";
  let rafId = null;

  function getHeaderLoader() {
    return document.querySelector(".header__loader");
  }

  function isElementHidden(el) {
    return (
      !el ||
      el.hidden ||
      el.style.display === "none" ||
      el.style.visibility === "hidden" ||
      el.style.opacity === "0" ||
      el.classList.contains("d-none") ||
      el.getAttribute("aria-hidden") === "true"
    );
  }

  function isStandardLoaderActive() {
    const loader = document.querySelector(STANDARD_LOADER_SELECTOR);

    return Boolean(loader && !isElementHidden(loader));
  }

  function setHeaderLoader(isActive) {
    getHeaderLoader()?.classList.toggle("is-active", isActive);
  }

  function syncHeaderLoader() {
    setHeaderLoader(isStandardLoaderActive());
  }

  function scheduleSync() {
    window.cancelAnimationFrame(rafId);
    rafId = window.requestAnimationFrame(syncHeaderLoader);
  }

  function wrapStandardLoaderMethods() {
    if (
      typeof window.ShowLoading === "function" &&
      !window.ShowLoading.headerLoaderWrapped
    ) {
      const showLoading = window.ShowLoading;

      window.ShowLoading = function (...args) {
        setHeaderLoader(true);
        return showLoading.apply(this, args);
      };
      window.ShowLoading.headerLoaderWrapped = true;
    }

    if (
      typeof window.HideLoading === "function" &&
      !window.HideLoading.headerLoaderWrapped
    ) {
      const hideLoading = window.HideLoading;

      window.HideLoading = function (...args) {
        const result = hideLoading.apply(this, args);
        scheduleSync();
        return result;
      };
      window.HideLoading.headerLoaderWrapped = true;
    }
  }

  function initHeaderLoader() {
    if (!getHeaderLoader()) return;

    wrapStandardLoaderMethods();
    syncHeaderLoader();

    const observer = new MutationObserver(scheduleSync);
    observer.observe(document.body, {
      childList: true,
      subtree: true,
      attributes: true,
      attributeFilter: ["class", "style", "hidden", "aria-hidden"],
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initHeaderLoader);
  } else {
    initHeaderLoader();
  }
})();

// Private messages layout
(() => {
  let frame = 0;

  function updatePmFooter(card) {
    const footer = card.querySelector(".pm-unread-filter");

    if (!footer) {
      card.classList.remove("is-footer-floating");
      return;
    }

    const icon = footer.querySelector(".pm-unread-filter__label use");

    if (icon?.getAttribute("href") !== "#message")
      icon?.setAttribute("href", "#message");

    const cardRect = card.getBoundingClientRect();
    const footerRect = footer.getBoundingClientRect();
    const isAtViewportBottom =
      Math.abs(footerRect.bottom - window.innerHeight) <= 2;
    const hasContentBelowViewport = cardRect.bottom > window.innerHeight + 2;

    card.classList.toggle(
      "is-footer-floating",
      isAtViewportBottom && hasContentBelowViewport,
    );
  }

  function initStickyPmLayout() {
    const cards = document.querySelectorAll(".pm");

    if (!cards.length) return;

    const updateAll = () => {
      frame = 0;
      cards.forEach(updatePmFooter);
    };
    const scheduleUpdate = () => {
      if (frame) return;
      frame = requestAnimationFrame(updateAll);
    };

    updateAll();
    window.addEventListener("scroll", scheduleUpdate, { passive: true });
    window.addEventListener("resize", scheduleUpdate);

    if ("ResizeObserver" in window) {
      const observer = new ResizeObserver(scheduleUpdate);
      cards.forEach((card) => observer.observe(card));
    }

    const mutationObserver = new MutationObserver(scheduleUpdate);
    cards.forEach((card) =>
      mutationObserver.observe(card, { childList: true, subtree: true }),
    );
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initStickyPmLayout);
  } else {
    initStickyPmLayout();
  }
})();

// Sticky widgets
(() => {
  const sidebar = document.querySelector("main .widgets");

  if (!sidebar) return;

  let frame = 0;

  function updateStickyPosition() {
    frame = 0;

    const sidebarHeight = sidebar.getBoundingClientRect().height;
    const viewportHeight = window.innerHeight;

    if (!sidebarHeight) return;

    sidebar.style.position = "sticky";
    sidebar.style.top =
      sidebarHeight > viewportHeight
        ? `${Math.round(viewportHeight - sidebarHeight)}px`
        : "calc(var(--header-height) + var(--main-gap))";
  }

  function scheduleUpdate() {
    if (frame) return;
    frame = requestAnimationFrame(updateStickyPosition);
  }

  window.addEventListener("resize", scheduleUpdate);
  window.addEventListener("load", scheduleUpdate, { once: true });

  if ("ResizeObserver" in window) {
    new ResizeObserver(scheduleUpdate).observe(sidebar);
  }

  scheduleUpdate();
})();

// Sensitive content
document.addEventListener("click", (event) => {
  const btn = event.target.closest("[data-censored-toggle]");

  if (!btn) return;

  const key = btn.dataset.censoredToggle;
  const censored = document.querySelector(`[data-censored="${key}"]`);

  censored?.remove();
});

// Hero digest
function initHeroDigestLoader() {
  document.querySelectorAll(".hero__digest").forEach((digest) => {
    window.requestAnimationFrame(() => {
      digest.classList.add("is-hero-loaded");
    });
  });
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initHeroDigestLoader);
} else {
  initHeroDigestLoader();
}

// Textarea autosize
(() => {
  let resizeFrame = 0;

  function resizeTextarea(textarea) {
    if (!(textarea instanceof HTMLTextAreaElement)) return;

    textarea.style.height = "0px";

    const minHeight =
      Number.parseFloat(window.getComputedStyle(textarea).minHeight) || 0;
    textarea.style.height = `${Math.max(minHeight, textarea.scrollHeight)}px`;
  }

  function resizeTextareas(scope = document) {
    if (scope instanceof HTMLTextAreaElement) resizeTextarea(scope);
    scope.querySelectorAll?.("textarea").forEach(resizeTextarea);
  }

  function scheduleResize(scope = document) {
    cancelAnimationFrame(resizeFrame);
    resizeFrame = requestAnimationFrame(() => {
      resizeFrame = 0;
      resizeTextareas(scope);
    });
  }

  function initTextareaAutosize() {
    resizeTextareas();

    new MutationObserver((mutations) => {
      mutations.forEach((mutation) => {
        mutation.addedNodes.forEach((node) => {
          if (node instanceof HTMLElement) resizeTextareas(node);
        });
      });
    }).observe(document.body, { childList: true, subtree: true });
  }

  document.addEventListener("input", (event) => {
    if (event.target instanceof HTMLTextAreaElement)
      resizeTextarea(event.target);
  });

  document.addEventListener("reset", (event) => {
    if (event.target instanceof HTMLFormElement) scheduleResize(event.target);
  });

  document.addEventListener("modal:open", (event) => {
    if (event.target instanceof HTMLElement) scheduleResize(event.target);
  });

  window.addEventListener("resize", () => scheduleResize(), { passive: true });

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initTextareaAutosize);
  } else {
    initTextareaAutosize();
  }
})();

// Password visibility
(() => {
  const BUTTON_SELECTOR = ".view-pass-btn[data-pass-btn]";

  function getPasswordInput(button) {
    const key = button.dataset.passBtn;
    const localInput = button
      .closest(".view-pass")
      ?.querySelector("[data-pass-form]");

    if (localInput?.dataset.passForm === key) return localInput;

    return Array.from(document.querySelectorAll("[data-pass-form]")).find(
      (input) => input.dataset.passForm === key,
    );
  }

  function updatePasswordVisibility(button, input, isVisible) {
    const label = isVisible ? "Hide password" : "Show password";

    input.type = isVisible ? "text" : "password";
    button.classList.toggle("is-visible", isVisible);
    button.setAttribute("aria-label", label);
    button.setAttribute("title", label);
    button.setAttribute("aria-pressed", String(isVisible));

    if (input.id) button.setAttribute("aria-controls", input.id);
  }

  function initPasswordButtons(scope = document) {
    scope.querySelectorAll?.(BUTTON_SELECTOR).forEach((button) => {
      const input = getPasswordInput(button);

      if (input) updatePasswordVisibility(button, input, input.type === "text");
    });
  }

  document.addEventListener("click", (event) => {
    const button = event.target.closest(BUTTON_SELECTOR);

    if (!button) return;

    const input = getPasswordInput(button);
    if (!input) return;

    event.preventDefault();
    updatePasswordVisibility(button, input, input.type === "password");
  });

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", () => initPasswordButtons());
  } else {
    initPasswordButtons();
  }
})();

// Date format
document.addEventListener("DOMContentLoaded", () => {
  const date = new Date();
  document.querySelectorAll('[data-date="Y"]').forEach((el) => {
    el.textContent = date.getFullYear();
  });
  document.querySelectorAll('[data-date="d F"]').forEach((el) => {
    const day = date.getDate();
    const monthNames = [
      "January",
      "February",
      "March",
      "April",
      "May",
      "June",
      "July",
      "August",
      "September",
      "October",
      "November",
      "December",
    ];
    el.textContent = `${day} ${monthNames[date.getMonth()]}`;
  });
});

// Toggle buttons

document.addEventListener("DOMContentLoaded", () => {
  const checkboxes = document.querySelectorAll("[data-check-form]");

  checkboxes.forEach((checkbox) => {
    const key = checkbox.getAttribute("data-check-form");
    const button = document.querySelector(`[data-check-btn="${key}"]`);

    if (!button) return;

    button.disabled = !checkbox.checked;

    checkbox.addEventListener("change", () => {
      button.disabled = !checkbox.checked;
    });
  });
});

// Avatar preview
document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".avatar__upload").forEach((avatarUpload) => {
    const imageInput = avatarUpload.querySelector('input[type="file"]');
    const imagePreview = avatarUpload.querySelector(".avatar__upload-image");

    if (!imageInput || !imagePreview) return;

    imageInput.addEventListener("change", () => {
      const file = imageInput.files?.[0];

      if (!file || !file.type.startsWith("image/")) return;

      const reader = new FileReader();

      reader.onload = (event) => {
        imagePreview.src = event.target.result;
      };

      reader.readAsDataURL(file);
    });
  });
});

// Expand trimmed text
(() => {
  const ROOT_SELECTOR = "[data-trim]";
  const CONTENT_SELECTOR = "[data-trim-content]";
  const TOGGLE_SELECTOR = "[data-trim-toggle]";
  const LABEL_SELECTOR = "[data-trim-label]";
  const states = new WeakMap();
  let trimId = 0;

  function getOwnedElement(root, selector) {
    return Array.from(root.querySelectorAll(selector)).find(
      (element) => element.closest(ROOT_SELECTOR) === root,
    );
  }

  function getLimit(block) {
    const limit = Number.parseInt(block.dataset.trimLimit, 10);
    return Number.isFinite(limit) && limit > 0 ? limit : 3;
  }

  function getLabel(toggle) {
    const existingLabel = toggle.querySelector(LABEL_SELECTOR);
    if (existingLabel) return existingLabel;

    const textNode = Array.from(toggle.childNodes).find(
      (node) => node.nodeType === Node.TEXT_NODE && node.textContent.trim(),
    );
    const label = document.createElement("span");

    label.dataset.trimLabel = "";
    label.textContent = textNode?.textContent.trim() || "Show more";

    if (textNode) {
      textNode.replaceWith(label);
    } else {
      toggle.prepend(label);
    }

    return label;
  }

  function updateToggle(block) {
    const state = states.get(block);
    if (!state) return;

    window.cancelAnimationFrame(state.frame);
    state.frame = window.requestAnimationFrame(() => {
      if (block.classList.contains("is-expanded")) {
        state.toggle.hidden = false;
        return;
      }

      state.toggle.hidden =
        state.content.scrollHeight <= state.content.clientHeight + 1;
    });
  }

  function setExpanded(block, expanded, emit = true) {
    const state = states.get(block);
    if (!state) return;

    block.classList.toggle("is-expanded", expanded);
    state.toggle.classList.toggle("show", expanded);
    state.toggle.setAttribute("aria-expanded", String(expanded));
    state.label.textContent = expanded
      ? state.expandedText
      : state.collapsedText;

    if (expanded) {
      state.toggle.hidden = false;
    } else {
      updateToggle(block);
    }

    if (emit) {
      block.dispatchEvent(
        new CustomEvent("trim:change", {
          bubbles: true,
          detail: { expanded },
        }),
      );
    }
  }

  const resizeObserver =
    "ResizeObserver" in window
      ? new ResizeObserver((entries) => {
          entries.forEach((entry) => {
            const block = entry.target.closest(ROOT_SELECTOR);
            if (block) updateToggle(block);
          });
        })
      : null;

  function initTrim(block) {
    if (!(block instanceof HTMLElement)) return;

    if (states.has(block)) {
      updateToggle(block);
      return;
    }

    const content = getOwnedElement(block, CONTENT_SELECTOR);
    const toggle = getOwnedElement(block, TOGGLE_SELECTOR);

    if (!content || !toggle) return;

    const label = getLabel(toggle);
    const limit = getLimit(block);

    trimId += 1;
    block.classList.add("trim");
    content.classList.add("trim__content");
    toggle.classList.add("trim__toggle");
    block.style.setProperty("--trim-lines", String(limit));

    if (!content.id) content.id = `trim-content-${trimId}`;

    toggle.setAttribute("aria-controls", content.id);
    states.set(block, {
      content,
      toggle,
      label,
      collapsedText: label.textContent.trim() || "Show more",
      expandedText:
        block.dataset.trimLinkText || toggle.dataset.trimLinkText || "Hide",
      frame: 0,
    });

    toggle.addEventListener("click", () => {
      setExpanded(block, !block.classList.contains("is-expanded"));
    });

    const startsExpanded =
      block.classList.contains("is-expanded") ||
      toggle.classList.contains("show") ||
      toggle.getAttribute("aria-expanded") === "true";

    setExpanded(block, startsExpanded, false);
    resizeObserver?.observe(content);
  }

  function initTrims(scope = document) {
    if (scope instanceof HTMLElement && scope.matches(ROOT_SELECTOR))
      initTrim(scope);
    scope.querySelectorAll?.(ROOT_SELECTOR).forEach(initTrim);
  }

  function startTrims() {
    initTrims();

    new MutationObserver((mutations) => {
      const blocks = new Set();

      mutations.forEach((mutation) => {
        const owner =
          mutation.target instanceof Element
            ? mutation.target.closest(ROOT_SELECTOR)
            : null;
        if (owner) blocks.add(owner);

        mutation.addedNodes.forEach((node) => {
          if (!(node instanceof HTMLElement)) return;

          if (node.matches(ROOT_SELECTOR)) blocks.add(node);
          node
            .querySelectorAll(ROOT_SELECTOR)
            .forEach((block) => blocks.add(block));
        });
      });

      blocks.forEach(initTrim);
    }).observe(document.body, { childList: true, subtree: true });

    window.addEventListener(
      "load",
      () => document.querySelectorAll(ROOT_SELECTOR).forEach(updateToggle),
      {
        once: true,
      },
    );
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", startTrims);
  } else {
    startTrims();
  }
})();

// Load content with AJAX

document.addEventListener("DOMContentLoaded", () => {
  const groups = {};

  document.querySelectorAll("[data-loadpost]").forEach((block) => {
    const key = block.dataset.loadpost;
    const limit = parseInt(block.dataset.loadpostLimit) || 0;
    if (!key || !limit) return;

    const items = block.querySelectorAll(`[data-loadpost-item="${key}"]`);
    if (!items.length) return;

    block.classList.add("load");
    groups[key] = { items: Array.from(items), shown: 0, limit };
    const group = groups[key];

    group.items.slice(0, limit).forEach((el) => {
      el.classList.remove("d-none");
      group.shown++;
    });

    const btn = document.querySelector(`[data-loadpost-more="${key}"]`);
    if (btn && group.shown >= group.items.length) btn.classList.add("d-none");
  });

  document.querySelectorAll("[data-loadpost-more]").forEach((btn) => {
    const key = btn.dataset.loadpostMore;
    if (!groups[key]) return;

    btn.addEventListener("click", () => {
      const group = groups[key];
      const next = group.items.slice(group.shown, group.shown + group.limit);
      next.forEach((el) => el.classList.remove("d-none"));
      group.shown += next.length;
      if (group.shown >= group.items.length) btn.classList.add("d-none");
    });
  });
});

// Mobile menu on scroll
function initMobileScrollMenu() {
  const menu = document.querySelector("[data-scroll-menu]");

  if (!menu || menu.dataset.scrollMenuInit) return;

  const SCROLL_THRESHOLD = 8;
  let lastScrollY = Math.max(window.scrollY, 0);
  let frame = 0;

  menu.dataset.scrollMenuInit = "true";
  menu.classList.add("is-scroll-aware", "is-hidden");

  requestAnimationFrame(() => {
    requestAnimationFrame(() => menu.classList.remove("is-hidden"));
  });

  window.addEventListener(
    "scroll",
    () => {
      if (frame) return;

      frame = requestAnimationFrame(() => {
        const currentScrollY = Math.max(window.scrollY, 0);
        const scrollDelta = currentScrollY - lastScrollY;

        if (currentScrollY === 0) {
          menu.classList.remove("is-hidden");
        } else if (scrollDelta > SCROLL_THRESHOLD) {
          menu.classList.add("is-hidden");
        } else if (scrollDelta < -SCROLL_THRESHOLD) {
          menu.classList.remove("is-hidden");
        }

        if (Math.abs(scrollDelta) > SCROLL_THRESHOLD || currentScrollY === 0) {
          lastScrollY = currentScrollY;
        }

        frame = 0;
      });
    },
    { passive: true },
  );
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initMobileScrollMenu);
} else {
  initMobileScrollMenu();
}

// Side panels
(() => {
  const PANEL_SELECTOR = "[data-side-panel]";
  const OPEN_SELECTOR = "[data-side-panel-open]";
  const CLOSE_SELECTOR = "[data-side-panel-close]";
  const FOCUSABLE_SELECTOR = [
    "a[href]",
    "button:not([disabled])",
    "input:not([disabled])",
    "select:not([disabled])",
    "textarea:not([disabled])",
    "[tabindex]:not([tabindex='-1'])",
  ].join(",");

  let activePanel = null;
  let activeTrigger = null;
  let pageStyles = null;

  function lockPage() {
    if (pageStyles) return;

    const body = document.body;
    const scrollbarWidth = Math.max(
      0,
      window.innerWidth - document.documentElement.clientWidth,
    );
    const currentPadding =
      Number.parseFloat(getComputedStyle(body).paddingRight) || 0;

    pageStyles = {
      overflow: body.style.overflow,
      paddingRight: body.style.paddingRight,
    };

    body.style.overflow = "hidden";
    if (scrollbarWidth)
      body.style.paddingRight = `${currentPadding + scrollbarWidth}px`;
  }

  function unlockPage() {
    if (!pageStyles) return;

    document.body.style.overflow = pageStyles.overflow;
    document.body.style.paddingRight = pageStyles.paddingRight;
    pageStyles = null;
  }

  function emit(panel, type) {
    panel.dispatchEvent(
      new CustomEvent(`side-panel:${type}`, { bubbles: true }),
    );
  }

  function initPanel(panel) {
    if (
      !(panel instanceof HTMLElement) ||
      panel.dataset.sidePanelInitialized === "true"
    )
      return;

    const dialog = panel.querySelector(".side-panel__dialog");
    if (!dialog) return;

    panel.dataset.sidePanelInitialized = "true";
    panel.setAttribute("aria-hidden", "true");
    panel.setAttribute("inert", "");
    dialog.setAttribute("role", "dialog");
    dialog.setAttribute("aria-modal", "true");

    if (!dialog.hasAttribute("tabindex")) dialog.setAttribute("tabindex", "-1");
    if (panel.parentElement !== document.body) document.body.append(panel);
  }

  function findPanel(name) {
    const panel = Array.from(document.querySelectorAll(PANEL_SELECTOR)).find(
      (panel) => panel.dataset.sidePanel === name,
    );

    if (panel) initPanel(panel);
    return panel;
  }

  function setTriggerState(panelName, isExpanded) {
    document.querySelectorAll(OPEN_SELECTOR).forEach((trigger) => {
      if (trigger.dataset.sidePanelOpen === panelName) {
        trigger.setAttribute("aria-expanded", String(isExpanded));
      }
    });
  }

  function openPanel(panel, trigger) {
    if (!panel || panel === activePanel) return;

    if (activePanel) closePanel(activePanel, false);

    activePanel = panel;
    activeTrigger = trigger || null;
    lockPage();
    panel.removeAttribute("inert");
    panel.classList.add("is-open");
    panel.setAttribute("aria-hidden", "false");
    document.body.classList.add("is-side-panel-open");
    setTriggerState(panel.dataset.sidePanel, true);
    emit(panel, "open");

    requestAnimationFrame(() =>
      panel.querySelector(".side-panel__dialog")?.focus(),
    );
  }

  function closePanel(panel, restoreFocus = true) {
    if (!panel) return;

    const trigger = activeTrigger;

    panel.classList.remove("is-open");
    panel.setAttribute("aria-hidden", "true");
    panel.setAttribute("inert", "");
    setTriggerState(panel.dataset.sidePanel, false);
    emit(panel, "close");

    if (panel === activePanel) {
      activePanel = null;
      activeTrigger = null;
      document.body.classList.remove("is-side-panel-open");
      unlockPage();
    }

    if (restoreFocus && trigger?.isConnected) trigger.focus();
  }

  document.addEventListener("click", (event) => {
    const openTrigger = event.target.closest(OPEN_SELECTOR);

    if (openTrigger) {
      const panel = findPanel(openTrigger.dataset.sidePanelOpen);

      if (!panel) return;

      event.preventDefault();
      openPanel(panel, openTrigger);
      return;
    }

    const closeTrigger = event.target.closest(CLOSE_SELECTOR);

    if (closeTrigger) {
      event.preventDefault();
      closePanel(closeTrigger.closest(PANEL_SELECTOR));
      return;
    }

    if (activePanel && event.target === activePanel) closePanel(activePanel);
  });

  document.addEventListener("keydown", (event) => {
    if (!activePanel) return;

    if (event.key === "Escape") {
      event.preventDefault();
      closePanel(activePanel);
      return;
    }

    if (event.key !== "Tab") return;

    const dialog = activePanel.querySelector(".side-panel__dialog");
    const focusableElements = dialog
      ? Array.from(dialog.querySelectorAll(FOCUSABLE_SELECTOR)).filter(
          (element) => element.getClientRects().length,
        )
      : [];

    if (!focusableElements.length) {
      event.preventDefault();
      dialog?.focus();
      return;
    }

    const first = focusableElements[0];
    const last = focusableElements[focusableElements.length - 1];

    if (event.shiftKey && document.activeElement === first) {
      event.preventDefault();
      last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
      event.preventDefault();
      first.focus();
    }
  });

  window.addEventListener(
    "resize",
    () => {
      if (activePanel && getComputedStyle(activePanel).display === "none") {
        closePanel(activePanel, false);
      }
    },
    { passive: true },
  );

  document.querySelectorAll(PANEL_SELECTOR).forEach(initPanel);
})();

// Scroll tags with the mouse wheel
function initChannelTagsWheel() {
  document.querySelectorAll(".channel__tags").forEach((tags) => {
    if (tags.dataset.wheelScrollInit) return;

    tags.dataset.wheelScrollInit = "true";
    tags.addEventListener(
      "wheel",
      (event) => {
        if (Math.abs(event.deltaY) <= Math.abs(event.deltaX)) return;

        const maxScrollLeft = tags.scrollWidth - tags.clientWidth;
        if (maxScrollLeft <= 0) return;

        const nextScrollLeft = Math.min(
          maxScrollLeft,
          Math.max(0, tags.scrollLeft + event.deltaY),
        );

        if (nextScrollLeft === tags.scrollLeft) return;

        event.preventDefault();
        tags.scrollLeft = nextScrollLeft;
      },
      { passive: false },
    );
  });
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initChannelTagsWheel);
} else {
  initChannelTagsWheel();
}

// Post form switches
document.querySelectorAll(".allowCheckbox").forEach((block) => {
  block.querySelectorAll(".checkbox").forEach((el) => {
    el.classList.add("form-check", "form-switch");
  });
  block.querySelectorAll("input.switch").forEach((el) => {
    el.classList.add("form-check-input");
  });
});

// Back to top
function initGoTop() {
  const button = document.querySelector("[data-gotop]");

  if (!button || button.dataset.gotopInit) return;

  const SHOW_AFTER = 0;
  let frame = 0;

  button.dataset.gotopInit = "true";

  const updateVisibility = () => {
    const isVisible = window.scrollY > SHOW_AFTER;

    button.classList.toggle("is-visible", isVisible);
    button.setAttribute("aria-hidden", String(!isVisible));
    button.tabIndex = isVisible ? 0 : -1;
    frame = 0;
  };

  window.addEventListener(
    "scroll",
    () => {
      if (!frame) frame = requestAnimationFrame(updateVisibility);
    },
    { passive: true },
  );

  button.addEventListener("click", () => {
    const reduceMotion = window.matchMedia(
      "(prefers-reduced-motion: reduce)",
    ).matches;

    window.scrollTo({
      top: 0,
      behavior: reduceMotion ? "auto" : "smooth",
    });
    button.blur();
  });

  updateVisibility();
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initGoTop);
} else {
  initGoTop();
}

// Dropdowns
(() => {
  const ROOT_SELECTOR = ".dropdown";
  const TOGGLE_SELECTOR = '[data-dropdown-toggle], [data-bs-toggle="dropdown"]';
  const MENU_SELECTOR = ".dropdown-menu";
  const ITEM_SELECTOR = ".dropdown-menu-item";
  const openDropdowns = new Set();
  let dropdownId = 0;

  function getParts(root) {
    return {
      toggle:
        Array.from(root.children).find((child) =>
          child.matches?.(TOGGLE_SELECTOR),
        ) || null,
      menu:
        Array.from(root.children).find((child) =>
          child.matches?.(MENU_SELECTOR),
        ) || null,
    };
  }

  function normalizeLegacyDropdown(root, toggle, menu) {
    const isLegacy = toggle.hasAttribute("data-bs-toggle");

    if (isLegacy) toggle.setAttribute("data-dropdown-toggle", "");

    Array.from(menu.children).forEach((item) => {
      if (!(item instanceof HTMLElement)) return;

      const action = item.querySelector(":scope > a, :scope > button");

      if (!action) return;

      item.classList.add("dropdown-menu-item");
      if (root.classList.contains("pm-item__dropdown"))
        item.classList.add("dropdown-menu-item-sm");
      if (action.classList.contains("text-danger"))
        item.classList.add("is-danger");

      action.classList.remove("dropdown-item", "text-danger");
      if (isLegacy) action.setAttribute("data-dropdown-close", "");
    });
  }

  function getItems(menu) {
    return Array.from(menu.querySelectorAll(ITEM_SELECTOR))
      .map((item) => {
        const action = item.querySelector(":scope > a, :scope > button");
        const isDisabled =
          item.classList.contains("is-disabled") ||
          item.getAttribute("aria-disabled") === "true" ||
          action?.disabled ||
          action?.getAttribute("aria-disabled") === "true";

        return isDisabled ? null : action;
      })
      .filter(Boolean);
  }

  function emit(root, type) {
    root.dispatchEvent(new CustomEvent(`dropdown:${type}`, { bubbles: true }));
  }

  function closeDropdown(root, restoreFocus = false) {
    if (!root.classList.contains("is-open")) return;

    const { toggle } = getParts(root);

    root.classList.remove("is-open");
    toggle?.setAttribute("aria-expanded", "false");
    openDropdowns.delete(root);
    emit(root, "close");

    if (restoreFocus) toggle?.focus();
  }

  function closeAll(except = null) {
    Array.from(openDropdowns).forEach((root) => {
      if (root !== except) closeDropdown(root);
    });
  }

  function openDropdown(root, focusIndex = null) {
    const { toggle, menu } = getParts(root);

    if (
      !toggle ||
      !menu ||
      toggle.disabled ||
      toggle.getAttribute("aria-disabled") === "true"
    )
      return;

    closeAll(root);

    if (!root.classList.contains("is-open")) {
      root.classList.add("is-open");
      toggle.setAttribute("aria-expanded", "true");
      openDropdowns.add(root);
      emit(root, "open");
    }

    if (focusIndex === null) return;

    const items = getItems(menu);
    const target = focusIndex < 0 ? items.at(-1) : items[focusIndex];

    requestAnimationFrame(() => target?.focus());
  }

  function toggleDropdown(root) {
    if (root.classList.contains("is-open")) {
      closeDropdown(root);
    } else {
      openDropdown(root);
    }
  }

  function initDropdown(root) {
    if (
      !(root instanceof HTMLElement) ||
      root.dataset.dropdownInitialized === "true"
    )
      return;

    const { toggle, menu } = getParts(root);

    if (!toggle || !menu) return;

    normalizeLegacyDropdown(root, toggle, menu);

    root.dataset.dropdownInitialized = "true";
    dropdownId += 1;

    if (!toggle.id) toggle.id = `dropdown-toggle-${dropdownId}`;
    if (!menu.id) menu.id = `dropdown-menu-${dropdownId}`;

    toggle.setAttribute("aria-haspopup", "menu");
    toggle.setAttribute("aria-expanded", "false");
    toggle.setAttribute("aria-controls", menu.id);
    menu.setAttribute("role", "menu");
    menu.setAttribute("aria-labelledby", toggle.id);

    menu.querySelectorAll(ITEM_SELECTOR).forEach((item) => {
      const action = item.querySelector(":scope > a, :scope > button");

      item.setAttribute("role", "none");
      if (!action) return;

      action.setAttribute("role", "menuitem");
      action.tabIndex = -1;
    });

    toggle.addEventListener("click", () => toggleDropdown(root));

    toggle.addEventListener("keydown", (event) => {
      if (["Enter", " ", "ArrowDown"].includes(event.key)) {
        event.preventDefault();
        openDropdown(root, 0);
      } else if (event.key === "ArrowUp") {
        event.preventDefault();
        openDropdown(root, -1);
      } else if (event.key === "Escape") {
        event.preventDefault();
        closeDropdown(root);
      }
    });

    let searchBuffer = "";
    let searchTimer = 0;

    menu.addEventListener("keydown", (event) => {
      const items = getItems(menu);
      const currentIndex = items.indexOf(document.activeElement);

      if (!items.length) return;

      if (event.key === "ArrowDown" || event.key === "ArrowUp") {
        event.preventDefault();

        const direction = event.key === "ArrowDown" ? 1 : -1;
        const nextIndex =
          (currentIndex + direction + items.length) % items.length;

        items[nextIndex].focus();
      } else if (event.key === "Home" || event.key === "End") {
        event.preventDefault();
        items[event.key === "Home" ? 0 : items.length - 1].focus();
      } else if (event.key === "Escape") {
        event.preventDefault();
        closeDropdown(root, true);
      } else if (event.key === "Tab") {
        closeDropdown(root);
      } else if (
        event.key.length === 1 &&
        !event.altKey &&
        !event.ctrlKey &&
        !event.metaKey
      ) {
        window.clearTimeout(searchTimer);
        searchBuffer += event.key.toLocaleLowerCase();
        searchTimer = window.setTimeout(() => {
          searchBuffer = "";
        }, 500);

        const orderedItems = [
          ...items.slice(currentIndex + 1),
          ...items.slice(0, currentIndex + 1),
        ];
        const match = orderedItems.find((item) =>
          item.textContent.trim().toLocaleLowerCase().startsWith(searchBuffer),
        );

        if (match) {
          event.preventDefault();
          match.focus();
        }
      }
    });

    menu.addEventListener("click", (event) => {
      const item = event.target.closest(ITEM_SELECTOR);

      if (!item || !menu.contains(item)) return;

      const action = item.querySelector(":scope > a, :scope > button");
      const isDisabled =
        item.classList.contains("is-disabled") ||
        item.getAttribute("aria-disabled") === "true" ||
        action?.disabled ||
        action?.getAttribute("aria-disabled") === "true";

      if (isDisabled) {
        event.preventDefault();
        return;
      }

      if (
        item.hasAttribute("data-dropdown-close") ||
        action?.hasAttribute("data-dropdown-close")
      ) {
        closeDropdown(root, true);
      }
    });

    root.addEventListener("focusout", (event) => {
      if (!root.contains(event.relatedTarget)) closeDropdown(root);
    });
  }

  function initDropdowns(scope = document) {
    if (scope instanceof HTMLElement && scope.matches(ROOT_SELECTOR))
      initDropdown(scope);
    scope.querySelectorAll?.(ROOT_SELECTOR).forEach(initDropdown);
  }

  function startDropdowns() {
    initDropdowns();

    new MutationObserver((mutations) => {
      mutations.forEach((mutation) => {
        mutation.addedNodes.forEach((node) => {
          if (node instanceof HTMLElement) initDropdowns(node);
        });
      });
    }).observe(document.body, { childList: true, subtree: true });
  }

  document.addEventListener("pointerdown", (event) => {
    Array.from(openDropdowns).forEach((root) => {
      if (!root.contains(event.target)) closeDropdown(root);
    });
  });

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", startDropdowns);
  } else {
    startDropdowns();
  }
})();

// Tabs
(() => {
  const NAV_SELECTOR = "[data-tabs-nav]";
  const CONTAINER_SELECTOR = "[data-tabs-container]";
  const TAB_SELECTOR = "[data-tab]";
  const PANEL_SELECTOR = "[data-tab-panel]";
  const activeGroups = new Map();
  let tabsId = 0;

  function getGroupName(element, attribute) {
    return element.getAttribute(attribute)?.trim() || "";
  }

  function getTabs(nav) {
    return Array.from(nav.querySelectorAll(TAB_SELECTOR)).filter(
      (tab) => tab.closest(NAV_SELECTOR) === nav,
    );
  }

  function getGroupNavs(group) {
    return Array.from(document.querySelectorAll(NAV_SELECTOR)).filter(
      (nav) => getGroupName(nav, "data-tabs-nav") === group,
    );
  }

  function getGroupContainers(group) {
    return Array.from(document.querySelectorAll(CONTAINER_SELECTOR)).filter(
      (container) => getGroupName(container, "data-tabs-container") === group,
    );
  }

  function getPanels(container) {
    return Array.from(container.querySelectorAll(PANEL_SELECTOR)).filter(
      (panel) => panel.closest(CONTAINER_SELECTOR) === container,
    );
  }

  function getGroupPanels(group) {
    return getGroupContainers(group).flatMap(getPanels);
  }

  function getTabKey(tab) {
    return tab?.dataset.tab?.trim() || "";
  }

  function getPanelKey(panel) {
    return panel.dataset.tabPanel?.trim() || "";
  }

  function isDisabled(tab) {
    return tab.disabled || tab.getAttribute("aria-disabled") === "true";
  }

  function activateGroup(group, key, sourceNav = null, emit = true) {
    const navs = getGroupNavs(group);
    const panels = getGroupPanels(group);
    const activePanels = panels.filter((panel) => getPanelKey(panel) === key);
    const hasEnabledTab = navs.some((nav) =>
      getTabs(nav).some((tab) => getTabKey(tab) === key && !isDisabled(tab)),
    );

    if (!key || !activePanels.length || !hasEnabledTab) return;

    activeGroups.set(group, key);

    navs.forEach((nav) => {
      getTabs(nav).forEach((tab) => {
        const isActive = getTabKey(tab) === key;

        tab.classList.toggle("is-active", isActive);
        tab.setAttribute("aria-selected", String(isActive));
        tab.tabIndex = isActive ? 0 : -1;
      });
    });

    panels.forEach((panel) => {
      const isActive = getPanelKey(panel) === key;

      panel.classList.toggle("is-active", isActive);
      panel.hidden = !isActive;
    });

    if (emit && sourceNav) {
      const activeTab =
        getTabs(sourceNav).find((tab) => getTabKey(tab) === key) || null;

      sourceNav.dispatchEvent(
        new CustomEvent("tabs:change", {
          bubbles: true,
          detail: { group, key, tab: activeTab, panels: activePanels },
        }),
      );
    }
  }

  function prepareNav(nav) {
    const group = getGroupName(nav, "data-tabs-nav");
    const safeGroup = group.replace(/[^a-zA-Z0-9_-]+/g, "-") || "group";
    const tabs = getTabs(nav);
    const panels = getGroupPanels(group);

    if (!group || !tabs.length || !panels.length) return;

    nav.classList.add("tabs-nav");
    nav.setAttribute("role", "tablist");

    tabs.forEach((tab, index) => {
      const key = getTabKey(tab);
      const matchingPanels = panels.filter(
        (panel) => getPanelKey(panel) === key,
      );
      const primaryPanel =
        matchingPanels.find(
          (panel) => panel.getAttribute("role") === "tabpanel",
        ) || matchingPanels[0];

      if (!key || !primaryPanel) return;

      tabsId += 1;
      tab.classList.add("tabs-nav__button");
      tab.setAttribute("role", "tab");
      if (!tab.id) tab.id = `tabs-${safeGroup}-tab-${tabsId}`;

      if (!primaryPanel.id)
        primaryPanel.id = `tabs-${safeGroup}-panel-${tabsId}`;
      tab.setAttribute("aria-controls", primaryPanel.id);

      matchingPanels.forEach((panel) => {
        panel.classList.add("tabs-panel");
        if (panel.getAttribute("role") === "tabpanel") {
          panel.setAttribute("aria-labelledby", tab.id);
          if (!panel.hasAttribute("tabindex")) panel.tabIndex = 0;
        }
      });
    });

    const activeKey =
      activeGroups.get(group) ||
      getTabKey(
        tabs.find(
          (tab) =>
            !isDisabled(tab) &&
            panels.some((panel) => getPanelKey(panel) === getTabKey(tab)) &&
            (tab.classList.contains("is-active") ||
              tab.getAttribute("aria-selected") === "true"),
        ) ||
          tabs.find(
            (tab) =>
              !isDisabled(tab) &&
              panels.some((panel) => getPanelKey(panel) === getTabKey(tab)),
          ),
      );

    if (activeKey) activateGroup(group, activeKey, null, false);
  }

  function prepareTabs() {
    document.querySelectorAll(NAV_SELECTOR).forEach(prepareNav);
  }

  function startTabs() {
    prepareTabs();

    document.addEventListener("click", (event) => {
      const tab = event.target.closest(TAB_SELECTOR);
      const nav = tab?.closest(NAV_SELECTOR);

      if (!tab || !nav || isDisabled(tab)) return;

      activateGroup(getGroupName(nav, "data-tabs-nav"), getTabKey(tab), nav);
    });

    document.addEventListener("keydown", (event) => {
      const currentTab = event.target.closest(TAB_SELECTOR);
      const nav = currentTab?.closest(NAV_SELECTOR);

      if (!currentTab || !nav) return;

      const group = getGroupName(nav, "data-tabs-nav");
      const panels = getGroupPanels(group);
      const enabledTabs = getTabs(nav).filter(
        (tab) =>
          !isDisabled(tab) &&
          panels.some((panel) => getPanelKey(panel) === getTabKey(tab)),
      );
      const currentIndex = enabledTabs.indexOf(currentTab);
      let nextIndex = currentIndex;

      if (currentIndex < 0) return;

      if (["ArrowRight", "ArrowDown"].includes(event.key)) {
        nextIndex = (currentIndex + 1) % enabledTabs.length;
      } else if (["ArrowLeft", "ArrowUp"].includes(event.key)) {
        nextIndex =
          (currentIndex - 1 + enabledTabs.length) % enabledTabs.length;
      } else if (event.key === "Home") {
        nextIndex = 0;
      } else if (event.key === "End") {
        nextIndex = enabledTabs.length - 1;
      } else {
        return;
      }

      event.preventDefault();
      enabledTabs[nextIndex].focus();
      activateGroup(group, getTabKey(enabledTabs[nextIndex]), nav);
    });

    new MutationObserver((mutations) => {
      const hasTabsChanges = mutations.some((mutation) =>
        Array.from(mutation.addedNodes).some(
          (node) =>
            node instanceof HTMLElement &&
            (node.matches(`${NAV_SELECTOR}, ${CONTAINER_SELECTOR}`) ||
              node.querySelector(`${NAV_SELECTOR}, ${CONTAINER_SELECTOR}`) ||
              node.closest(CONTAINER_SELECTOR)),
        ),
      );

      if (hasTabsChanges) prepareTabs();
    }).observe(document.body, { childList: true, subtree: true });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", startTabs);
  } else {
    startTabs();
  }
})();

// Tooltips
(() => {
  const TRIGGER_SELECTOR = "[data-tooltip]";
  const POSITIONS = new Set(["top", "bottom", "start", "end"]);
  const TOOLTIP_ID = "app-tooltip";
  const GAP = 8;
  const VIEWPORT_PADDING = 8;
  const SHOW_DELAY = 120;
  let tooltip = null;
  let activeTrigger = null;
  let showTimer = 0;
  let frame = 0;

  function createTooltip() {
    if (tooltip) return tooltip;

    tooltip = document.createElement("div");
    tooltip.id = TOOLTIP_ID;
    tooltip.className = "tooltip";
    tooltip.setAttribute("role", "tooltip");
    tooltip.setAttribute("aria-hidden", "true");
    document.body.append(tooltip);

    return tooltip;
  }

  function getPreferredPosition(trigger) {
    const position = trigger.dataset.tooltipPosition;
    return POSITIONS.has(position) ? position : "top";
  }

  function getPhysicalPosition(position) {
    if (position !== "start" && position !== "end") return position;

    const isRtl =
      getComputedStyle(document.documentElement).direction === "rtl";

    if (position === "start") return isRtl ? "end" : "start";
    return isRtl ? "start" : "end";
  }

  function getOppositePosition(position) {
    return {
      top: "bottom",
      bottom: "top",
      start: "end",
      end: "start",
    }[position];
  }

  function hasSpace(position, triggerRect, tooltipRect) {
    if (position === "top")
      return triggerRect.top >= tooltipRect.height + GAP + VIEWPORT_PADDING;
    if (position === "bottom") {
      return (
        window.innerHeight - triggerRect.bottom >=
        tooltipRect.height + GAP + VIEWPORT_PADDING
      );
    }
    if (position === "start")
      return triggerRect.left >= tooltipRect.width + GAP + VIEWPORT_PADDING;

    return (
      window.innerWidth - triggerRect.right >=
      tooltipRect.width + GAP + VIEWPORT_PADDING
    );
  }

  function positionTooltip() {
    if (!tooltip || !activeTrigger || !activeTrigger.isConnected) return;

    const triggerRect = activeTrigger.getBoundingClientRect();
    const tooltipRect = {
      width: tooltip.offsetWidth,
      height: tooltip.offsetHeight,
    };
    let position = getPhysicalPosition(getPreferredPosition(activeTrigger));

    if (!hasSpace(position, triggerRect, tooltipRect)) {
      const opposite = getOppositePosition(position);
      if (hasSpace(opposite, triggerRect, tooltipRect)) position = opposite;
    }

    let top;
    let left;

    if (position === "top") {
      top = triggerRect.top - tooltipRect.height - GAP;
      left = triggerRect.left + (triggerRect.width - tooltipRect.width) / 2;
    } else if (position === "bottom") {
      top = triggerRect.bottom + GAP;
      left = triggerRect.left + (triggerRect.width - tooltipRect.width) / 2;
    } else if (position === "start") {
      top = triggerRect.top + (triggerRect.height - tooltipRect.height) / 2;
      left = triggerRect.left - tooltipRect.width - GAP;
    } else {
      top = triggerRect.top + (triggerRect.height - tooltipRect.height) / 2;
      left = triggerRect.right + GAP;
    }

    top = Math.min(
      window.innerHeight - tooltipRect.height - VIEWPORT_PADDING,
      Math.max(VIEWPORT_PADDING, top),
    );
    left = Math.min(
      window.innerWidth - tooltipRect.width - VIEWPORT_PADDING,
      Math.max(VIEWPORT_PADDING, left),
    );

    tooltip.dataset.position = position;
    tooltip.style.top = `${Math.round(top)}px`;
    tooltip.style.left = `${Math.round(left)}px`;
  }

  function updatePosition() {
    if (frame || !activeTrigger) return;

    frame = requestAnimationFrame(() => {
      positionTooltip();
      frame = 0;
    });
  }

  function addDescription(trigger) {
    const ids = new Set(
      (trigger.getAttribute("aria-describedby") || "")
        .split(/\s+/)
        .filter(Boolean),
    );
    ids.add(TOOLTIP_ID);
    trigger.setAttribute("aria-describedby", Array.from(ids).join(" "));
  }

  function removeDescription(trigger) {
    if (!trigger) return;

    const ids = (trigger.getAttribute("aria-describedby") || "")
      .split(/\s+/)
      .filter((id) => id && id !== TOOLTIP_ID);

    if (ids.length) {
      trigger.setAttribute("aria-describedby", ids.join(" "));
    } else {
      trigger.removeAttribute("aria-describedby");
    }
  }

  function showTooltip(trigger, immediate = false) {
    const text =
      trigger.dataset.tooltip?.trim() ||
      trigger.dataset.tooltipFallback?.trim();

    window.clearTimeout(showTimer);
    if (!text) return;

    const show = () => {
      if (activeTrigger && activeTrigger !== trigger)
        removeDescription(activeTrigger);

      const element = createTooltip();

      activeTrigger = trigger;
      element.textContent = text;
      element.dataset.position = getPhysicalPosition(
        getPreferredPosition(trigger),
      );
      element.setAttribute("aria-hidden", "false");
      addDescription(trigger);
      positionTooltip();
      element.classList.add("is-visible");
    };

    if (immediate) {
      show();
    } else {
      showTimer = window.setTimeout(show, SHOW_DELAY);
    }
  }

  function hideTooltip(trigger = activeTrigger) {
    window.clearTimeout(showTimer);
    showTimer = 0;

    if (trigger && activeTrigger && trigger !== activeTrigger) return;

    removeDescription(activeTrigger);
    activeTrigger = null;

    if (!tooltip) return;

    tooltip.classList.remove("is-visible");
    tooltip.setAttribute("aria-hidden", "true");
  }

  document.addEventListener("pointerover", (event) => {
    if (event.pointerType === "touch") return;

    const trigger = event.target.closest(TRIGGER_SELECTOR);

    if (!trigger || trigger.contains(event.relatedTarget)) return;
    showTooltip(trigger);
  });

  document.addEventListener("pointerout", (event) => {
    const trigger = event.target.closest(TRIGGER_SELECTOR);

    if (!trigger || trigger.contains(event.relatedTarget)) return;
    hideTooltip(trigger);
  });

  document.addEventListener("focusin", (event) => {
    const trigger = event.target.closest(TRIGGER_SELECTOR);
    if (trigger) showTooltip(trigger, true);
  });

  document.addEventListener("focusout", (event) => {
    const trigger = event.target.closest(TRIGGER_SELECTOR);

    if (!trigger || trigger.contains(event.relatedTarget)) return;
    hideTooltip(trigger);
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") hideTooltip();
  });

  window.addEventListener("resize", updatePosition, { passive: true });
  document.addEventListener("scroll", updatePosition, {
    passive: true,
    capture: true,
  });
})();

// Modals
(() => {
  const MODAL_SELECTOR = "[data-modal]";
  const OPEN_SELECTOR = "[data-modal-btn]";
  const CLOSE_SELECTOR = "[data-modal-close]";
  const CONTENT_SELECTOR = ":scope > .modal-content";
  const FOCUSABLE_SELECTOR = [
    "a[href]",
    "button:not([disabled])",
    "input:not([disabled]):not([type='hidden'])",
    "select:not([disabled])",
    "textarea:not([disabled])",
    "[tabindex]:not([tabindex='-1'])",
  ].join(",");

  let activeModal = null;
  let activeTrigger = null;
  let pageStyles = null;

  function getModal(name) {
    return Array.from(document.querySelectorAll(MODAL_SELECTOR)).find(
      (modal) => modal.dataset.modal === name,
    );
  }

  function getContent(modal) {
    return modal?.querySelector(CONTENT_SELECTOR);
  }

  function getFocusableElements(modal) {
    const content = getContent(modal);

    if (!content) return [];

    return Array.from(content.querySelectorAll(FOCUSABLE_SELECTOR)).filter(
      (element) =>
        !element.hasAttribute("hidden") &&
        element.getAttribute("aria-hidden") !== "true" &&
        element.getClientRects().length > 0,
    );
  }

  function updateModalOverflow(modal) {
    const content = getContent(modal);

    if (!content) return;

    const header = content.querySelector(":scope > .modal-header");
    const body = content.querySelector(":scope > .modal-body");
    const footer = content.querySelector(":scope > .modal-footer");
    const requiredHeight = body
      ? (header?.offsetHeight || 0) +
        body.scrollHeight +
        (footer?.offsetHeight || 0)
      : content.scrollHeight;

    modal.classList.toggle(
      "is-overflowing",
      requiredHeight > window.innerHeight,
    );
  }

  function lockPage() {
    if (pageStyles) return;

    const body = document.body;
    const scrollbarWidth = Math.max(
      0,
      window.innerWidth - document.documentElement.clientWidth,
    );
    const currentPadding =
      Number.parseFloat(getComputedStyle(body).paddingRight) || 0;

    pageStyles = {
      overflow: body.style.overflow,
      paddingRight: body.style.paddingRight,
    };

    body.style.overflow = "hidden";

    if (scrollbarWidth) {
      body.style.paddingRight = `${currentPadding + scrollbarWidth}px`;
    }
  }

  function unlockPage() {
    if (!pageStyles) return;

    document.body.style.overflow = pageStyles.overflow;
    document.body.style.paddingRight = pageStyles.paddingRight;
    pageStyles = null;
  }

  function emit(modal, type) {
    modal.dispatchEvent(new CustomEvent(`modal:${type}`, { bubbles: true }));
  }

  function initModal(modal) {
    if (
      !(modal instanceof HTMLElement) ||
      modal.dataset.modalInitialized === "true"
    )
      return;

    const content = getContent(modal);

    if (!content) return;

    modal.dataset.modalInitialized = "true";
    modal.setAttribute("aria-hidden", "true");
    modal.setAttribute("inert", "");
    content.setAttribute("role", "dialog");
    content.setAttribute("aria-modal", "true");

    if (!content.hasAttribute("tabindex")) content.tabIndex = -1;

    if (!modal.closest("form") && modal.parentElement !== document.body) {
      document.body.append(modal);
    }
  }

  function closeModal(
    modal = activeModal,
    restoreFocus = true,
    releasePage = true,
  ) {
    if (!modal || !modal.classList.contains("is-open")) return;

    modal.classList.remove("is-open");
    modal.classList.remove("is-overflowing");
    modal.setAttribute("aria-hidden", "true");
    modal.setAttribute("inert", "");
    emit(modal, "close");

    if (modal === activeModal) activeModal = null;
    if (releasePage) unlockPage();

    if (restoreFocus && activeTrigger?.isConnected) activeTrigger.focus();
    if (releasePage) activeTrigger = null;
  }

  function openModal(modal, trigger = null) {
    if (!modal) return;

    initModal(modal);

    const content = getContent(modal);
    if (!content) return;

    if (activeModal && activeModal !== modal) {
      closeModal(activeModal, false, false);
    }

    activeModal = modal;
    activeTrigger = trigger;
    lockPage();

    modal.removeAttribute("inert");
    modal.setAttribute("aria-hidden", "false");
    modal.classList.add("is-open");
    updateModalOverflow(modal);
    emit(modal, "open");

    requestAnimationFrame(() => {
      if (activeModal !== modal) return;

      const autofocus = content.querySelector("[autofocus]");
      const closeButton = content.querySelector(CLOSE_SELECTOR);
      const firstFocusable = getFocusableElements(modal)[0];

      (autofocus || closeButton || firstFocusable || content).focus({
        preventScroll: true,
      });
    });
  }

  function initModals(scope = document) {
    if (scope instanceof HTMLElement && scope.matches(MODAL_SELECTOR))
      initModal(scope);
    scope.querySelectorAll?.(MODAL_SELECTOR).forEach(initModal);
  }

  function startModals() {
    initModals();

    new MutationObserver((mutations) => {
      mutations.forEach((mutation) => {
        mutation.addedNodes.forEach((node) => {
          if (node instanceof HTMLElement) initModals(node);
        });
      });

      if (activeModal) updateModalOverflow(activeModal);
    }).observe(document.body, { childList: true, subtree: true });
  }

  document.addEventListener("click", (event) => {
    const openButton = event.target.closest(OPEN_SELECTOR);

    if (openButton) {
      const modal = getModal(openButton.dataset.modalBtn);

      if (modal) {
        event.preventDefault();
        openModal(modal, openButton);
      }

      return;
    }

    const closeButton = event.target.closest(CLOSE_SELECTOR);

    if (closeButton) {
      const modal = closeButton.closest(MODAL_SELECTOR);

      if (modal) {
        event.preventDefault();
        closeModal(modal);
      }

      return;
    }

    if (activeModal && event.target === activeModal) closeModal(activeModal);
  });

  document.addEventListener("keydown", (event) => {
    if (!activeModal) return;

    if (event.key === "Escape") {
      event.preventDefault();
      closeModal(activeModal);
      return;
    }

    if (event.key !== "Tab") return;

    const focusableElements = getFocusableElements(activeModal);
    const content = getContent(activeModal);

    if (!focusableElements.length) {
      event.preventDefault();
      content?.focus();
      return;
    }

    const first = focusableElements[0];
    const last = focusableElements[focusableElements.length - 1];

    if (
      event.shiftKey &&
      (document.activeElement === first || document.activeElement === content)
    ) {
      event.preventDefault();
      last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
      event.preventDefault();
      first.focus();
    }
  });

  window.addEventListener(
    "resize",
    () => {
      if (activeModal) updateModalOverflow(activeModal);
    },
    { passive: true },
  );

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", startModals);
  } else {
    startModals();
  }
})();

document.addEventListener("click", (event) => {
  const button = event.target.closest("[data-comment-images-trigger]");

  if (!button) return;

  document.getElementById("comment-images")?.click();
});

document.addEventListener("change", (event) => {
  if (event.target.id !== "comment-images") return;

  const selection = document.querySelector("[data-comment-images-selection]");

  if (selection) {
    selection.textContent = event.target.files.length
      ? `${event.target.files.length} resim seçildi`
      : "";
  }
});

document.addEventListener("click", async (event) => {
  const button = event.target.closest("[data-share-post]");

  if (!button) return;

  const shareData = {
    title: button.dataset.shareTitle,
    url: button.dataset.shareUrl,
  };

  try {
    if (navigator.share) {
      await navigator.share(shareData);
    } else if (navigator.clipboard?.writeText) {
      await navigator.clipboard.writeText(shareData.url);
      button.setAttribute("aria-label", "Bağlantı kopyalandı");
      button.setAttribute("title", "Bağlantı kopyalandı");
    } else {
      window.prompt("Yazı bağlantısını kopyalayın:", shareData.url);
    }
  } catch (error) {
    if (error instanceof DOMException && error.name === "AbortError") return;

    console.error("Yazı paylaşılırken hata oluştu.", error);
  }
});


// Comment editing
document.addEventListener("click", (event) => {
  const editButton = event.target.closest("[data-comment-edit-toggle]");
  const cancelButton = event.target.closest("[data-comment-edit-cancel]");

  if (editButton) {
    event.preventDefault();

    const id = editButton.dataset.commentEditToggle;
    const form = document.getElementById(`comment-edit-${id}`);

    if (!form) return;

    document.querySelectorAll("[data-comment-edit-form]").forEach((editForm) => {
      if (editForm !== form) editForm.hidden = true;
    });

    form.hidden = false;
    form.querySelector("textarea")?.focus();
    return;
  }

  if (cancelButton) {
    const id = cancelButton.dataset.commentEditCancel;
    const form = document.getElementById(`comment-edit-${id}`);

    if (form) form.hidden = true;
  }
});


// Post composer: live text preview, character counters and selected media feedback.
(() => {
  const title = document.querySelector("[data-post-title-input]");
  const excerpt = document.querySelector("[data-post-excerpt-input]");
  const titleCount = document.querySelector("[data-title-count]");
  const excerptCount = document.querySelector("[data-excerpt-count]");
  const previewTitle = document.querySelector("[data-post-preview-title]");
  const previewExcerpt = document.querySelector("[data-post-preview-excerpt]");
  const updateText = () => {
    if (title && titleCount) titleCount.textContent = String(title.value.length);
    if (excerpt && excerptCount) excerptCount.textContent = String(excerpt.value.length);
    if (previewTitle) previewTitle.textContent = title?.value.trim() || "Yazı başlığın burada görünecek";
    if (previewExcerpt) previewExcerpt.textContent = excerpt?.value.trim() || "Kısa açıklaman burada görünecek.";
  };
  title?.addEventListener("input", updateText);
  excerpt?.addEventListener("input", updateText);
  updateText();

  const galleryInput = document.querySelector("[data-post-gallery-input]");
  const galleryList = document.querySelector("[data-post-gallery-list]");
  galleryInput?.addEventListener("change", () => {
    if (!galleryList) return;
    galleryList.replaceChildren();
    Array.from(galleryInput.files || []).forEach((file) => {
      const item = document.createElement("span");
      item.className = "post-composer-file-item";
      item.textContent = file.name;
      galleryList.append(item);
    });
    if (galleryInput.files?.length) {
      const count = document.createElement("span");
      count.className = "post-composer-file-item";
      count.textContent = galleryInput.files.length + " / 10 görsel seçildi";
      galleryList.append(count);
    }
  });

  const coverInput = document.querySelector("[data-post-cover-input]");
  const coverPreview = document.querySelector("[data-post-cover-preview]");
  coverInput?.addEventListener("change", () => {
    if (!coverPreview) return;
    coverPreview.replaceChildren();
    const file = coverInput.files?.[0];
    if (!file || !file.type.startsWith("image/")) return;
    const image = document.createElement("img");
    image.alt = "Seçilen kapak görseli önizlemesi";
    image.src = URL.createObjectURL(file);
    image.onload = () => URL.revokeObjectURL(image.src);
    coverPreview.append(image);
  });
})();
