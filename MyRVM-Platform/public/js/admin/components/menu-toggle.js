// Menu Toggle Functionality for MyRVM Platform

(function() {
    'use strict';
    
    function initMenuToggle() {
        console.log('Initializing MyRVM menu toggle...');
        
        // Remove all existing event listeners first
        const existingToggles = document.querySelectorAll('.layout-menu .menu-toggle');
        existingToggles.forEach(toggle => {
            toggle.removeEventListener('click', handleMenuToggle);
        });
        
        const menuToggles = document.querySelectorAll('.layout-menu .menu-toggle');
        console.log('Found menu toggles:', menuToggles.length);

        menuToggles.forEach((toggle, index) => {
            console.log(`Setting up toggle ${index + 1}:`, toggle);
            toggle.addEventListener('click', handleMenuToggle);
        });

        // Close all menus when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.layout-menu .menu-item')) {
                closeAllMenus();
            }
        });
    }
    
    function handleMenuToggle(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Menu toggle clicked:', this);

        const currentMenuItem = this.closest('.menu-item');
        const currentSubMenu = currentMenuItem.querySelector(':scope > .menu-sub');

        if (!currentSubMenu) {
            console.log('No sub-menu found for this item');
            return;
        }

        console.log('Current menu item:', currentMenuItem);
        console.log('Current sub-menu:', currentSubMenu);

        // Check if current menu is already open
        const isCurrentlyOpen = currentMenuItem.classList.contains('open');
        
        // Close all other menus first
        closeAllMenus();

        // Toggle current menu
        if (!isCurrentlyOpen) {
            currentMenuItem.classList.add('open');
            currentSubMenu.classList.add('show');
            console.log('Menu opened');
        } else {
            console.log('Menu closed');
        }
    }
    
    function closeAllMenus() {
        const allMenuItems = document.querySelectorAll('.layout-menu .menu-item');
        allMenuItems.forEach(item => {
            item.classList.remove('open');
            const subMenu = item.querySelector(':scope > .menu-sub');
            if (subMenu) {
                subMenu.classList.remove('show');
            }
        });
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMenuToggle);
    } else {
        initMenuToggle();
    }
    
    // Re-initialize after a short delay to ensure all scripts are loaded
    setTimeout(initMenuToggle, 100);
    
})();