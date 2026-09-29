-- Выполняется один раз при инициализации тома с данными.

-- gen_random_uuid() и хеши; checksum моделей считается в PHP, но digest()
-- полезен для проверок на стороне БД.
CREATE EXTENSION IF NOT EXISTS pgcrypto;

-- Поиск по названиям материалов и схем укладки без точного совпадения.
CREATE EXTENSION IF NOT EXISTS pg_trgm;

-- Диагностика тяжёлых запросов по калькуляциям.
CREATE EXTENSION IF NOT EXISTS pg_stat_statements;
