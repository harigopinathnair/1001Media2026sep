<?php
require_once __DIR__ . '/config.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($message)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO contact_messages (name, email, subject, message) 
                VALUES (:name, :email, :subject, :message)
            ");
            $stmt->execute([
                'name' => $name,
                'email' => $email,
                'subject' => $subject,
                'message' => $message
            ]);
            
            header("Location: " . BASE_URL . "thank-you.php");
            exit;
        } catch (\PDOException $e) {
            $error = 'Sorry, there was an error sending your message. Please try again later.';
        }
    }
}

$page_title = "Contact Us";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container contact-section">
    <div class="contact-grid">
        <!-- Info Column -->
        <div class="contact-info">
            <div>
                <h2>Get in Touch</h2>
                <p style="color: #cbd5e1;">Have questions about our digital marketing services, SEO campaigns, or custom strategies? Drop us a line. We are here to help!</p>
                
                <div class="contact-details">
                    <div class="contact-detail-item">
                        <i class="fa-solid fa-location-dot"></i>
                        <span>1001 Media LLC, 1st floor, Zarouni building, Dubai, UAE</span>
                    </div>
                    <div class="contact-detail-item">
                        <i class="fa-solid fa-envelope"></i>
                        <span>contact@1001media.me</span>
                    </div>
                    <div class="contact-detail-item">
                        <i class="fa-solid fa-phone"></i>
                        <span>+971 585819533</span>
                    </div>
                    <div class="contact-detail-item">
                        <i class="fa-solid fa-clock"></i>
                        <span>Mon – Sat: 9 am – 6 pm</span>
                    </div>
                </div>
            </div>

            <div>
                <p style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; color: var(--accent-color); margin-bottom: 0.8rem; font-weight: 700;">Follow Our Journey</p>
                <div class="contact-socials">
                    <a href="#" onclick="event.preventDefault();"><i class="fa-brands fa-instagram"></i></a>
                    <a href="#" onclick="event.preventDefault();"><i class="fa-brands fa-facebook"></i></a>
                    <a href="#" onclick="event.preventDefault();"><i class="fa-brands fa-youtube"></i></a>
                    <a href="#" onclick="event.preventDefault();"><i class="fa-brands fa-pinterest"></i></a>
                </div>
            </div>
        </div>

        <!-- Form Column -->
        <div class="contact-form-wrap">
            <h2 style="font-size: 2rem; margin-bottom: 1.5rem;">Send a Message</h2>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?php echo e($success); ?></div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo e($error); ?></div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="grid-2">
                    <div class="form-group">
                        <label for="name">Your Name *</label>
                        <input type="text" id="name" name="name" class="form-control" value="<?php echo e($name ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?php echo e($email ?? ''); ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="subject">Subject</label>
                    <input type="text" id="subject" name="subject" class="form-control" value="<?php echo e($subject ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="message">Message *</label>
                    <textarea id="message" name="message" class="form-control" required><?php echo e($message ?? ''); ?></textarea>
                </div>
                <button type="submit" class="btn" style="width: 100%;">Send Message</button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
