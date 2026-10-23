CREATE TABLE IF NOT EXISTS khachhang (
    id INT NOT NULL AUTO_INCREMENT,
    ho_ten VARCHAR(100) NOT NULL,
    sdt VARCHAR(20) DEFAULT NULL,
    email VARCHAR(100) DEFAULT NULL,
    mat_khau VARCHAR(255) NOT NULL,
    reset_token CHAR(64) DEFAULT NULL,
    reset_expires_at DATETIME DEFAULT NULL,
    ngay_tao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY khachhang_email_unique (email),
    UNIQUE KEY khachhang_sdt_unique (sdt),
    UNIQUE KEY khachhang_reset_token_unique (reset_token)
);

ALTER TABLE donhang ADD COLUMN customer_id INT NULL AFTER id;
ALTER TABLE donhang ADD INDEX donhang_customer_id_idx (customer_id);
ALTER TABLE donhang ADD CONSTRAINT donhang_customer_fk
    FOREIGN KEY (customer_id) REFERENCES khachhang(id) ON DELETE SET NULL;
