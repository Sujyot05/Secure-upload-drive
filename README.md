# Secure Storage — Password-Gated File Upload

A minimal single-file PHP application that gates access behind a shared password and lets authorized users upload files and download previously uploaded ones. Uploaded file metadata (filename and path) is stored in a MySQL database.

## Features

- Single shared password required to view/use the upload page
- File upload via a standard multipart form
- File metadata stored in MySQL (`files` table)
- List of previously uploaded files with download links
- Self-contained: PHP logic, HTML, and CSS all live in one file

## Requirements

- PHP with the `mysqli` extension enabled
- A MySQL/MariaDB server
- A web server capable of running PHP (Apache, Nginx + PHP-FPM, etc.)

## Database Setup

Create a database and a `files` table before running the app:

```sql
CREATE DATABASE dbname;

USE dbname;

CREATE TABLE files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    filepath VARCHAR(255) NOT NULL
);
```

## Configuration

Open the PHP file and set the following values at the top:

```php
$UPLOAD_PASSWORD = "your-password-here";

$conn = mysqli_connect(
    "URL",          // DB host, e.g. "localhost" or "127.0.0.1"
    "DB_host url",  // DB username
    "Password",     // DB password
    "dbname",       // Database name
    3306            // Port
);
```

Replace the placeholder values with your actual database host, username, password, and database name.

## Usage

1. Deploy the PHP file to your web server (e.g. `index.php`).
2. Make sure the web server user has write permission to the directory, since an `uploads/` folder is created automatically on first upload.
3. Visit the page in a browser and enter the configured password.
4. Once authenticated, use the upload form to add files. Uploaded files appear in the table below with a download link.

## File Storage

Uploaded files are saved to an `uploads/` subdirectory next to the script, using their original filename. The database only stores the filename and relative path — the actual file lives on disk.

## ⚠️ Security Notice

This script is intentionally simple and is **not production-hardened**. Before deploying it anywhere reachable by untrusted users, be aware of these issues:

- **SQL injection**: the `INSERT` query builds SQL by directly concatenating `$filename`, which comes from user input. This should use a prepared statement (`mysqli_prepare` / bound parameters) instead of string interpolation.
- **No file type/size validation**: any file type can be uploaded, including executable scripts (e.g. `.php`), which could lead to remote code execution if the `uploads/` folder is web-accessible and the server executes PHP there.
- **No filename sanitization**: `basename()` strips directory components but doesn't prevent overwriting existing files or uploading files with dangerous names/extensions.
- **Password exposed to the client**: the upload password is echoed into a hidden form field (`upload_pass`) in the page's HTML, so it's visible to anyone who views the page source once authenticated.
- **Plaintext password comparison**: the password is stored and compared as plaintext in code rather than hashed.
- **No session management**: "access" is only granted per-request based on POST data, not via a server-side session, so the password must effectively travel with every request.
- **No CSRF protection** on the upload form.

Recommended hardening steps if you plan to use this beyond a personal/trusted environment:
1. Use prepared statements for all database queries.
2. Restrict uploads to an allow-list of file extensions/MIME types, and store files outside the web root (or block execution in the uploads directory via server config).
3. Store the password as a hash (e.g. `password_hash` / `password_verify`) and keep it out of client-rendered HTML.
4. Use PHP sessions to track authentication instead of resending the password on every upload.
5. Add CSRF tokens to forms.
6. Move database credentials to environment variables or a `.env` file excluded from version control, rather than hardcoding them in the script.

