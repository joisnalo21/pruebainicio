pipeline {
    agent any

    environment {
        COMPOSER_HOME = "${WORKSPACE}/.composer"
        APP_ENV = "testing"
    }

    stages {
        stage('Preparar entorno') {
            steps {
                echo 'Instalando dependencias PHP y Composer...'
                sh 'composer install --no-interaction --prefer-dist'
                sh 'cp -n .env.example .env || true' // no sobreescribe si ya existe
                sh 'php artisan key:generate || true'
            }
        }

        stage('Verificar código') {
            steps {
                echo 'Corriendo verificación de Laravel...'
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
                echo 'Instalando dependencias Node y construyendo assets...'
                sh 'npm install'
                withEnv(["NODE_OPTIONS=--openssl-legacy-provider"]) {
                    sh 'npm run build'
                }
            }
        }

        stage('Limpiar caché') {
            steps {
                echo 'Limpiando caché y configuraciones...'
                script {
                    // Verifica si hay conexión a la DB antes de limpiar cache
                    def dbAvailable = sh(script: "php -r \"try { new PDO('mysql:host=${env.DB_HOST};dbname=${env.DB_DATABASE}', '${env.DB_USERNAME}', '${env.DB_PASSWORD}'); echo 'ok'; } catch (Exception \$e) { echo 'fail'; }\"", returnStdout: true).trim()
                    if (dbAvailable == 'ok') {
                        sh 'php artisan cache:clear'
                        sh 'php artisan config:clear'
                        sh 'php artisan route:clear'
                        sh 'php artisan view:clear'
                    } else {
                        echo '⚠️ Base de datos no disponible, se omite limpieza de cache'
                    }
                }
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
