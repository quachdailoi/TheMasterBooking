<?php

class Rebuild {
    public static function run() {
        echo shell_exec(join(" && ", [
            "php artisan config:clear",
            "php artisan cache:clear",
            "php artisan env",
            "php artisan key:generate",
            "php artisan migrate",
            "php artisan db:seed",
            "php artisan storage:link",
            "php artisan passport:install",
        ]));
        echo "Rebuild done!!!";
    }
}

Rebuild::run();
