-- Dijalankan otomatis oleh image MariaDB/MySQL saat container Master pertama kali start
-- (letakkan di /docker-entrypoint-initdb.d/ pada container db-master)

CREATE USER IF NOT EXISTS 'replica_user'@'%' IDENTIFIED BY 'replica_p455w0rd';
GRANT REPLICATION SLAVE ON *.* TO 'replica_user'@'%';
FLUSH PRIVILEGES;
