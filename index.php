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
                <ul class="nav-menu">
                    <li><a href="#home">Home</a></li>
                    <li><a href="#features">Features</a></li>
                    <li><a href="#how-it-works">How It Works</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
                <div class="mobile-menu">☰</div>
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
                <div class="feature-card">
                    <div class="feature-icon">🔧</div>
                    <h3 class="feature-title">Maintenance Management</h3>
                    <p class="feature-desc">Track maintenance requests, schedule repairs, manage contractor information, and maintain service history for each property and unit.</p>
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
                    <a href="register.php" class="cta-btn cta-primary">Get Started Now</a>
                    <a href="#contact" class="cta-btn cta-secondary">Contact Us</a>
                </div>
            </div>
        </div>
    </section>

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
    </script>
</body>
</html>
