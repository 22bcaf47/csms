pipeline {
    agent any
    environment {
        DOCKER_HOST = 'tcp://host.docker.internal:2375' // Connect to Docker daemon
    }
    stages {
        stage('Clone') {
            steps {
                git branch: 'main', url: 'https://github.com/22bcaf47/csms.git'
            }
        }

        stage('Build App Image') {
            steps {
                sh 'docker build -t my-csm-app .'
            }
        }

        stage('Run App Container') {
            steps {
                sh 'docker-compose -f docker-compose.yml up -d --build'
            }
        }

     stage('Run Selenium Tests') {
            steps {
                sh '''
    docker build -f Dockerfile.selenium -t selenium-runner .
    docker run --rm selenium-runner
'''

            }
        }



    }
}
