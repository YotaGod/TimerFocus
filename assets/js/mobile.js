// Mobile-specific JavaScript enhancements

// Detect mobile device
function isMobile() {
  return (
    /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(
      navigator.userAgent
    ) || window.innerWidth <= 768
  );
}

// Safe vibration function
function safeVibrate(duration = 50) {
  // Check if vibration is supported and allowed
  if (navigator.vibrate && document.hasFocus() && userHasInteracted) {
    try {
      navigator.vibrate(duration);
      return true;
    } catch (error) {
      console.log("Vibration failed:", error.message);
      return false;
    }
  }
  return false;
}

// Alternative haptic feedback for devices without vibration
function hapticFeedback() {
  if (safeVibrate(50)) {
    return true;
  }

  // Fallback: visual feedback
  const activeElement = document.activeElement;
  if (activeElement && activeElement.classList.contains("btn")) {
    activeElement.classList.add("touch-feedback");
    setTimeout(() => {
      activeElement.classList.remove("touch-feedback");
    }, 100);
  }

  return false;
}

// Track user interaction for vibration permission
let userHasInteracted = false;

document.addEventListener(
  "touchstart",
  function () {
    userHasInteracted = true;
    console.log("User interaction detected - vibration enabled");
  },
  { once: true }
);

document.addEventListener(
  "mousedown",
  function () {
    userHasInteracted = true;
    console.log("User interaction detected - vibration enabled");
  },
  { once: true }
);

// Log vibration support on page load
document.addEventListener("DOMContentLoaded", function () {
  if (navigator.vibrate) {
    console.log("Vibration API supported");
  } else {
    console.log("Vibration API not supported - using visual feedback");
  }
});

// Prevent double-tap zoom on buttons
document.addEventListener("DOMContentLoaded", function () {
  if (isMobile()) {
    const buttons = document.querySelectorAll(".btn");
    buttons.forEach((button) => {
      button.addEventListener(
        "touchstart",
        function (e) {
          e.preventDefault();
        },
        { passive: false }
      );
    });
  }
});

// Better mobile keyboard handling
document.addEventListener("DOMContentLoaded", function () {
  if (isMobile()) {
    const inputs = document.querySelectorAll(
      'input[type="text"], input[type="date"]'
    );
    inputs.forEach((input) => {
      input.addEventListener("focus", function () {
        // Scroll to input on focus
        setTimeout(() => {
          this.scrollIntoView({ behavior: "smooth", block: "center" });
        }, 300);
      });
    });
  }
});

// Swipe gestures for table navigation
document.addEventListener("DOMContentLoaded", function () {
  if (isMobile()) {
    const tableWrapper = document.querySelector(".table-wrapper");
    if (tableWrapper) {
      let startX = 0;
      let startY = 0;
      let isScrolling = false;

      tableWrapper.addEventListener("touchstart", function (e) {
        startX = e.touches[0].clientX;
        startY = e.touches[0].clientY;
        isScrolling = false;
      });

      tableWrapper.addEventListener("touchmove", function (e) {
        if (!isScrolling) {
          const deltaX = Math.abs(e.touches[0].clientX - startX);
          const deltaY = Math.abs(e.touches[0].clientY - startY);

          if (deltaY > deltaX) {
            isScrolling = true;
          }
        }
      });

      tableWrapper.addEventListener("touchend", function (e) {
        if (!isScrolling) {
          const deltaX = e.changedTouches[0].clientX - startX;
          const deltaY = e.changedTouches[0].clientY - startY;

          if (Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > 50) {
            // Horizontal swipe detected
            if (deltaX > 0) {
              // Swipe right - could be used for navigation
              console.log("Swipe right detected");
            } else {
              // Swipe left - could be used for navigation
              console.log("Swipe left detected");
            }
          }
        }
      });
    }
  }
});

// Mobile-optimized timer controls
document.addEventListener("DOMContentLoaded", function () {
  if (isMobile()) {
    const timerControls = document.querySelector(".timer-controls");
    if (timerControls) {
      // Add haptic feedback for timer buttons
      const buttons = timerControls.querySelectorAll(".btn");
      buttons.forEach((button) => {
        button.addEventListener("touchstart", function (e) {
          // Provide haptic feedback
          hapticFeedback();
        });
      });
    }
  }
});

// Prevent pull-to-refresh on mobile
document.addEventListener("DOMContentLoaded", function () {
  if (isMobile()) {
    let startY = 0;
    let currentY = 0;
    let initialScrollTop = 0;

    document.addEventListener(
      "touchstart",
      function (e) {
        startY = e.touches[0].clientY;
        initialScrollTop = window.pageYOffset;
      },
      { passive: true }
    );

    document.addEventListener(
      "touchmove",
      function (e) {
        currentY = e.touches[0].clientY;
        const deltaY = currentY - startY;

        // Prevent pull-to-refresh when scrolling down from top
        if (window.pageYOffset === 0 && deltaY > 0) {
          e.preventDefault();
        }
      },
      { passive: false }
    );
  }
});

// Mobile-optimized chart interactions
document.addEventListener("DOMContentLoaded", function () {
  if (isMobile()) {
    // Improve chart touch interactions
    const charts = document.querySelectorAll("canvas");
    charts.forEach((canvas) => {
      canvas.style.touchAction = "manipulation";
    });
  }
});
