# Runtime Requirements

This document specifies the hardware, software, and environment requirements needed to install and run the web application.

## 1. Hardware Platform

- **Device**: Raspberry Pi Zero 2 W
    - CPU: Quad-core 64-bit ARM Cortex-A53 @ 1GHz
    - RAM: 512MB SDRAM
- **Storage**: Minimum 16GB microSD card (SSD optional for additional storage)
- **Network**: Built-in Wi-Fi (2.4GHz)
- **Power Supply**: 5V USB power supply

## 2. Operating System

This application can run on the following Raspberry Pi-compatible operating systems:

- **Raspberry Pi OS** (Lite or Full, Debian-based)
- **DietPi** (a lightweight, minimal Debian-based OS optimized for Raspberry Pi)

Both OS options are suitable, but DietPi is recommended for lightweight performance.

## 3. Software Stack

### Web Server

- **HTTP Server:** Apache2 (Version 2.4+)

### Server-Side Language

- **Scripting Language:** PHP (Version 7.4+)
  - Required Extensions:
     - mysqli
     - json


### Database System

- **Relational Database:** MariaDB (Version 10.3+)
  - Used to store:
    - User accounts
    - Found item records
    - Claim requests
    - Chat messages


## 4. Environment Requirements

- Network Configuration:
  - The application will be accessible on the local network via:
  - `http://<raspberrypi-local-ip>/`
- Default web root: `/var/www/html`
- Port forwarding or public DNS is optional, not required for demo.


- File Permissions:
  - Database must be accessible to PHP
  - Proper ownership (www-data:www-data)

Note: This application is specifically designed to run on Raspberry Pi Zero 2 W without any server-side frameworks or additional scripting engines beyond PHP.

## 5. Summary Table

| Component        | Requirement                        |
|------------------|-------------------------------------|
| Hardware         | Raspberry Pi Zero 2 W              |
| OS               | Raspberry Pi OS / DietPi           |
| Web Server       | Apache2                            |
| Backend Language | PHP (no frameworks)               |
| Database         | MariaDB                            |
| Frontend         | Native HTML/CSS/JavaScript only   |

