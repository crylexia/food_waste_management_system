/**
 * EcoManager Navigation Highlighting
 * Handles active tab highlighting and state persistence
 */

(function() {
    'use strict';

    // Initialize navigation highlighting on page load
    document.addEventListener('DOMContentLoaded', function() {
        initializeNavigation();
    });

    /**
     * Initialize navigation highlighting
     */
    function initializeNavigation() {
        const navItems = document.querySelectorAll('.nav-item');
        const currentPath = window.location.pathname;

        // Store the current active route in sessionStorage for persistence
        storeActiveRoute(currentPath);

        // Add click event listeners to navigation items
        navItems.forEach(function(item) {
            item.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                
                // Store the clicked route before navigation
                if (href) {
                    storeActiveRoute(href);
                }

                // Add visual feedback
                highlightNavItem(this);
            });
        });

        // Restore active state from sessionStorage if needed
        restoreActiveState();
    }

    /**
     * Store the active route in sessionStorage
     * @param {string} path - The current path
     */
    function storeActiveRoute(path) {
        try {
            sessionStorage.setItem('ecomanager_active_route', path);
        } catch (e) {
            // SessionStorage might not be available in some browsers
            console.warn('SessionStorage not available:', e);
        }
    }

    /**
     * Restore active state from sessionStorage
     * This ensures the active state persists across page reloads
     */
    function restoreActiveState() {
        try {
            const storedRoute = sessionStorage.getItem('ecomanager_active_route');
            const currentPath = window.location.pathname;

            // If stored route matches current path, ensure highlighting is correct
            if (storedRoute && storedRoute === currentPath) {
                const navItems = document.querySelectorAll('.nav-item');
                navItems.forEach(function(item) {
                    const href = item.getAttribute('href');
                    
                    // Check if this nav item should be active
                    if (href && isRouteActive(href, currentPath)) {
                        item.classList.add('active');
                    }
                });
            }
        } catch (e) {
            console.warn('Could not restore active state:', e);
        }
    }

    /**
     * Check if a route should be considered active
     * @param {string} href - The navigation item's href
     * @param {string} currentPath - The current page path
     * @returns {boolean}
     */
    function isRouteActive(href, currentPath) {
        // Exact match
        if (href === currentPath) {
            return true;
        }

        // Check if current path starts with the href (for nested routes)
        // e.g., /items/create should highlight the /items nav item
        const hrefBase = href.split('?')[0]; // Remove query params
        const currentBase = currentPath.split('?')[0];

        if (currentBase.startsWith(hrefBase) && hrefBase !== '/') {
            return true;
        }

        return false;
    }

    /**
     * Highlight a navigation item with smooth transition
     * @param {HTMLElement} navItem - The navigation item to highlight
     */
    function highlightNavItem(navItem) {
        // Remove active class from all items
        const allNavItems = document.querySelectorAll('.nav-item');
        allNavItems.forEach(function(item) {
            item.classList.remove('active');
        });

        // Add active class to clicked item
        navItem.classList.add('active');

        // Add a subtle animation effect
        navItem.style.transition = 'all 0.3s ease';
    }

    /**
     * Clear stored navigation state (useful for logout)
     */
    function clearNavigationState() {
        try {
            sessionStorage.removeItem('ecomanager_active_route');
        } catch (e) {
            console.warn('Could not clear navigation state:', e);
        }
    }

    // Expose clearNavigationState globally for logout functionality
    window.EcoManager = window.EcoManager || {};
    window.EcoManager.clearNavigationState = clearNavigationState;

})();
