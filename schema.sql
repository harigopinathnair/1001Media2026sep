DROP DATABASE IF EXISTS `1001mediame`;
CREATE DATABASE `1001mediame` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `1001mediame`;

-- Users table (For admin login)
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Categories table
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `description` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Posts table
CREATE TABLE IF NOT EXISTS `posts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `content` LONGTEXT NOT NULL,
  `summary` TEXT NOT NULL,
  `image_url` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('draft', 'published') DEFAULT 'draft',
  `views` INT DEFAULT 0,
  `is_featured` TINYINT(1) DEFAULT 0,
  `meta_title` VARCHAR(255) DEFAULT NULL,
  `meta_description` TEXT DEFAULT NULL,
  `meta_keywords` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Contact Messages table
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `subject` VARCHAR(150) DEFAULT NULL,
  `message` TEXT NOT NULL,
  `is_read` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Admin User (username: admin@1001media.me, password: Master_123@)
-- Password hash generated using PHP's password_hash('Master_123@', PASSWORD_DEFAULT)
INSERT INTO `users` (`id`, `username`, `password`, `email`) VALUES
(1, 'admin@1001media.me', '$2y$10$iIqkagvNuvaQGuoPJis32OAtxTQ5/9.LAr3n0LpvCB0Mqh6Ix26sW', 'admin@1001media.me')
ON DUPLICATE KEY UPDATE `id`=`id`;

-- Seed Categories
INSERT INTO `categories` (`id`, `name`, `slug`, `description`) VALUES
(1, 'SEO', 'seo', 'Search Engine Optimization strategy, tips, link building, and core web vitals.'),
(2, 'Content Marketing', 'content-marketing', 'Content creation, strategy, blogging, copywriting, and storytelling for business growth.'),
(3, 'Social Media', 'social-media', 'Engage and grow your audience on platforms like LinkedIn, Twitter, Instagram, and TikTok.'),
(4, 'Paid Ads', 'paid-ads', 'Paid advertising strategies, Google Ads, Meta Ads, remarketing, and maximizing ROI.')
ON DUPLICATE KEY UPDATE `id`=`id`;

-- Seed Posts
INSERT INTO `posts` (`category_id`, `title`, `slug`, `content`, `summary`, `image_url`, `status`, `views`, `is_featured`) VALUES
(3, 'Social Media Management in Dubai', 'social-media-management-dubai', '<p>To assist you in saving time and getting better results from your social media efforts, our company offers social media management services.</p><p>Some may think, "What can a social media marketing agency do for me?" If you hire us, you\'ll have more free time and a better investment return for very little money.</p><p>Improving your return on investment and lowering your cost per acquisition are the keys to a successful social media management strategy or any digital marketing effort. Because of this possibility, we help many companies with their social media marketing.</p><p>Targeting choices on social media sites like Facebook and Instagram, for example, are rather flexible.</p><p>You may choose specific Facebook users to advertise to based on their demographics, interests, and behaviors and upload your list of customers or email subscribers to retarget or locate new consumers who are similar to them in a lookalike audience.</p>', 'To assist you in saving time and getting better results from your social media efforts, our company offers social media management services.', 'assets/images/content_strategy.jpg', 'published', 245, 1),

(4, 'The Ultimate Guide to Google Ads & PPC Advertising in Dubai', 'google-ads-ppc-dubai-guide', '<p>Pay-Per-Click (PPC) advertising is one of the fastest ways to drive high-intent traffic to your website. Google Ads allows businesses of all sizes to show their ads to users precisely when they search for products or services. In Dubai\'s competitive landscape, a well-optimized Google Ads campaign can deliver instant visibility, highly qualified leads, and measurable ROI.</p><h3>Why Google Ads is Essential</h3><p>Unlike organic search which takes time to build, Google Ads gives you instantaneous reach. You can target specific keywords, regions, languages, and demographics, ensuring your budget is spent only on prospects most likely to convert.</p>', 'The Ultimate Guide to Google Ads and pay-per-click marketing to scale your Dubai business growth.', 'assets/images/paid_ads_seo.jpg', 'published', 189, 0),

(1, 'How to Choose the Best SEO Agency in Dubai for ROI-Driven Campaigns', 'best-seo-agency-dubai-roi', '<p>Selecting the right Search Engine Optimization (SEO) partner is critical to achieving long-term search engine visibility. A qualified SEO consultant or agency should focus on metrics that align with your business goals: organic traffic growth, quality lead generation, and overall return on investment.</p><h3>What to Look For in an SEO Partner</h3><ul><li>Proven track record of ranking clients in competitive niches.</li><li>Deep understanding of technical SEO, schema markup, and site structure.</li><li>Comprehensive reporting focused on business conversions rather than vanity metrics.</li></ul>', 'How to choose the right SEO partner in Dubai focused on business conversions and ROI.', 'assets/images/seo_guide.jpg', 'published', 142, 0),

(2, '10 Content Marketing Strategies to Scale Your E-Commerce Store', 'content-marketing-ecommerce-scale', '<p>In the digital commerce space, content is more than just words on a page—it is the bridge between product awareness and buying decisions. E-commerce businesses can leverage blogging, buying guides, and educational content to answer buyer questions and build brand authority.</p>', '10 actionable content strategy tips to increase conversions and organic visibility for e-commerce shops.', 'assets/images/content_strategy.jpg', 'published', 312, 0),

(1, 'Understanding Core Web Vitals: The Key to Boosting Your Google Rankings', 'understanding-core-web-vitals-google-rankings', '<p>Google\'s Page Experience update has made technical site performance a direct ranking factor. Core Web Vitals measure real-world user experience for loading performance, interactivity, and visual stability of a webpage.</p><h3>The Three Core Web Vitals Metrics</h3><ul><li><strong>Largest Contentful Paint (LCP):</strong> Measures loading speed (aim for under 2.5 seconds).</li><li><strong>First Input Delay (FID):</strong> Measures page responsiveness (aim for under 100 milliseconds).</li><li><strong>Cumulative Layout Shift (CLS):</strong> Measures visual stability of elements during loading (aim for under 0.1).</li></ul>', 'A guide to optimizing Core Web Vitals (LCP, FID, CLS) to improve user experience and Google search rankings.', 'assets/images/seo_guide.jpg', 'published', 276, 0),

(3, 'How to Optimize Your Facebook and Instagram Ads for Local Businesses', 'optimize-facebook-instagram-ads-local', '<p>Social media advertising allows local companies to target buyers based on precise geography and behavior. By tailoring your creative assets and bidding strategy for local audiences, you can maximize conversions and foot traffic.</p>', 'Tips to run high-converting localized ad campaigns on Facebook and Instagram for maximum reach.', 'assets/images/content_strategy.jpg', 'published', 198, 0),

(4, 'The Power of Retargeting: How to Recover Lost Customers with Remarketing', 'power-of-retargeting-remarketing-ads', '<p>Most first-time visitors to your website will leave without making a purchase. Remarketing campaigns allow you to re-engage these warm prospects with personalized ads across Google, social media, and third-party websites.</p>', 'Learn how to set up remarketing funnels to bring back website visitors and close more sales.', 'assets/images/paid_ads_seo.jpg', 'published', 154, 0),

(2, 'Why High-Quality Copywriting is the Secret to Higher Conversion Rates', 'copywriting-secret-higher-conversion-rates', '<p>Beautiful design attracts visitors, but persuasive copywriting converts them. From landing page copy to email campaigns, your words must address user pain points, convey value, and present clear calls-to-action.</p>', 'Discover how compelling copy drives actions, builds trust, and elevates your landing page conversions.', 'assets/images/content_strategy.jpg', 'published', 211, 0),

(1, 'A Beginner\'s Guide to Schema Markup and Structured Data for SEO', 'beginners-guide-schema-markup-seo', '<p>Schema markup is a code snippet added to your website that helps search engines return more informative results for users. It provides rich snippets in search results, improving click-through rates and SEO visibility.</p>', 'An introductory guide on structured data and schema markup to get rich results in Google search.', 'assets/images/seo_guide.jpg', 'published', 178, 0),

(3, 'The Pros and Cons of Influencer Marketing: Is It Right for Your Brand?', 'pros-cons-influencer-marketing-brand', '<p>Influencer marketing can quickly amplify brand reach by leveraging trust already built by content creators. However, finding the right alignment, managing campaigns, and tracking direct ROI can pose significant challenges.</p>', 'An honest review of influencer marketing benefits, risks, and performance tracking strategies.', 'assets/images/content_strategy.jpg', 'published', 290, 0),

(4, 'How to Build a High-Converting Landing Page for Your PPC Campaigns', 'high-converting-landing-page-ppc', '<p>Your pay-per-click ads are only as good as the landing page they lead to. A high-converting landing page must have a single focus, zero distractions, a strong value proposition, and an easily accessible lead capture form.</p>', 'A checklist to design and optimize dedicated landing pages for PPC and paid ad funnels.', 'assets/images/paid_ads_seo.jpg', 'published', 225, 0)
ON DUPLICATE KEY UPDATE `slug`=`slug`;

-- Subscribers table
CREATE TABLE IF NOT EXISTS `subscribers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) DEFAULT '',
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Settings table
CREATE TABLE IF NOT EXISTS `settings` (
  `name` VARCHAR(100) PRIMARY KEY,
  `value` LONGTEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed default settings
INSERT INTO `settings` (`name`, `value`) VALUES
('header_code', ''),
('body_code', ''),
('footer_code', ''),
('logo_url', ''),
('favicon_url', '')
ON DUPLICATE KEY UPDATE `name`=`name`;

