<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriSage - Smart Agriculture Solutions</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            overflow-x: hidden;
        }

        /* Navigation */
        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            background: linear-gradient(135deg, #2c5530, #4a7c59);
            padding: 1rem 0;
            z-index: 1000;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .navbar.scrolled {
            background: rgba(44, 85, 48, 0.95);
            backdrop-filter: blur(10px);
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
        }

        .logo {
            font-size: 2rem;
            font-weight: bold;
            color: #fff;
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .logo i {
            margin-right: 0.5rem;
            color: #7bc142;
        }

        .nav-menu {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        .nav-item a {
            color: #fff;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
            position: relative;
        }

        .nav-item a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: #7bc142;
            transition: width 0.3s ease;
        }

        .nav-item a:hover::after {
            width: 100%;
        }

        .nav-item a:hover {
            color: #7bc142;
        }

        .mobile-toggle {
            display: none;
            color: #fff;
            font-size: 1.5rem;
            cursor: pointer;
        }

        /* Hero Section */
        .hero {
            height: 100vh;
            background: linear-gradient(rgba(44, 85, 48, 0.7), rgba(74, 124, 89, 0.7)), 
                        url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 800"><defs><linearGradient id="grad1" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" style="stop-color:%234a7c59;stop-opacity:1" /><stop offset="100%" style="stop-color:%232c5530;stop-opacity:1" /></linearGradient></defs><rect width="1200" height="800" fill="url(%23grad1)"/><path d="M0,400 Q300,300 600,400 T1200,400 L1200,800 L0,800 Z" fill="%237bc142" opacity="0.1"/><circle cx="200" cy="200" r="50" fill="%237bc142" opacity="0.2"/><circle cx="800" cy="150" r="30" fill="%237bc142" opacity="0.3"/><circle cx="1000" cy="300" r="40" fill="%237bc142" opacity="0.2"/></svg>');
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            position: relative;
        }

        .hero-content {
            max-width: 800px;
            color: #fff;
            z-index: 2;
            animation: fadeInUp 1s ease;
        }

        .hero h1 {
            font-size: 4rem;
            margin-bottom: 1rem;
            background: linear-gradient(45deg, #fff, #7bc142);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero p {
            font-size: 1.3rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .cta-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 1rem 2rem;
            border: none;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(45deg, #7bc142, #5a9b32);
            color: #fff;
            box-shadow: 0 4px 15px rgba(123, 193, 66, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(123, 193, 66, 0.4);
        }

        .btn-secondary {
            background: transparent;
            color: #fff;
            border: 2px solid #fff;
        }

        .btn-secondary:hover {
            background: #fff;
            color: #2c5530;
        }

        /* Features Section */
        .features {
            padding: 5rem 0;
            background: #f8f9fa;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .section-title {
            text-align: center;
            margin-bottom: 3rem;
        }

        .section-title h2 {
            font-size: 2.5rem;
            color: #2c5530;
            margin-bottom: 1rem;
        }

        .section-title p {
            font-size: 1.2rem;
            color: #666;
            max-width: 600px;
            margin: 0 auto;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .feature-card {
            background: #fff;
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-10px);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(45deg, #7bc142, #5a9b32);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2rem;
            color: #fff;
        }

        .feature-card h3 {
            color: #2c5530;
            margin-bottom: 1rem;
            font-size: 1.3rem;
        }

        /* Stats Section */
        .stats {
            padding: 5rem 0;
            background: linear-gradient(135deg, #2c5530, #4a7c59);
            color: #fff;
            text-align: center;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .stat-item h3 {
            font-size: 3rem;
            margin-bottom: 0.5rem;
            color: #7bc142;
        }

        .stat-item p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        /* Footer */
        .footer {
            background: #1a3a1e;
            color: #fff;
            padding: 3rem 0 1rem;
            text-align: center;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .footer-section h3 {
            color: #7bc142;
            margin-bottom: 1rem;
        }

        .footer-section a {
            color: #ccc;
            text-decoration: none;
            display: block;
            margin-bottom: 0.5rem;
            transition: color 0.3s ease;
        }

        .footer-section a:hover {
            color: #7bc142;
        }

        .social-links {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 1rem;
        }

        .social-links a {
            width: 40px;
            height: 40px;
            background: #7bc142;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: #fff;
        }

        .footer-bottom {
            border-top: 1px solid #333;
            padding-top: 1rem;
            margin-top: 2rem;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .mobile-toggle {
                display: block;
            }

            .nav-menu {
                position: fixed;
                top: 100%;
                left: -100%;
                width: 100%;
                height: 100vh;
                background: rgba(44, 85, 48, 0.95);
                flex-direction: column;
                justify-content: center;
                align-items: center;
                transition: left 0.3s ease;
            }

            .nav-menu.active {
                left: 0;
            }

            .hero h1 {
                font-size: 2.5rem;
            }

            .hero p {
                font-size: 1.1rem;
            }

            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }

            .section-title h2 {
                font-size: 2rem;
            }
        }

/* Popup Modal Styles */
.modal {
  display: none; 
  position: fixed; 
  z-index: 2000; 
  left: 0; top: 0;
  width: 100%; height: 100%;
  background: rgba(0,0,0,0.7);
  display: flex; align-items: center; justify-content: center;
}

.modal-content {
  background: #fff;
  padding: 2rem;
  border-radius: 15px;
  width: 90%;
  max-width: 400px;
  position: relative;
  animation: fadeIn 0.3s ease;
}

.modal-content h2 {
  margin-bottom: 1rem;
  color: #2c5530;
  text-align: center;
}

.modal-content form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.modal-content input {
  padding: 0.8rem;
  border: 1px solid #ccc;
  border-radius: 8px;
  font-size: 1rem;
}

.modal-content .btn {
  width: 100%;
}

.modal-content p {
  margin-top: 1rem;
  text-align: center;
}

.close {
  position: absolute;
  top: 15px; right: 15px;
  font-size: 1.5rem;
  cursor: pointer;
  color: #333;
}

.password-match {
  color: #2ed573;
  font-size: 0.8rem;
  margin-top: -0.5rem;
}

.password-mismatch {
  color: #ff4757;
  font-size: 0.8rem;
  margin-top: -0.5rem;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-20px); }
  to { opacity: 1; transform: translateY(0); }
}

    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar" id="navbar">
        <div class="nav-container">
            <a href="#" class="logo">
                <i class="fas fa-seedling"></i>
                AgriSage
            </a>
        <ul class="nav-menu" id="nav-menu">
            <li class="nav-item"><a href="#home">Home</a></li>
            <li class="nav-item"><a href="services.html">Services</a></li>
            <li class="nav-item"><a href="products.html">Products</a></li>
            <li class="nav-item"><a href="about.html">About</a></li>
            <li class="nav-item"><a href="blog.html">Blog</a></li>
            <li class="nav-item"><a href="contact.html">Contact</a></li>
            <li class="nav-item"><a href="#" id="loginBtn">Login</a></li>
            <li class="nav-item"><a href="#" id="signupBtn">Sign Up</a></li>
        </ul>
            <div class="mobile-toggle" id="mobile-toggle">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-content">
            <h1>Smart Agriculture for Tomorrow</h1>
            <p>Revolutionizing farming with AI-powered insights, precision agriculture, and sustainable solutions for maximum yield and environmental stewardship.</p>
            <div class="cta-buttons">
                <a href="services.html" class="btn btn-primary">Explore Solutions</a>
                <a href="contact.html" class="btn btn-secondary">Get Started</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <div class="section-title">
                <h2>Why Choose AgriSage?</h2>
                <p>Cutting-edge technology meets agricultural expertise to deliver unprecedented farming solutions</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-brain"></i>
                    </div>
                    <h3>AI-Powered Analytics</h3>
                    <p>Advanced machine learning algorithms analyze crop data to provide intelligent farming recommendations and predictive insights.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-satellite"></i>
                    </div>
                    <h3>Precision Monitoring</h3>
                    <p>Real-time satellite and IoT sensor monitoring for soil health, weather conditions, and crop growth patterns.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <h3>Sustainable Practices</h3>
                    <p>Eco-friendly solutions that maximize yield while minimizing environmental impact and resource consumption.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3>Mobile Integration</h3>
                    <p>Access all your farm data and controls through our intuitive mobile app, available anywhere, anytime.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Yield Optimization</h3>
                    <p>Data-driven strategies to increase crop productivity and profitability through optimized resource allocation.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Expert Support</h3>
                    <p>24/7 access to agricultural experts and agronomists for personalized consultation and guidance.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats">
        <div class="container">
            <div class="section-title">
                <h2>Our Impact</h2>
                <p>Numbers that speak for themselves</p>
            </div>
            <div class="stats-grid">
                <div class="stat-item">
                    <h3 data-count="50000">0</h3>
                    <p>Farmers Empowered</p>
                </div>
                <div class="stat-item">
                    <h3 data-count="35">0</h3>
                    <p>Yield Increase (%)</p>
                </div>
                <div class="stat-item">
                    <h3 data-count="2500000">0</h3>
                    <p>Acres Monitored</p>
                </div>
                <div class="stat-item">
                    <h3 data-count="25">0</h3>
                    <p>Water Savings (%)</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>AgriSage</h3>
                    <p>Leading the agricultural revolution with smart, sustainable farming solutions.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <a href="services.html">Services</a>
                    <a href="products.html">Products</a>
                    <a href="about.html">About Us</a>
                    <a href="blog.html">Blog</a>
                </div>
                <div class="footer-section">
                    <h3>Solutions</h3>
                    <a href="#">Crop Monitoring</a>
                    <a href="#">Precision Irrigation</a>
                    <a href="#">Soil Analysis</a>
                    <a href="#">Weather Forecasting</a>
                </div>
                <div class="footer-section">
                    <h3>Contact Info</h3>
                    <p><i class="fas fa-envelope"></i> info@agrisage.com</p>
                    <p><i class="fas fa-phone"></i> +1 (555) 123-4567</p>
                    <p><i class="fas fa-map-marker-alt"></i> 123 Agriculture St, Farm City, FC 12345</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 AgriSage. All rights reserved. | Privacy Policy | Terms of Service</p>
            </div>
        </div>
    </footer>


<!-- Login Modal -->
<div id="loginModal" class="modal">
  <div class="modal-content">
    <span class="close" id="closeLogin">&times;</span>
    <h2>Login</h2>
    <form id="loginForm">
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit" class="btn btn-primary">Login</button>
    </form>
    <p>Don't have an account? <a href="#" id="switchToSignup">Sign up</a></p>
    <div id="loginMessage" style="margin-top: 1rem;"></div>
  </div>
</div>

<!-- Signup Modal -->
<div id="signupModal" class="modal">
  <div class="modal-content">
    <span class="close" id="closeSignup">&times;</span>
    <h2>Sign Up</h2>
    <form id="signupForm">
      <input type="text" name="name" placeholder="Full Name" required>
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" id="password" placeholder="Password" required minlength="6">
      <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required>
      <div id="passwordMatchMessage"></div>
      <button type="submit" class="btn btn-primary" id="signupButton">Sign Up</button>
    </form>
    <p>Already have an account? <a href="#" id="switchToLogin">Login</a></p>
    <div id="signupMessage" style="margin-top: 1rem;"></div>
  </div>
</div>


    <script>
        // Mobile navigation toggle
        const mobileToggle = document.getElementById('mobile-toggle');
        const navMenu = document.getElementById('nav-menu');

        mobileToggle.addEventListener('click', () => {
            navMenu.classList.toggle('active');
            const icon = mobileToggle.querySelector('i');
            icon.classList.toggle('fa-bars');
            icon.classList.toggle('fa-times');
        });

        // Navbar scroll effect
        window.addEventListener('scroll', () => {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 100) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Counter animation
        function animateCounters() {
            const counters = document.querySelectorAll('[data-count]');
            
            counters.forEach(counter => {
                const target = parseInt(counter.getAttribute('data-count'));
                const duration = 2000;
                const increment = target / (duration / 16);
                let current = 0;
                
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        current = target;
                        clearInterval(timer);
                    }
                    
                    if (target >= 1000000) {
                        counter.textContent = (current / 1000000).toFixed(1) + 'M';
                    } else if (target >= 1000) {
                        counter.textContent = (current / 1000).toFixed(0) + 'K';
                    } else {
                        counter.textContent = Math.floor(current) + (target < 100 ? '%' : '');
                    }
                }, 16);
            });
        }

        // Intersection Observer for animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    if (entry.target.classList.contains('stats')) {
                        animateCounters();
                        observer.unobserve(entry.target);
                    }
                }
            });
        }, observerOptions);

        // Observe the stats section
        const statsSection = document.querySelector('.stats');
        observer.observe(statsSection);

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
                // Close mobile menu if open
                navMenu.classList.remove('active');
                mobileToggle.querySelector('i').classList.add('fa-bars');
                mobileToggle.querySelector('i').classList.remove('fa-times');
            });
        });

        // Add loading animation
        window.addEventListener('load', () => {
            document.body.style.opacity = '0';
            document.body.style.transition = 'opacity 0.5s ease';
            setTimeout(() => {
                document.body.style.opacity = '1';
            }, 100);
        });

        // Get elements
        const loginBtn = document.getElementById("loginBtn");
        const signupBtn = document.getElementById("signupBtn");
        const loginModal = document.getElementById("loginModal");
        const signupModal = document.getElementById("signupModal");
        const closeLogin = document.getElementById("closeLogin");
        const closeSignup = document.getElementById("closeSignup");
        const switchToSignup = document.getElementById("switchToSignup");
        const switchToLogin = document.getElementById("switchToLogin");

        // Open Modals
        loginBtn.onclick = () => { loginModal.style.display = "flex"; }
        signupBtn.onclick = () => { signupModal.style.display = "flex"; }

        // Close Modals
        closeLogin.onclick = () => { loginModal.style.display = "none"; }
        closeSignup.onclick = () => { signupModal.style.display = "none"; }

        // Switch between login/signup
        switchToSignup.onclick = (e) => {
            e.preventDefault();
            loginModal.style.display = "none";
            signupModal.style.display = "flex";
        }
        switchToLogin.onclick = (e) => {
            e.preventDefault();
            signupModal.style.display = "none";
            loginModal.style.display = "flex";
        }

        // Close modal on outside click
        window.onclick = (e) => {
            if (e.target === loginModal) loginModal.style.display = "none";
            if (e.target === signupModal) signupModal.style.display = "none";
        }

        // Password match validation
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        const passwordMatchMessage = document.getElementById('passwordMatchMessage');
        const signupButton = document.getElementById('signupButton');

        function validatePassword() {
            if (password.value === '' && confirmPassword.value === '') {
                passwordMatchMessage.textContent = '';
                passwordMatchMessage.className = '';
                signupButton.disabled = false;
                return;
            }

            if (password.value === confirmPassword.value) {
                passwordMatchMessage.textContent = '✓ Passwords match';
                passwordMatchMessage.className = 'password-match';
                signupButton.disabled = false;
            } else {
                passwordMatchMessage.textContent = '✗ Passwords do not match';
                passwordMatchMessage.className = 'password-mismatch';
                signupButton.disabled = true;
            }
        }

        password.addEventListener('input', validatePassword);
        confirmPassword.addEventListener('input', validatePassword);

        // Form handling
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const data = {
                email: formData.get('email'),
                password: formData.get('password')
            };

            fetch('login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                const messageDiv = document.getElementById('loginMessage');
                if (data.success) {
                    messageDiv.style.color = 'green';
                    messageDiv.textContent = data.message;
                    setTimeout(() => {
                        loginModal.style.display = 'none';
                        // Redirect to dashboard
                        window.location.href = 'dashboard.php';
                    }, 1000);
                } else {
                    messageDiv.style.color = 'red';
                    messageDiv.textContent = data.message;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('loginMessage').textContent = 'An error occurred';
            });
        });

        document.getElementById('signupForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Final password validation before submission
            if (password.value !== confirmPassword.value) {
                passwordMatchMessage.textContent = '✗ Please make sure passwords match';
                passwordMatchMessage.className = 'password-mismatch';
                return;
            }

            const formData = new FormData(this);
            const data = {
                name: formData.get('name'),
                email: formData.get('email'),
                password: formData.get('password')
            };

            fetch('signup.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                const messageDiv = document.getElementById('signupMessage');
                if (data.success) {
                    messageDiv.style.color = 'green';
                    messageDiv.textContent = data.message;
                    setTimeout(() => {
                        signupModal.style.display = 'none';
                        // Redirect to dashboard
                        window.location.href = 'dashboard.php';
                    }, 1000);
                } else {
                    messageDiv.style.color = 'red';
                    messageDiv.textContent = data.message;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('signupMessage').textContent = 'An error occurred';
            });
        });

        // Clear password validation when modal closes
        function clearPasswordFields() {
            password.value = '';
            confirmPassword.value = '';
            passwordMatchMessage.textContent = '';
            passwordMatchMessage.className = '';
            signupButton.disabled = false;
        }

        // Clear fields when modals are closed
        closeSignup.addEventListener('click', clearPasswordFields);
        switchToLogin.addEventListener('click', clearPasswordFields);
    </script>
</body>
</html>