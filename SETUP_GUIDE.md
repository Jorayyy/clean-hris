# Setup Instructions for Clean HRIS

Follow these steps to set up the project on a new Windows device.

## Prerequisites

1.  **PHP 8.3+**
    - Ensure PHP is installed and added to your system `PATH`.
    - **Required Extensions**: In your `php.ini`, ensure the following are enabled (remove the `;` prefix):
        - `extension=fileinfo`
        - `extension=openssl`
        - `extension=pdo_sqlite`
        - `extension=zip`
2.  **Composer** (PHP Dependency Manager)
    - Download from [getcomposer.org](https://getcomposer.org/).
3.  **Node.js & NPM** (v20+ recommended)
    - Download from [nodejs.org](https://nodejs.org/).
4.  **PowerShell Execution Policy** (If using PowerShell)
    - Run this command in an Administrator terminal to allow local scripts:
      ```powershell
      Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
      ```

## Installation Steps

1.  **Clone the Repository**
    ```powershell
    git clone https://github.com/Jorayyy/clean-hris.git
    cd clean-hris
    ```

2.  **Install PHP Dependencies**
    ```powershell
    composer install
    ```

3.  **Install JS Dependencies & Build Assets**
    ```powershell
    npm install
    npm run build
    ```

4.  **Environment Setup**
    ```powershell
    copy .env.example .env
    php artisan key:generate
    ```

5.  **Database Setup (SQLite)**
    - The application is configured to use SQLite by default.
    - Run migrations and seed the database:
    ```powershell
    php artisan migrate --seed
    ```
    - *Note: When prompted to create the `database.sqlite` file, type `yes`.*

6.  **Start the Application**
    ```powershell
    php artisan serve
    ```
    - Visit the application at: [http://127.0.0.1:8000](http://127.0.0.1:8000)

## Troubleshooting

- **"Could not find driver" (SQLite)**: Ensure `extension=pdo_sqlite` is uncommented in your `php.ini`.
- **"zip extension missing"**: Ensure `extension=zip` is uncommented in your `php.ini`.
- **"npm is not recognized"**: Restart your terminal or VS Code after installing Node.js.
