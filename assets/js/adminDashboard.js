// assets/js/adminDashboard.js

document.addEventListener('DOMContentLoaded', function() {
  console.log("Admin Dashboard JS Loaded"); // For checking if file loads

  // --- Sidebar Toggle ---
  // *** TARGET THE NEW ARROW BUTTON ID ***
  const toggleButton = document.getElementById('sidebarCollapseArrow');
  const bodyElement = document.body; // Get the body element

  if (toggleButton && bodyElement) {
      toggleButton.addEventListener('click', function() {
          bodyElement.classList.toggle('sidebar-toggled'); // Toggle class on body
          console.log("Body class 'sidebar-toggled' toggled by arrow."); // Log event

          // --- Icon rotation is handled by CSS now, no JS needed for icon change ---

          // Optional: Save state in localStorage if needed
          // localStorage.setItem('sidebarToggled', bodyElement.classList.contains('sidebar-toggled'));
      });

      // Optional: Check localStorage on load to restore state
      // if (localStorage.getItem('sidebarToggled') === 'true') {
      //    bodyElement.classList.add('sidebar-toggled');
      //    // Note: CSS will handle the arrow rotation based on the body class
      // }

  } else {
      // *** UPDATE ERROR MESSAGE TO REFLECT NEW ID ***
      if (!toggleButton) console.error("Sidebar collapse arrow (#sidebarCollapseArrow) not found.");
      if (!bodyElement) console.error("Body element not found."); // Should always exist
  }


  // --- Clock Functionality (Remains the same) ---
  const clockElement = document.getElementById('clock');

  function updateClock() {
      if (clockElement) {
          const now = new Date();
          // Using shorter options, adjust as needed
          const options = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true };
          clockElement.textContent = now.toLocaleDateString('en-US', options);
      }
  }

  if (clockElement) {
      updateClock(); // Initial call
      setInterval(updateClock, 1000); // Update every second
  } else {
       console.warn("Clock element not found.");
  }

});