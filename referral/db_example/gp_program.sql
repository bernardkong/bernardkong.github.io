CREATE TABLE gp_programs (
    program_id INT PRIMARY KEY AUTO_INCREMENT,
    program_title VARCHAR(255) NOT NULL,
    program_description TEXT,
    program_link VARCHAR(255),
    program_image VARCHAR(255),
    is_published BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);