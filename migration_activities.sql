-- Ejecutar SOLO si ya importaste la base de datos de la versión anterior.
USE willow_linen;
ALTER TABLE bookings
  ADD COLUMN extras_summary VARCHAR(500) NOT NULL DEFAULT 'Ninguno',
  ADD COLUMN extras_total DECIMAL(10,2) NOT NULL DEFAULT 0;
