# Shop Scrape

Welcome to the Shop Scrape API! This is a RESTful API designed to scrape, manage, and analyze product data from various e-commerce sources. It allows users to collect, store, and retrieve product details, ratings, images, and pricing history. Built with Laravel, the API provides a structured way to track product changes over time, ensuring accurate and up-to-date information.

Key features include:

* Product Data Scraping – Automatically gather product details from multiple retailers.
* Ratings Management – Store and analyze user ratings with calculated average scores.
* Historical Price Tracking – Keep a record of price fluctuations for better insights.
* Secure Authorization – Enforces strict access control to ensure data integrity.

## Prerequisites

Before deploying the project, ensure you have the following installed:

- **Docker**: Docker and Docker Compose are required to containerize the application and MySQL database. You can download Docker from [here](https://www.docker.com/get-started).
- **PHP**: PHP is used for managing Laravel dependencies; however, it will be handled by Docker containers.
- **Git**: Git is required to clone the repository.
- **Composer**: Composer is a PHP dependency manager, and it will be used to install the Laravel project dependencies (although this will be managed via Docker, having it installed locally can help with troubleshooting).

## Step-by-Step Deployment Instructions

Follow these steps to deploy the application:

### 1. Clone the Repository

Clone the repository to your local machine using Git:

```bash
git clone https://github.com/dmitrycrockodile/shop-scrape.git your-repository-name
cd your-repository-name
```

### 2. Create the ```.env``` File

Copy the example environment file to create your own `.env `file:

```bash
cp .env.example .env
```

This file contains the environment variables required for the Laravel application to function, including database connection settings.

### 3. Docker setup

The project uses Docker Compose to set up the environment. In the project root directory, run the following command to start the Docker containers:

```bash
docker-compose up -d
```

This will build and start the following containers:

app: The Laravel application container.  
db: The MySQL database container.  
Make sure that the `.env` file is properly configured to connect to the MySQL db container:

```bash
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_username
DB_PASSWORD=your_database_password
```

The `DB_HOST` should be set to db, which is the name of the MySQL container defined in the `docker-compose.yml` file.

### 4. Install Composer Dependencies

Once the containers are running, install the Laravel dependencies using Composer. Enter the `app` container and run:

```bash
docker exec -it <container_name> bash
composer install
```

### 5. Set Up the Database

After Composer installs the dependencies, run the following command to migrate the database:

```bash
php artisan migrate
```

This will create the necessary database tables according to the migrations defined in the Laravel application.

### 6. Create and Seed the Database to get pre-created scraped data for every product of every retailer for 1 year

To populate the database with test users run:

```bash
php artisan db:seed
```

The average scraped_data table size is ~ 545,000 rows. 
The average waiting time is ~ 5:40 min.

## API Base URL
All API requests should be made to:
`http://localhost:8876/api`

## Pre-created data

For testing purposes, the following tables have pre-created data (if you run the seed) in the database. You can use these tables as an examples to test the API:

### products table

| id | title                   | description                                              | manufacturer_part_number | pack_size |
|----|-------------------------|----------------------------------------------------------|--------------------------|-----------|
| 1  | voluptatem et laudantium| Accusantium quis exercitationem dolor harum.             | qzj-61510                | each      |
| 2  | perferendis aut laborum | Repudiandae fuga ut tempora consequuntur arc.            | rih-60401                | each      |
| 3  | autem ex error          | Molestias et earum quaerat molestiae eum est.            | eql-24507                | box       |

### retailers table

| id | title                | url                                        | currency | logo                               |
|----|----------------------|--------------------------------------------|----------|------------------------------------|
| 1  | maxime nemo quae     | (http://www.rosenbaum.biz/nostrum-maxime-vel-commodi-quaerat-quidem-reiciendis) | CLP      | (https://via.placeholder.com/400x400.png/0099bb?text=business+quo) |
| 2  | ut minima non        | (http://hoeger.com/est-perspiciatis-et-doloribus.html) | KRW      | (https://via.placeholder.com/400x400.png/0088ff?text=business+est) |
| 3  | qui at enim          | (http://jones.com/asperiores-consequatur-error-dolorem-laudantium-perferendis-et.html) | PHP      | (https://via.placeholder.com/400x400.png/00bb77?text=business+laudantium) |

### scraped_data table

| id | product_id | retailer_id | title                     | description                            | price    | stock_count | avg_rating |
|----|------------|-------------|---------------------------|----------------------------------------|----------|-------------|------------|
| 1  | 1          | 3           | voluptatem et laudantium | Accusantium quis exercitationem dolor harum... | 1552.88 | 276         | 3.2        |
| 2  | 1          | 3           | voluptatem et laudantium | Accusantium quis exercitationem dolor harum... | 7805.51 | 12          | 3.6        |
| 3  | 1          | 3           | voluptatem et laudantium | Accusantium quis exercitationem dolor harum... | 905.26  | 357         | 2.3        |

### product_retailers table

| id | product_id | retailer_id |
|----|------------|-------------|
| 1  | 1          | 3           |
| 2  | 2          | 4           |
| 3  | 2          | 6           |

### ratings table

| id | scraped_data_id | one_star | two_stars | three_stars | four_stars | five_stars |
|----|-----------------|----------|-----------|-------------|------------|------------|
| 1  | 1               | 49       | 32        | 78          | 73         | 56         |
| 2  | 2               | 8        | 38        | 69          | 49         | 81         |
| 3  | 3               | 98       | 2         | 55          | 34         | 9          |

### images table

| id | imageable_type        | imageable_id | file_url                                              |
|----|-----------------------|--------------|-------------------------------------------------------|
| 1  | App\Models\Product    | 1            | (https://via.placeholder.com/400x400.png/00ddd) |
| 2  | App\Models\Product    | 1            | (https://via.placeholder.com/400x400.png/00117) |
| 3  | App\Models\Product    | 2            | (https://via.placeholder.com/400x400.png/0077a) |
| 4  | App\Models\Product    | 2            | (https://via.placeholder.com/400x400.png/00ae5) |
