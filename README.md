# kaleidoscope-api

API for Kaleidoscope Australia retailer and admin systems.

## Overview

-   Laravel 8.83.29 on PHP 7.4
-   Imports shipping costs from CSVs in 01data (AusPost.csv, DirectFreight.csv)
-   Serves /api/shipping/cost endpoint for retailer frontend
-   Backend for retailer.kaleidoscope.com.au

## Setup

-   Clone repo
-   Run `composer install`
-   Copy `.env.example` to `.env` and configure
-   Run `php artisan key:generate`
-   Set up database (`kndb`) and run migrations
-   Run `php artisan serve --host=api.localhost --port=8000`
