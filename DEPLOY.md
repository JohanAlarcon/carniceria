# Despliegue con Docker — Carnicería B2B

Guía para levantar la aplicación (Laravel 12 + Filament + Inertia/React + Reverb)
en un servidor con Docker. Pensada para convivir con otros proyectos ya
dockerizados en la misma máquina, usando puertos que no colisionan.

## Arquitectura

La imagen se construye en 3 etapas (Node para los assets, Composer para las
dependencias PHP, y una imagen final PHP-FPM + Nginx + Supervisor). El stack de
`docker-compose` levanta 4 contenedores:

| Servicio  | Contenedor          | Puerto host | Función                              |
|-----------|---------------------|-------------|--------------------------------------|
| `app`     | `carniceria_app`    | **8090→80** | Nginx + PHP-FPM (web, tienda, admin) |
| `db`      | `carniceria_db`     | interno     | MySQL 8                              |
| `queue`   | `carniceria_queue`  | —           | Worker de colas (`queue:work`)       |
| `reverb`  | `carniceria_reverb` | **8091→8080** | WebSockets Reverb (tiempo real)    |

- Aplicación:  `http://<IP>:8090`
- Panel admin: `http://<IP>:8090/admin`  (Filament)
- Reverb (ws):  `ws://<IP>:8091`

> Reverb es **opcional**: las notificaciones de pedidos en el panel funcionan por
> polling de la base de datos; Reverb solo añade el aviso instantáneo (sonido +
> toast). El backend publica el evento dentro de un `try/catch`, así que si Reverb
> falla nunca rompe la creación del pedido.

## Requisitos en el servidor

- Docker Engine + plugin `docker compose` v2+
- Git
- Puertos `8090` y `8091` libres (ajústalos en `docker-compose.yml` y `.env` si no lo están)

## Puesta en marcha

```bash
# 1. Clonar el repositorio
cd /opt
git clone https://github.com/JohanAlarcon/carniceria.git
cd carniceria

# 2. Crear el archivo de entorno de producción
cp .env.docker.example .env
#   Rellenar los secretos (o generarlos):
#     APP_KEY           = base64:$(openssl rand -base64 32)
#     DB_PASSWORD       = $(openssl rand -hex 16)
#     DB_ROOT_PASSWORD  = $(openssl rand -hex 16)
#     REVERB_APP_KEY    = $(openssl rand -hex 16)
#     REVERB_APP_SECRET = $(openssl rand -hex 16)
#   Ajustar APP_URL y REVERB_HOST con la IP/puerto reales.

# 3. Construir la imagen de la aplicación
docker compose build

# 4. Levantar el stack (la primera vez, sembrando datos de ejemplo)
RUN_SEED=true docker compose up -d

# 5. Ver el estado y los logs
docker compose ps
docker compose logs -f app
```

En el primer arranque el contenedor `app`:
1. Espera a que MySQL esté disponible.
2. Ejecuta `php artisan migrate --force`.
3. Si `RUN_SEED=true`, ejecuta `php artisan db:seed --force` (catálogo demo + admin).
4. Crea el enlace `public/storage`.
5. Cachea configuración y vistas (las rutas NO se cachean: contienen closures).

### Usuario administrador inicial (del seeder)

- Email: `johandarioalarcon@gmail.com`
- Password: `password`  ← **cámbialo tras el primer login.**

## Operación diaria

```bash
# Actualizar a la última versión del código
git pull
docker compose build app
docker compose up -d            # recrea los contenedores con la nueva imagen

# Migraciones puntuales (el contenedor app ya las corre al arrancar)
docker compose exec app php artisan migrate --force

# Consola / Tinker
docker compose exec app php artisan tinker

# Reiniciar solo el worker de colas
docker compose restart queue

# Parar / arrancar todo
docker compose down             # conserva los volúmenes (datos)
docker compose up -d
```

## Datos persistentes

Dos volúmenes con nombre sobreviven a los `down`/rebuild:

- `carniceria_dbdata`  → base de datos MySQL
- `carniceria_storage` → `storage/` de Laravel (subidas, logs, sesiones de framework)

`docker compose down -v` **borra** estos volúmenes; no lo uses salvo que quieras
empezar de cero.

## Cambiar los puertos

Si `8090`/`8091` estuvieran ocupados, edita en `docker-compose.yml` los mapeos
`"8090:80"` y `"8091:8080"`, y en `.env` los valores `APP_URL` y `REVERB_PORT`.
Luego `docker compose up -d`.

## (Opcional) Dominio + HTTPS

Para servir en un dominio con TLS, añade un `server` en el Nginx del host (el que
ya escucha en 80/443) que haga `proxy_pass http://127.0.0.1:8090;` con cabeceras
`X-Forwarded-*`, y para el WebSocket un bloque `location /app` con `proxy_pass
http://127.0.0.1:8091;` y `Upgrade`/`Connection` para WebSocket. Después ajusta
`APP_URL`, `REVERB_HOST`, `REVERB_PORT` (443) y `REVERB_SCHEME=https`.
