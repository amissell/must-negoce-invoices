#!/bin/bash

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_step() {
    echo -e "${BLUE}==>${NC} $1"
}

print_success() {
    echo -e "${GREEN}✓${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    print_error "Docker is not installed. Please install Docker first."
    exit 1
fi

# Check if Docker Compose is installed
if ! command -v docker-compose &> /dev/null; then
    print_error "Docker Compose is not installed. Please install Docker Compose first."
    exit 1
fi

print_step "Starting MUST-NEGOCE Invoice System Setup..."
echo ""

# Step 1: Build Docker images
print_step "Step 1/8: Building Docker images (this may take 5-10 minutes on first run)..."
docker-compose build
if [ $? -eq 0 ]; then
    print_success "Docker images built successfully"
else
    print_error "Failed to build Docker images"
    exit 1
fi
echo ""

# Step 2: Start containers
print_step "Step 2/8: Starting Docker containers..."
docker-compose up -d
if [ $? -eq 0 ]; then
    print_success "Containers started successfully"
else
    print_error "Failed to start containers"
    exit 1
fi
echo ""

# Wait for MySQL to be ready
print_step "Waiting for MySQL to be ready..."
sleep 20
print_success "MySQL should be ready now"
echo ""

# Step 3: Create temporary Laravel installation directory
print_step "Step 3/8: Preparing Laravel installation..."
docker-compose exec -u laravel app bash -c "cd /tmp && composer create-project laravel/laravel temp-laravel --prefer-dist --no-interaction"
if [ $? -eq 0 ]; then
    print_success "Laravel downloaded successfully"
else
    print_error "Failed to download Laravel"
    exit 1
fi
echo ""

# Step 4: Move Laravel files to project directory
print_step "Step 4/8: Installing Laravel files..."
docker-compose exec -u laravel app bash -c "shopt -s dotglob && cp -r /tmp/temp-laravel/* /var/www/html/ && rm -rf /tmp/temp-laravel"
if [ $? -eq 0 ]; then
    print_success "Laravel files installed successfully"
else
    print_error "Failed to install Laravel files"
    exit 1
fi
echo ""

# Step 5: Install additional packages
print_step "Step 5/8: Installing PDF library..."
docker-compose exec -u laravel app composer require barryvdh/laravel-dompdf --no-interaction
if [ $? -eq 0 ]; then
    print_success "PDF library installed"
else
    print_error "Failed to install PDF library"
    exit 1
fi
echo ""

# Step 6: Set up environment file
print_step "Step 6/8: Configuring environment..."
if [ -f ".env.docker" ]; then
    cp .env.docker .env
    print_success "Environment file configured"
else
    print_warning ".env.docker not found, keeping Laravel default .env"
fi
echo ""

# Step 7: Generate application key
print_step "Step 7/8: Generating application key..."
docker-compose exec app php artisan key:generate --force
if [ $? -eq 0 ]; then
    print_success "Application key generated"
else
    print_error "Failed to generate application key"
    exit 1
fi
echo ""

# Step 8: Set permissions
print_step "Step 8/8: Setting correct permissions..."
docker-compose exec app chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
docker-compose exec app chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
print_success "Permissions set correctly"
echo ""

# Final message
echo ""
echo -e "${GREEN}================================================${NC}"
echo -e "${GREEN}     Setup Complete! 🎉${NC}"
echo -e "${GREEN}================================================${NC}"
echo ""
echo -e "Your application is now running!"
echo ""
echo -e "${BLUE}Access your application:${NC}"
echo -e "  🌐 Web Interface:   ${GREEN}http://localhost:8080${NC}"
echo -e "  🗄️  phpMyAdmin:      ${GREEN}http://localhost:8081${NC}"
echo -e "     Username: root"
echo -e "     Password: root_password"
echo ""
echo -e "${BLUE}Useful Docker commands:${NC}"
echo -e "  Stop containers:    ${YELLOW}docker-compose down${NC}"
echo -e "  Start containers:   ${YELLOW}docker-compose up -d${NC}"
echo -e "  View logs:          ${YELLOW}docker-compose logs -f${NC}"
echo -e "  Laravel commands:   ${YELLOW}docker-compose exec app php artisan [command]${NC}"
echo -e "  Enter container:    ${YELLOW}docker-compose exec app bash${NC}"
echo ""
echo -e "${BLUE}Next Steps:${NC}"
echo -e "  1. Visit http://localhost:8080 to see Laravel welcome page"
echo -e "  2. Create migrations: ${YELLOW}docker-compose exec app php artisan make:migration [name]${NC}"
echo -e "  3. Run migrations:    ${YELLOW}docker-compose exec app php artisan migrate${NC}"
echo -e "  4. Create models:     ${YELLOW}docker-compose exec app php artisan make:model [name]${NC}"
echo ""