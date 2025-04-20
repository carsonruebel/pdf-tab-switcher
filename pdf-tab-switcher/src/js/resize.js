/**
 * PDF Tab Switcher - Auto-Resize Script
 *
 * Dynamically adjusts font size for overlay span elements to ensure content fits within its container.
 * Works with Elementor live preview, typography updates, and responsive breakpoints.
 *
 * @file        resize.js
 * @author      Carson Ruebel
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 */

// ─────────────────────────────────────────────
// Resize Throttling (uses requestAnimationFrame)
// ─────────────────────────────────────────────
let resizeRunning = false;

window.addEventListener("resize", function () {
  if (!resizeRunning) {
    resizeRunning = true;
    requestAnimationFrame(function () {
      runAllResizers();
      resizeRunning = false;
    });
  }
});

/**
 * Run all auto-resizing logic for overlays.
 */
function runAllResizers() {
  runAutoResize();          // Resize span overlays
}

/**
 * Resize single-line text overlays using binary search.
 * Targets spans inside .auto-resize containers.
 */
function runAutoResize() {
  var overlays = document.querySelectorAll(".auto-resize span");
  if (!overlays || overlays.length === 0) return;

  overlays.forEach(function (span) {
    var container = span.parentElement;
    var containerHeight = container.clientHeight;
    var containerWidth = container.clientWidth;

    var minFontSize = 4;
    var maxFontSize = containerHeight * 0.8;
    var bestFit = minFontSize;
    var attempts = 0;

    // Reset styles before fitting
    span.style.whiteSpace = "nowrap";
    span.style.overflow = "visible";
    span.style.display = "inline-block";
    span.style.padding = "0";
    span.style.margin = "0";
    span.style.maxWidth = "none";
    span.style.maxHeight = "none";
    span.style.transition = "opacity 0.3s ease";

    // Binary search for best font size
    while (maxFontSize - minFontSize > 0.5 && attempts < 20) {
      attempts++;
      var mid = (minFontSize + maxFontSize) / 2;
      span.style.fontSize = mid + "px";

      var fits =
        span.scrollWidth <= containerWidth &&
        span.scrollHeight <= containerHeight;

      if (fits) {
        bestFit = mid;
        minFontSize = mid;
      } else {
        maxFontSize = mid;
      }
    }

    // Apply final size and cleanup
    span.style.fontSize = bestFit + "px";
    span.style.overflow = "hidden";
    span.style.maxWidth = "100%";
    span.style.maxHeight = "100%";
    span.style.visibility = "visible";
    span.style.opacity = "1";

    // ───────────────────────────────────────────────────────────────
    // ResizeObserver logic for Elementor container responsiveness
    // ───────────────────────────────────────────────────────────────
    //
    // In the Elementor editor, changing a column width (dragging edges)
    // does NOT trigger a window resize event, so this ensures that if
    // the *parent container* of our widget resizes, we rerun our resizer.
    //
    // 1. span.closest(".elementor-widget-switcher-widget")
    //    → Gets the current instance of our PDF Switcher widget.
    //
    // 2. .closest(".elementor-column")
    //    → Finds the column wrapping the widget so we can observe it.
    //
    // 3. We store a flag `__resizeObserved` to prevent double observers.
    const widgetWrapper = span.closest(".elementor-widget-switcher-widget");
    let containerToWatch = null;
    
    if (widgetWrapper) {
      const column = widgetWrapper.closest(".elementor-column");
      containerToWatch = column || widgetWrapper;
    }

    if (containerToWatch && !containerToWatch.__resizeObserved) {
      const observer = new ResizeObserver(() => {
        console.log("📐 ResizeObserver triggered on Elementor column");
        runAllResizers();
      });
      observer.observe(containerToWatch);
      containerToWatch.__resizeObserved = true;
    }
  });
}

/**
 * Hook into Elementor's frontend and editor events to rerun resizing logic.
 */
function waitForElementorReady() {
  var interval = setInterval(function () {
    if (
      window.elementorFrontend &&
      window.elementorFrontend.hooks
    ) {
      // Elementor live preview render
      window.elementorFrontend.hooks.addAction(
        "frontend/element_ready/switcher-widget.default",
        function () {
          setTimeout(runAllResizers, 150); // Let DOM settle
        }
      );

      // Elementor font setting changes
      if (
        window.elementor &&
        window.elementor.channels &&
        window.elementor.channels.editor
      ) {
        var fontChangeTimeout;
        window.elementor.channels.editor.on("change", function (model) {
          var modelData = model && model.model && model.model.attributes;
          if (modelData && modelData.name && modelData.name.indexOf("job_title_typography_") === 0) {
            clearTimeout(fontChangeTimeout);
            fontChangeTimeout = setTimeout(runAllResizers, 150);
          }
        });
      }

      // Elementor device mode changes
      if (
        window.elementor &&
        window.elementor.channels &&
        window.elementor.channels.deviceMode
      ) {
        window.elementor.channels.deviceMode.on("change", function () {
          setTimeout(runAllResizers, 100);
        });
      }

      clearInterval(interval); // Stop checking
    }
  }, 200);
}

/**
 * Trigger auto-resize after fonts load on initial page load.
 */
document.addEventListener("DOMContentLoaded", function () {
  var runWithFonts = function () {
    setTimeout(runAllResizers, 0); // Let fonts apply (especially bold/weight)
  };

  if (document.fonts && document.fonts.ready) {
    document.fonts.ready.then(runWithFonts);
  } else {
    runWithFonts();
  }
});

// Start Elementor hooks listener
waitForElementorReady();