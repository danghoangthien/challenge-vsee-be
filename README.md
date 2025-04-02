# Lounge Queue API

A Laravel-based API for managing a lounge queue system, allowing providers to manage visitor examinations and queue management.

## Features

- **Visitor Queue Management**
  - Join/exit queue functionality
  - Real-time queue position updates
  - Estimated wait time calculations
  - Queue position tracking

- **Examination Management**
  - Provider-visitor examination workflow
  - Examination status tracking
  - Real-time examination updates
  - Examination completion handling

- **Real-time Updates**
  - WebSocket-based event broadcasting
  - Private channels for secure communication
  - Real-time queue position updates
  - Examination status notifications

- **Authentication & Authorization**
  - JWT-based authentication
  - Role-based access control
  - Provider and visitor specific endpoints
  - Secure private channels

## Tech Stack

- **Framework**: Laravel 10.x
- **Database**: 
  - MySQL (Primary)
  - MongoDB (Queue Management)
- **Real-time**: Pusher
- **Authentication**: JWT
- **API Documentation**: OpenAPI/Swagger

## Prerequisites

- PHP 8.1 or higher
- Composer
- MySQL
- MongoDB
- Pusher account (for real-time features)

## Installation

1. Clone the repository:
```bash
git clone <repository-url>
cd lounge-queue-api
```

2. Install dependencies:
```bash
composer install
```

3. Copy environment file:
```bash
cp .env.example .env
```

4. Generate application key:
```bash
php artisan key:generate
```

5. Configure your environment variables in `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lounge_queue
DB_USERNAME=your_username
DB_PASSWORD=your_password

MONGODB_URI=your_mongodb_uri

PUSHER_APP_ID=your_pusher_app_id
PUSHER_APP_KEY=your_pusher_key
PUSHER_APP_SECRET=your_pusher_secret
PUSHER_APP_CLUSTER=your_pusher_cluster

JWT_SECRET=your_jwt_secret
```

6. Run MySQL migrations:
```bash
php artisan migrate
```

7. Seed the database with initial data:
```bash
php artisan db:seed
```

This will create:
- Default admin user
- Sample providers and visitors
- Initial system configurations

8. Setup MongoDB collections and indexes:
```bash
php artisan mongodb:setup
```

This command will:
- Create the required MongoDB collections
- Set up indexes for better query performance:
  - Unique index on `visitor_id`
  - Index on `position` for queue management
  - Index on `joined_at` for time-based queries

## API Documentation

The API documentation is available at `/api/documentation` when running the application.

## Development

1. Start the development server:
```bash
php artisan serve
```

2. Run tests:
```bash
php artisan test
```

## Deployment

The application is configured for Heroku deployment. To deploy:

1. Create a new Heroku app:
```bash
heroku create your-app-name
```

2. Configure environment variables:
```bash
heroku config:set APP_ENV=production
heroku config:set APP_DEBUG=false
# Set other required environment variables
```

3. Deploy:
```bash
git push heroku main
```

4. Run migrations:
```bash
heroku run php artisan migrate
```

## API Endpoints

### Authentication
- `POST /api/login` - Authenticate user
- `POST /api/logout` - Logout user
- `POST /api/refresh` - Refresh JWT token

### Queue Management
- `POST /api/v1/lounge/queue/enqueue` - Join queue
- `POST /api/v1/lounge/queue/exit` - Exit queue
- `GET /api/v1/lounge/queue/position` - Get queue position

### Examination
- `POST /api/v1/examination/pickup` - Provider pickup visitor
- `POST /api/v1/examination/complete` - Complete examination
- `GET /api/v1/examination/current` - Get current examination

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a new Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.
