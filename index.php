<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OVACS - Welcome</title>
    <meta name="description" content="OVACS - Your trusted partner in innovative solutions">
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">Welcome to <span class="brand">OVACS</span></h1>
                <p class="hero-subtitle">Innovative solutions for your business needs. We help you grow, scale, and succeed in today's digital world.</p>
                <div class="hero-buttons">
                    <a href="#about" class="btn btn-primary">Learn More</a>
                    <a href="#contact" class="btn btn-secondary">Contact Us</a>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about">
        <div class="container">
            <h2 class="section-title">About OVACS</h2>
            <div class="about-content">
                <div class="about-text">
                    <p>At OVACS, we're committed to delivering exceptional value through innovative solutions and unparalleled service. Our team of experts combines cutting-edge technology with deep industry knowledge to help businesses thrive.</p>
                </div>
                <div class="stats">
                    <div class="stat">
                        <h3 class="stat-number">100+</h3>
                        <p class="stat-label">Happy Clients</p>
                    </div>
                    <div class="stat">
                        <h3 class="stat-number">500+</h3>
                        <p class="stat-label">Projects Completed</p>
                    </div>
                    <div class="stat">
                        <h3 class="stat-number">10+</h3>
                        <p class="stat-label">Years Experience</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <h2 class="section-title">Why Choose OVACS?</h2>
            <div class="features-grid">
                <div class="feature">
                    <div class="feature-icon">⚡</div>
                    <h3 class="feature-title">Fast & Reliable</h3>
                    <p class="feature-description">Lightning-fast solutions that you can count on, delivered with precision and reliability.</p>
                </div>
                <div class="feature">
                    <div class="feature-icon">🎯</div>
                    <h3 class="feature-title">Targeted Solutions</h3>
                    <p class="feature-description">Customized approaches tailored specifically to meet your unique business requirements.</p>
                </div>
                <div class="feature">
                    <div class="feature-icon">🌟</div>
                    <h3 class="feature-title">Premium Quality</h3>
                    <p class="feature-description">Exceptional quality standards maintained throughout every project and service delivery.</p>
                </div>
                <div class="feature">
                    <div class="feature-icon">🤝</div>
                    <h3 class="feature-title">24/7 Support</h3>
                    <p class="feature-description">Round-the-clock support to ensure your success, whenever you need assistance.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact">
        <div class="container">
            <h2 class="section-title">Get In Touch</h2>
            <div class="contact-content">
                <div class="contact-info">
                    <h3>Ready to get started?</h3>
                    <p>Let's discuss how OVACS can help transform your business. Reach out to us today for a consultation.</p>
                    <div class="contact-details">
                        <div class="contact-item">
                            <strong>Email:</strong> info@ovacs.com
                        </div>
                        <div class="contact-item">
                            <strong>Phone:</strong> (555) 123-4567
                        </div>
                    </div>
                </div>
                <div class="contact-form">
                    <form id="contactForm" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                        <div class="form-group">
                            <input type="text" id="name" name="name" placeholder="Your Name" required>
                        </div>
                        <div class="form-group">
                            <input type="email" id="email" name="email" placeholder="Your Email" required>
                        </div>
                        <div class="form-group">
                            <textarea id="message" name="message" rows="5" placeholder="Your Message" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <!-- Contact Form Handler -->
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = htmlspecialchars(trim($_POST['name'] ?? ''));
        $email = htmlspecialchars(trim($_POST['email'] ?? ''));
        $message = htmlspecialchars(trim($_POST['message'] ?? ''));
        
        if (!empty($name) && !empty($email) && !empty($message) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // In a real application, you would send this to a database or email
            $success_message = "Thank you, $name! Your message has been sent successfully.";
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    alert('$success_message');
                });
            </script>";
        } else {
            $error_message = "Please fill in all fields with valid information.";
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    alert('$error_message');
                });
            </script>";
        }
    }
    ?>

    <script src="js/main.js"></script>
</body>
</html>