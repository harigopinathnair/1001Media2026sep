<footer class="newsletter-footer">
    <div class="container">
        <div class="newsletter-content">
            <h2>Grow Your Online Presence</h2>
            <p>Subscribe to our newsletter for weekly guides on search engine optimization, paid traffic, social media marketing, and content strategy.</p>
            <?php if (isset($_GET['subscribed']) && $_GET['subscribed'] === 'success'): ?>
                <div class="alert alert-success" style="padding: 0.5rem 1rem; margin-bottom: 1rem; font-size: 0.85rem; text-align: center;"><i class="fa-solid fa-circle-check"></i> Subscribed successfully!</div>
            <?php endif; ?>
            <form class="newsletter-form" action="<?php echo BASE_URL; ?>subscribe.php" method="POST">
                <input type="text" name="name" placeholder="Your Name" required style="padding: 1rem 1.5rem; border-radius: 50px; border: none; outline: none; font-family: var(--font-body); flex-grow: 0.5;">
                <input type="email" name="email" placeholder="Your Email Address" required style="padding: 1rem 1.5rem; border-radius: 50px; border: none; outline: none; font-family: var(--font-body); flex-grow: 1;">
                <button type="submit">Subscribe</button>
            </form>
        </div>

        <div class="footer-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 2.5rem; margin-top: 3rem; text-align: left;">
            <div class="footer-widget">
                <h4 style="color: var(--bg-white); font-size: 1.25rem; font-family: var(--font-heading); margin-bottom: 1.2rem;">Your Digital Marketing Partner</h4>
                <p style="font-size: 0.9rem; color: rgba(255, 255, 255, 0.7); line-height: 1.6; margin-bottom: 1.5rem;">We use seo, search marketing and social media marketing strategies to develop local and small businesses.</p>
                <p style="font-size: 0.9rem; color: rgba(255, 255, 255, 0.7);"><strong style="color: var(--accent-color);">Open Hours:</strong><br>Mon – Sat: 9 am – 6 pm</p>
            </div>
            
            <div class="footer-widget">
                <h4 style="color: var(--bg-white); font-size: 1.25rem; font-family: var(--font-heading); margin-bottom: 1.2rem;">Contact Details</h4>
                <ul class="footer-links" style="list-style: none; padding: 0; margin: 0; font-size: 0.9rem; color: rgba(255, 255, 255, 0.7);">
                    <li style="margin-bottom: 0.75rem;"><i class="fa-solid fa-location-dot" style="color: var(--accent-color); margin-right: 0.5rem;"></i> 1001 Media LLC, 1st floor, Zarouni building, Dubai, UAE</li>
                    <li style="margin-bottom: 0.75rem;"><i class="fa-solid fa-envelope" style="color: var(--accent-color); margin-right: 0.5rem;"></i> <a href="mailto:contact@1001media.me" style="color: inherit;">contact@1001media.me</a></li>
                    <li style="margin-bottom: 0.75rem;"><i class="fa-solid fa-phone" style="color: var(--accent-color); margin-right: 0.5rem;"></i> <a href="tel:+971585819533" style="color: inherit;">+971 585819533</a></li>
                </ul>
            </div>

            <div class="footer-widget">
                <h4 style="color: var(--bg-white); font-size: 1.25rem; font-family: var(--font-heading); margin-bottom: 1.2rem;">Quick Links</h4>
                <ul class="footer-links">
                    <li><a href="<?php echo BASE_URL; ?>">Home</a></li>
                    <li><a href="<?php echo BASE_URL; ?>blog.php?category=seo">SEO Dubai</a></li>
                    <li><a href="<?php echo BASE_URL; ?>blog.php?category=paid-ads">PPC Advertising</a></li>
                    <li><a href="<?php echo BASE_URL; ?>blog.php?category=social-media">Social Media</a></li>
                    <li><a href="<?php echo BASE_URL; ?>contact.php">Contact</a></li>
                    <li><a href="<?php echo BASE_URL; ?>blog.php">Blog</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom" style="margin-top: 3rem; padding-top: 1.5rem; border-top: 1px solid rgba(255, 255, 255, 0.1); text-align: center; font-size: 0.85rem; color: rgba(255, 255, 255, 0.5);">
            <p>&copy; <?php echo date('Y'); ?> 1001media | <a href="#" onclick="event.preventDefault();" style="color: inherit; text-decoration: underline;">Privacy Policy</a> | <a href="#" onclick="event.preventDefault();" style="color: inherit; text-decoration: underline;">Disclosure</a> | <a href="#" onclick="event.preventDefault();" style="color: inherit; text-decoration: underline;">Terms and Conditions</a></p>
        </div>
    </div>
</footer>

<!-- Floating Chat Widget -->
<div id="chat-widget-container" style="position: fixed; bottom: 30px; right: 30px; z-index: 9999; font-family: var(--font-body);">
    <!-- Chat Toggle Button -->
    <button id="chat-widget-toggle" style="background-color: var(--primary-light); color: #fff; border: none; width: 60px; height: 60px; border-radius: 50px; cursor: pointer; box-shadow: 0 4px 16px rgba(37, 99, 235, 0.3); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; transition: var(--transition); outline: none;">
        <i class="fa-solid fa-comments"></i>
    </button>

    <!-- Chat Box Panel -->
    <div id="chat-widget-box" style="display: none; position: absolute; bottom: 80px; right: 0; width: 320px; background-color: #fff; border-radius: var(--radius-lg); box-shadow: var(--shadow-lg); border: 1px solid var(--border-color); overflow: hidden; flex-direction: column; opacity: 0; transform: translateY(20px); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);">
        <!-- Header -->
        <div style="background-color: var(--primary-color); color: #fff; padding: 1.25rem; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="position: relative;">
                    <div style="background-color: rgba(255, 255, 255, 0.2); width: 40px; height: 40px; border-radius: 50px; display: flex; align-items: center; justify-content: center; font-size: 1rem; font-weight: 800; color: #fff;">1001</div>
                    <span style="position: absolute; bottom: 0; right: 0; width: 10px; height: 10px; background-color: #10b981; border: 2px solid var(--primary-color); border-radius: 50px;"></span>
                </div>
                <div style="text-align: left;">
                    <h4 style="font-size: 0.95rem; font-weight: 700; margin: 0; color: #fff; font-family: var(--font-body);">1001Media Live</h4>
                    <p style="font-size: 0.7rem; color: rgba(255, 255, 255, 0.7); margin: 0;">We will get back to you</p>
                </div>
            </div>
            <button id="chat-widget-close" style="background: none; border: none; color: #fff; cursor: pointer; font-size: 1.1rem; outline: none;"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <!-- Body / Form -->
        <div id="chat-widget-body" style="padding: 1.5rem; max-height: 380px; overflow-y: auto; background-color: #fff;">
            <p style="font-size: 0.8rem; color: var(--text-muted); line-height: 1.5; margin-bottom: 1.25rem; text-align: left;">Hello! Fill in your contact info below and we will get back to you shortly.</p>
            
            <form id="chat-widget-form" style="display: flex; flex-direction: column; gap: 0.9rem; text-align: left;">
                <div style="display: flex; flex-direction: column; gap: 0.3rem;">
                    <label style="font-size: 0.75rem; font-weight: 700; color: var(--text-dark);">Name</label>
                    <input type="text" id="chat-name" placeholder="Your Name" required style="width: 100%; padding: 0.6rem 0.8rem; border: 1px solid var(--border-color); border-radius: var(--radius-sm); font-size: 0.85rem; outline: none; font-family: var(--font-body); background-color: #fff; color: var(--text-dark);">
                </div>
                <div style="display: flex; flex-direction: column; gap: 0.3rem;">
                    <label style="font-size: 0.75rem; font-weight: 700; color: var(--text-dark);">Email</label>
                    <input type="email" id="chat-email" placeholder="Your Email" required style="width: 100%; padding: 0.6rem 0.8rem; border: 1px solid var(--border-color); border-radius: var(--radius-sm); font-size: 0.85rem; outline: none; font-family: var(--font-body); background-color: #fff; color: var(--text-dark);">
                </div>
                <div style="display: flex; flex-direction: column; gap: 0.3rem;">
                    <label style="font-size: 0.75rem; font-weight: 700; color: var(--text-dark);">Message</label>
                    <textarea id="chat-message" placeholder="How can we help you?" required style="width: 100%; padding: 0.6rem 0.8rem; border: 1px solid var(--border-color); border-radius: var(--radius-sm); font-size: 0.85rem; outline: none; height: 80px; resize: none; font-family: var(--font-body); background-color: #fff; color: var(--text-dark);"></textarea>
                </div>
                <button type="submit" style="background-color: var(--primary-light); color: #fff; border: none; border-radius: var(--radius-sm); padding: 0.75rem; font-weight: 700; font-family: var(--font-body); cursor: pointer; transition: var(--transition); font-size: 0.85rem; margin-top: 0.3rem; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                    <span>Start Chat</span> <i class="fa-solid fa-paper-plane"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('chat-widget-toggle');
    const chatBox = document.getElementById('chat-widget-box');
    const closeBtn = document.getElementById('chat-widget-close');
    const form = document.getElementById('chat-widget-form');
    const chatBody = document.getElementById('chat-widget-body');

    let isOpen = false;

    function toggleChat() {
        if (!isOpen) {
            chatBox.style.display = 'flex';
            setTimeout(() => {
                chatBox.style.opacity = '1';
                chatBox.style.transform = 'translateY(0)';
            }, 10);
            toggleBtn.innerHTML = '<i class="fa-solid fa-minus"></i>';
        } else {
            chatBox.style.opacity = '0';
            chatBox.style.transform = 'translateY(20px)';
            setTimeout(() => {
                chatBox.style.display = 'none';
            }, 300);
            toggleBtn.innerHTML = '<i class="fa-solid fa-comments"></i>';
        }
        isOpen = !isOpen;
    }

    toggleBtn.addEventListener('click', toggleChat);
    closeBtn.addEventListener('click', toggleChat);

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Sending...';

        const name = document.getElementById('chat-name').value;
        const email = document.getElementById('chat-email').value;
        const message = document.getElementById('chat-message').value;

        fetch('<?php echo BASE_URL; ?>submit_chat.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ name, email, message })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                chatBody.innerHTML = `
                    <div style="text-align: center; padding: 2rem 0; font-family: var(--font-body);">
                        <div style="color: #10b981; font-size: 3rem; margin-bottom: 1rem;"><i class="fa-solid fa-circle-check"></i></div>
                        <h4 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--primary-color);">Thank You!</h4>
                        <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.5;">Your message has been captured. Our team will contact you via email shortly.</p>
                    </div>
                `;
            } else {
                alert(data.error || 'Something went wrong. Please try again.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<span>Start Chat</span> <i class="fa-solid fa-paper-plane"></i>';
            }
        })
        .catch(err => {
            alert('Connection error. Please try again.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<span>Start Chat</span> <i class="fa-solid fa-paper-plane"></i>';
        });
    });
});
</script>

<?php echo getSetting('footer_code'); ?>
</body>
</html>
