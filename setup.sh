#!/bin/sh
# =====================================================
# Setup Sistem Rekam Medis di Alpine Linux (code-server)
# Jalankan dari folder proyek:  sh setup.sh
# =====================================================
set -e

# Selalu bekerja dari folder tempat setup.sh berada
cd "$(dirname "$0")"

if [ ! -f database.sql ]; then
    echo "ERROR: database.sql tidak ditemukan di $(pwd)."
    echo "Pastikan SEMUA file proyek tersalin (ekstrak seluruh isi rekam_medis.zip)."
    exit 1
fi

# 1) Pasang paket (hanya di Alpine; lewati bila apk tidak ada)
if command -v apk >/dev/null 2>&1; then
    apk add --no-cache mariadb mariadb-client php82 php82-mysqli php82-session php82-json
    # beberapa image Alpine menamai binari php82 saja
    command -v php >/dev/null 2>&1 || ln -sf /usr/bin/php82 /usr/bin/php
fi

# 2) Inisialisasi & jalankan MariaDB bila belum aktif
if ! mariadb -uroot -e "SELECT 1" >/dev/null 2>&1; then
    if [ ! -d /var/lib/mysql/mysql ]; then
        mariadb-install-db --user=mysql --datadir=/var/lib/mysql >/dev/null
    fi
    mkdir -p /run/mysqld && chown mysql:mysql /run/mysqld
    (mysqld_safe --user=mysql >/tmp/mysqld.log 2>&1 &)
    for i in $(seq 1 30); do
        mariadb -uroot -e "SELECT 1" >/dev/null 2>&1 && break
        sleep 1
    done
fi

# 3) Import database + buat user aplikasi (sesuai config.php)
mariadb -uroot < database.sql
mariadb -uroot -e "CREATE USER IF NOT EXISTS 'rekam'@'localhost' IDENTIFIED BY 'rekam123';
                   GRANT ALL PRIVILEGES ON db_rekam_medis.* TO 'rekam'@'localhost';
                   FLUSH PRIVILEGES;"

echo
echo "Selesai. Database dan user aplikasi sudah siap."
echo "Jalankan aplikasi dengan:  php -S 0.0.0.0:8000"
