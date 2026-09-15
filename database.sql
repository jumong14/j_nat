CREATE DATABASE IF NOT EXISTS library_db
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE library_db;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    phone VARCHAR(30),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    barcode VARCHAR(100) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(150),
    category VARCHAR(100),
    quantity INT NOT NULL DEFAULT 1,
    available INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE borrowings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    book_id INT NOT NULL,
    borrow_date DATE NOT NULL,
    due_date DATE NOT NULL,
    return_date DATE NULL,
    status ENUM('borrowed','returned') NOT NULL DEFAULT 'borrowed',
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
);

INSERT INTO users (username, password)
VALUES ('admin', SHA2('admin123', 256));

INSERT INTO members (member_code, name, phone) VALUES
('M001', 'สมชาย ใจดี', '0812345678'),
('M002', 'สมหญิง รักเรียน', '0898765432');

INSERT INTO books (barcode, title, author, category, quantity, available) VALUES
('9786160000011', 'พื้นฐานการเขียนโปรแกรม', 'ผู้เขียนตัวอย่าง', 'คอมพิวเตอร์', 5, 5),
('9786160000028', 'การพัฒนาเว็บไซต์', 'ผู้เขียนตัวอย่าง', 'คอมพิวเตอร์', 3, 3);
