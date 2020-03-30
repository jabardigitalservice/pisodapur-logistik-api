pipeline {


    options {
        timeout(time: 1, unit: 'HOURS')
    }

    stages{

        stage('Deliver for development') {
            when {
                branch 'development'
            }
            steps {
               sh 'echo "hello production"' 
            }
        }
        stage('Deploy for production') {

            when {
                branch 'master'
            }
            steps {
                sh 'echo "hello production"' 
            }
        }

    }
}