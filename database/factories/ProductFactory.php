<?php

namespace Database\Factories;

use App\Models\Model;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            Product::COL_NAME => $this->faker->userName(),
            Product::COL_PRICE => $this->faker->randomFloat(null, 0, 100),
            Product::COL_QUANTITY => $this->faker->numberBetween(0, 50),
            Product::COL_DESCRIPTION => $this->faker->text(100),
            Product::COL_CATEGORY_ID => $this->faker->numberBetween(1, 2),
        ];
    }
}
