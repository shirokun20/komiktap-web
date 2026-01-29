# Tech Stack Recommendation for License Management System

Based on the requirements in `BRD.md` (Simple, Lightweight, Admin Dashboard, API), here is the recommended stack.

## 🏆 Top Recommendation: Laravel + Filament PHP

This is the **fastest** way to build what you need.

*   **Backend Framework**: **Laravel**
    *   *Why*: Industry standard, robust security, built-in API support.
*   **Admin Panel**: **Filament PHP**
    *   *Why*: This is the "Cheat Code". You define your "License" model, and Filament **automatically generates** the Admin Dashboard (Tables, Filters, Forms, Device Reset actions) with beautiful UI. You save 90% of development time compared to building an admin panel from scratch.
*   **Database**: **SQLite** (for start) or **MySQL**.
    *   *Why*: SQLite is file-based (zero config) and perfect for the scale mentioned. Easily upgradable to MySQL.
*   **Frontend (Public)**: **Blade + Tailwind CSS**.
    *   *Why*: Keeps everything in one project. No need to manage a separate API-Frontend connection for the landing page.

### Why not Node.js/React?
While Node.js is great, building a *secured Admin Panel with CRUD, Filtering, and Actions* from scratch takes weeks. Laravel Filament gives it to you in minutes.

---

## 🎨 Frontend Design: Premium License Service

For the "Price License Premium" frontend (The Landing / Purchase Page), we prioritize **Conversion** and **Trust**.

### Design Concept
*   **Style**: Modern, Clean, Dark Mode option.
*   **Framework**: Tailwind CSS (Utility-first, responsive).
*   **Key Sections**:
    1.  **Hero**: Clear value proposition ("Unlimited Comics on All Devices").
    2.  **Pricing Cards**: Simple tiers (e.g. "Lifetime" vs "Yearly").
    3.  **Feature Comparison**: Free vs Premium.
    4.  **FAQ**: Addressing "Device Limit" and "Validation".

I have generated a responsive **HTML + Tailwind** mockup for this page in `designs/pricing.html`.
