// Navigation functionality
function initNavigation() {
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileNav = document.getElementById('mobileNav');
    const mobileOverlay = document.getElementById('mobileOverlay');
    const mobileCloseBtn = document.getElementById('mobileCloseBtn');
    
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', () => {
            mobileNav.classList.add('active');
            mobileOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
    }
    
    function closeMobileMenu() {
        if (mobileNav) mobileNav.classList.remove('active');
        if (mobileOverlay) mobileOverlay.classList.remove('active');
        document.body.style.overflow = '';
        
        // Close all dropdowns
        document.querySelectorAll('.mobile-dropdown-menu').forEach(menu => {
            menu.classList.remove('active');
        });
        
        document.querySelectorAll('.mobile-dropdown-toggle').forEach(toggle => {
            toggle.classList.remove('active');
        });
    }
    
    if (mobileCloseBtn) {
        mobileCloseBtn.addEventListener('click', closeMobileMenu);
    }
    
    if (mobileOverlay) {
        mobileOverlay.addEventListener('click', closeMobileMenu);
    }
    
    // Mobile dropdown toggle
    document.querySelectorAll('.mobile-dropdown-toggle').forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const menu = this.nextElementSibling;
            
            // Toggle current dropdown
            this.classList.toggle('active');
            if (menu) menu.classList.toggle('active');
            
            // Close other dropdowns
            document.querySelectorAll('.mobile-dropdown-toggle').forEach(otherToggle => {
                if (otherToggle !== this) {
                    otherToggle.classList.remove('active');
                }
            });
            
            document.querySelectorAll('.mobile-dropdown-menu').forEach(otherMenu => {
                if (otherMenu !== menu) {
                    otherMenu.classList.remove('active');
                }
            });
        });
    });
    
    // Sticky Header
    const header = document.getElementById('header');
    if (header) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 100) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
    
            // Show/hide back to top button
            const backToTop = document.getElementById('backToTop');
            if (backToTop) {
                if (window.scrollY > 500) {
                    backToTop.classList.add('active');
                } else {
                    backToTop.classList.remove('active');
                }
            }
        });
    }
    
    // Smooth Scrolling for Anchor Links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                e.preventDefault();
                
                window.scrollTo({
                    top: targetElement.offsetTop - 80,
                    behavior: 'smooth'
                });
                
                // Close mobile menu if open
                closeMobileMenu();
                
                // Update active nav item
                document.querySelectorAll('.nav-main a, .mobile-nav a').forEach(link => {
                    link.classList.remove('active');
                });
                this.classList.add('active');
            }
        });
    });
    
    // Update active nav item on scroll
    window.addEventListener('scroll', () => {
        const sections = document.querySelectorAll('section');
        const navLinks = document.querySelectorAll('.nav-main a, .mobile-nav > ul > li > a');
        
        let current = '';
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;
            if (scrollY >= sectionTop - 100) {
                current = section.getAttribute('id');
            }
        });
        
        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === `#${current}`) {
                link.classList.add('active');
            }
        });
    });
}