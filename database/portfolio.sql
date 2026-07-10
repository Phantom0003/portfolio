-- ============================================================
-- Portfolio CMS Database Schema
-- Galaxy Career Portfolio
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS `if0_42179562_portfolio_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `if0_42179562_portfolio_db`;

-- ============================================================
-- TABLE: admins
-- ============================================================
CREATE TABLE `admins` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin','super_admin') NOT NULL DEFAULT 'admin',
  `avatar` VARCHAR(255) DEFAULT NULL,
  `last_login` DATETIME DEFAULT NULL,
  `login_attempts` TINYINT UNSIGNED DEFAULT 0,
  `locked_until` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: users (visitors)
-- ============================================================
CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `avatar` VARCHAR(255) DEFAULT NULL,
  `bio` TEXT DEFAULT NULL,
  `website` VARCHAR(255) DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `email_verified` TINYINT(1) DEFAULT 0,
  `last_login` DATETIME DEFAULT NULL,
  `login_attempts` TINYINT UNSIGNED DEFAULT 0,
  `locked_until` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: settings
-- ============================================================
CREATE TABLE `settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `key_name` VARCHAR(100) NOT NULL UNIQUE,
  `value` TEXT DEFAULT NULL,
  `type` ENUM('text','textarea','color','boolean','json','image') DEFAULT 'text',
  `group_name` VARCHAR(50) DEFAULT 'general',
  `label` VARCHAR(150) DEFAULT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: projects
-- ============================================================
CREATE TABLE `projects` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `description` TEXT NOT NULL,
  `long_description` LONGTEXT DEFAULT NULL,
  `thumbnail` VARCHAR(255) DEFAULT NULL,
  `images` JSON DEFAULT NULL,
  `technologies` JSON DEFAULT NULL,
  `category` VARCHAR(100) DEFAULT NULL,
  `demo_url` VARCHAR(255) DEFAULT NULL,
  `github_url` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('active','archived','in_progress') DEFAULT 'active',
  `is_pinned` TINYINT(1) DEFAULT 0,
  `sort_order` INT UNSIGNED DEFAULT 0,
  `views` INT UNSIGNED DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_pinned` (`is_pinned`),
  INDEX `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: skills
-- ============================================================
CREATE TABLE `skills` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `category` ENUM('frontend','backend','database','tools','languages','devops','design','other') DEFAULT 'other',
  `level` TINYINT UNSIGNED DEFAULT 80 COMMENT '0-100 percentage',
  `icon` VARCHAR(255) DEFAULT NULL,
  `color` VARCHAR(20) DEFAULT '#9333EA',
  `sort_order` INT UNSIGNED DEFAULT 0,
  `is_featured` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: experience
-- ============================================================
CREATE TABLE `experience` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `organization` VARCHAR(255) NOT NULL,
  `type` ENUM('work','education','achievement','milestone') DEFAULT 'work',
  `description` TEXT DEFAULT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE DEFAULT NULL,
  `is_current` TINYINT(1) DEFAULT 0,
  `location` VARCHAR(255) DEFAULT NULL,
  `url` VARCHAR(255) DEFAULT NULL,
  `icon` VARCHAR(100) DEFAULT 'bi-briefcase',
  `color` VARCHAR(20) DEFAULT '#9333EA',
  `sort_order` INT UNSIGNED DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: achievements
-- ============================================================
CREATE TABLE `achievements` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `issuer` VARCHAR(255) DEFAULT NULL,
  `type` ENUM('certificate','award','badge','event','publication','other') DEFAULT 'certificate',
  `description` TEXT DEFAULT NULL,
  `file_path` VARCHAR(255) DEFAULT NULL,
  `thumbnail` VARCHAR(255) DEFAULT NULL,
  `date_awarded` DATE DEFAULT NULL,
  `expiry_date` DATE DEFAULT NULL,
  `credential_id` VARCHAR(255) DEFAULT NULL,
  `credential_url` VARCHAR(255) DEFAULT NULL,
  `is_featured` TINYINT(1) DEFAULT 0,
  `sort_order` INT UNSIGNED DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: blog_posts
-- ============================================================
CREATE TABLE `blog_posts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `admin_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(500) NOT NULL,
  `slug` VARCHAR(500) NOT NULL UNIQUE,
  `excerpt` TEXT DEFAULT NULL,
  `content` LONGTEXT NOT NULL,
  `cover_image` VARCHAR(255) DEFAULT NULL,
  `tags` JSON DEFAULT NULL,
  `category` VARCHAR(100) DEFAULT NULL,
  `status` ENUM('draft','published','archived') DEFAULT 'draft',
  `is_featured` TINYINT(1) DEFAULT 0,
  `views` INT UNSIGNED DEFAULT 0,
  `likes` INT UNSIGNED DEFAULT 0,
  `reading_time` TINYINT UNSIGNED DEFAULT 5,
  `published_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_admin` (`admin_id`),
  FOREIGN KEY (`admin_id`) REFERENCES `admins`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: comments
-- ============================================================
CREATE TABLE `comments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `post_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `guest_name` VARCHAR(150) DEFAULT NULL,
  `guest_email` VARCHAR(255) DEFAULT NULL,
  `content` TEXT NOT NULL,
  `status` ENUM('pending','approved','spam') DEFAULT 'pending',
  `parent_id` INT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_post` (`post_id`),
  FOREIGN KEY (`post_id`) REFERENCES `blog_posts`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: messages
-- ============================================================
CREATE TABLE `messages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `subject` VARCHAR(255) DEFAULT NULL,
  `message` TEXT NOT NULL,
  `is_read` TINYINT(1) DEFAULT 0,
  `replied_at` DATETIME DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: notifications
-- ============================================================
CREATE TABLE `notifications` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `recipient_type` ENUM('admin','user') NOT NULL,
  `recipient_id` INT UNSIGNED NOT NULL,
  `type` VARCHAR(100) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT DEFAULT NULL,
  `action_url` VARCHAR(255) DEFAULT NULL,
  `icon` VARCHAR(100) DEFAULT 'bi-bell',
  `color` VARCHAR(20) DEFAULT '#9333EA',
  `is_read` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_recipient` (`recipient_type`, `recipient_id`, `is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: media
-- ============================================================
CREATE TABLE `media` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `admin_id` INT UNSIGNED DEFAULT NULL,
  `filename` VARCHAR(255) NOT NULL,
  `original_name` VARCHAR(255) NOT NULL,
  `file_type` VARCHAR(100) DEFAULT NULL,
  `file_size` INT UNSIGNED DEFAULT 0,
  `folder` VARCHAR(100) DEFAULT 'general',
  `path` VARCHAR(500) NOT NULL,
  `url` VARCHAR(500) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_folder` (`folder`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: saved_projects
-- ============================================================
CREATE TABLE `saved_projects` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `project_id` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_save` (`user_id`, `project_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: post_likes
-- ============================================================
CREATE TABLE `post_likes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `post_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_like` (`post_id`, `user_id`),
  FOREIGN KEY (`post_id`) REFERENCES `blog_posts`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- SEED DATA: Default Admin (super_admin)
-- Password: Admin@123
-- ============================================================
INSERT INTO `admins` (`name`, `email`, `password`, `role`) VALUES
('Portfolio Admin', 'admin@portfolio.local', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin');

-- ============================================================
-- SEED DATA: Default Settings
-- ============================================================
INSERT INTO `settings` (`key_name`, `value`, `type`, `group_name`, `label`) VALUES
('site_name', 'My Galaxy Portfolio', 'text', 'general', 'Site Name'),
('site_tagline', 'Full Stack Developer & Digital Creator', 'text', 'general', 'Site Tagline'),
('owner_name', 'Your Name', 'text', 'profile', 'Your Name'),
('owner_title', 'Full Stack Developer', 'text', 'profile', 'Career Title'),
('owner_bio', 'Passionate developer crafting digital experiences from the cosmos. I turn ideas into reality through elegant code and stunning design.', 'textarea', 'profile', 'Short Bio'),
('owner_long_bio', 'Welcome to my digital universe! I am a passionate full-stack developer with a love for creating beautiful, functional applications. My journey in tech started with curiosity and has evolved into a mission to build solutions that make a difference.\n\nI specialize in modern web technologies, cloud architectures, and user experience design. When not coding, I am exploring new technologies, contributing to open source, and dreaming up the next big project.', 'textarea', 'profile', 'Long Bio'),
('owner_email', 'contact@yourportfolio.com', 'text', 'profile', 'Contact Email'),
('owner_phone', '+1 234 567 8900', 'text', 'profile', 'Phone Number'),
('owner_location', 'Earth, Milky Way Galaxy', 'text', 'profile', 'Location'),
('owner_avatar', '', 'image', 'profile', 'Profile Photo'),
('social_github', 'https://github.com/yourusername', 'text', 'social', 'GitHub URL'),
('social_linkedin', 'https://linkedin.com/in/yourusername', 'text', 'social', 'LinkedIn URL'),
('social_twitter', 'https://twitter.com/yourusername', 'text', 'social', 'Twitter URL'),
('social_instagram', 'https://instagram.com/yourusername', 'text', 'social', 'Instagram URL'),
('social_youtube', '', 'text', 'social', 'YouTube URL'),
('resume_url', '', 'text', 'profile', 'Resume PDF URL'),
('theme_primary', '#9333EA', 'color', 'theme', 'Primary Color'),
('theme_secondary', '#4F46E5', 'color', 'theme', 'Secondary Color'),
('theme_accent', '#D946EF', 'color', 'theme', 'Accent Color'),
('theme_bg_from', '#0a0015', 'color', 'theme', 'Background Start'),
('theme_bg_to', '#130025', 'color', 'theme', 'Background End'),
('theme_font', 'Space Grotesk', 'text', 'theme', 'Primary Font'),
('enable_animations', '1', 'boolean', 'theme', 'Enable Animations'),
('enable_particles', '1', 'boolean', 'theme', 'Enable Star Particles'),
('enable_parallax', '1', 'boolean', 'theme', 'Enable Parallax'),
('card_blur', '20', 'text', 'theme', 'Glass Card Blur (px)'),
('card_radius', '20', 'text', 'theme', 'Card Border Radius (px)'),
('years_experience', '5', 'text', 'stats', 'Years of Experience'),
('projects_count', '0', 'text', 'stats', 'Projects Completed'),
('clients_count', '20+', 'text', 'stats', 'Happy Clients'),
('hero_cta_text', 'Explore My Universe', 'text', 'hero', 'Hero CTA Button Text'),
('hero_cta_url', '#projects', 'text', 'hero', 'Hero CTA Button URL'),
('maintenance_mode', '0', 'boolean', 'general', 'Maintenance Mode');

-- ============================================================
-- SEED DATA: Sample Skills
-- ============================================================
INSERT INTO `skills` (`name`, `category`, `level`, `icon`, `color`, `sort_order`, `is_featured`) VALUES
('HTML5', 'frontend', 95, 'bi-filetype-html', '#E34F26', 1, 1),
('CSS3 / Sass', 'frontend', 90, 'bi-filetype-css', '#1572B6', 2, 1),
('JavaScript', 'frontend', 88, 'bi-filetype-js', '#F7DF1E', 3, 1),
('React.js', 'frontend', 85, 'bi-gear', '#61DAFB', 4, 1),
('Vue.js', 'frontend', 78, 'bi-gear-fill', '#4FC08D', 5, 0),
('PHP', 'backend', 90, 'bi-filetype-php', '#777BB4', 6, 1),
('Node.js', 'backend', 80, 'bi-server', '#339933', 7, 1),
('Python', 'backend', 75, 'bi-filetype-py', '#3776AB', 8, 0),
('MySQL', 'database', 88, 'bi-database', '#4479A1', 9, 1),
('MongoDB', 'database', 75, 'bi-database-fill', '#47A248', 10, 0),
('Docker', 'devops', 70, 'bi-box', '#2496ED', 11, 0),
('Git', 'tools', 92, 'bi-git', '#F05032', 12, 1),
('Figma', 'design', 80, 'bi-pen', '#F24E1E', 13, 0),
('Bootstrap', 'frontend', 95, 'bi-bootstrap', '#7952B3', 14, 0);

-- ============================================================
-- SEED DATA: Sample Experience
-- ============================================================
INSERT INTO `experience` (`title`, `organization`, `type`, `description`, `start_date`, `end_date`, `is_current`, `location`, `icon`, `color`, `sort_order`) VALUES
('Bachelor of Computer Science', 'Universe University', 'education', 'Graduated with honors. Specialized in Software Engineering and Web Technologies. GPA: 3.9/4.0', '2018-09-01', '2022-06-30', 0, 'Earth City', 'bi-mortarboard', '#9333EA', 1),
('Junior Web Developer', 'Startup Galaxy Inc.', 'work', 'Built responsive web applications using React and Node.js. Contributed to 10+ projects serving 50K+ users.', '2022-07-01', '2023-06-30', 0, 'Remote', 'bi-code-slash', '#4F46E5', 2),
('Full Stack Developer', 'NebulaCode Solutions', 'work', 'Lead developer for enterprise SaaS platform. Architected microservices, improved performance by 60%.', '2023-07-01', NULL, 1, 'Remote / Hybrid', 'bi-laptop', '#D946EF', 3),
('Open Source Contributor', 'GitHub Universe', 'achievement', 'Active contributor to multiple open-source projects. Merged 50+ pull requests across 15 repositories.', '2021-01-01', NULL, 1, 'Worldwide', 'bi-github', '#818CF8', 4);

-- ============================================================
-- SEED DATA: Sample Projects
-- ============================================================
INSERT INTO `projects` (`title`, `slug`, `description`, `long_description`, `technologies`, `category`, `demo_url`, `github_url`, `status`, `is_pinned`, `sort_order`) VALUES
('Galaxy Portfolio CMS', 'galaxy-portfolio-cms', 'A futuristic full-stack portfolio CMS with glassmorphism UI, dual authentication, and complete content management.', 'This is the very portfolio you are looking at! Built with PHP, MySQL, and pure CSS glassmorphism effects. Features a complete CMS with admin and visitor authentication.', '["PHP", "MySQL", "JavaScript", "Bootstrap 5", "CSS3"]', 'Web App', '#', 'https://github.com/yourusername/galaxy-portfolio', 'active', 1, 1),
('Space Weather Dashboard', 'space-weather-dashboard', 'Real-time space weather monitoring app with beautiful data visualizations and NASA API integration.', 'An interactive dashboard that displays real-time solar wind data, geomagnetic storm alerts, and aurora forecasts using NASA and NOAA APIs.', '["React.js", "D3.js", "Node.js", "REST API"]', 'Dashboard', 'https://demo.example.com', 'https://github.com', 'active', 1, 2),
('NeuralChat AI', 'neuralchat-ai', 'AI-powered chat application with natural language processing, conversation history, and multi-model support.', 'A sophisticated chat interface powered by multiple AI models. Supports conversation threading, file uploads, and intelligent context management.', '["Python", "FastAPI", "React", "WebSocket", "OpenAI API"]', 'AI/ML', 'https://demo.example.com', 'https://github.com', 'active', 0, 3);

-- ============================================================
-- SEED DATA: Sample Blog Post
-- ============================================================
INSERT INTO `blog_posts` (`admin_id`, `title`, `slug`, `excerpt`, `content`, `tags`, `category`, `status`, `is_featured`, `reading_time`, `published_at`) VALUES
(1, 'Welcome to My Digital Universe', 'welcome-to-my-digital-universe', 'This is the beginning of my journey sharing knowledge, projects, and experiences from my corner of the digital cosmos.', '<h2>Hello, Universe! 🚀</h2><p>Welcome to my personal space on the internet. This blog is where I share my thoughts on technology, showcase my projects, and document my continuous learning journey.</p><p>As a full-stack developer, I work across the entire technology stack — from crafting pixel-perfect UIs to architecting robust backend systems. This portfolio is built to grow with my career.</p><h3>What You Will Find Here</h3><ul><li>Technical deep-dives and tutorials</li><li>Project showcases with behind-the-scenes details</li><li>Career reflections and lessons learned</li><li>Resources and tools I swear by</li></ul><p>The universe is vast, and so is the world of technology. Let us explore it together!</p>', '["welcome","career","web development"]', 'Career', 'published', 1, 3, NOW());

COMMIT;
