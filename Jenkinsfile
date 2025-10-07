pipeline {
    agent any

    environment {
        APP_ENV = 'local'
        APP_DEBUG = 'true'
        DB_CONNECTION = 'mysql'
        DB_HOST = '127.0.0.1'
        DB_PORT = '3306'
        DB_DATABASE = 'pruebainicio'
        DB_USERNAME = 'laravel_user'
        DB_PASSWORD = 'admin'
    }

    stages {

        stage('Checkout SCM') {
            steps {
                checkout scm
            }
        }

        stage('Cargar variables .env') {
            steps {
                sh '''
                echo "🔄 Creando .env desde .env.example..."
                cp -n .env.example .env
                php artisan key:generate
                '''
            }
        }

        stage('Preparar entorno') {
            steps {
                echo 'Instalando dependencias PHP y Composer...'
                sh '''
                composer install --no-interaction --prefer-dist
                '''
            }
        }

        stage('Verificar Laravel') {
            steps {
                sh 'php artisan --version'
            }
        }

        stage('Preparar base de datos') {
            steps {
                echo 'Ejecutando migraciones...'
                sh 'php artisan migrate --force'
            }
        }

        stage('Ejecutar pruebas unitarias') {
            steps {
                echo 'Ejecutando PHPUnit...'
                sh 'vendor/bin/phpunit --configuration phpunit.xml'
            }
        }

        stage('Instalar Node y compilar assets') {
            steps {
                sh '''
                npm install
                npm run build
                '''
            }
        }

        stage('Limpiar cache y configuraciones') {
            steps {
                sh '''
                php artisan cache:clear
                php artisan config:clear
                php artisan route:clear
                php artisan view:clear
                '''
            }
        }

        stage('Deploy (opcional)') {
            steps {
                echo 'Aquí podrías desplegar a staging o producción'
            }
        }

    }

    post {
        always {
            echo 'Pipeline completado ✅'
        }
    }
}
