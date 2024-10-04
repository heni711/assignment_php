CREATE DATABASE ecommerce;

USE ecommerce;

CREATE TABLE shoes (
    ShoeID INT AUTO_INCREMENT PRIMARY KEY,
    ShoeName VARCHAR(255) NOT NULL,
    ShoeDescription TEXT NOT NULL,
    QuantityAvailable INT NOT NULL,
    Price DECIMAL(10, 2) NOT NULL,
    ProductAddedBy VARCHAR(100) NOT NULL DEFAULT 'Heni Patel'
);

 //  ALTER TABLE shoes ADD COLUMN Size VARCHAR(50);
  //ALTER TABLE shoes ADD COLUMN Color VARCHAR(50);
