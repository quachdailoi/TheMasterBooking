<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\File;
use App\Models\Product;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Store;
use App\Models\User;
use Exception;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        try {
        } catch (Exception $ex) {
        }
        Role::create([
            Role::COL_ID => 1,
            Role::COL_NAME => 'CUSTOMER'
        ]);
        Role::create([
            Role::COL_ID => 2,
            Role::COL_NAME => 'MANAGER'
        ]);
        Role::create([
            Role::COL_ID => 3,
            Role::COL_NAME => 'ADMIN'
        ]);
        Store::create([
            Store::COL_ID => 1,
            Store::COL_PHONE => '+0123456789',
            Store::COL_NAME => '30Shine LVV',
            Store::COL_ADDRESS => '123 Le Van Viet',
            Store::COL_WORK_SCHEDULE => [
                Store::MODAY => [
                    Store::VAL_OPEN_AT => '07:30',
                    Store::VAL_CLOSE_AT => '22:00',
                ],
                Store::TUESDAY => [
                    Store::VAL_OPEN_AT => '07:30',
                    Store::VAL_CLOSE_AT => '22:00',
                ],
                Store::WEDNESDAY => [
                    Store::VAL_OPEN_AT => '07:30',
                    Store::VAL_CLOSE_AT => '22:00',
                ],
                Store::THURSDAY => [
                    Store::VAL_OPEN_AT => '07:30',
                    Store::VAL_CLOSE_AT => '22:00',
                ],
                Store::FRIDAY => [
                    Store::VAL_OPEN_AT => '07:30',
                    Store::VAL_CLOSE_AT => '22:00',
                ],
                Store::SATURDAY => [
                    Store::VAL_OPEN_AT => '08:00',
                    Store::VAL_CLOSE_AT => '23:00',
                ],
                Store::SUNDAY => [
                    Store::VAL_OPEN_AT => '08:00',
                    Store::VAL_CLOSE_AT => '23:00',
                ]
            ]
        ]);
        File::create([
            File::COL_OWNER_TYPE => Store::class,
            File::COL_OWNER_ID => 1,
            File::COL_PATH => getenv('DEFAULT_STORE_IMAGE_URL'),
        ]);
        User::create([
            User::COL_ID => 1,
            User::COL_NAME => 'customer',
            User::COL_EMAIL => 'customer@gmail.com',
            User::COL_GENDER => 1,
            User::COL_PASSWORD => '$2a$12$cmUxGn156Fj//2kKrredDO34iqJLXVEtghMKEgkYldNx1Li8AuuP2',//customer123
            User::COL_BIRTHDAY => '2000/01/01',
            User::COL_ROLE_ID => User::CUSTOMER_ROLE_ID
        ]);
        File::create([
            File::COL_OWNER_TYPE => User::class,
            File::COL_OWNER_ID => 1,
            File::COL_PATH => getenv('DEFAULT_USER_AVATAR_URL'),
        ]);
        //-------
        User::create([
            User::COL_ID => 2,
            User::COL_NAME => 'manager',
            User::COL_EMAIL => 'manager@gmail.com',
            User::COL_GENDER => 1,
            User::COL_PASSWORD => '$2a$12$gqcE3BRUShpqiO4C04mtkuPVIUoxWsUEbzvYBoa9XnvcCzcgbPfv2',//manager123
            User::COL_BIRTHDAY => '2000/01/01',
            User::COL_ROLE_ID => User::MANAGER_ROLE_ID
        ]);
        File::create([
            File::COL_OWNER_TYPE => User::class,
            File::COL_OWNER_ID => 2,
            File::COL_PATH => getenv('DEFAULT_USER_AVATAR_URL'),
        ]);
        //--------
        User::create([
            User::COL_ID => 3,
            User::COL_NAME => 'admin',
            User::COL_EMAIL => 'admin@gmail.com',
            User::COL_GENDER => 1,
            User::COL_PASSWORD => '$2a$12$c0clL6UNsNcuY8aNtzuiF.BH2UZaDLgE4YNZdKrWeIRaK4t2brixy',//admin123
            User::COL_BIRTHDAY => '2000/01/01',
            User::COL_ROLE_ID => User::ADMIN_ROLE_ID
        ]);
        File::create([
            File::COL_OWNER_TYPE => User::class,
            File::COL_OWNER_ID => 3,
            File::COL_PATH => getenv('DEFAULT_USER_AVATAR_URL'),
        ]);
        //---------
        User::create([
            User::COL_ID => 4,
            User::COL_NAME => 'customer2',
            User::COL_EMAIL => 'customer2@gmail.com',
            User::COL_GENDER => 1,
            User::COL_PASSWORD => '$2a$12$cmUxGn156Fj//2kKrredDO34iqJLXVEtghMKEgkYldNx1Li8AuuP2',//customer123
            User::COL_BIRTHDAY => '2000/01/01',
            User::COL_ROLE_ID => User::CUSTOMER_ROLE_ID
        ]);
        File::create([
            File::COL_OWNER_TYPE => User::class,
            File::COL_OWNER_ID => 4,
            File::COL_PATH => getenv('DEFAULT_USER_AVATAR_URL'),
        ]);
        //---------
        User::create([
            User::COL_ID => 5,
            User::COL_NAME => 'customer3',
            User::COL_EMAIL => 'customer3@gmail.com',
            User::COL_GENDER => 1,
            User::COL_PASSWORD => '$2a$12$cmUxGn156Fj//2kKrredDO34iqJLXVEtghMKEgkYldNx1Li8AuuP2',//customer123
            User::COL_BIRTHDAY => '2000/01/01',
            User::COL_ROLE_ID => User::CUSTOMER_ROLE_ID
        ]);
        File::create([
            File::COL_OWNER_TYPE => User::class,
            File::COL_OWNER_ID => 5,
            File::COL_PATH => getenv('DEFAULT_USER_AVATAR_URL'),
        ]);
        //---------
        ServiceCategory::create([
            ServiceCategory::COL_ID => 1,
            ServiceCategory::COL_NAME => 'Chăm sóc tóc',
        ]);
        File::create([
            File::COL_OWNER_TYPE => ServiceCategory::class,
            File::COL_OWNER_ID => 1,
            File::COL_PATH => getenv('DEFAULT_SERVICE_CATEGORY_IMAGE_URL'),
        ]);

        Service::create([
            Service::COL_ID => 1,
            Service::COL_NAME => 'Cắt tóc nam',
            Service::COL_DESCRIPTION => 'Siêu đẹp',
            Service::COL_PRICE => 100000,
            Service::COL_CATEGORY_ID => 1,
        ]);
        File::create([
            File::COL_OWNER_TYPE => Service::class,
            File::COL_OWNER_ID => 1,
            File::COL_PATH => getenv('DEFAULT_SERVICE_IMAGE_URL'),
        ]);

        Category::create([
            Category::COL_ID => 1,
            Category::COL_NAME => 'Sản phẩm cho nam',
            Category::COL_STORE_ID => 1,
        ]);

        \App\Models\Product::factory(50)->create();
        for ($i = 1; $i <= 50; $i++) {
            File::create([
                File::COL_OWNER_TYPE => Product::class,
                File::COL_OWNER_ID => $i,
                File::COL_PATH => getenv('DEFAULT_PRODUCT_IMAGE_URL'),
            ]);
        }
    }
}
