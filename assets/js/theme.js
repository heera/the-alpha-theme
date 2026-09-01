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

  /* ---- Page loader: reveal as soon as the LCP hero image has decoded — so it
     is actually painted (no pop-in), not waiting on every below-fold image and
     font the way window.load does. Pages without a hero (posts, archives)
     reveal immediately. At reveal we flip body.is-ready so the layout + footer
     cross-fade up into view (CSS handles the transition). A 3-second cap keeps
     a stuck/oversized request from trapping the user behind the overlay.
     (Previously gated on window.load, which delayed LCP by ~2.3s — the hero was
     downloaded early via fetchpriority but held at opacity:0 until full load.) */
  var loader = document.querySelector(".page-loader");
  var revealed = false;
  var revealPage = function () {
    if (revealed) return;
    revealed = true;
    body.classList.add("is-ready");
    if (loader) {
      loader.classList.add("is-hidden");
      // Remove after the transition so it's gone from the a11y tree.
      setTimeout(function () {
        if (loader && loader.parentNode) loader.parentNode.removeChild(loader);
      }, 700);
    }
  };
  var heroImg = document.querySelector(".hero__bg img");
  if (heroImg && !heroImg.complete && heroImg.decode) {
    heroImg.decode().then(revealPage).catch(revealPage);
  } else {
    revealPage();
  }
  setTimeout(revealPage, 3000); // safety net.

  /* ---- Colour theme: tri-state System / Light / Dark ---------------------
     (the no-FOUC script already set data-theme + data-theme-mode). All three
     modes sit in the control as icons; a click selects that mode directly.
     System follows the OS — live too, via the change listener below; an
     explicit Light/Dark pick pins that palette until the next pick. The
     selected highlight is pure CSS off data-theme-mode; JS only maintains
     aria-pressed. */
  var toggleOpts = Array.prototype.slice.call(
    document.querySelectorAll(".theme-toggle__opt")
  );
  var MODES = ["system", "light", "dark"];
  var osLightMq = window.matchMedia
    ? window.matchMedia("(prefers-color-scheme: light)")
    : null;

  function osScheme() {
    return osLightMq && osLightMq.matches ? "light" : "dark";
  }

  function currentMode() {
    var m = root.getAttribute("data-theme-mode");
    return MODES.indexOf(m) !== -1 ? m : "system";
  }

  function syncToggle() {
    var mode = currentMode();
    toggleOpts.forEach(function (opt) {
      opt.setAttribute(
        "aria-pressed",
        String(opt.getAttribute("data-mode") === mode)
      );
    });
  }
  syncToggle();

  function applyMode(mode, persist) {
    var next = mode === "system" ? osScheme() : mode;
    var changed = root.getAttribute("data-theme") !== next;
    root.setAttribute("data-theme-mode", mode);
    root.setAttribute("data-theme", next);
    if (persist) {
      try {
        localStorage.setItem("the-alpha-theme", mode);
      } catch (e) {}
    }
    syncToggle();
    if (changed) syncDisqus();
  }

  /* Keep the Disqus thread in sync with the colour theme. Disqus samples the
     page background to pick its light/dark scheme ONCE, when its embed loads, so
     toggling the theme afterwards leaves the comment iframe on the old scheme
     until a full page reload. When Disqus is present (it's lazy-loaded on scroll,
     so often it isn't), reset({reload:true}) rebuilds the thread, which re-runs
     that detection against the new background. `config` re-applies the page's
     identity (the global the plugin's embed defines). Deferred a beat so the new
     background is painted before Disqus samples it, and debounced so a flurry of
     toggles only triggers one reload. */
  var disqusSyncTimer = null;
  function syncDisqus() {
    if (!window.DISQUS || typeof window.DISQUS.reset !== "function") return;
    if (disqusSyncTimer) clearTimeout(disqusSyncTimer);
    disqusSyncTimer = setTimeout(function () {
      try {
        window.DISQUS.reset({ reload: true, config: window.disqus_config });
      } catch (e) {}
    }, 350);
  }

  // Crossfade the whole page between light/dark via the View Transitions
  // API (CSS controls the timing). Falls back to an instant switch where
  // it's unsupported or when the user prefers reduced motion. Shared by the
  // toggle and the OS-flip listener below, so both switch the same way.
  function withThemeFade(fn) {
    if (!reduceMotion && typeof document.startViewTransition === "function") {
      document.startViewTransition(fn);
    } else {
      fn();
    }
  }

  toggleOpts.forEach(function (opt) {
    opt.addEventListener("click", function () {
      var mode = opt.getAttribute("data-mode");
      if (MODES.indexOf(mode) === -1 || mode === currentMode()) return;
      // Crossfade only when the palette actually changes — picking e.g.
      // System while the OS is already dark repaints nothing worth animating.
      if (
        root.getAttribute("data-theme") !==
        (mode === "system" ? osScheme() : mode)
      ) {
        withThemeFade(function () {
          applyMode(mode, true);
        });
      } else {
        applyMode(mode, true);
      }
    });
  });

  /* System mode follows the OS while a page is open (macOS/iOS switch
     automatically at sunrise/sunset); explicit Light/Dark picks are pinned
     and ignore the flip. Applies without persisting: nothing the visitor
     didn't choose is written, and the no-FOUC script re-resolves to the same
     answer on the next load anyway. */
  if (osLightMq) {
    var onOsFlip = function () {
      if (currentMode() !== "system") return;
      if (root.getAttribute("data-theme") === osScheme()) return;
      withThemeFade(function () {
        applyMode("system", false);
      });
    };
    if (osLightMq.addEventListener) {
      osLightMq.addEventListener("change", onOsFlip);
    } else if (osLightMq.addListener) {
      osLightMq.addListener(onOsFlip); // Safari < 14
    }
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

  /* ---- Footer height -> --footer-h ---------------------------------------
     #contact is sized (desktop only) to fill the viewport MINUS the footer, so
     the two read as the page's last screen: the seam landing below then puts
     contact's top at the viewport top with the whole footer under it. That only
     holds if the CSS subtracts the footer's REAL height, and the footer's
     height is content-dependent — the machine-links line is absent whenever
     the_alpha_machine_links() comes back empty (plugin inactive), which alone
     moves it by a line. Measure and publish it; main.css carries 14.5rem, the
     measured desktop height today, as the no-JS fallback. offsetHeight, not
     getBoundingClientRect, so the pre-.is-ready translateY can't skew it. */
  var siteFooter = document.querySelector(".site-footer");
  if (siteFooter) {
    var syncFooterHeight = function () {
      root.style.setProperty("--footer-h", siteFooter.offsetHeight + "px");
    };
    syncFooterHeight();
    // Resizing #contact can't resize the footer, so this can't feed back.
    if ("ResizeObserver" in window) {
      new ResizeObserver(syncFooterHeight).observe(siteFooter);
    } else {
      window.addEventListener("resize", syncFooterHeight, { passive: true });
    }
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
      // sit with the footer in one viewport, so landing on its top seam also
      // brings the whole footer into view. Aim at the SEAM, not the document
      // bottom: the two are the same position once the sizing is right, but on
      // a viewport too short to hold contact + footer the document bottom
      // overshoots and cuts the section's own top off. On mobile/tablet
      // (<=1024px) sections are stacked at content height and the footer is a
      // full screen below, so let the native anchor jump handle it (CSS
      // .section scroll-margin-top lands it just below the sticky topbar).
      // Only override when #contact exists (front page); on archive / single
      // pages let the link navigate normally.
      var contactEl = document.getElementById("contact");
      if (
        l.getAttribute("data-section") === "contact" &&
        contactEl &&
        window.innerWidth > 1024
      ) {
        e.preventDefault();
        if (history.replaceState) history.replaceState(null, "", "#contact");
        window.scrollTo({ top: sectionTop(contactEl), behavior: "smooth" });
      }
    });
  });

  /* Deep-link to #contact (e.g. coming from a post page) — land on the
     section's top seam, which on desktop also brings the footer fully into
     view (contact is sized to the viewport minus --footer-h). */
  function sectionTop(el) {
    return Math.round(el.getBoundingClientRect().top + window.pageYOffset);
  }
  function alignContact() {
    // Desktop only — mirrors the click handler above. On mobile/tablet the
    // native hash scroll (offset by .section scroll-margin-top) already lands
    // at the contact section's start.
    var el = document.getElementById("contact");
    if (location.hash === "#contact" && el && window.innerWidth > 1024) {
      requestAnimationFrame(function () {
        window.scrollTo(0, sectionTop(el));
      });
    }
  }
  alignContact();
  window.addEventListener("load", alignContact);
  window.addEventListener("hashchange", alignContact);

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

  /* ---- Portrait scanner sweep trigger -----------------------------------
     The scan + AUTHORIZED is its own trigger, NOT the generic .reveal `.in`
     class: that class is also applied by the safety-net sweep() above to
     anything already scrolled past, which would burn the animation off-screen.
     We want the sweep to play only when you actually LAND on About — arriving
     there and stopping, from any direction (scrolling down, scrolling up, or a
     nav-anchor click) — NOT while merely passing through it on the way to
     Contact. So instead of firing the instant the frame crosses a visibility
     threshold, we wait for scrolling to SETTLE and then check whether the
     portrait is parked in the viewport (straddling its vertical middle, or
     mostly on screen). ONCE-PER-LOAD: after it plays we stop listening; a
     refresh / new page load arms it again. */
  var aboutGrid = document.querySelector(".about__grid");
  if (aboutGrid) {
    var portrait = aboutGrid.querySelector(".portrait") || aboutGrid;

    // Arm the soft-focus immediately so the portrait is already blurred when it
    // scrolls into view; the scan (on landing) resolves it to sharp. CSS gates
    // the blur to no-reduced-motion, and no-JS never arms it — both stay crisp.
    aboutGrid.classList.add("scan-armed");

    var landed = function () {
      var r = portrait.getBoundingClientRect();
      var vh = window.innerHeight || document.documentElement.clientHeight;
      if (r.height === 0) return false;
      var mid = vh / 2;
      // Parked here if the portrait straddles the viewport's middle, or at
      // least half of it (capped to viewport height) is on screen.
      var onScreen = Math.min(r.bottom, vh) - Math.max(r.top, 0);
      var ratio = onScreen / Math.min(r.height, vh);
      return (r.top <= mid && r.bottom >= mid) || ratio >= 0.5;
    };

    var fire = function () {
      aboutGrid.classList.add("scanned");
      window.removeEventListener("scroll", onScanScroll);
      window.removeEventListener("resize", onScanScroll);
      // Lock the scan in after it has played once, so the focus blur never
      // re-fires — switching theme swaps the per-theme photo via display, which
      // would otherwise restart the CSS focus animation on the newly shown <img>.
      setTimeout(function () {
        aboutGrid.classList.add("scan-complete");
        aboutGrid.classList.remove("scan-armed");
      }, 5200);
    };

    var settleTimer = null;
    var onScanScroll = function () {
      if (settleTimer) clearTimeout(settleTimer);
      // Fire only after scrolling has paused (~200ms) AND we ended up on About,
      // so a smooth-scroll/flick that passes through About never burns it.
      settleTimer = setTimeout(function () {
        if (landed()) fire();
      }, 200);
    };

    window.addEventListener("scroll", onScanScroll, { passive: true });
    window.addEventListener("resize", onScanScroll, { passive: true });
    // Cover the case where the page loads already parked on About (refresh
    // mid-page, or a #about deep link) — no scroll event would fire otherwise.
    onScanScroll();
  }

  /* ---- Footer seal: first-view glow flourish ----------------------------
     The seal is always visible at its resting glow. The first time it's dwelt
     on — in view, mostly on screen, for ~1s of settled attention — its glow
     swells smoothly to a bright sci-fi bloom and eases back down to rest. The
     observer then disconnects so it plays just once this load (a refresh
     re-arms it). Purely additive: no fade-in, no hidden state — the seal sits
     at rest before and after, so it can never be left invisible. Skipped for
     reduced-motion and no-JS (both just see the resting seal). */
  var seal = document.querySelector(".site-footer__seal");
  if (seal && !reduceMotion && "IntersectionObserver" in window) {
    var sealDwell = null;
    var sealObs = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting && entry.intersectionRatio >= 0.55) {
            // On screen — wait out a ~1s dwell before swelling the glow.
            if (!sealDwell) {
              sealDwell = setTimeout(function () {
                seal.classList.add("site-footer__seal--lit");
                sealObs.disconnect();
              }, 1000);
            }
          } else if (sealDwell) {
            // Scrolled away before settling — cancel; arm again next time.
            clearTimeout(sealDwell);
            sealDwell = null;
          }
        });
      },
      { threshold: [0, 0.55, 1] }
    );
    sealObs.observe(seal);
  }

  /* ---- Footer seal: hover reveal -----------------------------------------
     At rest the seal shows its real engraving (CODE / SNIFF / EAT / SLEEP),
     sitting square. Pointing at or focusing it swaps the four words to the hover
     set and turns the WHOLE seal 45°, so the pairs settle together — WRONG TURN
     across the top, DON'T CLICK across the bottom. Leaving turns it back and
     restores the words. The swap works for everyone; the turn is skipped under
     reduced-motion. Independent of the first-view bloom (which animates glow). */
  var sealLink = seal && document.querySelector(".site-footer__seal-link");
  if (seal && sealLink) {
    var sealWords = seal.querySelectorAll(".site-footer__seal-text textPath"); // N, E, S, W
    var arrowsCorners = seal.querySelector(".site-footer__seal-arrows");        // four corner arrows (rest)
    var arrowsSides = seal.querySelector(".site-footer__seal-arrows--sides");   // two side arrows (hover)
    var restWords = ["CODE", "SNIFF", "EAT", "SLEEP"];
    // On hover the two phrases ride the top and bottom arcs (sides cleared), so
    // they read upright and in order. A rigid partial turn would flip whichever
    // words land in the lower half, so the motion is a full 360° flourish that
    // lands the seal square again — spin AND readable.
    var hoverWords = ["WRONG TURN", "", "DON’T CLICK", ""];
    var setSealWords = function (words) {
      for (var i = 0; i < sealWords.length; i++) {
        if (words[i] !== undefined) sealWords[i].textContent = words[i];
      }
    };

    var canSpin = !reduceMotion && seal.animate;
    var spin = null;
    var SPIN_MS = 850;
    var spinOnce = function () {
      if (spin) spin.cancel();
      spin = seal.animate(
        [{ transform: "rotate(0deg)" }, { transform: "rotate(360deg)" }],
        { duration: SPIN_MS, easing: "cubic-bezier(0.22, 1, 0.36, 1)" }
      );
      spin.onfinish = function () {
        seal.style.transform = ""; // settle upright
        if (spin) {
          spin.cancel();
          spin = null;
        }
      };
    };
    var resetSpin = function () {
      if (spin) {
        spin.cancel();
        spin = null;
      }
      seal.style.transform = "";
    };

    var enter = function () {
      setSealWords(hoverWords);
      // Corner arrows out (they'd cross the long phrases), side arrows in.
      if (arrowsCorners) arrowsCorners.style.opacity = "0";
      if (arrowsSides) arrowsSides.style.opacity = "1";
      if (canSpin) spinOnce();
    };
    var leave = function () {
      setSealWords(restWords);
      if (arrowsCorners) arrowsCorners.style.opacity = "";
      if (arrowsSides) arrowsSides.style.opacity = "";
      resetSpin();
    };
    sealLink.addEventListener("pointerenter", enter);
    sealLink.addEventListener("focusin", enter);
    sealLink.addEventListener("pointerleave", leave);
    sealLink.addEventListener("focusout", leave);

    // Not a crawlable <a href> (bots would follow it to the 404). Navigate on
    // activation instead — resolves to <origin>/wrong-turn, which 404s into the
    // "lost in the woods" page.
    var go = function () {
      window.location.href = "/wrong-turn";
    };
    sealLink.addEventListener("click", go);
    sealLink.addEventListener("keydown", function (e) {
      if (e.key === "Enter" || e.key === " " || e.key === "Spacebar") {
        e.preventDefault();
        go();
      }
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

  /* ---- Phone + email reveal ----------------------------------------------
     Numbers and addresses live base64-encoded in `data-p` / `data-m` so the
     raw HTML carries no `tel:` prefix, no continuous digit run and no `@` for
     regex harvesters. JS decodes and writes the visible text and the href.

     Takes a root so it can run again over content the drawer injects — same
     problem the subscribe copy button solves with delegation above, except a
     one-shot DOM transform can't be delegated, so it gets re-run instead.

     This is also why contact addresses must stay encoded in the markup rather
     than written as a plain mailto:. A literal one gets rewritten at the edge
     by Cloudflare's Email Address Obfuscation into [email protected], and
     CF's decoder is one-shot over the initial DOM with no way to re-run it —
     so it would break in the drawer permanently. Encoded here, Cloudflare
     finds nothing to rewrite and this stays in charge. */
  function revealContacts(root) {
    var scope = root || document;

    scope.querySelectorAll("a.js-tel").forEach(function (a) {
      var enc = a.getAttribute("data-p");
      if (!enc) return;
      var pretty;
      try { pretty = atob(enc); } catch (e) { return; }
      a.setAttribute("href", "tel:" + pretty.replace(/\s+/g, ""));
      a.setAttribute("aria-label", pretty);
      a.textContent = pretty;
    });

    scope.querySelectorAll("a.js-mail").forEach(function (a) {
      var enc = a.getAttribute("data-m");
      if (!enc) return;
      var addr;
      try { addr = atob(enc); } catch (e) { return; }
      a.setAttribute("href", "mailto:" + addr);
      a.setAttribute("aria-label", addr);
      a.textContent = addr;
    });
  }
  revealContacts(document);

  /* ---- Office availability live tick -------------------------------------
     The status is rendered correct server-side; this just keeps it honest if
     the page stays open across an open/close boundary. Reads the schedule from
     data-* and evaluates "now" in the office timezone via Intl, so it doesn't
     depend on the visitor's own clock. Only the dot + primary label update —
     the schedule line and any reopen hint are left as the server set them. */
  var statusEl = document.querySelector(".contact__status[data-tz]");
  if (statusEl) {
    var stTz = statusEl.getAttribute("data-tz");
    var stOpen = parseInt(statusEl.getAttribute("data-open"), 10);
    var stClose = parseInt(statusEl.getAttribute("data-close"), 10);
    var stTextEl = statusEl.querySelector(".contact__status-text");
    var stLabelOpen = statusEl.getAttribute("data-label-open") || "Available now";
    var stLabelAway = statusEl.getAttribute("data-label-away") || "Away";
    var stLabelHoliday = statusEl.getAttribute("data-label-holiday") || "Holiday";
    var stHolidays = (statusEl.getAttribute("data-holidays") || "")
      .split(",").filter(Boolean);
    var stWeekdays = ["Mon", "Tue", "Wed", "Thu", "Fri"];
    var officeState = function () {
      var parts = new Intl.DateTimeFormat("en-US", {
        timeZone: stTz, weekday: "short", year: "numeric", month: "2-digit",
        day: "2-digit", hour: "2-digit", minute: "2-digit", hour12: false
      }).formatToParts(new Date());
      var m = {};
      parts.forEach(function (p) { m[p.type] = p.value; });
      var ymd = m.year + "-" + m.month + "-" + m.day;
      var holiday = stHolidays.indexOf(ymd) !== -1;
      var mins = (parseInt(m.hour, 10) % 24) * 60 + parseInt(m.minute, 10);
      var inHours = stWeekdays.indexOf(m.weekday) !== -1 && !holiday &&
        mins >= stOpen * 60 && mins < stClose * 60;
      return { open: inHours, holiday: holiday };
    };
    var stTick = function () {
      var s = officeState();
      statusEl.classList.toggle("is-open", s.open);
      statusEl.classList.toggle("is-closed", !s.open);
      if (stTextEl) {
        stTextEl.textContent = s.open ? stLabelOpen
          : (s.holiday ? stLabelHoliday : stLabelAway);
      }
    };
    stTick();
    // Recompute every minute for an open tab, and immediately whenever the page
    // is shown again — background tabs get their timers throttled, so this keeps
    // someone who left the page open across closing time from seeing a stale
    // "Available now" when they return.
    setInterval(stTick, 60000);
    document.addEventListener("visibilitychange", function () {
      if (document.visibilityState === "visible") stTick();
    });
  }

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
    if (pre.parentElement && pre.parentElement.classList.contains("code-wrap")) return;

    /* The button is a SIBLING of <pre>, never a child. Plugins that re-render
       code blocks (SyntaxHighlighter) re-serialise the <pre>'s contents; a
       button living inside it gets escaped and printed as a line of code. */
    var wrap = document.createElement("div");
    wrap.className = "code-wrap";
    pre.parentNode.insertBefore(wrap, pre);
    wrap.appendChild(pre);

    var btn = document.createElement("button");
    btn.type = "button";
    btn.className = "copy-btn";
    btn.setAttribute("aria-label", "Copy code to clipboard");
    btn.innerHTML = copyIcon + "<span>Copy</span>";
    wrap.appendChild(btn);

    btn.addEventListener("click", function (e) {
      e.preventDefault();
      var text = pre.textContent.replace(/\s+$/, "");

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
     The drawer is pure URL-hash state, just like the #home/#about section nav:
     #terms / #subscribe open the matching drawer, anything else closes it, and
     `hashchange` drives the whole thing. A click sets the hash; closing is
     history.back() (so the previous hash, e.g. #about, comes back for free).
     The link href stays the real /terms/ page — it's both the content source
     the panel fetches and the no-JS fallback. Adds focus trap, Esc, scrim,
     scroll-lock, and a graceful fallback to the real page if the fetch fails. */
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

    // The drawer is pure hash state, exactly like the #home/#about/#contact
    // section nav: the hash decides what's open, hashchange drives it, and
    // closing is just history.back() — which returns to whatever hash was there
    // before (e.g. #about) on its own. No bespoke history bookkeeping.
    var isOpen = false;
    var lastFocus = null;
    var closing = false; // guards against a double history.back() (e.g. Esc×2)
    var reqId = 0;

    // The last path segment of a URL, lower-cased: "/terms/" → "terms". Pairs a
    // trigger with its hash so #terms ↔ the /terms/ link.
    function slugOf(href) {
      return href
        .replace(/[?#].*$/, "")
        .replace(/\/+$/, "")
        .split("/")
        .pop()
        .toLowerCase();
    }

    // Map the current URL hash (#terms) to the matching trigger, or null.
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
          // Injected markup missed the load-time pass, so decode any encoded
          // phone/email links this page brought with it.
          revealContacts(contentEl);
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

    // Fetch + show the page named by a trigger; opens the panel if needed, or
    // just swaps the content when already open.
    function show(trigger) {
      if (!isOpen) {
        isOpen = true;
        closing = false;
        lastFocus = trigger || document.activeElement;
        drawer.hidden = false;
        void drawer.offsetWidth; // reflow so the open transition runs
        drawer.classList.add("is-open");
        body.classList.add("drawer-open");
        setInert(true);
      }
      load(trigger.getAttribute("href"), trigger.textContent.trim());
      requestAnimationFrame(function () { panel.focus(); });
    }

    function closeUI() {
      if (!isOpen) return;
      isOpen = false;
      closing = false;
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

    // The drawer's open state mirrors the hash: #terms/#subscribe → that drawer,
    // anything else → closed. Driven by load, click (below), and hashchange.
    function applyHash() {
      var t = triggerForHash();
      if (t) show(t);
      else if (isOpen) closeUI();
    }

    // Close = go back one entry. Whatever hash preceded the drawer (e.g. #about,
    // or nothing) is restored by the browser; the resulting hashchange closes
    // the UI. `closing` stops a repeated Esc/click from popping twice.
    function requestClose() {
      if (!isOpen || closing) return;
      closing = true;
      history.back();
    }

    triggers.forEach(function (a) {
      a.addEventListener("click", function (e) {
        // Let modified / non-primary clicks navigate to the real page (new tab).
        if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button) return;
        var slug = slugOf(a.getAttribute("href"));
        if (!slug) return; // can't derive a slug → let it navigate
        e.preventDefault();
        if (isOpen) {
          // Already open — swap in place without stacking a history entry.
          if (history.replaceState) history.replaceState(null, "", "#" + slug);
          applyHash();
        } else if (location.hash.replace(/^#/, "").toLowerCase() === slug) {
          applyHash(); // hash already matches but closed → open
        } else {
          location.hash = slug; // pushes an entry; hashchange opens the drawer
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

    window.addEventListener("hashchange", applyHash);

    // A shared/deep-link #terms URL (e.g. heera.it/#terms) opens the drawer over
    // whatever page loaded. If that hash is the very first history entry, slip a
    // hash-free entry in behind it so closing (Back) returns to the page instead
    // of leaving the site.
    if (triggerForHash()) {
      if (history.replaceState) {
        var h = location.hash;
        history.replaceState(null, "", location.pathname + location.search);
        history.pushState(null, "", h);
      }
      applyHash();
    }
  })();
})();
