// Mobile Menu JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize mobile menu
    initializeMobileMenu();
    
    // Handle window resize
    window.addEventListener('resize', handleWindowResize);
});

function initializeMobileMenu() {
    const mobileMenuToggle = document.querySelector('[data-bs-target="#mobileMenu"]');
    const mobileMenu = document.getElementById('mobileMenu');
    
    if (mobileMenuToggle && mobileMenu) {
        // Initialize Bootstrap dropdowns
        const dropdownElementList = mobileMenu.querySelectorAll('.dropdown-toggle');
        const dropdownList = [...dropdownElementList].map(dropdownToggleEl => {
            return new bootstrap.Dropdown(dropdownToggleEl, {
                autoClose: false // Prevent auto-close in mobile menu
            });
        });
        
        // Close mobile menu when clicking on a link (except dropdowns)
        const menuLinks = mobileMenu.querySelectorAll('.nav-link:not(.dropdown-toggle)');
        menuLinks.forEach(link => {
            link.addEventListener('click', function() {
                const offcanvas = bootstrap.Offcanvas.getInstance(mobileMenu);
                if (offcanvas) {
                    offcanvas.hide();
                }
            });
        });
        
        // Handle dropdown item clicks
        const dropdownItems = mobileMenu.querySelectorAll('.dropdown-item');
        dropdownItems.forEach(item => {
            item.addEventListener('click', function(e) {
                // Don't prevent default for modal triggers or external links
                const href = this.getAttribute('href');
                if (href && !href.startsWith('#') && !this.hasAttribute('data-bs-toggle')) {
                    // Close mobile menu after clicking dropdown item
                    setTimeout(() => {
                        const offcanvas = bootstrap.Offcanvas.getInstance(mobileMenu);
                        if (offcanvas) {
                            offcanvas.hide();
                        }
                    }, 100);
                }
            });
        });
        
        // Close all dropdowns when offcanvas is hidden
        mobileMenu.addEventListener('hidden.bs.offcanvas', function() {
            dropdownList.forEach(dropdown => {
                dropdown.hide();
            });
        });
        
        // Handle dropdown show/hide events for custom styling
        mobileMenu.addEventListener('show.bs.dropdown', function(e) {
            const dropdownMenu = e.target.nextElementSibling;
            if (dropdownMenu) {
                dropdownMenu.style.display = 'block';
                dropdownMenu.classList.add('show');
            }
        });
        
        mobileMenu.addEventListener('hide.bs.dropdown', function(e) {
            const dropdownMenu = e.target.nextElementSibling;
            if (dropdownMenu) {
                dropdownMenu.classList.remove('show');
                setTimeout(() => {
                    dropdownMenu.style.display = 'none';
                }, 300);
            }
        });
    }
}

function handleWindowResize() {
    const mobileMenu = document.getElementById('mobileMenu');
    
    // Close mobile menu if window is resized to desktop size
    if (window.innerWidth >= 1200 && mobileMenu) {
        const offcanvas = bootstrap.Offcanvas.getInstance(mobileMenu);
        if (offcanvas) {
            offcanvas.hide();
        }
    }
}

// Add smooth scrolling for mobile menu links
function smoothScrollToSection(targetId) {
    const target = document.getElementById(targetId);
    if (target) {
        target.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }
}

// Add active state management for mobile menu
function updateMobileMenuActiveState() {
    const currentPath = window.location.pathname;
    const mobileMenu = document.getElementById('mobileMenu');
    
    if (mobileMenu) {
        const menuLinks = mobileMenu.querySelectorAll('.nav-link');
        menuLinks.forEach(link => {
            const href = link.getAttribute('href');
            if (href && currentPath.includes(href.replace(window.location.origin, ''))) {
                link.classList.add('active');
            } else {
                link.classList.remove('active');
            }
        });
    }
}

// Initialize active state on page load
document.addEventListener('DOMContentLoaded', updateMobileMenuActiveState);