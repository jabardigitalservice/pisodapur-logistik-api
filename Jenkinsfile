
pipeline {

    agent any

    environment {
        appNameDevelopment= 'api-logistik-development'
        appNameProduction = 'api-logistik-production'
        STAGING_USER = "${env.STAGING_USER}"
        STAGING_HOST_LOGISTIK = "${env.STAGING_HOST_LOGISTIK}"
        BRANCH = "${env.BRANCH_STAGING}"        
    }





    options {
        timeout(time: 1, unit: 'HOURS')
    }

     triggers {
                githubPush()
     }

    stages{

        stage('Deliver for development') {
            when {
                branch 'development'
            }
            // make sure using branch development
            environment {
                SSH_COMMAND = "ssh-agent bash -c 'ssh-add ~/.ssh/id_rsa; git pull origin development'"     
            }

            steps{
                   sshagent (['64308515-2447-4273-b8f8-b1c06cff7c83']){
                        // ssh block
                       sh 'ssh -o StrictHostKeyChecking=no $STAGING_USER@$STAGING_HOST_LOGISTIK "cd /home/ubuntu/app/pikobar-logistik-api && $SSH_COMMAND  \
                                                                                        && docker-compose -f docker-compose-development.yml down \
                                                                                        && docker-compose -f docker-compose-development.yml up -d"'
                    }
            }     
        }

        stage('Deliver for development composer') {
            when {
                branch 'development'
            }

            steps{
                   sshagent (['64308515-2447-4273-b8f8-b1c06cff7c83']){
                        // ssh block
                       sh 'ssh -o StrictHostKeyChecking=no $STAGING_USER@$STAGING_HOST_LOGISTIK "cd /home/ubuntu/app/pikobar-logistik-api  \
                                                                                        && docker exec $appNameDevelopment php composer.phar install \
                                                                                        && docker exec  $appNameDevelopment php composer.phar dump-autoload \
                                                                                        && docker exec  $appNameDevelopment php artisan optimize"'
                    }
            }     
        }

        stage('Deliver for development migrate') {
            when {
                branch 'development'
            }

            input{
                message "Press Ok to continue to Action `php artisan migrate` ?"
            }

            steps{
                   sshagent (['64308515-2447-4273-b8f8-b1c06cff7c83']){
                        // ssh block
                       sh 'ssh -o StrictHostKeyChecking=no $STAGING_USER@$STAGING_HOST_LOGISTIK "cd /home/ubuntu/app/pikobar-logistik-api  \
                                                                                        && docker exec $appNameDevelopment php artisan migrate"'
                    }
            }     
        }


        stage('Deliver for production') {


            // make sure using branch master
            environment {
                SSH_COMMAND = "ssh-agent bash -c 'ssh-add ~/.ssh/id_rsa; git pull origin master'"     
            }

            when {
                branch 'master'
            }

            steps{
                   sshagent (['64308515-2447-4273-b8f8-b1c06cff7c83']){
                        // ssh block
                       sh 'ssh -o StrictHostKeyChecking=no $STAGING_USER@$PRODUCTION_HOST_LOGISTIK "cd /data/app/pikobar-logistik-api && $SSH_COMMAND  \
                                                                                        && docker-compose -f docker-compose-development.yml down \
                                                                                        && docker-compose -f docker-compose-development.yml up -d"'
                    }
            }  
        }

        stage('Deliver for production composer') {

            when {
                branch 'master'
            }

            steps{
                   sshagent (['64308515-2447-4273-b8f8-b1c06cff7c83']){
                        // ssh block
                       sh 'ssh -o StrictHostKeyChecking=no $STAGING_USER@$PRODUCTION_HOST_LOGISTIK "cd /data/app/pikobar-logistik-api  \
                                                                                        && docker exec -it $appNameProduction php composer.phar install \
                                                                                        && docker exec -it $appNameProduction php composer.phar dump-autoload \
                                                                                        && docker exec -it $appNameProduction php artisan optimize"'
                    }
            }    
        }

    } 

}
