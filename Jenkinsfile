pipeline {
    agent any

    environment {
        COMPOSER_HOME = "${WORKSPACE}/.composer"
        APP_ENV = "testing"
    }

    stages {
        stage('Preparar entorno') {
            steps {
                echo 'Instalando dependencias...'
                sh 'composer install --no-interaction --prefer-dist'
                sh 'cp .env.example .env'
                sh 'php artisan key:generate'
            }
        }

        stage('Verificar código') {
            steps {
                echo 'Corriendo análisis de código...'
                sh 'php artisan --version'
            }
        }

        stage('Ejecutar pruebas unitarias') {
            steps {
                echo 'Ejecutando PHPUnit...'
                sh 'vendor/bin/phpunit --configuration phpunit.xml'
            }
        }

        stage('Construir assets') {
            steps {
                echo 'Compilando assets...'
                sh 'npm install'
                sh 'npm run build'
            }
        }

        stage('Limpiar caché') {
            steps {
                echo 'Limpiando caché...'
                sh 'php artisan cache:clear'
                sh 'php artisan config:clear'
            }
        }

        stage('Deploy (opcional)') {
            steps {
                echo 'Aquí podrías desplegar a staging o producción'
                // Ejemplo: sh 'rsync -avz ./ user@servidor:/ruta/app'
            }
        }
    }

    post {
        success {
            echo 'Pipeline completado correctamente ✅'
        }
        failure {
            echo 'Pipeline falló ❌'
        }
    }
}
