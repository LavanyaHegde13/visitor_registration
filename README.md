# Visitor Registration System

A complete PHP-based Visitor Registration System for securely collecting and managing visitor information with PDF generation and admin dashboard support.

## 📌 Features

- Visitor registration form with:
  - Personal details and address
  - Uploads: PAN Card, Aadhaar (Front & Back), Selfie, and Signature
  - Signature drawing using canvas (mobile-friendly)
- PDF generation with FPDF (including all uploaded images)
- MySQL database integration
- Admin panel with:
  - Login/Logout
  - Dashboard showing all visitor entries
  - Downloadable PDF for each visitor

## 🗂️ Folder Structure

lavanya/
├── index.html # Visitor registration form
├── upload.php # Handles form submission, uploads, and PDF generation
├── admin/
│ ├── login.php # Admin login page
│ ├── dashboard.php # Admin dashboard with user data
│ ├── logout.php # Admin logout script
├── db/
│ └── config.php # Database connection file
├── pdfs/ # Generated PDF files (saved here)
├── imgs/ # Uploaded images from form
├── assets/ # CSS, JS, and frontend assets

markdown
Copy
Edit

## ⚙️ Technologies Used

- **Frontend**: HTML, CSS, JavaScript (Canvas)
- **Backend**: PHP
- **Database**: MySQL
- **PDF Generation**: [FPDF](http://www.fpdf.org/)

## 🚀 Setup Instructions

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/visitor_registration.git
   cd visitor_registration
Import the database

Create a MySQL database (e.g., visitor_db)

Import the provided database.sql file (if available)

Update db/config.php with your DB credentials

Set folder permissions

bash
Copy
Edit
chmod -R 755 pdfs imgs
Access the app

Visit http://localhost/lavanya/index.html for the registration form

Admin login at http://localhost/lavanya/admin/login.php

🔐 Default Admin Credentials
Change these before deploying to production.

Username: admin

Password: admin123

📦 Dependencies
PHP 7+

MySQL/MariaDB

FPDF Library

jQuery (optional, if used)

🛠️ To Do / Future Improvements
Add email notification after form submission

Add search/filter to the admin dashboard

Generate visitor QR codes

Enhance form UI/UX
