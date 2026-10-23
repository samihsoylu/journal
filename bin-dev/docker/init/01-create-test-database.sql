-- Create test database for PHPUnit integration tests
CREATE DATABASE IF NOT EXISTS journal_test;

-- Grant journal user full access to test database
GRANT ALL PRIVILEGES ON journal_test.* TO 'journal'@'%';
FLUSH PRIVILEGES;
