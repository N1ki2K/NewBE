-- School CMS Database Schema
-- Compatible with MySQL/MariaDB

-- Create database (if not exists)
CREATE DATABASE IF NOT EXISTS nukgszco_3ou_Cms DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nukgszco_3ou_Cms;

-- Users table
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  name VARCHAR(100),
  role ENUM('admin', 'user') DEFAULT 'user',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_username (username),
  INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- News table
CREATE TABLE IF NOT EXISTS news (
  id VARCHAR(50) PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  excerpt TEXT NOT NULL,
  content TEXT NOT NULL,
  featured_image_url VARCHAR(500),
  published_date DATETIME DEFAULT CURRENT_TIMESTAMP,
  is_published BOOLEAN DEFAULT FALSE,
  is_featured BOOLEAN DEFAULT FALSE,
  language VARCHAR(10) DEFAULT 'bg',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_published (is_published, published_date),
  INDEX idx_featured (is_featured, published_date),
  INDEX idx_language (language)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- News attachments table
CREATE TABLE IF NOT EXISTS news_attachments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  news_id VARCHAR(50) NOT NULL,
  filename VARCHAR(255) NOT NULL,
  original_name VARCHAR(255) NOT NULL,
  url VARCHAR(500) NOT NULL,
  file_type VARCHAR(100),
  file_size INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (news_id) REFERENCES news(id) ON DELETE CASCADE,
  INDEX idx_news_id (news_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Events table
CREATE TABLE IF NOT EXISTS events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  description TEXT NOT NULL,
  date DATE NOT NULL,
  startTime TIME NOT NULL,
  endTime TIME,
  type ENUM('academic', 'extracurricular', 'meeting', 'holiday', 'other') DEFAULT 'other',
  location VARCHAR(255),
  locale VARCHAR(10) DEFAULT 'en',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_date (date),
  INDEX idx_type (type),
  INDEX idx_locale (locale)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Content sections table
CREATE TABLE IF NOT EXISTS content (
  id VARCHAR(100) PRIMARY KEY,
  page_id VARCHAR(50) NOT NULL,
  section_key VARCHAR(50) NOT NULL,
  content TEXT NOT NULL,
  content_type VARCHAR(20) DEFAULT 'text',
  language VARCHAR(10) DEFAULT 'bg',
  position INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_page_id (page_id),
  INDEX idx_language (language)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Images table
CREATE TABLE IF NOT EXISTS images (
  id VARCHAR(100) PRIMARY KEY,
  filename VARCHAR(255) NOT NULL,
  original_name VARCHAR(255),
  url VARCHAR(500) NOT NULL,
  alt_text VARCHAR(255),
  page_id VARCHAR(50),
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_page_id (page_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Uploaded files table (generic file uploads)
CREATE TABLE IF NOT EXISTS uploaded_files (
  id INT AUTO_INCREMENT PRIMARY KEY,
  filename VARCHAR(255) NOT NULL,
  original_name VARCHAR(255) NOT NULL,
  url VARCHAR(500) NOT NULL,
  alt_text VARCHAR(255),
  file_type VARCHAR(100),
  size INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Staff table (council members, directors, etc.)
CREATE TABLE IF NOT EXISTS staff (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  position VARCHAR(100) NOT NULL,
  bio TEXT,
  email VARCHAR(100),
  phone VARCHAR(50),
  image_url VARCHAR(500),
  is_director BOOLEAN DEFAULT FALSE,
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_sort_order (sort_order),
  INDEX idx_is_director (is_director)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- School staff table (teachers, administrators)
CREATE TABLE IF NOT EXISTS school_staff (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  position VARCHAR(100) NOT NULL,
  department VARCHAR(100),
  email VARCHAR(100),
  phone VARCHAR(50),
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_sort_order (sort_order),
  INDEX idx_department (department)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Pages table (optional - for dynamic page management)
CREATE TABLE IF NOT EXISTS pages (
  id VARCHAR(50) PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  slug VARCHAR(255) UNIQUE NOT NULL,
  description TEXT,
  is_active BOOLEAN DEFAULT TRUE,
  position INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_slug (slug),
  INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create default admin user (password: admin123)
-- Note: Change this password immediately in production!
INSERT INTO users (username, email, password, name, role)
VALUES (
  'admin',
  'admin@school.com',
  '$2a$10$xK5xJxK5xJxK5xJxK5xJxOeXYZ1234567890abcdefghijklmnopqr', -- hashed 'admin123'
  'Administrator',
  'admin'
) ON DUPLICATE KEY UPDATE username=username;

-- Sample data for testing (optional)

-- Sample news
INSERT INTO news (id, title, excerpt, content, published_date, is_published, is_featured, language)
VALUES
  ('news-1-bg', 'Добре дошли в нашето училище', 'Кратко описание на новината', 'Пълно съдържание на новината...', NOW(), TRUE, TRUE, 'bg'),
  ('news-1-en', 'Welcome to our school', 'Short description of the news', 'Full content of the news...', NOW(), TRUE, TRUE, 'en')
ON DUPLICATE KEY UPDATE id=id;

-- Sample events
INSERT INTO events (title, description, date, startTime, endTime, type, locale)
VALUES
  ('School Opening Ceremony', 'Welcome ceremony for new students', '2025-09-01', '09:00:00', '11:00:00', 'academic', 'en'),
  ('Церемония по откриване', 'Церемония за посрещане на новите ученици', '2025-09-01', '09:00:00', '11:00:00', 'academic', 'bg')
ON DUPLICATE KEY UPDATE id=id;

-- Sample content
INSERT INTO content (id, page_id, section_key, content, content_type, language, position)
VALUES
  ('home-intro-bg', 'home', 'intro', 'Добре дошли в нашето училище', 'text', 'bg', 0),
  ('home-intro-en', 'home', 'intro', 'Welcome to our school', 'text', 'en', 0)
ON DUPLICATE KEY UPDATE id=id;

-- Sample staff
INSERT INTO staff (name, position, bio, email, is_director, sort_order)
VALUES
  ('Иван Иванов', 'Директор', 'Биография на директора...', 'director@school.com', TRUE, 0),
  ('Мария Петрова', 'Заместник директор', 'Биография на заместник директора...', 'deputy@school.com', FALSE, 1)
ON DUPLICATE KEY UPDATE id=id;

-- Sample pages
INSERT INTO pages (id, title, slug, description, is_active, position)
VALUES
  ('home', 'Home', 'home', 'Home page', TRUE, 0),
  ('about', 'About Us', 'about', 'About the school', TRUE, 1),
  ('news', 'News', 'news', 'School news', TRUE, 2),
  ('events', 'Events', 'events', 'School events', TRUE, 3),
  ('contact', 'Contact', 'contact', 'Contact information', TRUE, 4)
ON DUPLICATE KEY UPDATE id=id;
