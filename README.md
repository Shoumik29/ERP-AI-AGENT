How to use locally?

Details set up instructions:

1. Clone the project
2. Navigate to the project's root directory using terminal
3. Create .env file by 'cp .env.example .env' command
4. Execute command 'composer install' to install dependencies
5. Set application key by command 'php artisan key:generate --ansi'
6. Go to 'app/Controllers/Controller.php' and add your huggingface token to use the APIs
6. Start Artisan server by 'php artisan serve' command

Here is a demo link: https://erpaiagent-llgfa0c1c-shoumik29s-projects.vercel.app