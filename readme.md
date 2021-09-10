## Sample docker for new Wordpress projects
### Requirements
- Docker for Desktop (Windows, macOS)
- Docker Engine, Docker Composer (Linux)

### Setup
- Make sure to update `ServerName` in `vhost.conf`
- Clone this repository inside your Wordpress project root directory
- `cd .docker`
- `docker-compose build`
- `docker-compose up -d`