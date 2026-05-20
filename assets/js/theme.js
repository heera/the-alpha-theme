/* The Alpha — ~3 KB, no dependencies, deferred.
   Theme toggle · mobile nav · scroll-spy · progress · reveal · quote rotator. */
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

  if (toggle) {
    toggle.addEventListener("click", function () {
      var next =
        root.getAttribute("data-theme") === "light" ? "dark" : "light";
      root.setAttribute("data-theme", next);
      try {
        localStorage.setItem("the-alpha-theme", next);
      } catch (e) {}
      syncToggle();
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
      // Contact is the last section on the front page — scroll to the
      // very bottom so the footer is fully visible. Only override when
      // the #contact section actually exists on the current page; on
      // post archive / single pages let the link navigate normally.
      if (
        l.getAttribute("data-section") === "contact" &&
        document.getElementById("contact")
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
    if (location.hash === "#contact" && document.getElementById("contact")) {
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
          if (
            history.replaceState &&
            location.hash !== "#" + id
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
    if (toTop) toTop.classList.toggle("is-visible", st > 600);
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
  document.querySelectorAll(".rotate-words").forEach(function (el) {
    cycle(el, 2600);
  });
  document.querySelectorAll(".rotator-quote").forEach(function (el) {
    cycle(el, 4200);
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
})();
