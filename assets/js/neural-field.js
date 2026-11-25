/* The Alpha — neural network behind the home page's Blog section.
   Ported from fluent-mcp-relay's NeuralField: faint nodes drift slowly and
   nearby ones are linked by thin lines. No travelling sparks.
   Dark mode: the relay's teal/cyan/blue/violet, as glints.
   Light mode: bronze pencil hairlines, slightly bowed, as if drawn on paper.
   Still for people who turn motion off; paused while off screen or the tab
   is hidden; redraws when the theme flips. */
(function () {
  "use strict";

  var canvas = document.querySelector("#blog .neural-field");
  var context = canvas && canvas.getContext && canvas.getContext("2d");
  if (!context) return;

  var root = document.documentElement;
  var GLINTS = [
    [45, 212, 191], // teal
    [34, 211, 238], // cyan
    [96, 165, 250], // blue
    [167, 139, 250], // soft violet
  ];
  var PENCIL = [138, 96, 55]; // light --accent, champagne bronze

  /* Nodes per million square pixels, and the farthest two may be linked.
     Light mode's pencil lines are softer, so it carries more of them. */
  var DENSITY = { dark: 95, light: 130 };
  var REACH = 150;
  var material = "";

  var still = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  var nodes = [];
  var width = 0;
  var height = 0;
  var frame = 0;
  var onScreen = true;

  function rgba(c, a) {
    return "rgba(" + c[0] + "," + c[1] + "," + c[2] + "," + a + ")";
  }

  function isLight() {
    return root.getAttribute("data-theme") === "light";
  }

  function seed() {
    material = isLight() ? "light" : "dark";
    var count = Math.round(((width * height) / 1e6) * DENSITY[material]);
    nodes = [];
    for (var i = 0; i < count; i++) {
      nodes.push({
        id: i,
        x: Math.random() * width,
        y: Math.random() * height,
        vx: (Math.random() - 0.5) * 0.12,
        vy: (Math.random() - 0.5) * 0.12,
        r: 0.8 + Math.random() * 1.6,
        c: GLINTS[Math.floor(Math.random() * GLINTS.length)],
      });
    }
  }

  function resize() {
    var ratio = Math.min(window.devicePixelRatio || 1, 2);
    width = canvas.clientWidth;
    height = canvas.clientHeight;
    canvas.width = width * ratio;
    canvas.height = height * ratio;
    context.setTransform(ratio, 0, 0, ratio, 0, 0);
    seed();
    draw();
  }

  function draw() {
    var light = isLight();
    var i, j, a, b, d, k, n;

    context.clearRect(0, 0, width, height);
    context.lineCap = "round";
    context.lineWidth = light ? 0.6 : 0.7;

    for (i = 0; i < nodes.length; i++) {
      a = nodes[i];
      for (j = i + 1; j < nodes.length; j++) {
        b = nodes[j];
        d = Math.hypot(a.x - b.x, a.y - b.y);
        if (d > REACH || d === 0) continue;
        k = 1 - d / REACH;
        context.beginPath();
        context.moveTo(a.x, a.y);
        if (light) {
          // A fixed small bow per pair, so each line keeps its own hand.
          var bend = (((a.id * 31 + b.id * 17) % 7) - 3) * 0.9;
          context.strokeStyle = rgba(PENCIL, k * 0.3);
          context.quadraticCurveTo(
            (a.x + b.x) / 2 - ((b.y - a.y) / d) * bend,
            (a.y + b.y) / 2 + ((b.x - a.x) / d) * bend,
            b.x,
            b.y
          );
        } else {
          var line = context.createLinearGradient(a.x, a.y, b.x, b.y);
          line.addColorStop(0, rgba(a.c, k * 0.22));
          line.addColorStop(1, rgba(b.c, k * 0.22));
          context.strokeStyle = line;
          context.lineTo(b.x, b.y);
        }
        context.stroke();
      }
    }

    for (i = 0; i < nodes.length; i++) {
      n = nodes[i];
      context.fillStyle = light ? rgba(PENCIL, 0.4) : rgba(n.c, 0.55);
      context.beginPath();
      context.arc(n.x, n.y, light ? n.r * 0.8 : n.r, 0, Math.PI * 2);
      context.fill();
    }
  }

  function step() {
    for (var i = 0; i < nodes.length; i++) {
      var n = nodes[i];
      n.x += n.vx;
      n.y += n.vy;
      if (n.x < 0 || n.x > width) n.vx *= -1;
      if (n.y < 0 || n.y > height) n.vy *= -1;
    }
    draw();
    frame = window.requestAnimationFrame(step);
  }

  function play() {
    window.cancelAnimationFrame(frame);
    if (!still && !document.hidden && onScreen) {
      frame = window.requestAnimationFrame(step);
    }
  }

  resize();
  play();

  if (typeof ResizeObserver === "function") {
    new ResizeObserver(resize).observe(canvas);
  }
  if (typeof IntersectionObserver === "function") {
    new IntersectionObserver(function (entries) {
      onScreen = entries[0].isIntersecting;
      play();
    }).observe(canvas);
  }
  document.addEventListener("visibilitychange", play);
  // The theme switch flips data-theme on <html>; repaint in the new material.
  new MutationObserver(function () {
    if ((isLight() ? "light" : "dark") !== material) seed();
    draw();
  }).observe(root, {
    attributes: true,
    attributeFilter: ["data-theme"],
  });
})();
