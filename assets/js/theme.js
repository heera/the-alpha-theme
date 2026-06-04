/* The Alpha — no dependencies, deferred.
   Theme toggle · mobile nav · scroll-spy · progress · reveal · quote rotator ·
   copy buttons · Terms/Subscribe drawer. */
(function () {
  "use strict";

  var root = document.documentElement;
  var body = document.body;
  var reduceMotion = window.matchMedia(
    "(prefers-reduced-motion: reduce)"
  ).matches;

  /* ---- Page loader: fade out once everything (incl. images & fonts) is
     ready. At the same moment we flip body.is-ready so the layout +
     footer cross-fade up into view (CSS handles the reveal transition).
     Falls back to a 6-second cap so a stuck request never traps the
     user behind the overlay. */
  var loader = document.querySelector(".page-loader");
  var revealPage = function () {
    body.classList.add("is-ready");
    if (loader) {
      loader.classList.add("is-hidden");
      // Remove after the transition so it's gone from the a11y tree.
      setTimeout(function () {
        if (loader && loader.parentNode) loader.parentNode.removeChild(loader);
      }, 700);
    }
  };
  if (document.readyState === "complete") {
    revealPage();
  } else {
    window.addEventListener("load", revealPage);
    setTimeout(revealPage, 6000); // safety net.
  }

  /* ---- Colour theme toggle (no-FOUC script already set the initial value) */
  var toggle = document.querySelector(".theme-toggle");

  function syncToggle() {
    if (!toggle) return;
    var isLight = root.getAttribute("data-theme") === "light";
    toggle.setAttribute("aria-pressed", String(isLight));
  }
  syncToggle();

  function setTheme(next) {
    root.setAttribute("data-theme", next);
    try {
      localStorage.setItem("the-alpha-theme", next);
    } catch (e) {}
    syncToggle();
  }

  if (toggle) {
    toggle.addEventListener("click", function () {
      var next =
        root.getAttribute("data-theme") === "light" ? "dark" : "light";
      // Crossfade the whole page between light/dark via the View Transitions
      // API (CSS controls the timing). Falls back to an instant switch where
      // it's unsupported or when the user prefers reduced motion.
      if (!reduceMotion && typeof document.startViewTransition === "function") {
        document.startViewTransition(function () {
          setTheme(next);
        });
      } else {
        setTheme(next);
      }
    });
  }

  /* ---- Mobile navigation -------------------------------------------------- */
  var navToggle = document.querySelector(".nav-toggle");
  var closers = document.querySelectorAll("[data-close-nav]");

  function setNav(open) {
    body.classList.toggle("nav-open", open);
    if (navToggle) navToggle.setAttribute("aria-expanded", String(open));
  }
  if (navToggle) {
    navToggle.addEventListener("click", function () {
      setNav(!body.classList.contains("nav-open"));
    });
  }
  closers.forEach(function (el) {
    el.addEventListener("click", function () {
      setNav(false);
    });
  });
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") {
      setNav(false);
      setSearch(false);
    }
  });

  /* ---- Topbar search (mobile) -------------------------------------------- */
  var searchToggle = document.querySelector(".topbar__search-toggle");
  var searchPanel = document.getElementById("topbar-search");
  var searchInput = searchPanel ? searchPanel.querySelector('input[type="search"]') : null;

  function setSearch(open) {
    if (!searchToggle || !searchPanel) return;
    body.classList.toggle("search-open", open);
    searchToggle.setAttribute("aria-expanded", String(open));
    if (open) {
      searchPanel.removeAttribute("hidden");
      if (searchInput) {
        // Defer focus so the panel is rendered before the input grabs focus.
        requestAnimationFrame(function () { searchInput.focus(); });
      }
    } else {
      searchPanel.setAttribute("hidden", "");
    }
  }
  if (searchToggle) {
    searchToggle.addEventListener("click", function () {
      setSearch(!body.classList.contains("search-open"));
    });
  }

  /* ---- Scroll-spy + close menu on section link click --------------------- */
  var links = Array.prototype.slice.call(
    document.querySelectorAll(".nav__link[data-section]")
  );
  var sections = links
    .map(function (l) {
      return document.getElementById(l.getAttribute("data-section"));
    })
    .filter(Boolean);

  links.forEach(function (l) {
    l.addEventListener("click", function (e) {
      if (window.innerWidth <= 1024) setNav(false);
      // Contact is the last section on the front page. On DESKTOP it's sized to
      // sit with the footer in one viewport, so scroll to the very bottom to
      // reveal the footer. On mobile/tablet (<=1024px) sections are stacked at
      // content height and the footer is a full screen below — scrolling to the
      // bottom would skip past the contact section, so let the native anchor
      // jump handle it (CSS .section scroll-margin-top lands it just below the
      // sticky topbar). Only override when #contact exists (front page); on
      // archive / single pages let the link navigate normally.
      if (
        l.getAttribute("data-section") === "contact" &&
        document.getElementById("contact") &&
        window.innerWidth > 1024
      ) {
        e.preventDefault();
        if (history.replaceState) history.replaceState(null, "", "#contact");
        window.scrollTo({
          top: document.documentElement.scrollHeight,
          behavior: "smooth"
        });
      }
    });
  });

  /* Deep-link to #contact (e.g. coming from a post page) — land the user
     at the document bottom so the footer is in view, not at the section's
     top with the footer cut below the viewport. */
  function alignContactToBottom() {
    // Desktop only — mirrors the click handler above. On mobile/tablet the
    // native hash scroll (offset by .section scroll-margin-top) already lands
    // at the contact section's start; forcing the document bottom would skip
    // past it to the footer.
    if (
      location.hash === "#contact" &&
      document.getElementById("contact") &&
      window.innerWidth > 1024
    ) {
      requestAnimationFrame(function () {
        window.scrollTo(0, document.documentElement.scrollHeight);
      });
    }
  }
  alignContactToBottom();
  window.addEventListener("load", alignContactToBottom);
  window.addEventListener("hashchange", alignContactToBottom);

  if (sections.length && "IntersectionObserver" in window) {
    var spy = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) return;
          var id = entry.target.id;
          links.forEach(function (l) {
            l.classList.toggle(
              "is-active",
              l.getAttribute("data-section") === id
            );
          });
          // Mirror the active section into the URL hash without
          // polluting browser history (replaceState, not pushState).
          // Skip while a drawer is open so a deep-link hash (e.g. #terms)
          // isn't clobbered by the section behind the overlay.
          if (
            history.replaceState &&
            location.hash !== "#" + id &&
            !body.classList.contains("drawer-open")
          ) {
            history.replaceState(null, "", "#" + id);
          }
        });
      },
      { rootMargin: "-45% 0px -45% 0px", threshold: 0 }
    );
    sections.forEach(function (s) {
      spy.observe(s);
    });
  }

  /* ---- Reveal on scroll (never leaves content stuck hidden) -------------- */
  var reveals = Array.prototype.slice.call(
    document.querySelectorAll(".reveal")
  );

  function show(el) {
    el.classList.add("in");
  }

  if (reduceMotion || !("IntersectionObserver" in window)) {
    reveals.forEach(show);
  } else {
    var revObs = new IntersectionObserver(
      function (entries, obs) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            show(entry.target);
            obs.unobserve(entry.target);
          }
        });
      },
      { rootMargin: "0px 0px -8% 0px", threshold: 0.06 }
    );
    reveals.forEach(function (el) {
      revObs.observe(el);
    });

    // Safety net: anything in/above the viewport (e.g. deep-link to #about,
    // or IO not re-firing after a programmatic hash jump) shows at once.
    var sweep = function () {
      var vh = window.innerHeight || document.documentElement.clientHeight;
      reveals.forEach(function (el) {
        if (el.classList.contains("in")) return;
        if (el.getBoundingClientRect().top < vh * 0.92) {
          show(el);
          revObs.unobserve(el);
        }
      });
    };
    sweep();
    window.addEventListener("load", sweep);
    window.addEventListener("hashchange", function () {
      setTimeout(sweep, 60);
    });
  }

  /* ---- Scroll progress + back-to-top ------------------------------------- */
  var bar = document.querySelector(".scroll-progress");
  var toTop = document.querySelector(".to-top");
  var ticking = false;

  function onScroll() {
    var st = window.scrollY || document.documentElement.scrollTop;
    var h =
      document.documentElement.scrollHeight -
      document.documentElement.clientHeight;
    var p = h > 0 ? st / h : 0;
    if (bar) bar.style.transform = "scaleX(" + p.toFixed(4) + ")";
    if (toTop) {
      toTop.classList.toggle("is-visible", st > 600);
      // Same scroll fraction drives the button's circular progress ring (CSS).
      toTop.style.setProperty("--p", p.toFixed(4));
    }
    ticking = false;
  }
  window.addEventListener(
    "scroll",
    function () {
      if (!ticking) {
        ticking = true;
        window.requestAnimationFrame(onScroll);
      }
    },
    { passive: true }
  );
  onScroll();

  if (toTop) {
    toTop.addEventListener("click", function () {
      window.scrollTo({
        top: 0,
        behavior: reduceMotion ? "auto" : "smooth",
      });
    });
  }

  /* ---- Word/quote rotators (About heading + fragrance quote) ------------- */
  function cycle(container, interval) {
    var items = container.querySelectorAll("b");
    if (items.length < 2) return;
    var idx = 0;
    items.forEach(function (el, i) {
      el.classList.toggle("on", i === 0);
    });
    setInterval(function () {
      items[idx].classList.remove("on");
      idx = (idx + 1) % items.length;
      items[idx].classList.add("on");
    }, interval);
  }
  document.querySelectorAll(".rotator-quote").forEach(function (el) {
    cycle(el, 4200);
  });

  /* ---- Phone-number reveal ----------------------------------------------
     Numbers live base64-encoded in `data-p` so the raw HTML has no `tel:`
     prefix and no continuous digit run — cheap regex harvesters bounce.
     JS decodes on load and writes both the visible text and the tel: href.
     If JS fails (rare), the email rows below are still usable. */
  document.querySelectorAll("a.js-tel").forEach(function (a) {
    var enc = a.getAttribute("data-p");
    if (!enc) return;
    var pretty;
    try { pretty = atob(enc); } catch (e) { return; }
    a.setAttribute("href", "tel:" + pretty.replace(/\s+/g, ""));
    a.setAttribute("aria-label", pretty);
    a.textContent = pretty;
  });

  /* ---- Copy button on code blocks --------------------------------------- */
  /* Adds a "Copy" button to every <pre> inside post content. Uses the
     Clipboard API with a textarea fallback for legacy/insecure contexts. */
  var copyIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="8" y="8" width="12" height="12" rx="2"/><path d="M16 8V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h2"/></svg>';
  var checkIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12.5l4 4L19 7"/></svg>';

  function fallbackCopy(text) {
    var ta = document.createElement("textarea");
    ta.value = text;
    ta.setAttribute("readonly", "");
    ta.style.cssText = "position:fixed;top:-1000px;left:0;opacity:0;";
    document.body.appendChild(ta);
    ta.select();
    var ok = false;
    try { ok = document.execCommand("copy"); } catch (e) {}
    document.body.removeChild(ta);
    return ok;
  }

  document.querySelectorAll(".prose pre").forEach(function (pre) {
    if (pre.querySelector(".copy-btn")) return;
    var btn = document.createElement("button");
    btn.type = "button";
    btn.className = "copy-btn";
    btn.setAttribute("aria-label", "Copy code to clipboard");
    btn.innerHTML = copyIcon + "<span>Copy</span>";
    pre.appendChild(btn);

    btn.addEventListener("click", function (e) {
      e.preventDefault();
      // Get pre's text excluding the button itself.
      var clone = pre.cloneNode(true);
      var inner = clone.querySelector(".copy-btn");
      if (inner) inner.remove();
      var text = clone.textContent.replace(/\s+$/, "");

      var done = function (ok) {
        btn.innerHTML = (ok ? checkIcon : copyIcon) +
          "<span>" + (ok ? "Copied" : "Failed") + "</span>";
        btn.classList.toggle("is-copied", ok);
        setTimeout(function () {
          btn.innerHTML = copyIcon + "<span>Copy</span>";
          btn.classList.remove("is-copied");
        }, 1800);
      };

      if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(
          function () { done(true); },
          function () { done(fallbackCopy(text)); }
        );
      } else {
        done(fallbackCopy(text));
      }
    });
  });

  /* ---- Distraction-free reading mode (single posts only). Injects a toggle
     that hides the sidebars/chrome and drops into a clean, centred reading
     column. Preference is remembered in localStorage; Esc exits. ------------- */
  if (body.classList.contains("single")) {
    var FOCUS_KEY = "the-alpha-focus";
    var eyeIcon =
      '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" ' +
      'stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' +
      '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/>' +
      '<circle cx="12" cy="12" r="3"/></svg>';
    var focusBtn = document.createElement("button");
    focusBtn.type = "button";
    focusBtn.className = "focus-toggle";

    function renderFocus(on) {
      focusBtn.setAttribute("aria-pressed", on ? "true" : "false");
      focusBtn.setAttribute(
        "aria-label",
        on ? "Exit distraction-free reading" : "Distraction-free reading"
      );
      focusBtn.innerHTML = eyeIcon + "<span>" + (on ? "Exit" : "Focus") + "</span>";
    }
    function setFocus(on) {
      body.classList.toggle("reading-focus", on);
      try { localStorage.setItem(FOCUS_KEY, on ? "1" : "0"); } catch (e) {}
      renderFocus(on);
    }

    var startOn = false;
    try { startOn = localStorage.getItem(FOCUS_KEY) === "1"; } catch (e) {}
    if (startOn) { body.classList.add("reading-focus"); }
    renderFocus(startOn);

    focusBtn.addEventListener("click", function () {
      setFocus(!body.classList.contains("reading-focus"));
    });
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && body.classList.contains("reading-focus")) {
        setFocus(false);
      }
    });
    /* Sit inline at the end of the post-meta row (contextual); fall back to the
       body if that row isn't present. */
    var metaRow = document.querySelector(".post-meta-single");
    (metaRow || body).appendChild(focusBtn);
  }

  /* ---- Subscribe feed-URL copy button -----------------------------------
     Delegated on document so it works both on the /subscribe/ page and when
     that page's content is loaded into the footer drawer (injected HTML never
     runs its own inline <script>). */
  document.addEventListener("click", function (e) {
    var btn = e.target.closest && e.target.closest(".subscribe__copy");
    if (!btn) return;
    var row = btn.closest(".subscribe__feed-row");
    var input = row
      ? row.querySelector("input")
      : document.querySelector(btn.getAttribute("data-copy-target"));
    if (!input) return;
    var label = btn.querySelector(".subscribe__copy-label");
    var orig = label ? label.textContent : "Copy";
    var copied = btn.getAttribute("data-copied-label") || "Copied";
    var done = function (ok) {
      if (label && ok) label.textContent = copied;
      btn.classList.toggle("is-copied", ok);
      setTimeout(function () {
        if (label) label.textContent = orig;
        btn.classList.remove("is-copied");
      }, 1600);
    };
    input.focus();
    input.select();
    input.setSelectionRange(0, 99999);
    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard.writeText(input.value).then(
        function () { done(true); },
        function () { done(fallbackCopy(input.value)); }
      );
    } else {
      done(fallbackCopy(input.value));
    }
  });

  /* ---- Slide-in drawer for Terms / Subscribe -----------------------------
     Footer [data-drawer] links load their target page's content into a
     right-hand drawer instead of navigating. The real pages stay the
     canonical, shareable, no-JS fallback. Handles focus trap, Esc, scrim,
     scroll-lock, browser Back, and a graceful fallback to full navigation if
     the fetch fails. */
  (function initDrawer() {
    var triggers = Array.prototype.slice.call(
      document.querySelectorAll("[data-drawer]")
    );
    var drawer = document.getElementById("site-drawer");
    if (!triggers.length || !drawer) return;

    var panel = drawer.querySelector(".drawer__panel");
    var titleEl = drawer.querySelector(".drawer__title");
    var contentEl = drawer.querySelector(".drawer__content");
    var scrollEl = drawer.querySelector(".drawer__scroll");
    var inertEls = [
      document.querySelector(".layout"),
      document.querySelector(".site-footer"),
      document.querySelector(".to-top")
    ].filter(Boolean);

    var isOpen = false;
    var lastFocus = null;
    var pushed = false;
    var hashMode = false; // opened via a #terms / #subscribe deep link
    var reqId = 0;

    // The last path segment of a URL, lower-cased: "/terms/" → "terms". Used to
    // pair a trigger with its hash so #terms ↔ the /terms/ link.
    function slugOf(href) {
      return href
        .replace(/[?#].*$/, "")
        .replace(/\/+$/, "")
        .split("/")
        .pop()
        .toLowerCase();
    }

    // Map a URL hash (#terms) to the matching trigger, so shared/clicked deep
    // links open the right drawer.
    function triggerForHash() {
      var h = location.hash.replace(/^#/, "").toLowerCase();
      if (!h) return null;
      for (var i = 0; i < triggers.length; i++) {
        if (slugOf(triggers[i].getAttribute("href")) === h) return triggers[i];
      }
      return null;
    }

    // Track the current input modality. We only return focus to the trigger
    // (which paints a focus ring) when the visitor is driving by keyboard —
    // where that ring is wanted. After a mouse/touch close, restoring focus
    // would flash an unwanted ring on the footer link, so we skip it.
    var keyboardMode = false;
    document.addEventListener("keydown", function () { keyboardMode = true; }, true);
    document.addEventListener("pointerdown", function () { keyboardMode = false; }, true);

    function setInert(on) {
      inertEls.forEach(function (el) {
        if (on) el.setAttribute("inert", "");
        else el.removeAttribute("inert");
      });
    }

    function focusables() {
      return Array.prototype.slice
        .call(
          panel.querySelectorAll(
            'a[href], button:not([disabled]), input:not([disabled]), [tabindex]:not([tabindex="-1"])'
          )
        )
        .filter(function (el) {
          return el.offsetWidth || el.offsetHeight || el === document.activeElement;
        });
    }

    function load(href, fallbackTitle) {
      var id = ++reqId;
      drawer.classList.add("is-loading");
      panel.setAttribute("aria-busy", "true");
      contentEl.className = "drawer__content prose";
      contentEl.innerHTML = "";
      titleEl.textContent = fallbackTitle || "";
      fetch(href, {
        credentials: "same-origin",
        headers: { "X-Requested-With": "fetch" }
      })
        .then(function (r) {
          if (!r.ok) throw new Error("HTTP " + r.status);
          return r.text();
        })
        .then(function (html) {
          if (id !== reqId) return; // a newer open() superseded this load
          var doc = new DOMParser().parseFromString(html, "text/html");
          var article = doc.querySelector(".content-grid .entry");
          var bodyEl = doc.querySelector(".content-grid .prose");
          var titleSrc = doc.querySelector(".page-title");
          if (!bodyEl) throw new Error("No content");
          var name = titleSrc ? titleSrc.textContent.trim() : fallbackTitle || "";
          titleEl.textContent = name;
          // Carry the page-specific class (e.g. .subscribe) so its scoped
          // styles apply; .drawer__content.subscribe neutralises the page's
          // full-height centring (see main.css).
          if (article && article.classList.contains("subscribe")) {
            contentEl.classList.add("subscribe");
          }
          contentEl.innerHTML = bodyEl.innerHTML;
          drawer.classList.remove("is-loading");
          panel.removeAttribute("aria-busy");
          scrollEl.scrollTop = 0;
        })
        .catch(function () {
          if (id !== reqId) return;
          drawer.classList.remove("is-loading");
          panel.removeAttribute("aria-busy");
          var link = document.createElement("p");
          var a = document.createElement("a");
          a.href = href;
          a.textContent = "Open the page →";
          link.appendChild(document.createTextNode("Couldn’t load this here. "));
          link.appendChild(a);
          contentEl.appendChild(link);
        });
    }

    function open(href, label, trigger, viaHash) {
      if (isOpen) {
        // Already open — just swap in the new page's content. No history change:
        // the single same-URL entry from the first open() still backs Esc/Back.
        load(href, label);
        requestAnimationFrame(function () { panel.focus(); });
        return;
      }
      isOpen = true;
      hashMode = !!viaHash;
      lastFocus = trigger || document.activeElement;
      drawer.hidden = false;
      void drawer.offsetWidth; // reflow so the open transition runs
      drawer.classList.add("is-open");
      body.classList.add("drawer-open");
      setInert(true);
      // In-site clicks: push a history entry WITHOUT changing the URL, so Back
      // (and our own close) can pop it to close the drawer while a refresh
      // reloads the page the visitor was on — a transient overlay, not a nav.
      // Deep links (#terms): the hash entry already represents the open drawer
      // and stays in the URL so the link is shareable and survives a refresh;
      // closing strips the hash back to the bare path (see requestClose).
      if (!viaHash && history.pushState) {
        history.pushState({ alphaDrawer: true }, "");
        pushed = true;
      }
      load(href, label);
      requestAnimationFrame(function () { panel.focus(); });
    }

    function closeUI() {
      if (!isOpen) return;
      isOpen = false;
      drawer.classList.remove("is-open");
      body.classList.remove("drawer-open");
      setInert(false);
      var hide = function () { drawer.hidden = true; };
      if (reduceMotion) {
        hide();
      } else {
        var done = false;
        var fin = function () {
          if (done) return;
          done = true;
          hide();
        };
        panel.addEventListener("transitionend", fin, { once: true });
        setTimeout(fin, 420); // fallback if transitionend doesn't fire
      }
      // Return focus to the trigger for keyboard users; for mouse/touch the
      // hidden panel drops focus to the body on its own, so no ring flashes on
      // the footer link.
      if (keyboardMode && lastFocus && document.contains(lastFocus)) {
        lastFocus.focus();
      }
    }

    // Manual close (button / scrim / Esc).
    function requestClose() {
      if (hashMode) {
        // Strip the deep-link hash so the address bar returns to the bare path
        // (e.g. "/") — replaceState avoids a history hop and an extra entry.
        hashMode = false;
        if (history.replaceState) {
          history.replaceState(null, "", location.pathname + location.search);
        }
        closeUI();
      } else if (pushed && history.state && history.state.alphaDrawer) {
        // Rewind the entry we pushed; popstate → closeUI returns the address bar.
        history.back();
      } else {
        closeUI();
      }
    }

    triggers.forEach(function (a) {
      a.addEventListener("click", function (e) {
        // Let modified / non-primary clicks navigate to the real page (new tab).
        if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button) return;
        var seg = slugOf(a.getAttribute("href"));
        if (!seg) return; // can't derive a slug → let it navigate
        e.preventDefault();
        // Route the click through the hash (#terms) so the drawer is shareable
        // and survives a refresh; the hashchange handler opens it. The href
        // stays the real page as the no-JS fallback.
        if (location.hash.replace(/^#/, "").toLowerCase() === seg) {
          syncFromHash(); // hash already set but drawer closed — force open
        } else {
          location.hash = seg;
        }
      });
    });

    drawer.addEventListener("click", function (e) {
      if (e.target.closest("[data-drawer-close]")) requestClose();
    });

    document.addEventListener("keydown", function (e) {
      if (!isOpen) return;
      if (e.key === "Escape") {
        e.preventDefault();
        requestClose();
        return;
      }
      if (e.key === "Tab") {
        var f = focusables();
        if (!f.length) {
          e.preventDefault();
          panel.focus();
          return;
        }
        var first = f[0];
        var last = f[f.length - 1];
        var active = document.activeElement;
        if (e.shiftKey && (active === first || active === panel)) {
          e.preventDefault();
          last.focus();
        } else if (!e.shiftKey && active === last) {
          e.preventDefault();
          first.focus();
        }
      }
    });

    window.addEventListener("popstate", function () {
      pushed = false;
      hashMode = false;
      if (isOpen) closeUI();
    });

    // Deep links: a shared #terms / #subscribe URL (e.g. heera.it/#terms) opens
    // the drawer over whatever page loaded — typically home. Runs on load and
    // whenever the hash changes (Back/forward, manual edits).
    function syncFromHash() {
      var t = triggerForHash();
      if (t) {
        // open() opens when closed, or swaps content when already open.
        open(t.getAttribute("href"), t.textContent.trim(), t, true);
      } else if (isOpen && hashMode) {
        hashMode = false;
        closeUI();
      }
    }
    window.addEventListener("hashchange", syncFromHash);
    syncFromHash();
  })();
})();
