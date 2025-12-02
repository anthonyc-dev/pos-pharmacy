-- SQL to add expiration date column to products table
-- Run this in your phpMyAdmin or MySQL client

ALTER TABLE products
ADD COLUMN expiration DATE DEFAULT NULL
AFTER stock;
