# Omishtu HR & Payroll Management System

## 🚀 Tech Stack & Architecture

* **Backend:** Laravel 11 (PHP 8.2+)
* **Frontend SPA:** Vue 3 with Inertia.js
* **Styling:** Tailwind CSS with Headless UI
* **State Management:** Pinia
* **Data Visualization:** Chart.js
* **Key Integrations:** 
  * `spatie/laravel-permission` (Role-Based Access Control)
  * `barryvdh/laravel-dompdf` (PDF Generation)
  * `cloudinary-labs/cloudinary-laravel` (Cloud Storage for Media/Documents)

---

## ✨ Core Features & Modules

### 1. Comprehensive Employee Management
* Full CRUD operations for managing Employees, Departments, and Positions.
* Secure storage and management of Employee Documents via Cloudinary integration.
* Company structuring and organizational hierarchy mapping.

### 2. Time & Attendance Tracking
* Robust attendance logging system allowing employees to clock in and out.
* Tracking of daily attendance records to ensure accurate payroll calculations.

### 3. Leave & Absence Management
* Dynamic creation of different `Leave Types` (Sick, Vacation, Maternity, etc.).
* A complete workflow for submitting, reviewing, and approving leave requests.

### 4. Advanced Payroll Processing
* Configurable salary structures mapped to different positions or employees.
* Automated calculation of bonuses and deductions.
* Generation of detailed payroll runs and individual payroll items.
* Automated PDF Payslip generation capabilities using Laravel DOMPDF.

### 5. Performance Management (KPIs)
* Definition and tracking of Key Performance Indicators (KPIs).
* System for logging KPI records to evaluate employee performance over time.

### 6. Employee Gamification & Engagement
* **Points System:** Rewarding employees with points based on performance or attendance.
* **Badges:** Creating custom badges and awarding them to employees for milestones.
* **Leaderboard:** A dynamic leaderboard to foster healthy competition and boost morale.
* **Event Management:** Built-in event creation for company-wide announcements or gatherings.

### 7. Secure Role-Based Access Control (RBAC)
* Granular permissions system ensuring that HR Managers, Admins, and standard Employees only see the modules and data they are authorized to access.
* Dedicated Role & Permission management dashboard.

### 8. Interactive Dashboards
* Real-time analytics and data visualization using Chart.js to monitor attendance, payroll costs, and performance metrics.
