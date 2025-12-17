================================================================================
  DYNAMIC STUDENT DEPARTMENT ALLOCATION SYSTEM (D-SDAS) - STANDALONE CORE
================================================================================

# D-SDAS
Dynamic Student Department Allocation System (D-SDAS): A secure, standalone portal using OOP PHP/PostgreSQL. It handles high-pressure university department allocation with a Tri-Factor Prediction Algorithm and a 30-minute Cooldown to ensure fairness and system stability.

PROJECT OVERVIEW
----------------
D-SDAS is a Mission-Critical Allocation Portal focused solely on dynamically 
assigning students to departments based on rank and real-time demand. 

This version strips out all general ERP features (Finance, Academics) to perfect 
the core Allocation Engine, the Tri-Factor Prediction Algorithm, and the 
30-Minute Cooldown mechanism.

The frontend uses a Microsoft Fluent Design style for a professional, tile-based UI.

CORE ENGINE LOGIC
-----------------
1. Tri-Factor Prediction: Ranks, Historical Cutoffs, and Live Seat Demand determine 
   the 'Traffic Light' status (Green, Yellow, Red) for each department choice.
2. Cooldown: A student's choice is locked for 30 minutes after submission to 
   prevent excessive server load and rash decision-making.
3. Allocation Finalization: Controlled exclusively by the Admin, marking choices 
   as permanent only after the round officially closes.

TECHNOLOGY STACK
----------------
- Backend:    OOP PHP 7.4+ (Classes: Auth, Database, Engine, Admin)
- Database:   MySQL/MariaDB (Connection on 'localhost:3306' / 'dsdas_erp' DB)
- Frontend:   HTML, CSS (Microsoft Style), JavaScript (AJAX for live prediction)

DIRECTORY STRUCTURE
----------------
/dsdas_project/
    |-- requirements            (System Requirements)
    |-- dsdas_core.sql          (Database Schema & Tables)
    |-- bootstrap.php           (Central Autoloader & Session Start)
    |-- README.txt              (This document)
    |
    |-- src/                    (SECURE BACKEND LOGIC)
    |   |-- Config.php          (DB Credentials: host=localhost, db=postgres, user=postgres, pass=123)
    |   |-- Database.php        (Singleton Connection Class)
    |   |-- Auth.php            (Login, Logout, Role Checks)
    |   |-- Engine.php          (Prediction Algorithm & Cooldown Logic)
    |   |-- Admin.php           (Round Lifecycle Management)
    |
    |-- public/                 (WEB SERVER DOCUMENT ROOT)
        |-- index.php           (Login Page)
        |-- logout.php
        |-- dashboard.php       (Student UI - Prediction and Lock)
        |-- admin.php           (Admin UI - Round Control)
        |-- api/                (AJAX Endpoints)
        |   |-- predict.php     (Calls Engine for Traffic Light)
        |   |-- lock.php        (Submits student choice)
        |-- assets/
            |-- css/style.css   (MS Fluent Design Styles)
            |-- js/app.js       (Frontend AJAX and Timer Logic)


INITIAL SETUP GUIDE
-------------------

1. DATABASE: 
   - Ensure MySQL is running on port 3306.
   - Run the `dsdas_core.sql` script on the `dsdas_erp` database.

2. CREDENTIALS:
   - The database credentials are hardcoded in `src/Config.php`.
   - The test password for all initial users is **`123456`**.

3. USERS:
   - Insert the following records into the `users` table:
     - **admin**: Role 'admin', Password '123456' (hashed)
     - **jsmith**: Role 'student', Admission Rank #450, Password '123456' (hashed)

4. STARTUP SEQUENCE:
   - **Access:** Point your web server to the `/public/` directory.
   - **Login:** Log in as **admin** (user: `admin`, pass: `123456`).
   - **Admin Panel:** Navigate to `public/admin.php`.
   - **Critical Step:** Schedule a Round, set its status to **ACTIVE**, and input the **Historical Cutoff Rank** for at least one department (e.g., Computer Science).
   - **Test:** Log in as **jsmith** to `public/dashboard.php` and test the prediction engine.

CONTACT
-------
Project Lead: D-SDAS Development Team