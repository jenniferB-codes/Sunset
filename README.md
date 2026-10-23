# Willow & Linen — versión mejorada

## Cómo iniciar con XAMPP

1. Copia la carpeta `willow-linen-mejorada` a `C:\xampp\htdocs\`.
2. Inicia **Apache** y **MySQL** desde XAMPP.
3. Entra a `http://localhost/phpmyadmin` e importa `database.sql` (pestaña **Importar**).
4. Revisa `config.php` si tu usuario o contraseña de MySQL son diferentes.
5. Abre `http://localhost/willow-linen-mejorada/index.html`.

**Importante:** abrir `index.html` directamente con doble clic o Live Server no permite que funcionen las cuentas ni las reservas. Se necesita PHP y MySQL activos.

## Estructura

- `index.html`: inicio con contenido adicional y experiencias destacadas.
- `picnics.html`, `brunches.html`: catálogos en páginas separadas.
- `reservar.html`: elegir paquete → actividad → fecha y personas → guardar solicitud.
- `cuenta.html`: crear cuenta e iniciar sesión.
- `mis-reservas.html`: ver solicitudes propias y totales estimados.
- `galeria.html`, `como-funciona.html`: páginas informativas.
- `api.php`: autenticación y guardado de solicitudes con sesiones PHP.
- `database.sql`: tablas MySQL.

## Limitaciones

Este proyecto es una **base educativa**, no una plataforma lista para producción. No confirma reservas automáticamente, no envía correos ni cobra pagos. Los precios son de ejemplo. Antes de publicarlo en internet hay que añadir HTTPS, protección CSRF, limitación de intentos de inicio de sesión, recuperación de contraseña, validación de disponibilidad y otras medidas de seguridad. Las imágenes usan Unsplash y requieren internet. 


## Sunset Activities (actualización)
- Nueva página `actividades.html`, accesible desde el menú de todas las páginas.
- El cliente elige libremente entre seis actividades (o ninguna) en `reservar.html`.
- Después puede seleccionar varios extras opcionales. Los extras se cobran una vez por reserva; paquete y actividad, por persona.
- El total se calcula en JavaScript y se **recalcula en PHP** antes de guardar en MySQL.
- **Si ya tenías la base de datos anterior**, importa `migration_activities.sql` en phpMyAdmin UNA SOLA VEZ. Si instalas desde cero, importa solo `database.sql`.
- Los precios son ilustrativos. El sistema no procesa pagos ni confirma reservas automáticamente.
