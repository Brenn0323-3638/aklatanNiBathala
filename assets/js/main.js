// assets/js/main.js

document.addEventListener('DOMContentLoaded', function () {
    console.log("User main JS starting...");

    // --- Active Navigation Link Highlighting ---
    try {
        const currentLocation = window.location.pathname;
        const navLinks = document.querySelectorAll('#mainNavbar .nav-link');

        function getBaseName(urlPath) {
            if (!urlPath || urlPath === '/' || urlPath.endsWith('/index.php')) {
                return 'index.php'; // Explicitly map root or index.php to index.php
            }
            const parts = urlPath.split('/');
            // Ensure the last part is not empty if URL ends with /
            let filename = parts[parts.length - 1];
            if (filename === '') {
                filename = parts[parts.length - 2]; // Try the directory name if filename is empty
            }
             // Remove query parameters and hash from filename
            if (filename) {
                filename = filename.split('?')[0].split('#')[0];
            }
            return filename || 'index.php'; // Fallback if still empty
        }

        const currentPageFile = getBaseName(currentLocation);
        // console.log("Current page determined as:", currentPageFile); // Debug current page

        if (navLinks.length > 0) {
            navLinks.forEach(link => {
                const href = link.getAttribute('href');
                if (href) { // Make sure href exists
                    const linkFile = getBaseName(href);
                    // console.log(`Checking link: ${href} -> ${linkFile}`); // Debug link checking

                    link.classList.remove('active');
                    link.removeAttribute('aria-current');

                    if (linkFile === currentPageFile) {
                        link.classList.add('active');
                        link.setAttribute('aria-current', 'page');
                        // console.log(`Setting active: ${href}`); // Debug active link
                    }
                }
            });
        } else {
            // console.warn("No navigation links found with selector '#mainNavbar .nav-link'.");
        }
    } catch (error) {
        console.error("Error during navigation link highlighting:", error);
    }


    // --- Intersection Observer for Scroll Animations ---
    try {
        const elementsToFade = document.querySelectorAll('.scroll-fade-in');

        if (elementsToFade.length > 0) {
            const observerOptions = {
                root: null,
                rootMargin: '0px',
                threshold: 0.1 // Adjust threshold as needed (0.1 means 10% visible)
            };

            const observerCallback = (entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        // console.log('Element intersecting:', entry.target); // Debug intersection
                        entry.target.classList.add('visible');
                        observer.unobserve(entry.target); // Stop observing once visible
                    }
                });
            };

            const observer = new IntersectionObserver(observerCallback, observerOptions);

            elementsToFade.forEach(el => {
                observer.observe(el);
            });

            // console.log(`Observing ${elementsToFade.length} elements for scroll fade-in.`);

        } else {
            // console.log("No elements found with class 'scroll-fade-in' to observe.");
        }
    } catch (error) {
        console.error("Error setting up Intersection Observer:", error);
    }

    // --- Profile Page Specific JavaScript ---
    // Check if we are on the profile page by looking for an element unique to it.
    const profileUpdateForm = document.getElementById('profileUpdateForm');
    const dangerZoneSection = document.getElementById('dangerZoneSection'); // ID added in profile.php

    if (profileUpdateForm || dangerZoneSection) { // Check if either element exists, indicating it's the profile page
        console.log("Profile page specific JS initializing...");

        // 1. Live preview for profile picture
        const profilePictureInput = document.getElementById('profilePicture');
        const profileImagePreview = document.getElementById('profileImagePreview');
        
        if (profilePictureInput && profileImagePreview) {
            profilePictureInput.addEventListener('change', function(event) {
                if (event.target.files && event.target.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        profileImagePreview.src = e.target.result;
                    }
                    reader.readAsDataURL(event.target.files[0]);
                }
            });
            // console.log("Profile picture preview handler attached.");
        }

        // 2. Delete Account Modal Logic
        const deleteModalElement = document.getElementById('deleteAccountModal');
        const deleteAccountFormErrorDisplay = document.getElementById('deleteAccountFormErrorDisplay');

        if (deleteModalElement && deleteAccountFormErrorDisplay) {
            // Retrieve PHP-passed data
            let phpDataForProfile = {};
            const profilePageDataElement = document.getElementById('profilePageData');
            if (profilePageDataElement && profilePageDataElement.textContent) {
                try {
                    phpDataForProfile = JSON.parse(profilePageDataElement.textContent);
                    // console.log("Parsed profilePageData:", phpDataForProfile);
                } catch (e) {
                    console.error("Error parsing profilePageData JSON:", e);
                }
            }

            // Handle delete_account_error from session by showing modal if error exists
            if (phpDataForProfile.deleteAccountError) {
                var deleteModalInstance = new bootstrap.Modal(deleteModalElement);
                deleteAccountFormErrorDisplay.textContent = phpDataForProfile.deleteAccountError;
                deleteAccountFormErrorDisplay.style.display = 'block';
                deleteModalInstance.show();
                // console.log("Delete account modal shown due to error:", phpDataForProfile.deleteAccountError);
            }

            // Clear password field and error in delete modal when it's hidden
            deleteModalElement.addEventListener('hidden.bs.modal', function () {
                const passwordField = document.getElementById('deleteConfirmPassword');
                if (passwordField) {
                    passwordField.value = ''; // Clear password
                }
                // Clear error message
                deleteAccountFormErrorDisplay.textContent = '';
                deleteAccountFormErrorDisplay.style.display = 'none';
                // console.log("Delete account modal hidden, fields cleared.");
            });
            // console.log("Delete account modal handlers attached.");
        }
        // console.log("Profile page specific JS initialized.");
    } // End of profile page specific JS


    // Add other user-facing JS here later

    console.log("User main JS finished.");
});