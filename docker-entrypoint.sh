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

    # 4. Check if tables exist (BASE TABLEs only)
    echo "Checking for tables in database ${DB_NAME}..."
    if ! TABLE_COUNT=$(mysql --skip-ssl -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -D "$DB_NAME" -se "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '$DB_NAME' AND TABLE_TYPE = 'BASE TABLE';"); then
        echo "Error: Failed to query database '${DB_NAME}'. Please check if the database exists and your user credentials have permission to access it."
        exit 1
    fi

    # Check for stored routines (procedures)
    echo "Checking for stored procedures in database ${DB_NAME}..."
    if ! ROUTINE_COUNT=$(mysql --skip-ssl -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -D "$DB_NAME" -se "SELECT COUNT(*) FROM information_schema.routines WHERE routine_schema = '$DB_NAME';"); then
        echo "Warning: Could not check stored procedures count."
        ROUTINE_COUNT=0
    fi

    # Check if the view v_posts_stats exists
    echo "Checking for view v_posts_stats in database ${DB_NAME}..."
    if ! VIEW_EXISTS=$(mysql --skip-ssl -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -D "$DB_NAME" -se "SELECT COUNT(*) FROM information_schema.views WHERE table_schema = '$DB_NAME' AND table_name = 'v_posts_stats';"); then
        echo "Warning: Could not check if view exists."
        VIEW_EXISTS=0
    fi

    echo "Status: Found ${TABLE_COUNT}/13 tables, ${ROUTINE_COUNT}/41 stored procedures, and view_exists=${VIEW_EXISTS}."

    # Determine import credentials (use root if DB_ROOT_PASS is provided to get SUPER privilege for triggers)
    IMP_USER=$DB_USER
    IMP_PASS=$DB_PASS
    if [ -n "$DB_ROOT_PASS" ]; then
        echo "Root password provided. Trusting function creators..."
        mysql --skip-ssl -h "$DB_HOST" -u root -p"$DB_ROOT_PASS" -e "SET GLOBAL log_bin_trust_function_creators = 1;" || echo "Warning: Could not set global log_bin_trust_function_creators"
        IMP_USER="root"
        IMP_PASS=$DB_ROOT_PASS
    fi

    # If any of the required parts are missing or incomplete, reset and import the full DB.
    # Total tables = 13, routines = 41, view = 1
    if [ "$TABLE_COUNT" -lt 13 ] || [ "$ROUTINE_COUNT" -lt 41 ] || [ "$VIEW_EXISTS" -eq 0 ]; then
        echo "Database is incomplete or corrupted. Resetting database to force a clean import..."
        if mysql --skip-ssl -h "$DB_HOST" -u "$IMP_USER" -p"$IMP_PASS" -e "DROP DATABASE IF EXISTS ${DB_NAME}; CREATE DATABASE ${DB_NAME};" 2>/dev/null; then
            echo "Database ${DB_NAME} recreated successfully."
        else
            echo "Failed to recreate database. Dropping all tables and views manually instead..."
            mysql --skip-ssl -h "$DB_HOST" -u "$IMP_USER" -p"$IMP_PASS" -D "$DB_NAME" -e '
                SET FOREIGN_KEY_CHECKS = 0;
                DROP VIEW IF EXISTS v_posts_stats;
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
        if ! mysql --skip-ssl -h "$DB_HOST" -u "$IMP_USER" -p"$IMP_PASS" "$DB_NAME" < /var/www/html/database/unired_db.sql; then
            echo "Error: Failed to import database/unired_db.sql"
            exit 1
        fi
        
        echo "Running performance optimization migrations..."
        if ! mysql --skip-ssl -h "$DB_HOST" -u "$IMP_USER" -p"$IMP_PASS" "$DB_NAME" < /var/www/html/database/migrations/2025_05_17_performance_indexes.sql; then
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
