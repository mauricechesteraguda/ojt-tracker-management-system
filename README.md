# OJT Tracker Management System (Batangas State University)

## Project Overview

This repository contains the OJT Tracker Management System, a research project designed to streamline the tracking and management of On-the-Job Training (OJT) activities for college students at Batangas State University. This system facilitates the monitoring, reporting, and evaluation of OJT performances for both students and supervisors, helping maintain an organized and efficient workflow throughout the OJT program.
Key Features

    Student Management: Manage student profiles, OJT schedules, and track progress.
    Supervisor Access: Allow supervisors to review and update students' performance, attendance, and provide feedback.
    Task & Report Submission: Enables students to submit tasks, progress reports, and final evaluations online.
    Evaluation System: Provide detailed evaluations for student progress and completion.
    Notification System: Email and dashboard notifications for deadlines, reports, and evaluations.

## Tech Stack

## Frontend:

    CoreUI (ReactJS): The frontend of the system is built using CoreUI for React, a powerful open-source dashboard template for React.js. It provides an intuitive user interface with a modern and responsive design.
    React Router: For routing and managing the various views and states of the application.
    Axios: For handling HTTP requests between the frontend and backend.

## Backend:

    Laravel: The backend is developed using Laravel, a PHP framework that provides a robust API for managing student data, evaluations, and tasks. It also handles authentication and manages database operations using Eloquent ORM.

## Database:

    MySQL: The system uses a MySQL database to store student data, supervisor feedback, task submissions, and evaluation results.

## Installation & Setup

## Prerequisites

## Ensure the following software is installed on your local machine:

    Node.js (v14 or later)
    Composer (for Laravel dependencies)
    MySQL (or any supported database system)
    PHP (v7.3 or later)
    NPM/Yarn (for React dependencies)

## Backend (Laravel) Setup

Clone the repository:

    

    git clone https://github.com/yourusername/ojt-tracker.git
    cd ojt-tracker/backend

## Install dependencies:

    composer install

## Set up environment:

Create a .env file based on .env.example:

    cp .env.example .env

    Configure the database settings and other environment variables in the .env file.

Generate the application key:

    php artisan key:generate
    
Run migrations to set up the database tables:

    php artisan migrate

Serve the application:

    php artisan serve

The backend API will now be accessible at http://localhost:8000.

## Frontend (ReactJS) Setup

Navigate to the frontend directory:
    
    cd ../frontend

Install dependencies:

    npm install

Set up environment variables:

    Create a .env file in the frontend folder.
    Set the API URL (provided by Laravel backend):



    REACT_APP_API_URL=http://localhost:8000/api

Start the development server:

    npm start
    
    The frontend will now be accessible at http://localhost:3000.

Usage

    Login: Students and supervisors will need to authenticate using their credentials.
    Dashboard: Students will see their assigned tasks, submission deadlines, and progress tracking.
    Task Management: Supervisors can assign tasks, review submissions, and provide feedback directly from their dashboard.
    Evaluation: At the end of the OJT, supervisors can submit a final evaluation which students can review.

## Project Structure

/backend

  ├── app/               # Laravel application files
  
  ├── database/          # Migrations and seeders
  
  ├── routes/            # API routes
  
  └── ...                # Other Laravel-specific directories

/frontend

  ├── src/               # ReactJS application files
  
  ├── public/            # Static assets
  
  └── ...                # Other frontend-specific directories

## API Documentation

The backend exposes a RESTful API for communication with the frontend. Below are some of the key API endpoints:

    Authentication:
        POST /api/login - Authenticate user.
        POST /api/logout - Logout user.

    Student Management:
        GET /api/students - Get all students.
        POST /api/students - Add a new student.

    Task Management:
        GET /api/tasks - Get all tasks for a student.
        POST /api/tasks - Create a new task.
        PUT /api/tasks/{id} - Update a task.
        DELETE /api/tasks/{id} - Delete a task.

    Evaluation:
        POST /api/evaluations - Submit a new evaluation for a student.

More detailed documentation can be found in the API Documentation (available soon).
Future Enhancements

    Mobile Responsiveness: Improving the mobile user experience.
    Advanced Reporting: Enhanced reporting features for students and supervisors to analyze OJT progress and performance metrics.
    Role-Based Access Control: More granular access control for admins, supervisors, and students.

## Contribution

This project is open for collaboration. If you wish to contribute:

    Fork the repository.
    Create a feature branch (git checkout -b feature/your-feature-name).
    Commit your changes (git commit -m 'Add your feature').
    Push to the branch (git push origin feature/your-feature-name).
    Open a pull request.

## License

This project is licensed under the [MIT License](LICENSE).

## Contact

For any questions or inquiries, please reach out to https://www.linkedin.com/in/maurice-chester-aguda-09b93981/.

## Support

If you find this project helpful and would like to support its ongoing development, consider buying me a coffee! Your support helps me keep working on this project and developing more features.

[![Buy Me a Coffee](https://www.buymeacoffee.com/assets/img/custom_images/yellow_img.png)](https://www.buymeacoffee.com/mauriceague)

    
