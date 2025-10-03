<!doctype html>
<html lang="en" class="layout-navbar-fixed layout-wide" dir="ltr" data-skin="default" data-bs-theme="light">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    
    <title>MyRVM-Ecosystem v2.0 - Smart Reverse Vending Machine Platform</title>
    
    <meta name="description" content="Advanced Reverse Vending Machine ecosystem with AI-powered computer vision, real-time monitoring, and comprehensive management platform." />
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}" />
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />
    
    <!-- Core CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        :root {
            --primary-color: #696cff;
            --secondary-color: #8592a3;
            --success-color: #71dd37;
            --info-color: #03c3ec;
            --warning-color: #ffab00;
            --danger-color: #ff3e1d;
            --dark-color: #233446;
            --light-color: #f8f9fa;
        }
        
        body {
            font-family: 'Public Sans', sans-serif;
            line-height: 1.6;
        }
        
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><polygon fill="rgba(255,255,255,0.1)" points="0,1000 1000,0 1000,1000"/></svg>');
            background-size: cover;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
            color: white;
        }
        
        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .hero-subtitle {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        
        .btn-hero {
            padding: 15px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }
        
        .btn-hero:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(0,0,0,0.3);
        }
        
        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin-bottom: 1.5rem;
        }
        
        .stats-section {
            background: #f8f9fa;
            padding: 5rem 0;
        }
        
        .stat-item {
            text-align: center;
            padding: 2rem;
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            color: var(--primary-color);
            display: block;
        }
        
        .stat-label {
            font-size: 1.1rem;
            color: var(--secondary-color);
            margin-top: 0.5rem;
        }
        
        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 3rem;
            color: var(--dark-color);
        }
        
        .section-subtitle {
            font-size: 1.2rem;
            text-align: center;
            color: var(--secondary-color);
            margin-bottom: 4rem;
        }
        
        .navbar-custom {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color) !important;
        }
        
        .nav-link {
            font-weight: 500;
            color: var(--dark-color) !important;
            transition: color 0.3s ease;
        }
        
        .nav-link:hover {
            color: var(--primary-color) !important;
        }
        
        .footer {
            background: var(--dark-color);
            color: white;
            padding: 3rem 0 1rem;
        }
        
        .footer h5 {
            color: white;
            margin-bottom: 1rem;
        }
        
        .footer a {
            color: #adb5bd;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .footer a:hover {
            color: white;
        }
        
        .floating-elements {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            pointer-events: none;
        }
        
        .floating-element {
            position: absolute;
            opacity: 0.1;
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        .rvm-animation {
            position: relative;
            width: 300px;
            height: 200px;
            margin: 0 auto;
        }
        
        .rvm-machine {
            width: 100%;
            height: 100%;
            background: linear-gradient(145deg, #e0e0e0, #f0f0f0);
            border-radius: 20px;
            position: relative;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .rvm-screen {
            position: absolute;
            top: 20px;
            left: 20px;
            right: 20px;
            height: 60px;
            background: #000;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #00ff00;
            font-family: monospace;
            font-size: 0.8rem;
        }
        
        .rvm-slot {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 20px;
            background: #333;
            border-radius: 10px;
        }
        
        .pulse {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-custom fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-recycle me-2"></i>
                MyRVM-Ecosystem v2.0
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-primary text-white px-3 ms-2" href="{{ route('login') }}">
                            <i class="fas fa-sign-in-alt me-1"></i> Login
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="floating-elements">
            <div class="floating-element" style="top: 20%; left: 10%; animation-delay: 0s;">
                <i class="fas fa-recycle fa-3x text-white"></i>
            </div>
            <div class="floating-element" style="top: 60%; right: 15%; animation-delay: 2s;">
                <i class="fas fa-robot fa-3x text-white"></i>
            </div>
            <div class="floating-element" style="top: 40%; left: 80%; animation-delay: 4s;">
                <i class="fas fa-chart-line fa-3x text-white"></i>
            </div>
        </div>
        
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="hero-content">
                        <h1 class="hero-title">
                            Smart Reverse Vending Machine
                            <span class="text-warning">Ecosystem</span>
                        </h1>
                        <p class="hero-subtitle">
                            Revolutionize waste management with AI-powered computer vision, 
                            real-time monitoring, and comprehensive analytics platform.
                        </p>
                        <div class="d-flex flex-wrap gap-3">
                            <a href="{{ route('login') }}" class="btn btn-warning btn-hero">
                                <i class="fas fa-rocket me-2"></i>Get Started
                            </a>
                            <a href="#features" class="btn btn-outline-light btn-hero">
                                <i class="fas fa-play me-2"></i>Learn More
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="rvm-animation">
                        <div class="rvm-machine">
                            <div class="rvm-screen pulse">
                                <span>AI DETECTION ACTIVE</span>
                            </div>
                            <div class="rvm-slot"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    <div class="stat-item">
                        <span class="stat-number">6</span>
                        <div class="stat-label">Active RVMs</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <span class="stat-number">1,247</span>
                        <div class="stat-label">Total Deposits</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <span class="stat-number">99.9%</span>
                        <div class="stat-label">Uptime</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <span class="stat-number">24/7</span>
                        <div class="stat-label">Monitoring</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="section-title">Powerful Features</h2>
                    <p class="section-subtitle">
                        Advanced technology stack for modern waste management solutions
                    </p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon bg-primary text-white mx-auto">
                            <i class="fas fa-eye"></i>
                        </div>
                        <h4 class="text-center mb-3">AI Computer Vision</h4>
                        <p class="text-center text-muted">
                            Advanced YOLO + SAM2 models for precise object detection and segmentation 
                            with real-time processing on Jetson Orin edge devices.
                        </p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon bg-success text-white mx-auto">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h4 class="text-center mb-3">Real-time Analytics</h4>
                        <p class="text-center text-muted">
                            Comprehensive dashboard with live monitoring, performance metrics, 
                            and predictive analytics for optimal RVM management.
                        </p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon bg-info text-white mx-auto">
                            <i class="fas fa-network-wired"></i>
                        </div>
                        <h4 class="text-center mb-3">Edge Computing</h4>
                        <p class="text-center text-muted">
                            Distributed architecture with Jetson Orin edge devices and centralized 
                            GPU server for scalable computer vision processing.
                        </p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon bg-warning text-white mx-auto">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h4 class="text-center mb-3">Secure Platform</h4>
                        <p class="text-center text-muted">
                            Enterprise-grade security with API key authentication, 
                            data encryption, and secure VPN communication.
                        </p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon bg-danger text-white mx-auto">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h4 class="text-center mb-3">Mobile Ready</h4>
                        <p class="text-center text-muted">
                            Responsive design with mobile-first approach, 
                            supporting all devices and screen sizes.
                        </p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon bg-dark text-white mx-auto">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <h4 class="text-center mb-3">Easy Integration</h4>
                        <p class="text-center text-muted">
                            RESTful APIs and comprehensive documentation for 
                            seamless integration with existing systems.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-5 bg-light">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h2 class="section-title text-start">About MyRVM-Ecosystem v2.0</h2>
                    <p class="lead">
                        A comprehensive platform designed to revolutionize reverse vending machine operations 
                        through cutting-edge AI technology and real-time monitoring capabilities.
                    </p>
                    <p>
                        Our ecosystem combines the power of computer vision, edge computing, and cloud analytics 
                        to provide intelligent waste management solutions. With support for multiple RVM devices, 
                        real-time processing, and comprehensive reporting, we help organizations optimize their 
                        recycling operations and reduce environmental impact.
                    </p>
                    <div class="row mt-4">
                        <div class="col-6">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-check-circle text-success me-3"></i>
                                <span>AI-Powered Detection</span>
                            </div>
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-check-circle text-success me-3"></i>
                                <span>Real-time Monitoring</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-check-circle text-success me-3"></i>
                                <span>Scalable Architecture</span>
                            </div>
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-check-circle text-success me-3"></i>
                                <span>Enterprise Security</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="text-center">
                        <div class="rvm-animation">
                            <div class="rvm-machine">
                                <div class="rvm-screen">
                                    <span>SYSTEM READY</span>
                                </div>
                                <div class="rvm-slot"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="section-title">Get Started Today</h2>
                    <p class="section-subtitle">
                        Ready to revolutionize your waste management operations? 
                        Contact us to learn more about MyRVM-Ecosystem v2.0
                    </p>
                    <div class="d-flex flex-wrap justify-content-center gap-3">
                        <a href="{{ route('login') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-sign-in-alt me-2"></i>Access Dashboard
                        </a>
                        <a href="mailto:info@myrvm-ecosystem.com" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-envelope me-2"></i>Contact Us
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4">
                    <h5><i class="fas fa-recycle me-2"></i>MyRVM-Ecosystem v2.0</h5>
                    <p class="text-muted">
                        Advanced reverse vending machine platform with AI-powered computer vision 
                        and real-time monitoring capabilities.
                    </p>
                </div>
                <div class="col-lg-2">
                    <h5>Platform</h5>
                    <ul class="list-unstyled">
                        <li><a href="#features">Features</a></li>
                        <li><a href="#about">About</a></li>
                        <li><a href="{{ route('login') }}">Dashboard</a></li>
                    </ul>
                </div>
                <div class="col-lg-2">
                    <h5>Technology</h5>
                    <ul class="list-unstyled">
                        <li><a href="#">AI Vision</a></li>
                        <li><a href="#">Edge Computing</a></li>
                        <li><a href="#">Real-time Analytics</a></li>
                    </ul>
                </div>
                <div class="col-lg-2">
                    <h5>Support</h5>
                    <ul class="list-unstyled">
                        <li><a href="#">Documentation</a></li>
                        <li><a href="#">API Reference</a></li>
                        <li><a href="#">Contact</a></li>
                    </ul>
                </div>
                <div class="col-lg-2">
                    <h5>Network</h5>
                    <ul class="list-unstyled">
                        <li><a href="#">Server: 100.123.143.87</a></li>
                        <li><a href="#">Edge: 100.117.234.2</a></li>
                        <li><a href="#">GPU: 100.98.142.94</a></li>
                    </ul>
                </div>
            </div>
            <hr class="my-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="text-muted mb-0">&copy; 2025 MyRVM-Ecosystem v2.0. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="text-muted mb-0">Powered by Laravel 12 + Vue.js 3 + AI Vision</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
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

        // Navbar background change on scroll
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar-custom');
            if (window.scrollY > 50) {
                navbar.style.background = 'rgba(255,255,255,0.98)';
            } else {
                navbar.style.background = 'rgba(255,255,255,0.95)';
            }
        });

        // Animate stats on scroll
        const observerOptions = {
            threshold: 0.5,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const statNumbers = entry.target.querySelectorAll('.stat-number');
                    statNumbers.forEach(stat => {
                        const finalNumber = stat.textContent;
                        const isPercentage = finalNumber.includes('%');
                        const isTime = finalNumber.includes('/');
                        const numericValue = parseInt(finalNumber.replace(/[^\d]/g, ''));
                        
                        if (!isNaN(numericValue)) {
                            let current = 0;
                            const increment = numericValue / 50;
                            const timer = setInterval(() => {
                                current += increment;
                                if (current >= numericValue) {
                                    current = numericValue;
                                    clearInterval(timer);
                                }
                                stat.textContent = Math.floor(current) + (isPercentage ? '%' : '') + (isTime ? '/7' : '');
                            }, 30);
                        }
                    });
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        document.addEventListener('DOMContentLoaded', function() {
            const statsSection = document.querySelector('.stats-section');
            if (statsSection) {
                observer.observe(statsSection);
            }
        });
    </script>
</body>
</html>
