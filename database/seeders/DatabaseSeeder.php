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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
            ServiceCategory::COL_NAME => 'Dịch vụ cho nam',
        ]);
        File::create([
            File::COL_OWNER_TYPE => ServiceCategory::class,
            File::COL_OWNER_ID => 1,
            File::COL_PATH => getenv('DEFAULT_SERVICE_CATEGORY_IMAGE_URL'),
        ]);
        ServiceCategory::create([
            ServiceCategory::COL_ID => 2,
            ServiceCategory::COL_NAME => 'Dịch vụ cho nữ',
        ]);
        File::create([
            File::COL_OWNER_TYPE => ServiceCategory::class,
            File::COL_OWNER_ID => 2,
            File::COL_PATH => getenv('DEFAULT_SERVICE_CATEGORY_IMAGE_URL'),
        ]);
        ServiceCategory::create([
            ServiceCategory::COL_ID => 3,
            ServiceCategory::COL_NAME => 'Chăm sóc tóc',
            ServiceCategory::COL_PARENT_ID => 1,
        ]);
        File::create([
            File::COL_OWNER_TYPE => ServiceCategory::class,
            File::COL_OWNER_ID => 3,
            File::COL_PATH => getenv('DEFAULT_SERVICE_CATEGORY_IMAGE_URL'),
        ]);
        ServiceCategory::create([
            ServiceCategory::COL_ID => 4,
            ServiceCategory::COL_NAME => 'Chăm sóc da mặt',
            ServiceCategory::COL_PARENT_ID => 1,
        ]);
        File::create([
            File::COL_OWNER_TYPE => ServiceCategory::class,
            File::COL_OWNER_ID => 4,
            File::COL_PATH => getenv('DEFAULT_SERVICE_CATEGORY_IMAGE_URL'),
        ]);
        ServiceCategory::create([
            ServiceCategory::COL_ID => 5,
            ServiceCategory::COL_NAME => 'Chăm sóc tóc',
            ServiceCategory::COL_PARENT_ID => 2,
        ]);
        File::create([
            File::COL_OWNER_TYPE => ServiceCategory::class,
            File::COL_OWNER_ID => 5,
            File::COL_PATH => getenv('DEFAULT_SERVICE_CATEGORY_IMAGE_URL'),
        ]);
        ServiceCategory::create([
            ServiceCategory::COL_ID => 6,
            ServiceCategory::COL_NAME => 'Chăm sóc da mặt',
            ServiceCategory::COL_PARENT_ID => 2,
        ]);
        File::create([
            File::COL_OWNER_TYPE => ServiceCategory::class,
            File::COL_OWNER_ID => 6,
            File::COL_PATH => getenv('DEFAULT_SERVICE_CATEGORY_IMAGE_URL'),
        ]);

        Service::create([
            Service::COL_ID => 1,
            Service::COL_NAME => 'Combo tạo kiểu, cắt, gội, wax',
            Service::COL_DESCRIPTION => 'Combo siêu rẻ, siêu đẹp',
            Service::COL_PRICE => 100000,
            Service::COL_CATEGORY_ID => 3,
        ]);
        File::create([
            File::COL_OWNER_TYPE => Service::class,
            File::COL_OWNER_ID => 1,
            File::COL_PATH => getenv('DEFAULT_SERVICE_IMAGE_URL'),
        ]);
        //---
        Service::create([
            Service::COL_ID => 2,
            Service::COL_NAME => 'Uốn tóc Hàn Quốc',
            Service::COL_DESCRIPTION => 'Đẹp như soái ca Hàn',
            Service::COL_PRICE => 300000,
            Service::COL_CATEGORY_ID => 3,
        ]);
        File::create([
            File::COL_OWNER_TYPE => Service::class,
            File::COL_OWNER_ID => 2,
            File::COL_PATH => getenv('DEFAULT_SERVICE_IMAGE_URL'),
        ]);
        Service::create([
            Service::COL_ID => 3,
            Service::COL_NAME => 'Detox dưỡng da',
            Service::COL_DESCRIPTION => 'Sạch sâu, sáng da',
            Service::COL_PRICE => 150000,
            Service::COL_CATEGORY_ID => 4,
        ]);
        File::create([
            File::COL_OWNER_TYPE => Service::class,
            File::COL_OWNER_ID => 3,
            File::COL_PATH => getenv('DEFAULT_SERVICE_IMAGE_URL'),
        ]);
        //---
        Service::create([
            Service::COL_ID => 4,
            Service::COL_NAME => 'Massage & đắp mặt nạ',
            Service::COL_DESCRIPTION => 'Sảng khoái, đẹp da',
            Service::COL_PRICE => 150000,
            Service::COL_CATEGORY_ID => 4,
        ]);
        File::create([
            File::COL_OWNER_TYPE => Service::class,
            File::COL_OWNER_ID => 4,
            File::COL_PATH => getenv('DEFAULT_SERVICE_IMAGE_URL'),
        ]);
        //-----
        Service::create([
            Service::COL_ID => 5,
            Service::COL_NAME => 'Combo tạo kiểu, dưỡng tóc',
            Service::COL_DESCRIPTION => 'Tái tạo và tạo kiểu tóc đep',
            Service::COL_PRICE => 550000,
            Service::COL_CATEGORY_ID => 5,
        ]);
        File::create([
            File::COL_OWNER_TYPE => Service::class,
            File::COL_OWNER_ID => 5,
            File::COL_PATH => getenv('DEFAULT_SERVICE_IMAGE_URL'),
        ]);
        //------
        Service::create([
            Service::COL_ID => 6,
            Service::COL_NAME => 'Uốn tóc + Dưỡng tóc',
            Service::COL_DESCRIPTION => 'Tóc trở nên bồng bềnh và mềm mượt',
            Service::COL_PRICE => 700000,
            Service::COL_CATEGORY_ID => 5,
        ]);
        File::create([
            File::COL_OWNER_TYPE => Service::class,
            File::COL_OWNER_ID => 6,
            File::COL_PATH => getenv('DEFAULT_SERVICE_IMAGE_URL'),
        ]);
        //------
        Service::create([
            Service::COL_ID => 7,
            Service::COL_NAME => 'Xông hơi, dưỡng da',
            Service::COL_DESCRIPTION => 'Da sạch, trắng sáng',
            Service::COL_PRICE => 250000,
            Service::COL_CATEGORY_ID => 6,
        ]);
        File::create([
            File::COL_OWNER_TYPE => Service::class,
            File::COL_OWNER_ID => 7,
            File::COL_PATH => getenv('DEFAULT_SERVICE_IMAGE_URL'),
        ]);
        //------
        Service::create([
            Service::COL_ID => 8,
            Service::COL_NAME => 'Combo da trắng, sạch, khỏe',
            Service::COL_DESCRIPTION => 'Da trở nên khỏe khoắn, trắng mịn',
            Service::COL_PRICE => 500000,
            Service::COL_CATEGORY_ID => 6,
        ]);
        File::create([
            File::COL_OWNER_TYPE => Service::class,
            File::COL_OWNER_ID => 8,
            File::COL_PATH => getenv('DEFAULT_SERVICE_IMAGE_URL'),
        ]);

        Category::create([
            Category::COL_ID => 1,
            Category::COL_NAME => 'Sản phẩm cho nam',
        ]);
        Category::create([
            Category::COL_ID => 2,
            Category::COL_NAME => 'Sản phẩm cho nữ',
        ]);

        \App\Models\Product::factory(100)->create();
        for ($i = 1; $i <= 50; $i++) {
            File::create([
                File::COL_OWNER_TYPE => Product::class,
                File::COL_OWNER_ID => $i,
                File::COL_PATH => getenv('DEFAULT_PRODUCT_IMAGE_URL'),
            ]);
        }

        $tables = DB::connection()->getDoctrineSchemaManager()->listTableNames();
        foreach ($tables as $table) {
            if (Schema::hasColumn($table, 'id')) {
                $beginInc = DB::table($table)->max('id') + 1;
                DB::statement("ALTER TABLE $table AUTO_INCREMENT=$beginInc");
            }
        }
    }
}
