<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rent Tracker - Simplify Property Management</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            overflow-x: hidden;
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #003c00, #006600);
            color: white;
            padding: 1rem 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        
        header .logo img {
            width: 95px;
        }

        .nav-menu {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        .nav-menu a {
            color: #fff;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .nav-menu a:hover {
            color: #ecc700;
        }

        .mobile-menu {
            display: none;
            cursor: pointer;
            color: #fff;
        }

        /* Hero Section with Slideshow */
        .hero {
            height: 100vh;
            position: relative;
            overflow: hidden;
            margin-top: 70px;
        }

        .slideshow-container {
            position: relative;
            height: 100%;
        }

        .slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 1s ease-in-out;
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .slide.active {
            opacity: 1;
        }

        .slide1 {
            background: linear-gradient(rgba(0, 60, 0, 0.8), rgba(236, 199, 0, 0.6)), 
                        url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 800"><rect fill="%23f8f9fa" width="1200" height="800"/><rect fill="%23e9ecef" x="100" y="150" width="300" height="200" rx="10"/><rect fill="%23003c00" x="120" y="170" width="260" height="20" rx="10"/><rect fill="%23006600" x="120" y="200" width="200" height="15" rx="7"/><rect fill="%23ecf0f1" x="120" y="225" width="180" height="15" rx="7"/><circle fill="%23ecc700" cx="1000" cy="200" r="80"/><rect fill="%23ffffff" x="950" y="180" width="100" height="40" rx="20"/></svg>');
        }

        .slide2 {
            background: linear-gradient(rgba(0, 102, 0, 0.7), rgba(236, 199, 0, 0.6)), 
                        url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 800"><rect fill="%23f8f9fa" width="1200" height="800"/><circle fill="%23006600" cx="200" cy="300" r="60"/><circle fill="%23003c00" cx="400" cy="200" r="40"/><rect fill="%23ecc700" x="600" y="250" width="150" height="100" rx="15"/><rect fill="%23004d00" x="900" y="180" width="120" height="80" rx="10"/></svg>');
        }

        .slide3 {
            background: linear-gradient(rgba(0, 60, 0, 0.8), rgba(0, 77, 0, 0.7)), 
                        url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 800"><rect fill="%23f8f9fa" width="1200" height="800"/><rect fill="%23003c00" x="200" y="200" width="800" height="400" rx="20"/><rect fill="%23006600" x="250" y="250" width="300" height="50" rx="25"/><rect fill="%23ecc700" x="250" y="320" width="250" height="40" rx="20"/><circle fill="%23004d00" cx="900" cy="300" r="50"/></svg>');
        }

        .hero-content {
            text-align: center;
            color: white;
            max-width: 800px;
            padding: 0 2rem;
            z-index: 2;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: bold;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            animation: fadeInUp 1s ease-out;
        }

        .hero-subtitle {
            font-size: 1.3rem;
            margin-bottom: 2rem;
            opacity: 0.9;
            animation: fadeInUp 1s ease-out 0.3s both;
        }

        .cta-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            animation: fadeInUp 1s ease-out 0.6s both;
        }

        .cta-btn {
            padding: 15px 30px;
            border: none;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .cta-primary {
            background: #ecc700;
            color: #003c00;
        }

        .cta-primary:hover {
            background: #d4b600;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .cta-secondary {
            background: transparent;
            color: white;
            border: 2px solid white;
        }

        .cta-secondary:hover {
            background: white;
            color: #003c00;
        }

        /* Features Section */
        .features {
            padding: 80px 0;
            background: #f8f9fa;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            color: #003c00;
            margin-bottom: 3rem;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 3rem;
            margin-bottom: 4rem;
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #006600, #003c00);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2rem;
            color: #ecc700;
        }

        .feature-title {
            font-size: 1.5rem;
            color: #003c00;
            margin-bottom: 1rem;
        }

        .feature-desc {
            color: #666;
            line-height: 1.6;
        }

        /* How It Works */
        .how-it-works {
            padding: 80px 0;
            background: white;
        }

        .steps-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .step {
            text-align: center;
            position: relative;
        }

        .step-number {
            width: 60px;
            height: 60px;
            background: #ecc700;
            color: #003c00;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: bold;
            margin: 0 auto 1rem;
        }

        .step-title {
            font-size: 1.3rem;
            color: #003c00;
            margin-bottom: 0.5rem;
        }

        .step-desc {
            color: #666;
        }

        /* CTA Section */
        .cta-section {
            background: linear-gradient(135deg, #003c00, #006600);
            color: white;
            padding: 80px 0;
            text-align: center;
        }

        .cta-content h2 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .cta-content p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        /* Footer */
        .footer {
            background: #003c00;
            color: white;
            padding: 50px 0 20px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .footer-section h3 {
            margin-bottom: 1rem;
            color: #ecc700;
        }

        .footer-section p, .footer-section li {
            margin-bottom: 0.5rem;
            opacity: 0.8;
        }

        .footer-section ul {
            list-style: none;
        }

        .footer-section a {
            color: white;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-section a:hover {
            color: #ecc700;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid #006600;
            opacity: 0.6;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .nav-menu {
                display: none;
            }

            .mobile-menu {
                display: block;
            }

            .hero-title {
                font-size: 2.5rem;
            }

            .hero-subtitle {
                font-size: 1.1rem;
            }

            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }

            .cta-btn {
                width: 250px;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }

            .steps-container {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .hero-title {
                font-size: 2rem;
            }

            .section-title {
                font-size: 2rem;
            }

            .cta-content h2 {
                font-size: 2rem;
            }
        }
    </style>
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
