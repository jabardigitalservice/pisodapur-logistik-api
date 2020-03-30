
pipeline {

    agent any
    options {
        timeout(time: 1, unit: 'HOURS')
    }

    properties([pipelineTriggers([githubPush()])])


    stages{

        stage('Deliver for development') {
            when {
                branch 'development'
            }
            steps {
               sh 'echo "hello production"' 
            }
        }


        stage('Deliver for production') {

            

            when {
                branch 'master'
            }
            steps {
                sh 'echo "hello production"' 
            }
        }

    } 

}
