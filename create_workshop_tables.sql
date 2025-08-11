-- Workshop Services Table Creation
-- Run this to create the workshop_services table if it doesn't exist

USE carhubapp;

-- Create workshops table if it doesn't exist
CREATE TABLE IF NOT EXISTS workshops (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    name VARCHAR(255) NOT NULL,
    owner_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    city VARCHAR(100) NOT NULL,
    description TEXT,
    status ENUM('pending', 'active', 'inactive', 'rejected') DEFAULT 'pending',
    is_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create workshop_services table
CREATE TABLE IF NOT EXISTS workshop_services (
    id INT PRIMARY KEY AUTO_INCREMENT,
    workshop_id INT NOT NULL,
    service_name VARCHAR(255) NOT NULL,
    service_category VARCHAR(100) DEFAULT 'General',
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    estimated_time VARCHAR(50) DEFAULT '60',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (workshop_id) REFERENCES workshops(id) ON DELETE CASCADE
);

-- Create workshop_reviews table if it doesn't exist
CREATE TABLE IF NOT EXISTS workshop_reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    workshop_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review_text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (workshop_id) REFERENCES workshops(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create service_bookings table if it doesn't exist
CREATE TABLE IF NOT EXISTS service_bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    workshop_id INT NOT NULL,
    service_id INT NOT NULL,
    user_id INT NOT NULL,
    booking_date DATE NOT NULL,
    booking_time TIME NOT NULL,
    customer_name VARCHAR(255) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    customer_email VARCHAR(255),
    status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
    total_price DECIMAL(10,2) NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (workshop_id) REFERENCES workshops(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES workshop_services(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Add indexes for better performance
CREATE INDEX IF NOT EXISTS idx_workshop_services_workshop_id ON workshop_services(workshop_id);
CREATE INDEX IF NOT EXISTS idx_workshop_services_active ON workshop_services(is_active);
CREATE INDEX IF NOT EXISTS idx_workshops_status ON workshops(status);
CREATE INDEX IF NOT EXISTS idx_workshops_verified ON workshops(is_verified);
CREATE INDEX IF NOT EXISTS idx_service_bookings_workshop_id ON service_bookings(workshop_id);
CREATE INDEX IF NOT EXISTS idx_service_bookings_user_id ON service_bookings(user_id);
CREATE INDEX IF NOT EXISTS idx_workshop_reviews_workshop_id ON workshop_reviews(workshop_id);
