pipeline {
    agent any

    environment {
        SONAR_HOST_URL = 'http://sonarqube:9000'
        SONAR_PROJECT_KEY = 'laravel-app'
        SONAR_PROJECT_NAME = 'LaravelApp'
    }

    stages {

        stage('Clonar repositorio') {
            steps {
                echo '🔄 Clonando el repositorio desde GitHub...'
                sh '''
                    set -e
                    echo "🧹 Limpiando workspace..."
                    find . -mindepth 1 -delete || true
                    git clone https://github.com/joisnalo21/pruebainicio.git .
                '''
            }
        }



       stage('Configurar .env') {
  steps {
    sh '''
      set -e
      if [ -f ".env.docker" ]; then
        rm -rf .env
        cp .env.docker .env
        echo "✅ Usando .env.docker"
      else
        echo "❌ No existe .env.docker"
        exit 1
      fi

      sed -i 's/^SESSION_DRIVER=.*/SESSION_DRIVER=file/' .env

      test -f .env
      ls -la .env .env.docker
    '''
  }
}


        stage('Instalar dependencias') {
            steps {
                echo '📦 Instalando dependencias de Laravel...'
                sh '''
                    set -e
                    apt-get update
                    apt-get install -y php php-cli php-zip unzip curl git \
                      php-curl php-dom php-xml php-mbstring php-intl php-gd php-mysql

                    # Node 20 (requerido por Vite/Laravel Vite Plugin)
                    curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
                    apt-get install -y nodejs
                    node -v
                    npm -v

                    # Composer
                    curl -sS https://getcomposer.org/installer | php
                    mv composer.phar /usr/local/bin/composer

                    composer install --no-interaction --prefer-dist

                    # Ahora sí artisan (ya existe vendor/)
                    php artisan key:generate || true
                    php artisan config:clear || true
                    php artisan cache:clear || true
                    php artisan route:clear || true
                    php artisan view:clear || true

                    npm install
                    npm run build
                '''
            }
        }

        stage('Análisis de calidad - SonarQube') {
            steps {
                withCredentials([string(credentialsId: 'SONAR_TOKEN', variable: 'SONAR_TOKEN')]) {
                    echo '🔍 Ejecutando análisis SonarQube...'
                    sh '''
                        set -e
                        sonar-scanner \
                          -Dsonar.projectKey=${SONAR_PROJECT_KEY} \
                          -Dsonar.projectName=${SONAR_PROJECT_NAME} \
                          -Dsonar.host.url=${SONAR_HOST_URL} \
                          -Dsonar.token=${SONAR_TOKEN} \
                          -Dsonar.sources=app,resources,routes,config \
                          -Dsonar.exclusions=vendor/**,node_modules/**,public/**,storage/** \
                          -Dsonar.sourceEncoding=UTF-8
                    '''
                }
            }
        }

        stage('Desplegar contenedores') {
            steps {
                echo '🚀 Desplegando Laravel y MySQL...'
                sh '''
                    set -e
                    docker network create devops-net || true

                    # --- MYSQL CONTAINER ---
                    if [ -z "$(docker ps -q -f name=mysql)" ]; then
                        echo "🔹 Iniciando contenedor MySQL..."
                        docker run -d \
                            --name mysql \
                            --network devops-net \
                            -v mysql_data:/var/lib/mysql \
                            -e MYSQL_ROOT_PASSWORD=admin \
                            -e MYSQL_DATABASE=pruebainicio \
                            -e MYSQL_USER=laravel_user \
                            -e MYSQL_PASSWORD=admin \
                            -p 3307:3306 \
                            mysql:8.0
                    else
                        echo "✅ MySQL ya está corriendo."
                    fi

                    echo "⌛ Esperando MySQL..."
                    for i in {1..30}; do
                        if docker exec mysql mysqladmin ping -h "mysql" --silent; then
                            echo "✅ MySQL disponible."
                            break
                        fi
                        sleep 2
                    done

                    # --- LARAVEL CONTAINER ---
                    docker stop laravel-container || true
                    docker rm laravel-container || true
                    # Asegurar que el .env dentro de la imagen sea el de docker
rm -f .env .env.local .env.dusk.local || true
cp .env.docker .env
                    docker build -t laravel-app .
                    docker run -d \
  --name laravel-container \
  --network devops-net \
  --env-file .env \
  -p 8000:8000 \
  laravel-app
                '''
            }
        }

        stage('Configurar Laravel') {
            steps {
                echo '🧩 Configurando Laravel dentro del contenedor...'
                sh '''
                    set -e
                    docker exec laravel-container php artisan config:clear
                    docker exec laravel-container php artisan cache:clear
                    docker exec laravel-container php artisan route:clear
                    docker exec laravel-container php artisan view:clear
                    docker exec laravel-container php artisan migrate --force
                '''
            }
        }
    }

    post {
        success { echo '✅ Pipeline ejecutado exitosamente.' }
        failure { echo '❌ Error en el pipeline. Revisa los logs.' }
    }
}
