# 🎂 The Birthday Wishbook

> **Your Birthday, Your Wishlist, One Link**

The Birthday Wishbook is a premium, interactive web application designed with modern **Claymorphic** aesthetics. It provides users with a central dashboard to track their birthday countdown, group wishlist items under custom categories, dynamically adjust categories order, and share a single public URL with their friends and family that refreshes silently in the background.

---

## ✨ Features

### 🎨 Visual & Design
- **Claymorphic Styling**: Vibrant dual-tone blurred floating gradients, soft 3D shadows, organic border-radii, and micro-interactions.
- **Custom Toast Alerts**: Smooth sliding toast notifications replacing standard browser alert dialogs.
- **Interactive Countdown**: Real-time birthday countdown timers and zodiac badges on both the dashboard and public views.

### 🛠️ Wishlist Management
- **Custom Categories**: Organize your products across custom categories of your choice.
- **Category Reordering Index**: A drag-and-drop index widget inside the sidebar that reorders your category blocks dynamically in the main view and persists in the database.
- **Interactive Card Overlays**: Compact wishlist cards where the entire card is clickable, while edit/delete actions remain isolated.
- **Custom Confirm Dialogs**: A clean, styled popup modal for confirming item deletions.

### 🔗 Public Sharing & Syncing
- **One-Link Sharing**: Visitors can view your themed birthday countdown page and wishlist products.
- **Zero-Flicker Background Sync**: The shared public page runs a silent background worker that updates your wishlist changes every 10 seconds via DOM-diffing.

---

## 📂 Project Structure

```text
├── api/                             # Backend CRUD actions
│   ├── add-item.php
│   ├── delete-item.php
│   ├── edit-item.php
│   └── update-category-order.php
├── assets/                          # Core frontend assets
│   ├── css/
│   │   └── style.css                # Premium styling system
│   └── js/
│       └── app.js                   # Application handlers and animations
├── includes/                        # Layout page snippets
│   ├── functions.php                # Date calculations
│   ├── header.php
│   └── footer.php
├── .gitignore                       # Ignored file list
├── .htaccess                        # Apache routing rules
├── config.php                       # Configuration loader
├── db.php                           # Database connector singleton
├── index.php                        # Home landing page
├── signin.php                       # Sign In page
├── signup.php                       # Sign Up page (with password warning notice)
├── signout.php                      # Sign Out script
├── change-password.php              # Account security controls
├── wishlist.php                     # Public shared wishlist page (auto-refreshing)
├── env.example.php                  # Database config template
└── README.md                        # Documentation
```

---

<p align="center">
  <b>Crafted with ❤️ by Alvin Sonny</b>
</p>
