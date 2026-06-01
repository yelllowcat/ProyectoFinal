#!/bin/bash
set -e

# 1. Generate .env file from environment variables if they are set
echo "Generating .env file from environment..."
cat <<EOF > /var/www/html/.env
DB_HOST=${DB_HOST:-localhost}
DB_NAME=${DB_NAME:-unired_DB}
DB_USER=${DB_USER:-yellow}
DB_PASS=${DB_PASS:-yellow}
EOF

# 2. Wait for MySQL to be available
if [ -n "$DB_HOST" ]; then
    echo "Waiting for database connection at ${DB_HOST}..."
    until mysqladmin ping -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" --silent; do
        echo "Database is not ready yet, sleeping..."
        sleep 2
    done
    echo "Database is online!"

    # 3. Adjust DB name references in database/unired_db.sql
    echo "Adapting unired_db.sql with DB name: ${DB_NAME}..."
    sed -i "s/unired_DB/${DB_NAME}/g" /var/www/html/database/unired_db.sql

    # 4. Check if tables exist
    TABLE_COUNT=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -D "$DB_NAME" -se "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '$DB_NAME';")
    
    if [ "$TABLE_COUNT" -eq 0 ]; then
        echo "No tables found in ${DB_NAME}. Importing unired_db.sql..."
        mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < /var/www/html/database/unired_db.sql
        
        echo "Running performance optimization migrations..."
        mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < /var/www/html/database/migrations/2025_05_17_performance_indexes.sql
        
        if [ "$SEED_DB" = "true" ]; then
            echo "SEED_DB is set to true. Seeding database..."
            php /var/www/html/database/seed.php
        fi
        echo "Database initialization completed successfully."
    else
        echo "Database already has tables ($TABLE_COUNT tables found). Skipping initialization."
    fi
fi

# Execute the CMD (default is apache2-foreground)
exec "$@"
