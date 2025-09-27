// Simple Menu Toggle for MyRVM Platform

document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing simple menu toggle...');
    
    // Get all menu toggles
    const menuToggles = document.querySelectorAll('.layout-menu .menu-toggle');
    console.log('Found menu toggles:', menuToggles.length);
    
    // Add click event to each toggle
    menuToggles.forEach((toggle, index) => {
        console.log(`Setting up toggle ${index + 1}:`, toggle);
        
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            console.log('Menu toggle clicked:', this);
            
            // Get the parent menu item
            const menuItem = this.closest('.menu-item');
            const subMenu = menuItem.querySelector(':scope > .menu-sub');
            
            if (!subMenu) {
                console.log('No sub-menu found');
                return;
            }
            
            console.log('Menu item:', menuItem);
            console.log('Sub menu:', subMenu);
            
            // Check if already open
            const isOpen = menuItem.classList.contains('open');
            
            // Close all menus at the same level first
            const parentMenu = menuItem.parentElement;
            const siblingMenuItems = parentMenu.querySelectorAll(':scope > .menu-item');
            
            siblingMenuItems.forEach(sibling => {
                if (sibling !== menuItem) {
                    sibling.classList.remove('open');
                    const siblingSub = sibling.querySelector(':scope > .menu-sub');
                    if (siblingSub) {
                        siblingSub.classList.remove('show');
                    }
                }
            });
            
            // Toggle current menu
            if (!isOpen) {
                menuItem.classList.add('open');
                subMenu.classList.add('show');
                console.log('Menu opened');
            } else {
                menuItem.classList.remove('open');
                subMenu.classList.remove('show');
                console.log('Menu closed');
            }
        });
    });
    
    // Close menus when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.layout-menu .menu-item')) {
            document.querySelectorAll('.layout-menu .menu-item').forEach(item => {
                item.classList.remove('open');
                const sub = item.querySelector(':scope > .menu-sub');
                if (sub) {
                    sub.classList.remove('show');
                }
            });
        }
    });
});