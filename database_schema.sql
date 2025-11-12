-- Create database
CREATE DATABASE IF NOT EXISTS nigerland_conference;
USE nigerland_conference;

-- Conference registrations table
CREATE TABLE IF NOT EXISTS conference_registrations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reference VARCHAR(50) UNIQUE NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    profession VARCHAR(100) NOT NULL,
    organization VARCHAR(255),
    payment_method ENUM('online-payment', 'bank-transfer') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    payment_reference VARCHAR(255),
    proof_of_payment VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_reference (reference),
    INDEX idx_email (email),
    INDEX idx_status (status)
    ALTER TABLE conference_registrations 
ADD COLUMN paystack_reference VARCHAR(255),
ADD COLUMN paystack_status VARCHAR(50),
ADD COLUMN payment_gateway ENUM('paystack', 'bank_transfer') DEFAULT 'bank_transfer';
);


-- Newsletter subscribers table
CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email)
);

-- Contact form submissions table
CREATE TABLE IF NOT EXISTS contact_submissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'replied') DEFAULT 'new',
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_status (status)
);


-- MoreLife sessions table
CREATE TABLE IF NOT EXISTS morelife_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reference VARCHAR(50) UNIQUE NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    location VARCHAR(255) NOT NULL,
    age INT NOT NULL,
    education_level VARCHAR(100) NOT NULL,
    challenges JSON,
    other_challenge TEXT,
    challenge_cause TEXT,
    challenge_duration VARCHAR(100),
    trigger_incident TEXT,
    on_medication ENUM('yes', 'no'),
    start_month VARCHAR(50),
    session_type VARCHAR(100) NOT NULL,
    session_price DECIMAL(10,2) NOT NULL,
    payment_method ENUM('paystack', 'bank_transfer'),
    paystack_reference VARCHAR(255),
    payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    status ENUM('pending', 'confirmed', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_reference (reference),
    INDEX idx_email (email)
);

-- Training registrations table
CREATE TABLE IF NOT EXISTS training_registrations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reference VARCHAR(50) UNIQUE NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    profession VARCHAR(100) NOT NULL,
    organization VARCHAR(255),
    position VARCHAR(100),
    experience VARCHAR(50),
    expectations TEXT,
    special_requirements TEXT,
    training_title VARCHAR(255) NOT NULL,
    training_id VARCHAR(100) NOT NULL,
    training_price DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_reference (reference),
    INDEX idx_email (email),
    INDEX idx_training_id (training_id)
);

-- Ebook purchases table
CREATE TABLE IF NOT EXISTS ebook_purchases (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reference VARCHAR(50) UNIQUE NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    ebook_title VARCHAR(255) NOT NULL,
    ebook_id VARCHAR(100) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    payment_method ENUM('paystack', 'bank_transfer') DEFAULT 'paystack',
    paystack_reference VARCHAR(255),
    payment_date DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_reference (reference),
    INDEX idx_email (email)
);