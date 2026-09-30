# PG Life - Student & Working Professional Housing Portal

Full-stack PHP and MySQL web application for searching and bookmarking paying guest (PG) accommodations across major cities.

## Running Locally (XAMPP)
1. Ensure Apache and MySQL are running in XAMPP.
2. Open `http://localhost/PG_ife_SOLUTON/index.php`.
3. Default Test Login:
   - **Email:** `rahul.sharma@example.com`
   - **Password:** `password123`

## Running with Docker
Run the complete stack (Apache + PHP 8.2 + MySQL 8.0 + phpMyAdmin) with a single command:

```bash
docker compose up --build -d
```

- **App:** [http://localhost:8080](http://localhost:8080)
- **phpMyAdmin:** [http://localhost:8081](http://localhost:8081)
- The database, tables, and sample data are automatically provisioned from `schema.sql` via `/docker-entrypoint-initdb.d/init.sql`.

To stop the containers:
```bash
docker compose down
```
