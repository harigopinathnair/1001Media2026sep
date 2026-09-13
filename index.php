<?php
require_once __DIR__ . '/config.php';

$error_msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['callback_submit'])) {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    
    if (empty($name) || empty($phone)) {
        $error_msg = "Please fill in all required fields.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (:name, 'callback@request.me', 'Call Back Request', :phone)");
            $stmt->execute(['name' => $name, 'phone' => "Phone: " . $phone]);
            header("Location: " . BASE_URL . "thank-you.php");
            exit;
        } catch (\PDOException $e) {
            $error_msg = "Error submitting request. Please try again.";
        }
    }
}

$page_title = "Home";
require_once __DIR__ . '/includes/header.php';

// Initialize variables
$featured = null;
$recent_posts = [];
$popular_posts = [];
$categories = [];

// Fetch Featured Post
try {
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name, c.slug as category_slug 
        FROM posts p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.status = 'published' AND p.is_featured = 1 
        ORDER BY p.created_at DESC 
        LIMIT 1
    ");
    $stmt->execute();
    $featured = $stmt->fetch();
    
    // Fallback if no featured post
    if (!$featured) {
        $stmt = $pdo->prepare("
            SELECT p.*, c.name as category_name, c.slug as category_slug 
            FROM posts p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.status = 'published' 
            ORDER BY p.created_at DESC 
            LIMIT 1
        ");
        $stmt->execute();
        $featured = $stmt->fetch();
    }

    // Fetch Recent Posts (excluding featured post if exists)
    $exclude_id = $featured ? $featured['id'] : 0;
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name, c.slug as category_slug 
        FROM posts p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.status = 'published' AND p.id != :exclude_id 
        ORDER BY p.created_at DESC 
        LIMIT 3
    ");
    $stmt->execute(['exclude_id' => $exclude_id]);
    $recent_posts = $stmt->fetchAll();

    // Fetch Popular Posts (by views)
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name, c.slug as category_slug 
        FROM posts p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.status = 'published' 
        ORDER BY p.views DESC 
        LIMIT 4
    ");
    $stmt->execute();
    $popular_posts = $stmt->fetchAll();

    // Fetch Categories with post count
    $stmt = $pdo->prepare("
        SELECT c.*, COUNT(p.id) as post_count 
        FROM categories c 
        LEFT JOIN posts p ON c.id = p.category_id AND p.status = 'published' 
        GROUP BY c.id 
        ORDER BY post_count DESC
    ");
    $stmt->execute();
    $categories = $stmt->fetchAll();
    
} catch (\PDOException $e) {
    // Graceful error display
    $db_error = true;
}
?>



<!-- Hero Section -->
<section class="hero-section" style="padding: 5rem 0; background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #fff;">
    <div class="container hero-grid" style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 3rem; align-items: center;">
        <div class="hero-content">
            <span class="hero-tag" style="background-color: var(--primary-light); color: #fff; padding: 0.4rem 1rem; border-radius: 50px; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; display: inline-block; margin-bottom: 1.5rem;">Welcome to 1001Media</span>
            <h1 style="font-size: 3rem; color: #fff; line-height: 1.2; margin-bottom: 1.5rem; font-weight: 800;">A Digital Marketing Agency That Delivers Growth</h1>
            <p style="font-size: 1.1rem; line-height: 1.6; color: #cbd5e1; margin-bottom: 2rem;">We specialize in driving traffic to your website, increasing conversions, and building a loyal customer base. Let us take your digital marketing efforts to the next level.</p>
            
            <div style="display: flex; gap: 1.5rem; align-items: center;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-circle-check" style="color: var(--accent-color);"></i>
                    <span style="font-size: 0.9rem; color: #cbd5e1;">Google Ads Management</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-circle-check" style="color: var(--accent-color);"></i>
                    <span style="font-size: 0.9rem; color: #cbd5e1;">SEO Optimization</span>
                </div>
            </div>
        </div>
        
        <div class="hero-form-container" style="background-color: #fff; color: var(--text-dark); padding: 2.5rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-lg);">
            <h3 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.5rem; text-align: center; color: var(--primary-color);">Request a Call Back Today!</h3>
            <p style="font-size: 0.85rem; color: var(--text-muted); text-align: center; margin-bottom: 1.5rem;">Get in touch with our marketing experts.</p>
            
            <?php if (!empty($success_msg)): ?>
                <div class="alert alert-success" style="padding: 0.75rem; margin-bottom: 1rem; border-radius: var(--radius-sm); font-size: 0.85rem;"><i class="fa-solid fa-circle-check"></i> <?php echo e($success_msg); ?></div>
            <?php endif; ?>
            <?php if (!empty($error_msg)): ?>
                <div class="alert alert-danger" style="padding: 0.75rem; margin-bottom: 1rem; border-radius: var(--radius-sm); font-size: 0.85rem;"><i class="fa-solid fa-circle-exclamation"></i> <?php echo e($error_msg); ?></div>
            <?php endif; ?>

            <form action="" method="POST" style="display: flex; flex-direction: column; gap: 1rem;">
                <div style="display: flex; flex-direction: column; gap: 0.4rem;">
                    <label style="font-size: 0.8rem; font-weight: 700; color: var(--text-dark);">Name</label>
                    <input type="text" name="name" placeholder="Your Name" required style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border-color); border-radius: var(--radius-sm); font-family: var(--font-body); font-size: 0.9rem;">
                </div>
                <div style="display: flex; flex-direction: column; gap: 0.4rem;">
                    <label style="font-size: 0.8rem; font-weight: 700; color: var(--text-dark);">Phone <span style="color: var(--admin-danger);">*Required</span></label>
                    <input type="tel" name="phone" placeholder="Your Phone Number" required style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border-color); border-radius: var(--radius-sm); font-family: var(--font-body); font-size: 0.9rem;">
                </div>
                <button type="submit" name="callback_submit" style="background-color: var(--primary-light); color: #fff; border: none; border-radius: var(--radius-sm); padding: 0.9rem; font-weight: 700; font-family: var(--font-body); cursor: pointer; transition: var(--transition); text-transform: uppercase; letter-spacing: 0.5px; font-size: 0.9rem; margin-top: 0.5rem;">Get Started</button>
            </form>
        </div>
    </div>
</section>

<!-- Brand Partner Marquee Strip Styling & HTML -->
<style>
.marquee-wrapper {
    overflow: hidden;
    width: 100%;
    background: #1e293b;
    color: #fff;
    padding: 1.25rem 0;
    border-bottom: 2px solid rgba(255,255,255,0.05);
    display: flex;
}
.marquee-track {
    display: flex;
    width: max-content;
    animation: scroll-marquee 45s linear infinite;
    white-space: nowrap;
}
@keyframes scroll-marquee {
    0% {
        transform: translate3d(0, 0, 0);
    }
    100% {
        transform: translate3d(-50%, 0, 0);
    }
}
</style>

<div class="marquee-wrapper">
    <div class="marquee-track">
        <span style="font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; padding: 0 4rem; color: #f8fafc; font-family: var(--font-body); display: flex; align-items: center; gap: 0.75rem;">
            <span>We don't just work with brands, we partner with them</span>
            <i class="fa-solid fa-star" style="color: var(--accent-color); font-size: 0.65rem;"></i>
            <span style="color: #94a3b8; font-weight: 500;">Our Experience: Satbir International, MEED, South View School, New Delhi Private School, Dion Villard, FirstCare, CommercePundit, ILG, Swiss Watch Group, Fortes Education, Candere.</span>
        </span>
        <!-- Duplicate for continuous scrolling -->
        <span style="font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; padding: 0 4rem; color: #f8fafc; font-family: var(--font-body); display: flex; align-items: center; gap: 0.75rem;">
            <span>We don't just work with brands, we partner with them</span>
            <i class="fa-solid fa-star" style="color: var(--accent-color); font-size: 0.65rem;"></i>
            <span style="color: #94a3b8; font-weight: 500;">Our Experience: Satbir International, MEED, South View School, New Delhi Private School, Dion Villard, FirstCare, CommercePundit, ILG, Swiss Watch Group, Fortes Education, Candere.</span>
        </span>
    </div>
</div>

<!-- About & Value Proposition Section -->
<section style="padding: 6rem 0; background-color: var(--bg-white);">
    <div class="container" style="display: grid; grid-template-columns: 1.12fr 0.88fr; gap: 5rem; align-items: center;">
        <div>
            <span style="font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--primary-light); display: block; margin-bottom: 0.75rem;">No More Guesswork. Just Results.</span>
            <h2 style="font-size: 2.5rem; margin-bottom: 1.5rem; line-height: 1.3;">Our digital marketing solutions help you achieve business growth effortlessly.</h2>
            <p style="font-size: 1.1rem; font-weight: 600; color: var(--text-muted); margin-bottom: 1.5rem; line-height: 1.6; font-style: italic;">"If you can envision it, we can optimize it."</p>
            <h3 style="font-size: 1.3rem; margin-bottom: 1rem; color: var(--primary-color);">Full-service Digital Marketing Agency</h3>
            <p style="color: var(--text-muted); line-height: 1.8; font-size: 0.95rem;">As a full-service digital agency with a passion for media buying, no matter what industry your business is in, we can increase your company’s visibility to attract more customers using our in-house built <strong>PROSPER Funnels</strong> strategy. This helps us deliver a cost-effective marketing strategy that’s affordable for successful marketing campaigns by increasing ROI.</p>
        </div>
        
        <div style="background-color: var(--bg-light); padding: 3rem; border-radius: var(--radius-lg); border: 1px solid var(--border-color);">
            <h3 style="font-size: 1.75rem; margin-bottom: 1rem; line-height: 1.3;">Your Full-Service Digital Marketing Partner</h3>
            <p style="color: var(--text-muted); line-height: 1.6; font-size: 0.9rem; margin-bottom: 2.5rem;">With our proven marketing strategies, you can grow revenue, generate more leads, and save time.</p>
            
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; text-align: center;">
                <div>
                    <span style="font-size: 2.25rem; font-weight: 800; color: var(--primary-light); display: block;">60%</span>
                    <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Increase</span>
                </div>
                <div>
                    <span style="font-size: 2.25rem; font-weight: 800; color: var(--primary-light); display: block;">70%</span>
                    <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">More Leads</span>
                </div>
                <div>
                    <span style="font-size: 2.25rem; font-weight: 800; color: var(--primary-light); display: block;">10h</span>
                    <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Hours Saved</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Our Services Section -->
<section style="padding: 6rem 0; background-color: var(--bg-light); border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color);">
    <div class="container" style="text-align: center;">
        <span style="font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--primary-light); display: block; margin-bottom: 0.75rem;">What We Excel At</span>
        <h2 style="font-size: 2.5rem; margin-bottom: 3.5rem; font-family: var(--font-heading);">Our Services</h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; text-align: left;">
            <!-- SEO Dubai Card -->
            <div style="background-color: var(--bg-white); padding: 2.5rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid var(--border-color); display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="background-color: rgba(37, 99, 235, 0.1); color: var(--primary-light); width: 60px; height: 60px; border-radius: 50px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 1.5rem;">
                        <i class="fa-solid fa-magnifying-glass-chart"></i>
                    </div>
                    <h3 style="font-size: 1.35rem; margin-bottom: 1rem;">SEO Dubai</h3>
                    <p style="color: var(--text-muted); font-size: 0.9rem; line-height: 1.6; margin-bottom: 2rem;">We strive deliver ROI-driven SEO campaigns that help you achieve consistent search engine results. With 15+years global experience, you can count on our SEO consultants to direct you in the right direction.</p>
                </div>
                <a href="<?php echo BASE_URL; ?>blog.php?category=seo" style="color: var(--primary-light); font-weight: 700; font-size: 0.9rem; display: flex; align-items: center; gap: 0.5rem; text-transform: uppercase; letter-spacing: 0.5px;">Know More <i class="fa-solid fa-arrow-right"></i></a>
            </div>

            <!-- PPC Advertising Card -->
            <div style="background-color: var(--bg-white); padding: 2.5rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid var(--border-color); display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="background-color: rgba(37, 99, 235, 0.1); color: var(--primary-light); width: 60px; height: 60px; border-radius: 50px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 1.5rem;">
                        <i class="fa-solid fa-rectangle-ad"></i>
                    </div>
                    <h3 style="font-size: 1.35rem; margin-bottom: 1rem;">PPC Advertising</h3>
                    <p style="color: var(--text-muted); font-size: 0.9rem; line-height: 1.6; margin-bottom: 2rem;">Our PPC Marketing experts goes above and beyond to help large and small businesses in Dubai with all facets of pay-per-click marketing, and help them to broadcast their message and expand their reach more effectively.</p>
                </div>
                <a href="<?php echo BASE_URL; ?>blog.php?category=paid-ads" style="color: var(--primary-light); font-weight: 700; font-size: 0.9rem; display: flex; align-items: center; gap: 0.5rem; text-transform: uppercase; letter-spacing: 0.5px;">Know More <i class="fa-solid fa-arrow-right"></i></a>
            </div>

            <!-- Social Media Marketing Card -->
            <div style="background-color: var(--bg-white); padding: 2.5rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid var(--border-color); display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="background-color: rgba(37, 99, 235, 0.1); color: var(--primary-light); width: 60px; height: 60px; border-radius: 50px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 1.5rem;">
                        <i class="fa-solid fa-share-nodes"></i>
                    </div>
                    <h3 style="font-size: 1.35rem; margin-bottom: 1rem;">Social Media Marketing</h3>
                    <p style="color: var(--text-muted); font-size: 0.9rem; line-height: 1.6; margin-bottom: 2rem;">Facebook and Instagram advertising enable a business to target prospects based on their browsing and buying behavior online. You can use this information to efficiently target and reach your potential customers.</p>
                </div>
                <a href="<?php echo BASE_URL; ?>blog.php?category=social-media" style="color: var(--primary-light); font-weight: 700; font-size: 0.9rem; display: flex; align-items: center; gap: 0.5rem; text-transform: uppercase; letter-spacing: 0.5px;">Know More <i class="fa-solid fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
</section>

<!-- Business Growth & Optimization Section -->
<section style="padding: 6rem 0; background-color: var(--bg-white);">
    <div class="container" style="display: grid; grid-template-columns: 0.9fr 1.1fr; gap: 5rem; align-items: center;">
        <div>
            <h2 style="font-size: 2.5rem; margin-bottom: 1.5rem; line-height: 1.3;">7+ Years of Helping Businesses Thrive</h2>
            <p style="color: var(--text-muted); line-height: 1.7; font-size: 0.95rem; margin-bottom: 2rem;">We partner with businesses to establish robust digital pipelines that turn impressions into revenue. Harness the power of optimization and funnel engineering.</p>
            <div style="background-color: var(--bg-light); border-left: 4px solid var(--primary-light); padding: 1.5rem; border-radius: 0 var(--radius-md) var(--radius-md) 0;">
                <p style="font-style: italic; color: var(--text-dark); font-weight: 600; font-size: 0.95rem;">"Start scaling today with expert-driven digital marketing solutions."</p>
            </div>
        </div>
        
        <div style="display: flex; flex-direction: column; gap: 2rem;">
            <!-- Save Time -->
            <div style="display: flex; gap: 1.5rem;">
                <div style="background-color: var(--bg-light); color: var(--primary-light); width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0; border: 1px solid var(--border-color);">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                <div>
                    <h4 style="font-size: 1.15rem; margin-bottom: 0.4rem; color: var(--primary-color);">Save Time & Boost Productivity</h4>
                    <p style="font-size: 0.9rem; color: var(--text-muted); line-height: 1.5;">Automate your digital marketing workflows and focus on what matters.</p>
                </div>
            </div>

            <!-- Convert More Leads -->
            <div style="display: flex; gap: 1.5rem;">
                <div style="background-color: var(--bg-light); color: var(--primary-light); width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0; border: 1px solid var(--border-color);">
                    <i class="fa-solid fa-filter"></i>
                </div>
                <div>
                    <h4 style="font-size: 1.15rem; margin-bottom: 0.4rem; color: var(--primary-color);">Convert More Leads</h4>
                    <p style="font-size: 0.9rem; color: var(--text-muted); line-height: 1.5;">Guide customers from awareness to purchase with a seamless experience.</p>
                </div>
            </div>

            <!-- Optimize Campaign Performance -->
            <div style="display: flex; gap: 1.5rem;">
                <div style="background-color: var(--bg-light); color: var(--primary-light); width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0; border: 1px solid var(--border-color);">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
                <div>
                    <h4 style="font-size: 1.15rem; margin-bottom: 0.4rem; color: var(--primary-color);">Optimize Campaign Performance</h4>
                    <p style="font-size: 0.9rem; color: var(--text-muted); line-height: 1.5;">Track, analyze, and refine strategies for maximum results.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Trust & Features List Section -->
<section style="padding: 6rem 0; background-color: var(--bg-light); border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color);">
    <div class="container">
        <div style="text-align: center; max-width: 800px; margin: 0 auto 4rem auto;">
            <span style="font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--primary-light); display: block; margin-bottom: 0.75rem;">Why Partner With Us</span>
            <h2 style="font-size: 2.25rem; margin-bottom: 1rem; line-height: 1.3;">When You Choose 1001 Media, You Get More Than Just Marketing</h2>
            <p style="color: var(--text-muted); line-height: 1.6; font-size: 0.95rem;">We provide the tools, support, and expertise to help your business succeed.</p>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1.2fr; gap: 5rem; align-items: center;">
            <div style="background-color: var(--bg-white); padding: 3rem; border-radius: var(--radius-lg); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);">
                <h3 style="font-size: 1.35rem; margin-bottom: 1.5rem; color: var(--primary-color);">Our Capabilities</h3>
                <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 1rem;">
                    <li style="display: flex; align-items: center; gap: 0.75rem; font-size: 0.95rem;"><i class="fa-solid fa-circle-check" style="color: var(--primary-light);"></i> Google Ads Management</li>
                    <li style="display: flex; align-items: center; gap: 0.75rem; font-size: 0.95rem;"><i class="fa-solid fa-circle-check" style="color: var(--primary-light);"></i> Social Media Marketing & Advertising</li>
                    <li style="display: flex; align-items: center; gap: 0.75rem; font-size: 0.95rem;"><i class="fa-solid fa-circle-check" style="color: var(--primary-light);"></i> Search Engine Optimization</li>
                    <li style="display: flex; align-items: center; gap: 0.75rem; font-size: 0.95rem;"><i class="fa-solid fa-circle-check" style="color: var(--primary-light);"></i> E-Commerce Development</li>
                </ul>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 2rem;">
                <div>
                    <h4 style="font-size: 1.05rem; margin-bottom: 0.5rem; color: var(--primary-color);"><i class="fa-solid fa-user-tie" style="color: var(--primary-light); margin-right: 0.5rem;"></i> Dedicated Account Manager</h4>
                    <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.5;">Direct access to a strategist focused entirely on your metrics.</p>
                </div>
                <div>
                    <h4 style="font-size: 1.05rem; margin-bottom: 0.5rem; color: var(--primary-color);"><i class="fa-solid fa-headset" style="color: var(--primary-light); margin-right: 0.5rem;"></i> 24/7 Customer Support</h4>
                    <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.5;">Friendly and expert assistance whenever you need it.</p>
                </div>
                <div>
                    <h4 style="font-size: 1.05rem; margin-bottom: 0.5rem; color: var(--primary-color);"><i class="fa-solid fa-chart-pie" style="color: var(--primary-light); margin-right: 0.5rem;"></i> Campaign Optimization</h4>
                    <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.5;">Continuous updates based on hard data and user performance.</p>
                </div>
                <div>
                    <h4 style="font-size: 1.05rem; margin-bottom: 0.5rem; color: var(--primary-color);"><i class="fa-solid fa-route" style="color: var(--primary-light); margin-right: 0.5rem;"></i> Custom Marketing Roadmap</h4>
                    <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.5;">Tailored strategic plans built specifically for your niche.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Blog Section Anchor -->
<span id="latest-blog-stories"></span>

<!-- Magazine Content Grid -->
<div class="container magazine-grid">
    <!-- Left Column: Featured & Recent Posts -->
    <main class="main-content-area">
        <?php if (isset($db_error)): ?>
            <div class="alert alert-danger">Error connecting to database. Please check your MySQL setup.</div>
        <?php else: ?>
            
            <!-- Featured Post Section -->
            <?php if ($featured): ?>
                <div class="section-title-wrap">
                    <h2 class="section-title">Featured Article</h2>
                </div>
                <article class="featured-card">
                    <div class="card-img-wrap">
                        <span class="category-badge"><?php echo e($featured['category_name']); ?></span>
                        <a href="<?php echo BASE_URL; ?>post.php?slug=<?php echo e($featured['slug']); ?>">
                            <img src="<?php echo BASE_URL . e($featured['image_url']); ?>" alt="<?php echo e($featured['title']); ?>" onerror="this.src='https://images.unsplash.com/photo-1592417817098-8f3d6eb19675?auto=format&fit=crop&w=800&q=80'">
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="card-meta">
                            <span><i class="fa-regular fa-calendar-days"></i> <?php echo formatDate($featured['created_at']); ?></span>
                            <span><i class="fa-regular fa-eye"></i> <?php echo e($featured['views']); ?> views</span>
                        </div>
                        <h3 class="card-title">
                            <a href="<?php echo BASE_URL; ?>post.php?slug=<?php echo e($featured['slug']); ?>"><?php echo e($featured['title']); ?></a>
                        </h3>
                        <p class="card-excerpt"><?php echo e($featured['summary']); ?></p>
                        <a href="<?php echo BASE_URL; ?>post.php?slug=<?php echo e($featured['slug']); ?>" class="read-more">Read Full Guide <i class="fa-solid fa-arrow-right"></i></a>
                    </div>
                </article>
            <?php endif; ?>

            <!-- Recent Articles Section -->
            <div class="section-title-wrap" style="margin-top: 4rem;">
                <h2 class="section-title">Recent Stories</h2>
                <a href="<?php echo BASE_URL; ?>blog.php" class="view-all-link">View All <i class="fa-solid fa-arrow-right-long"></i></a>
            </div>

            <div class="posts-grid" style="padding: 0; display: grid; gap: 2rem; grid-template-columns: 1fr;">
                <?php if (empty($recent_posts)): ?>
                    <p>No other posts found. Check back later!</p>
                <?php else: ?>
                    <?php foreach ($recent_posts as $post): ?>
                        <div class="post-card" style="display: flex; flex-direction: row; align-items: stretch; min-height: 200px;">
                            <div class="card-img-wrap" style="width: 240px; aspect-ratio: auto; flex-shrink: 0;">
                                <span class="category-badge" style="top: 0.75rem; left: 0.75rem; font-size: 0.65rem; padding: 0.25rem 0.6rem;"><?php echo e($post['category_name']); ?></span>
                                <a href="<?php echo BASE_URL; ?>post.php?slug=<?php echo e($post['slug']); ?>">
                                    <img src="<?php echo BASE_URL . e($post['image_url']); ?>" alt="<?php echo e($post['title']); ?>" style="height: 100%; object-fit: cover;" onerror="this.src='https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w=600&q=80'">
                                </a>
                            </div>
                            <div class="card-body" style="padding: 1.5rem;">
                                <div class="card-meta">
                                    <span><i class="fa-regular fa-calendar-days"></i> <?php echo formatDate($post['created_at']); ?></span>
                                </div>
                                <h3 class="card-title" style="font-size: 1.3rem; margin-bottom: 0.5rem;">
                                    <a href="<?php echo BASE_URL; ?>post.php?slug=<?php echo e($post['slug']); ?>"><?php echo e($post['title']); ?></a>
                                </h3>
                                <p class="card-excerpt" style="font-size: 0.85rem; margin-bottom: 1rem;"><?php echo e($post['summary']); ?></p>
                                <a href="<?php echo BASE_URL; ?>post.php?slug=<?php echo e($post['slug']); ?>" class="read-more" style="font-size: 0.8rem;">Read More <i class="fa-solid fa-arrow-right"></i></a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        <?php endif; ?>
    </main>

    <!-- Right Column: Sidebar -->
    <aside class="sidebar">
        <!-- Search widget for Sidebar -->
        <div class="widget">
            <h3 class="widget-title">Search Articles</h3>
            <form action="<?php echo BASE_URL; ?>blog.php" method="GET" class="search-box">
                <input type="text" name="search" placeholder="Type keywords..." required style="padding: 0.75rem 1rem;">
                <button type="submit" style="padding: 0 1rem; font-size: 0.9rem;">Go</button>
            </form>
        </div>

        <!-- Popular Posts Widget -->
        <div class="widget">
            <h3 class="widget-title">Popular Articles</h3>
            <div class="mini-card-list">
                <?php if (empty($popular_posts)): ?>
                    <p>No articles to display.</p>
                <?php else: ?>
                    <?php foreach ($popular_posts as $pop): ?>
                        <article class="mini-card">
                            <div class="mini-img-wrap">
                                <img src="<?php echo BASE_URL . e($pop['image_url']); ?>" alt="<?php echo e($pop['title']); ?>" onerror="this.src='https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w=150&q=80'">
                            </div>
                            <div class="mini-content">
                                <span class="mini-category"><?php echo e($pop['category_name']); ?></span>
                                <h4 class="mini-title">
                                    <a href="<?php echo BASE_URL; ?>post.php?slug=<?php echo e($pop['slug']); ?>"><?php echo e($pop['title']); ?></a>
                                </h4>
                                <span class="mini-date"><?php echo e($pop['views']); ?> reads</span>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Categories Widget -->
        <div class="widget">
            <h3 class="widget-title">Categories</h3>
            <ul style="list-style: none; display: flex; flex-direction: column; gap: 0.8rem;">
                <?php foreach ($categories as $cat): ?>
                    <li>
                        <a href="<?php echo BASE_URL; ?>blog.php?category=<?php echo e($cat['slug']); ?>" style="display: flex; justify-content: space-between; font-weight: 600; color: var(--text-dark);">
                            <span><i class="fa-solid fa-chevron-right" style="font-size: 0.75rem; color: var(--accent-color); margin-right: 0.5rem;"></i> <?php echo e($cat['name']); ?></span>
                            <span style="background-color: var(--border-color); font-size: 0.75rem; padding: 0.15rem 0.5rem; border-radius: 20px; color: var(--text-muted);"><?php echo e($cat['post_count']); ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </aside>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
