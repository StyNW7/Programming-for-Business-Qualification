-- Create database
CREATE DATABASE IF NOT EXISTS task_manager;
USE task_manager;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_username (username),
    INDEX idx_email (email)
);

-- Tasks table
CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    status ENUM('pending', 'in_progress', 'completed') DEFAULT 'pending',
    due_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_priority (priority),
    INDEX idx_due_date (due_date)
);

-- Insert sample data (optional)
-- Sample user (password: 123456)
INSERT INTO users (username, email, password, full_name) VALUES 
('admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator'),
('john_doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Doe');

-- Sample tasks
INSERT INTO tasks (user_id, title, description, priority, status, due_date) VALUES 
(1, 'Complete project documentation', 'Write comprehensive documentation for the task manager project', 'high', 'completed', '2025-06-10'),
(1, 'Review code changes', 'Review and approve pending pull requests', 'medium', 'pending', '2025-06-08'),
(1, 'Update website design', 'Implement new UI/UX improvements', 'low', 'pending', '2025-06-15'),
(2, 'Prepare presentation', 'Create slides for the monthly meeting', 'high', 'pending', '2025-06-09'),
(2, 'Buy groceries', 'Get items for the weekend', 'low', 'completed', '2025-06-06');