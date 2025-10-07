pipeline {
    agent any

    environment {
        APP_ENV = 'testing'
        APP_DEBUG = 'true'
        DB_CONNECTION = 'sqlite'
        DB_DATABASE = ':memory:'
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

        stage('Ejecutar migraciones') {
            steps {
                echo 'Ejecutando migraciones en SQLite...'
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
