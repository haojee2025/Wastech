# Wastech

# ♻️ Wastech – E-Waste Management System

Wastech is a lightweight e-waste collection platform built with **vanilla PHP**, **vanilla JavaScript**, and **MySQL**.  
It enables the public (**Recyclers**) to deposit e-waste items into digital containers while staff (**Collectors**) monitor fill levels and perform pickups.  
Verification of items is handled **automatically by the machine** (capacity and validity checks), not by users.

---

## 📖 Table of Contents
1. [Features](#-features)  
2. [System Requirements](#-system-requirements)  
3. [Architecture Overview](#-architecture-overview)  
4. [Installation & Setup](#-installation--setup)  
5. [Usage Flow](#-usage-flow)  
6. [Deliverables](#-deliverables)  
7. [Testing Guide](#-testing-guide)  
8. [Future Enhancements](#-future-enhancements)  
9. [Credits](#-credits)  

---

## 🚀 Features

### Recycler (Public User)
- Register and log in to the system.
- View list of **machine locations** and select one for deposit.
- Submit a **drop event**:
  - Select item type (phone, laptop, battery, etc.)
  - Enter optional details (estimated weight/volume, notes).
- Machine enforces **capacity checks** (max weight & max volume).
- Items are marked:
  - **Accepted** (machine verified + within limits).
  - **Rejected** (invalid item or container full).
- View **drop history** with statuses (Accepted / Rejected).

### Collector (Staff)
- Log in to access **machine dashboard**.
- View each machine as a **digital recycling bin**:
  - Fill levels (% weight and volume).
  - Status colors: Green = OK, Amber = Near Full, Red = Full.
- Perform **pickup operations**:
  - Record total collected weight & volume.
  - Add optional notes/photo.
  - Reset machine’s totals back to zero.
- View **pickup history** for auditing.

### Shared UI
- **Digital Recycling Bin visualization** (core UI component).
- Responsive, mobile-first design.
- Status colors and fill bars provide instant machine feedback.

---

## 🖥 System Requirements
- **PHP:** 8.0+  
- **MySQL:** 8.0+  
- **Web server:** Apache / Nginx (XAMPP, WAMP, or LAMP recommended).  
- **Browser:** Any modern browser (Chrome, Edge, Firefox).  
- **Environment:** Works fully offline / LAN, no external API dependencies.  

---

## 🏗 Architecture Overview

**Frontend:**  
- HTML5, CSS3, Vanilla JS (ES6 modules).  
- Digital Recycling Bin rendered as a reusable JS component.  

**Backend:**  
- Vanilla PHP with PDO for database connections.  
- Session-based authentication and role guards.  
- CSRF tokens for all forms.  

**Database:**  
- MySQL schema with tables:  
  - `users`, `machines`, `item_types`, `drop_events`, `pickups`, `audit_logs`.  

---

## ⚙️ Installation & Setup

### 1. Clone the repository
```bash
git clone https://github.com/haojee2025/wastech.git
cd wastech

2. Database setup

Create a new MySQL database wastech.

Run the schema:
mysql -u root -p wastech < sql/001_schema.sql
mysql -u root -p wastech < sql/010_seed_item_types.sql

3. Configure database connection

Update /lib/db.php:
$dsn = "mysql:host=127.0.0.1;dbname=wastech;charset=utf8mb4";
$username = "root";
$password = "";

4. Deploy locally

If using XAMPP: move project to htdocs/wastech/

Start Apache + MySQL.

Access in browser:
http://localhost/wastech/public

🔄 Usage Flow
Recycler

Register → Log in.

Select a machine location.

Fill in drop form: item type + optional details.

Submit drop → machine auto-checks capacity + validity.

Drop result appears as Accepted or Rejected.

View all past drops in History.

Collector

Log in with collector account.

Dashboard shows all machines with digital recycling bins.

Select a machine → see fill level, recent drops, last pickup.

Perform pickup → record totals, add notes/photo → machine resets.

Review past pickups in History.

Deliverables

Recycler Deliverables

Machine selection + digital bin view

Drop submission form with machine checks

Drop history page

Collector Deliverables

Machines dashboard with bin status (Green/Amber/Red)

Machine detail view with fill levels

Pickup form + pickup history

Technical Deliverables

MySQL schema and seeds (sql/001_schema.sql, sql/010_seed_item_types.sql)

Vanilla PHP controllers for auth, drops, pickups

Shared JS component for digital bin visualization

Role-based session management + CSRF tokens

Documentation Deliverables

README (this file)

Schema diagrams & ERD (optional)

Test checklist

Deployment instructions

🧪 Testing Guide

Login Tests

Register as recycler → login → logout.

Register as collector → login → logout.

Recycler Tests

Submit drop within machine limits → Accepted.

Submit drop exceeding machine capacity → Rejected.

View history and confirm correct statuses.

Collector Tests

View machine dashboard (statuses reflect DB).

Perform pickup (totals reset correctly).

View pickup history.

Security Tests

Attempt access to collector routes as recycler → blocked.

Check passwords stored hashed in DB.

Ensure CSRF tokens are validated on form submissions.

🚧 Future Enhancements

Analytics dashboard (total kg collected, item type distribution).

Multi-language support.

Drag-and-drop UI for drop submission.

Enhanced bin graphics with animations.

👨‍💻 Credits

Developed as a student project.
Built with PHP, JavaScript, and MySQL.
© 2025 Wastech Project.