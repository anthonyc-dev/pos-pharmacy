# POS Pharmacy

A simple Point of Sale (POS) system designed for pharmacies, built using **native PHP**, **HTML**, and **Bootstrap CSS**.

## Problem Statement

Many small pharmacies still depend on manual or outdated systems for handling sales, inventory, and customer records. This results in:

- Inaccurate inventory control
- Slow transaction processing
- Difficulty in monitoring sales and stock levels
- Higher risk of human error

## Solution

This POS system offers a streamlined, user-friendly solution aimed at small pharmacies. It automates sales and inventory management with a straightforward tech stack, making it easy to deploy and maintain.

### Features

- **Sales Management**: Quickly process sales transactions.
- **Inventory Tracking**: Add, update, and monitor medicine and product stock.
- **Customer Management**: Store and search customer details and their transaction history.
- **Thermal Printer Integration**: Print receipts directly.
- **Barcode Scanner Support**: Speed up input and lookup of products using barcodes.
- **Responsive UI**: Mobile-ready, clean interface powered by Bootstrap CSS.
- **Simple Deployment**: Runs on any server supporting PHP.
- **Composer Support**: Manage PHP libraries easily with Composer.

## Technology Stack

- **PHP** (no frameworks): Backend logic
- **Composer**: PHP dependency management
- **HTML**: Page structure
- **Bootstrap CSS**: Styling and responsive design

## Getting Started

### Requirements

- PHP 7.x or above
- Composer
- Web server (Apache, Nginx, etc.)
- MySQL or compatible database
- (Optional) Thermal printer for receipts
- (Optional) Barcode scanner for product lookup

### Installation

1. **Clone the repository:**

   ```bash
   git clone https://github.com/anthonyc-dev/pos-pharmacy.git
   cd pos-pharmacy
   ```

2. **Install PHP dependencies using Composer:**

   ```bash
   composer install
   ```

3. **Set up the database:**

   - Import the provided SQL file (such as `database.sql`) into your MySQL server.
   - Update your database credentials inside the main configuration file (like `config.php`).

4. **Configure environment variables:**

   - Copy `.env.example` to `.env` and customize for your environment and email settings:

     | Key              | Description                            | Example/Default Value    |
     | ---------------- | -------------------------------------- | ------------------------ |
     | `MAIL_HOST`      | SMTP mail server host                  | smtp.gmail.com           |
     | `MAIL_USERNAME`  | Sender email address                   | yourgmail@gmail.com      |
     | `MAIL_PASSWORD`  | App password for that email address    | your-gmail-app-password  |
     | `MAIL_PORT`      | SMTP server port                       | 587                      |
     | `MAIL_FROM`      | The email address shown as sender      | yourgmail@gmail.com      |
     | `MAIL_FROM_NAME` | Displayed sender name                  | "Your App"               |
     | `APP_URL`        | The base URL for your app installation | http://localhost/project |

   - **Note:** Update SMTP/mail settings if you want to enable email notifications (optional).

5. **Run the project:**

   - Place the project directory on your local web server (such as `htdocs` for XAMPP or `www` for WAMP).
   - Open the system in your browser, e.g.: `http://localhost/pos-pharmacy`

## Usage

> Log in with credentials created in your database, add products/medicines, and perform sales either using a barcode scanner or manual entry. You can print receipts via an attached thermal printer!

## Contributing

Pull requests are encouraged! For major changes, please [open an issue](https://github.com/anthonyc-dev/pos-pharmacy/issues) to discuss your ideas first.

## License

[MIT License](LICENSE)
