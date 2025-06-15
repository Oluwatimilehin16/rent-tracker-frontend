<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rent Tracker - Simplify Property Management</title>
    <link rel="stylesheet" href="index.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="nav-container">
            <div class="logo">
        <a href="index.php"><img src="./assets/logo.png" alt="RentTracker"></a>
    </div>
             <nav>
                <ul class="nav-menu" id="navMenu">
                    <li><a href="#home">Home</a></li>
                    <li><a href="#features">Features</a></li>
                    <li><a href="#how-it-works">How It Works</a></li>
                    <li><a href="#blog">Blog</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
                <div class="mobile-menu" id="mobileMenu">☰</div>
            </nav>
        </div>
    </header>

    <!-- Hero Section with Slideshow -->
    <section class="hero" id="home">
        <div class="slideshow-container">
            <div class="slide slide1 active">
                <div class="hero-content">
                    <h1 class="hero-title">Simplify Property Management</h1>
                    <p class="hero-subtitle">Connect landlords and tenants through seamless communication and transparent bill tracking</p>
                    <div class="cta-buttons">
                        <a href="register.php" class="cta-btn cta-primary">Start as Landlord</a>
                        <a href="register.php" class="cta-btn cta-secondary">Join as Tenant</a>
                    </div>
                </div>
            </div>
            <div class="slide slide2">
                <div class="hero-content">
                    <h1 class="hero-title">Real-Time Communication</h1>
                    <p class="hero-subtitle">Stay connected with instant messaging and group chat functionality for each property</p>
                    <div class="cta-buttons">
                        <a href="register.php" class="cta-btn cta-primary">Get Started Today</a>
                        <a href="#features" class="cta-btn cta-secondary">Learn More</a>
                    </div>
                </div>
            </div>
            <div class="slide slide3">
                <div class="hero-content">
                    <h1 class="hero-title">Comprehensive Bill Management</h1>
                    <p class="hero-subtitle">Track rent, utilities, maintenance fees and all property expenses in one organized platform</p>
                    <div class="cta-buttons">
                        <a href="register.php" class="cta-btn cta-primary">Explore Platform</a>
                        <a href="#how-it-works" class="cta-btn cta-secondary">See How It Works</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container">
            <h2 class="section-title">What We Offer</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">🏠</div>
                    <h3 class="feature-title">Property Management</h3>
                    <p class="feature-desc">Manage multiple properties with ease. Generate unique codes for each flat or building, organize tenants by property, and track all property-related activities from one dashboard.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">💬</div>
                    <h3 class="feature-title">Group Communication</h3>
                    <p class="feature-desc">Create dedicated chat groups for each property. Communicate with all tenants in a building or send targeted messages to specific flats or rooms.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📊</div>
                    <h3 class="feature-title">Comprehensive Bill Tracking</h3>
                    <p class="feature-desc">Add and track all types of bills including rent, electricity, water, gas, maintenance fees, security charges, and any custom billing requirements.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🔐</div>
                    <h3 class="feature-title">Secure Access Control</h3>
                    <p class="feature-desc">Each property gets unique access codes. Tenants can only access their specific property group, ensuring privacy and data security across all your properties.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📱</div>
                    <h3 class="feature-title">Multi-Device Access</h3>
                    <p class="feature-desc">Access your property management tools from any device - desktop, tablet, or mobile. Stay connected and manage your properties on the go.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">⚡</div>
                    <h3 class="feature-title">Smart Notifications</h3>
                    <p class="feature-desc">Automated reminders for upcoming bills, overdue payments, and important announcements. Keep everyone informed without manual follow-ups.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Blog Section -->
    <section class="blog" id="blog">
        <div class="container">
            <h2 class="section-title">Latest Blog Posts</h2>
            <p style="text-align: center; font-size: 1.1rem; color: #666; margin-bottom: 3rem;">
                Stay informed with the latest insights on property management and rental trends
            </p>
            <div class="blog-grid">
                <article class="blog-card">
                    <div class="blog-content">
                        <div class="blog-meta">
                            <span class="blog-date">January 15, 2025</span>
                            <span class="blog-category">Property Management</span>
                        </div>
                        <h3 class="blog-title">10 Essential Property Management Tips for New Landlords</h3>
                        <p class="blog-excerpt">Learn the fundamental strategies every new landlord should know to successfully manage their rental properties and maintain positive tenant relationships.</p>
                        <a href="https://www.bellastaging.ca/blogs/news/property-management-tips" target="_blank" class="blog-link">Read More →</a>
                    </div>
                </article>

                <article class="blog-card">
                    <div class="blog-content">
                        <div class="blog-meta">
                            <span class="blog-date">January 12, 2025</span>
                            <span class="blog-category">Technology</span>
                        </div>
                        <h3 class="blog-title">How Digital Rent Tracking is Revolutionizing Property Management</h3>
                        <p class="blog-excerpt">Discover how modern landlords are using digital tools to streamline rent collection, reduce late payments, and improve tenant communication.</p>
                        <a href="https://www.porterhouseprop.com/post/unlocking-the-future-of-renting-discover-why-online-payments-are-a-game-changer-for-tenants-and-lan" target="_blank" class="blog-link">Read More →</a>
                    </div>
                </article>

                <article class="blog-card">
                    <div class="blog-content">
                        <div class="blog-meta">
                            <span class="blog-date">January 8, 2025</span>
                            <span class="blog-category">Communication</span>
                        </div>
                        <h3 class="blog-title">Building Better Landlord-Tenant Relationships Through Communication</h3>
                        <p class="blog-excerpt">Effective communication strategies that help landlords maintain positive relationships with tenants while ensuring property standards are met.</p>
                        <a href="https://www.elliotleigh.com/post/building-strong-landlord-tenant-relationships" target="_blank" class="blog-link">Read More →</a>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials">
        <div class="container">
            <h2 class="section-title">What Our Users Say</h2>
            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <div class="quote-icon">"</div>
                        <p>"RentTracker has completely transformed how I manage my 15 properties. The automatic bill generation and tenant communication features have saved me countless hours every month."</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <img src="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><circle fill='%23ecc700' cx='50' cy='50' r='50'/><circle fill='%23003c00' cx='50' cy='40' r='15'/><ellipse fill='%23003c00' cx='50' cy='75' rx='20' ry='15'/></svg>" alt="Adebayo Ogundimu">
                        </div>
                        <div class="author-info">
                            <h4>Adebayo Ogundimu</h4>
                            <p>Property Owner, Lagos</p>
                        </div>
                    </div>
                </div>

                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <div class="quote-icon">"</div>
                        <p>"As a tenant, I love how transparent everything is. I can see all my bills, payment history, and communicate directly with my landlord. No more confusion about payments!"</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <img src="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><circle fill='%23006600' cx='50' cy='50' r='50'/><circle fill='%23fff' cx='50' cy='40' r='15'/><ellipse fill='%23fff' cx='50' cy='75' rx='20' ry='15'/></svg>" alt="Fatima Mohammed">
                        </div>
                        <div class="author-info">
                            <h4>Fatima Mohammed</h4>
                            <p>Tenant, Abuja</p>
                        </div>
                    </div>
                </div>

                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <div class="quote-icon">"</div>
                        <p>"The group chat feature is brilliant! All tenants in our building can communicate easily, and our landlord keeps us updated on maintenance schedules and announcements."</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <img src="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><circle fill='%23003c00' cx='50' cy='50' r='50'/><circle fill='%23ecc700' cx='50' cy='40' r='15'/><ellipse fill='%23ecc700' cx='50' cy='75' rx='20' ry='15'/></svg>" alt="Chinedu Okoro">
                        </div>
                        <div class="author-info">
                            <h4>Chinedu Okoro</h4>
                            <p>Tenant, Port Harcourt</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq">
        <div class="container">
            <h2 class="section-title">Frequently Asked Questions</h2>
            <div class="faq-container">
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>How does the landlord-tenant system work?</h3>
                        <span class="faq-toggle">+</span>
                    </div>
                    <div class="faq-answer">
                        <p>Our application is landlord-dependent, meaning landlords must initiate the process. The landlord creates property groups and generates unique access codes for each property or flat. Tenants cannot access the system without the specific code provided by their landlord. This ensures secure access and proper organization of property management.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <h3>What types of bills can be tracked on RentTracker?</h3>
                        <span class="faq-toggle">+</span>
                    </div>
                    <div class="faq-answer">
                        <p>RentTracker supports all types of property-related bills including rent, electricity, water, gas, maintenance fees, security charges, cleaning fees, and any custom billing requirements. Landlords can create and customize bill categories according to their property needs.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Can I manage multiple properties on one account?</h3>
                        <span class="faq-toggle">+</span>
                    </div>
                    <div class="faq-answer">
                        <p>Yes! Landlords can manage unlimited properties from a single account. Each property gets its own unique code and separate group for tenants. You can easily switch between properties and manage all your rental units from one centralized dashboard.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Is my financial information secure on RentTracker?</h3>
                        <span class="faq-toggle">+</span>
                    </div>
                    <div class="faq-answer">
                        <p>Absolutely. We use industry-standard encryption to protect all financial data. Each property group is isolated with unique access codes, ensuring tenants can only view their own billing information. We comply with data protection regulations and regularly update our security measures.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <h3>How do tenants receive notifications about bills and payments?</h3>
                        <span class="faq-toggle">+</span>
                    </div>
                    <div class="faq-answer">
                        <p>Tenants receive automatic notifications through the platform when new bills are added, payment due dates approach, or when payments are confirmed. The system also supports group announcements for property-wide communications from landlords.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Can tenants communicate with each other through the platform?</h3>
                        <span class="faq-toggle">+</span>
                    </div>
                    <div class="faq-answer">
                        <p>Yes! Each property has its own group chat where tenants can communicate with each other and their landlord. This creates a community feel and makes it easy to coordinate building-related activities, share information, and stay connected.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="how-it-works" id="how-it-works">
        <div class="container">
            <h2 class="section-title">How RentTracker Works</h2>
            <p style="text-align: center; font-size: 1.1rem; color: #666; margin-bottom: 2rem;">
                <strong>Note:</strong> Our system is landlord-initiated. Landlords create property groups and generate unique access codes for tenants to join their specific property or flat.
            </p>
            <div class="steps-container">
                <div class="step">
                    <div class="step-number">1</div>
                    <h3 class="step-title">Create Property Groups</h3>
                    <p class="step-desc">Landlord registers and creates property groups for each building or flat. The system automatically generates unique access codes for each property unit.</p>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <h3 class="step-title">Add Bills & Expenses</h3>
                    <p class="step-desc">Landlord adds various bills for tenants including rent, electricity, water, gas, maintenance fees, security charges, and any other property-related expenses.</p>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <h3 class="step-title">Share Access Codes</h3>
                    <p class="step-desc">Landlord shares the unique property code with tenants of that specific flat or building, enabling them to join their dedicated property group.</p>
                </div>
                <div class="step">
                    <div class="step-number">4</div>
                    <h3 class="step-title">Tenants Access Platform</h3>
                    <p class="step-desc">Tenants use the provided code to access their property group, view all bills, payment history, due dates, and communicate with their landlord and other tenants.</p>
                </div>
                <div class="step">
                    <div class="step-number">5</div>
                    <h3 class="step-title">Ongoing Management</h3>
                    <p class="step-desc">Both parties can track payments, communicate in real-time, receive notifications, and maintain transparent financial records throughout the tenancy period.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>Ready to Transform Your Property Management?</h2>
                <p>Join thousands of landlords and tenants who have simplified their rental experience with RentTracker's comprehensive platform</p>
                <div class="cta-buttons">
                    <a href="#" class="cta-btn cta-primary">Get Started Now</a>
                    <a href="#" class="cta-btn cta-secondary">Contact Us</a>
                </div>
            </div>
        </div>
    </section>

<?php if (!isset($_SESSION['user_id'])): ?>
<div class="floating-auth-box" id="authBox">
    <button class="close-auth-box" onclick="document.getElementById('authBox').style.display='none'">&times;</button>
    <button class="auth-btn login-btn" onclick="window.location.href='login.php'">Login</button>
    <button class="auth-btn signup-btn" onclick="window.location.href='register.php'">Sign Up</button>
</div>
<?php endif; ?>

    <!-- Footer -->
    <footer class="footer" id="contact">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>RentTracker</h3>
                    <p>Simplifying property management across Africa through innovative technology, seamless communication, and comprehensive bill tracking solutions.</p>
                    <p>&copy; 2025 RentTracker. All rights reserved.</p>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="#home">Home</a></li>
                        <li><a href="#features">Features</a></li>
                        <li><a href="#how-it-works">How It Works</a></li>
                        <li><a href="#">About Us</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Support</h3>
                    <ul>
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Contact Support</a></li>
                        <li><a href="#">User Guide</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Connect With Us</h3>
                    <p>Email: support@renttracker.com</p>
                    <p>Phone: +234 800 RENT TRACK</p>
                    <div style="margin-top: 1rem;">
                        <a href="#" style="margin-right: 1rem;">Facebook</a>
                        <a href="#" style="margin-right: 1rem;">Twitter</a>
                        <a href="#" style="margin-right: 1rem;">LinkedIn</a>
                        <a href="#">Instagram</a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>Built with ❤️ for African Property Management</p>
            </div>
        </div>
    </footer>

    <script>
        // Slideshow functionality
        let currentSlide = 0;
        const slides = document.querySelectorAll('.slide');
        
        function showSlide(index) {
            slides.forEach(slide => slide.classList.remove('active'));
            slides[index].classList.add('active');
        }
        
        function nextSlide() {
            currentSlide = (currentSlide + 1) % slides.length;
            showSlide(currentSlide);
        }
        
        // Auto-advance slideshow
        setInterval(nextSlide, 5000);
        
        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        
        // Mobile menu toggle (basic implementation)
        document.querySelector('.mobile-menu').addEventListener('click', function() {
            const navMenu = document.querySelector('.nav-menu');
            navMenu.style.display = navMenu.style.display === 'flex' ? 'none' : 'flex';
        });
        
        // Add scroll effect to header
        window.addEventListener('scroll', function() {
            const header = document.querySelector('.header');
            if (window.scrollY > 100) {
                header.style.backgroundColor = 'rgba(0, 60, 0, 0.95)';
            } else {
                header.style.backgroundColor = 'transparent';
            }
        });
        // FAQ Functionality
document.querySelectorAll('.faq-question').forEach(question => {
    question.addEventListener('click', function() {
        const faqItem = this.parentElement;
        const isActive = faqItem.classList.contains('active');

        // Close all FAQ items
        document.querySelectorAll('.faq-item').forEach(item => {
            item.classList.remove('active');
        });

        // Toggle the current one
        if (!isActive) {
            faqItem.classList.add('active');
        }
    });
});

    document.addEventListener('DOMContentLoaded', function () {
        const loginBtn = document.getElementById('loginBtn');
        const signupBtn = document.getElementById('signupBtn');
        const loginModal = document.getElementById('loginModal');
        const signupModal = document.getElementById('signupModal');
        const closeLogin = document.getElementById('closeLogin');
        const closeSignup = document.getElementById('closeSignup');
        const switchToSignup = document.getElementById('switchToSignup');
        const switchToLogin = document.getElementById('switchToLogin');

        // Open modals
        loginBtn.addEventListener('click', () => {
            loginModal.style.display = 'flex';
        });

        signupBtn.addEventListener('click', () => {
            signupModal.style.display = 'flex';
        });

        // Close modals
        closeLogin.addEventListener('click', () => {
            loginModal.style.display = 'none';
        });

        closeSignup.addEventListener('click', () => {
            signupModal.style.display = 'none';
        });

        // Switch modals
        switchToSignup.addEventListener('click', (e) => {
            e.preventDefault();
            loginModal.style.display = 'none';
            signupModal.style.display = 'flex';
        });

        switchToLogin.addEventListener('click', (e) => {
            e.preventDefault();
            signupModal.style.display = 'none';
            loginModal.style.display = 'flex';
        });

        // Optional: Close modals when clicking outside
        window.addEventListener('click', (e) => {
            if (e.target === loginModal) loginModal.style.display = 'none';
            if (e.target === signupModal) signupModal.style.display = 'none';
        });

        // Handle form submit: simulate login/signup then close modal
        document.getElementById('loginForm').addEventListener('submit', function (e) {
            e.preventDefault();
            // Do your AJAX or form logic here...
            // Example: redirect
            loginModal.style.display = 'none';
            window.location.href = 'dashboard.php'; // or wherever
        });

        document.getElementById('signupForm').addEventListener('submit', function (e) {
            e.preventDefault();
            // Do your AJAX or form logic here...
            // Example: redirect
            signupModal.style.display = 'none';
            window.location.href = 'dashboard.php'; // or wherever
        });
    });

    window.addEventListener("load", () => {
        const authBox = document.getElementById("authBox");
        if (authBox) {
            authBox.style.opacity = "0";
            authBox.style.transition = "opacity 0.8s ease";
            setTimeout(() => authBox.style.opacity = "1", 100);
        }
    });
    // Enhanced Mobile Menu Functionality
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuBtn = document.getElementById('mobileMenu');
    const navMenu = document.getElementById('navMenu');
    const navLinks = document.querySelectorAll('.nav-menu a');
    const body = document.body;
    
    // Toggle mobile menu
    function toggleMobileMenu() {
        const isOpen = navMenu.classList.contains('active');
        
        if (isOpen) {
            closeMobileMenu();
        } else {
            openMobileMenu();
        }
    }
    
    // Open mobile menu
    function openMobileMenu() {
        navMenu.classList.add('active');
        mobileMenuBtn.classList.add('active');
        body.classList.add('menu-open');
        
        // Change hamburger to X
        mobileMenuBtn.innerHTML = '✕';
        
        // Animate menu items
        navLinks.forEach((link, index) => {
            link.style.opacity = '0';
            link.style.transform = 'translateY(-20px)';
            setTimeout(() => {
                link.style.opacity = '1';
                link.style.transform = 'translateY(0)';
            }, index * 100 + 100);
        });
    }
    
    // Close mobile menu
    function closeMobileMenu() {
        navMenu.classList.remove('active');
        mobileMenuBtn.classList.remove('active');
        body.classList.remove('menu-open');
        
        // Change X back to hamburger
        mobileMenuBtn.innerHTML = '☰';
        
        // Reset link animations
        navLinks.forEach(link => {
            link.style.opacity = '';
            link.style.transform = '';
        });
    }
    
    // Event listeners
    mobileMenuBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        toggleMobileMenu();
    });
    
    // Close menu when clicking on navigation links
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            closeMobileMenu();
        });
    });
    
    // Close menu when clicking outside
    document.addEventListener('click', function(e) {
        if (!navMenu.contains(e.target) && !mobileMenuBtn.contains(e.target)) {
            closeMobileMenu();
        }
    });
    
    // Close menu on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeMobileMenu();
        }
    });
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            closeMobileMenu();
        }
    });
    
    // Prevent menu from staying open during orientation change
    window.addEventListener('orientationchange', function() {
        setTimeout(function() {
            if (window.innerWidth > 768) {
                closeMobileMenu();
            }
        }, 100);
    });
});

</script>

</body>
</html>