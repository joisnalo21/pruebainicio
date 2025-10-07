pipeline {
    agent any

    environment {
        // Cargar variables de .env más adelante usando sh
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
                cp -n .env.example .env
                php artisan key:generate
                '''
            }
        }

        stage('Verificar Laravel') {
            steps {
                sh 'php artisan --version'
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

        stage('Iniciar MySQL con Docker') {
            steps {
                echo 'Iniciando MySQL...'
                sh '''
                docker rm -f mysql-jenkins || true
                docker run --name mysql-jenkins -e MYSQL_ROOT_PASSWORD=admin -e MYSQL_DATABASE=pruebainicio -e MYSQL_USER=laravel_user -e MYSQL_PASSWORD=admin -p 3306:3306 -d mysql:8
                echo "⏳ Esperando 20s a que MySQL esté listo..."
                sleep 20
                '''
            }
        }

        stage('Limpiar cache y configuraciones') {
            steps {
                script {
                    def dbAvailable = sh(
                        script: "php -r 'try { new PDO(\"mysql:host=${DB_HOST};dbname=${DB_DATABASE}\", \"${DB_USERNAME}\", \"${DB_PASSWORD}\"); echo \"ok\"; } catch (Exception \$e) { echo \"fail\"; }'",
                        returnStdout: true
                    ).trim()

                    if (dbAvailable == 'ok') {
                        sh '''
                        php artisan cache:clear
                        php artisan config:clear
                        php artisan route:clear
                        php artisan view:clear
                        '''
                    } else {
                        echo "⚠️ Base de datos no disponible, se omite limpieza de cache"
                    }
                }
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
