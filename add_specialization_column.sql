-- Add specialization column to workshops table
USE carhubapp;

-- Add specialization column if it doesn't exist
ALTER TABLE workshops 
ADD COLUMN IF NOT EXISTS specialization TEXT AFTER description;

-- Verify the table structure
DESCRIBE workshops;
