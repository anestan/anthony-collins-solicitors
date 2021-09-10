# ACS Rebuild

## Local Development Setup

### 1. Dependencies
    Docker

### 2. Build and run Docker

    cd project/.docker
    docker-compose build
    docker-compose up -d

### 3. Install dependencies

    cd project/wp-content/themes/acs
    npm i

#### Browsersync

    npm run watch