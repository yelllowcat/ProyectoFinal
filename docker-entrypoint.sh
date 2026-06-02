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
    until mysqladmin --skip-ssl ping -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS"; do
        echo "Database is not ready yet, sleeping..."
        sleep 2
    done
    echo "Database is online!"

    # 3. Adjust DB name references in database/unired_db.sql
    echo "Adapting unired_db.sql with DB name: ${DB_NAME}..."
    sed -i "s/unired_DB/${DB_NAME}/g" /var/www/html/database/unired_db.sql

    # 4. Check if tables exist
    echo "Checking for tables in database ${DB_NAME}..."
    if ! TABLE_COUNT=$(mysql --skip-ssl -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -D "$DB_NAME" -se "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '$DB_NAME';"); then
        echo "Error: Failed to query database '${DB_NAME}'. Please check if the database exists and your user credentials have permission to access it."
        exit 1
    fi

    # Check for stored routines (procedures)
    echo "Checking for stored procedures in database ${DB_NAME}..."
    if ! ROUTINE_COUNT=$(mysql --skip-ssl -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -D "$DB_NAME" -se "SELECT COUNT(*) FROM information_schema.routines WHERE routine_schema = '$DB_NAME';"); then
        echo "Warning: Could not check stored procedures count."
        ROUTINE_COUNT=0
    fi
    echo "Found ${TABLE_COUNT} tables and ${ROUTINE_COUNT} stored procedures."

    # If tables exist but routines are missing, the DB is in a corrupted/partial state. Reset it.
    if [ "$TABLE_COUNT" -gt 0 ] && [ "$ROUTINE_COUNT" -eq 0 ]; then
        echo "Database has tables but is missing stored procedures. Resetting database..."
        if mysql --skip-ssl -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -e "DROP DATABASE IF EXISTS ${DB_NAME}; CREATE DATABASE ${DB_NAME};" 2>/dev/null; then
            echo "Database ${DB_NAME} recreated successfully."
        else
            echo "Failed to recreate database. Dropping all tables manually instead..."
            mysql --skip-ssl -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -D "$DB_NAME" -e '
                SET FOREIGN_KEY_CHECKS = 0;
                SET @tables = NULL;
                SELECT GROUP_CONCAT("`", table_name, "`") INTO @tables FROM information_schema.tables WHERE table_schema = (SELECT DATABASE());
                SELECT IFNULL(CONCAT("DROP TABLE IF EXISTS ", @tables), "SELECT 1") INTO @stmt;
                PREPARE stmt FROM @stmt;
                EXECUTE stmt;
                DEALLOCATE PREPARE stmt;
                SET FOREIGN_KEY_CHECKS = 1;
            '
        fi
        TABLE_COUNT=0
    fi
    
    if [ "$TABLE_COUNT" -eq 0 ]; then
        echo "No tables found in ${DB_NAME}. Importing unired_db.sql..."
        if ! mysql --skip-ssl -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < /var/www/html/database/unired_db.sql; then
            echo "Error: Failed to import database/unired_db.sql"
            exit 1
        fi
        
        echo "Running performance optimization migrations..."
        if ! mysql --skip-ssl -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < /var/www/html/database/migrations/2025_05_17_performance_indexes.sql; then
            echo "Error: Failed to run database/migrations/2025_05_17_performance_indexes.sql"
            exit 1
        fi
        
        if [ "$SEED_DB" = "true" ]; then
            echo "SEED_DB is set to true. Seeding database..."
            if ! php /var/www/html/database/seed.php; then
                echo "Warning: Database seeding failed."
            fi
        fi
        echo "Database initialization completed successfully."
    else
        echo "Database already has tables ($TABLE_COUNT tables found). Skipping initialization."
    fi

    echo "Ensuring admin user exists..."
    if ! php /var/www/html/database/create_admin.php; then
        echo "Warning: Could not verify or create admin user."
    fi
fi

# Execute the CMD (default is apache2-foreground)
exec "$@"
