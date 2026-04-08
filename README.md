 Laboratory Resources Request System

 Project Overview
This is a multi-role web-based system developed as a 3-member internship project for the Faculty of Science, University of Kelaniya. The system is designed to streamline laboratory resource management, including equipment reservations, approval workflows, logbook submissions, and automated notifications.


 Key Features

- 🔐 Multi-role Authentication System (Admin, Lecturer, Student)
- 📦 Equipment Reservation System
- ✅ Approval Workflow (Request → Review → Approve/Reject)
- 📘 Logbook Submission and Tracking
- 📧 Email Notification System
- 📊 Dashboard for Monitoring Requests and Usage
- 🗂️ Organized and user-friendly interface

---

 Tech Stack

- Frontend: HTML, CSS, Bootstrap 5, JavaScript
- Backend: PHP
- Database: MySQL
- Cloud Database: AWS RDS
- Version Control: Git & GitHub

---

Team Information

This project was developed by a 3-member team as part of an internship program.

---

 How to Run the Project

1. Clone the repository:
   git clone https://github.com/Thisaru2001/Laboratory_Resources_Request_System

2. Move project folder to your local server (e.g., XAMPP htdocs)

3. Import the database:
   - Open phpMyAdmin
   - Create a new database
   - Import the provided .sql file

4. Configure database connection:
   - Open config file
   - Update database name, username, and password

5. Start Apache and MySQL using XAMPP

6. Run the project in browser:
   http://localhost/Laboratory_Resources_Request_System

---

 🔑 User Roles

- Admin:
  - Manage users and system settings
  - Approve or reject requests

- Lecturer:
  - Review and approve student requests
  - Monitor lab usage

- Student:
  - Request laboratory equipment
  - Submit logbooks
  - Track request status

---

 Email Notification

The system sends automated email notifications for:
- Request submission
- Approval or rejection updates
- Important system alerts

---

 Project Structure

- /assets → Images, styles, static files
- /views → UI components and pages
- /config → Database configuration
- /controllers → Backend logic
- /models → Database operations

---

 Requirements

- PHP 7+
- MySQL
- XAMPP or any local server
- Internet connection (for AWS RDS if used)

---

 Future Improvements

- Add real-time notifications
- Improve UI/UX design
- Mobile responsiveness enhancement
- Role-based analytics dashboard

---






