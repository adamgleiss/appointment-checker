# Global Entry Checker

## Summary

The purpose of this repo is to deploy a AWS Lambda that scan the Global Entry API for upcoming appointments.  
Appointments are hard to get and when there is a cancellation you can fill the slot if you know about it soon enough.

There are two notifiers in this project, Twitter and email, but it's easy to add other implementations.

## Installation

The project comes with a Docker support and is the recommended way to run it. There is a Makefile included for convenience.

1. Build the Docker image and run a container.  Use the Makefile's `make build` then `make start`.
2. Login to the container using `make login`. Running composer and npm there will pull all the compatible library versions.
3. Run `composer install` and `npm install` in the container.
4. Serverless then needs to be configured with AWS credentials.  
   `.node_modules\.bin\serverless config credentials --provider aws --key <key> --secret <secret>`
5. At this point you should be able to run the tests and the AppointmentChecker command locally.

## Deployment
We deploy using *bref* which provides a PHP runtime for AWS Lambda. There is no native runtime provided by AWS. Bref  
runs on top of *serverless* which in turn provides a simple wrapper around AWS CloudFormation. 

1. Get the project ready for a production deployment. This composer command will optimize composer's autoloader for production.
   `composer install --prefer-dist --optimize-autoloader --no-dev`
2. Deploy using serverless. There are some convenience command you can access via composer.
  `composer aws-deploy`
