<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Puma Speedcut
        Product::create([
            'name' => 'Puma Speedcut',
            'slug' => 'puma-speedcut',
            'description' => 'The Puma Speedcut offers exceptional comfort and traction for your daily runs. Lightweight and breathable with durable soles.',
            'price' => 5899.00,
            
            // UPDATED FIELDS TO MATCH MIGRATION:
            'quantity' => 10,  // Fixed: Was 'stock'
            'image' => '/img/products-imgs/puma.png', // Fixed: Was 'image_url'
            'brand' => 'Puma', // New required field
            'category' => 'Men', // New required field
            
            'is_active' => true,
        ]);

        // 2. Travis Scott Low Fragments
        Product::create([
            'name' => 'Travis Scott Low Fragments',
            'slug' => 'travis-scott-low-fragments',
            'description' => 'A collaboration between Travis Scott, Fragment Design, and Jordan Brand. Features a premium leather upper in white, black, and royal blue.',
            'price' => 67899.00,
            'quantity' => 5,
            'image' => '/img/products-imgs/nike.png',
            'brand' => 'Nike',
            'category' => 'Men',
            'is_active' => true,
        ]);

        // 3. Adidas Samba
        Product::create([
            'name' => 'Adidas Samba',
            'slug' => 'adidas-samba',
            'description' => 'A classic indoor soccer shoe turned street style icon. Features a leather upper with suede overlays and a gum rubber cupsole.',
            'price' => 8899.00,
            'quantity' => 20,
            'image' => '/img/products-imgs/adidas.png',
            'brand' => 'Adidas',
            'category' => 'Women',
            'is_active' => true,
        ]);

        // 4. Converse Allstar Highcut
        Product::create([
            'name' => 'Converse Allstar Highcut',
            'slug' => 'converse-allstar-highcut',
            'description' => 'The definitive sneaker. The Chuck Taylor All Star High Top is the most iconic sneaker in the world, recognized for its unmistakable silhouette.',
            'price' => 3899.00,
            'quantity' => 15,
            'image' => '/img/products-imgs/converse.png',
            'brand' => 'Converse',
            'category' => 'Women',
            'is_active' => true,
        ]);

        // 5. Adidas Ultraboost 23
        Product::create([
            'name' => 'Adidas Ultraboost 23',
            'slug' => 'adidas-ultraboost-23',
            'description' => 'Experience epic energy with the new Ultraboost Light, our lightest Ultraboost ever. The magic lies in the Light BOOST midsole.',
            'price' => 10999.00,
            'quantity' => 8,
            'image' => '/img/products-imgs/ultraboost.png',
            'brand' => 'Adidas',
            'category' => 'Kids',
            'is_active' => true,
        ]);

        // 6. Nike Air Force 1 '07
        Product::create([
            'name' => "Nike Air Force 1 '07",
            'slug' => 'nike-air-force-1-07',
            'description' => 'The radiance lives on in the Nike Air Force 1 \'07, the b-ball OG that puts a fresh spin on what you know best.',
            'price' => 6499.00,
            'quantity' => 12,
            'image' => '/img/products-imgs/nikeAF.png',
            'brand' => 'Nike',
            'category' => 'Men',
            'is_active' => true,
        ]);

        // 7. Adidas Superstar Classic
        Product::create([
            'name' => 'Adidas Superstar Classic',
            'slug' => 'adidas-superstar-classic',
            'description' => 'Originally made for basketball courts in the \'70s. Celebrated by hip hop royalty in the \'80s. The adidas Superstar shoe is now a lifestyle staple.',
            'price' => 5299.00,
            'quantity' => 18,
            'image' => '/img/products-imgs/adidasAS.png',
            'brand' => 'Adidas',
            'category' => 'Women',
            'is_active' => true,
        ]);

        // 8. Puma RS-X3
        Product::create([
            'name' => 'Puma RS-X3',
            'slug' => 'puma-rs-x3',
            'description' => 'X marks extreme. Exaggerated. Remixed. X3 takes things to a new level: cubed, enhanced, extra.',
            'price' => 5699.00,
            'quantity' => 7,
            'image' => '/img/products-imgs/pumars.png',
            'brand' => 'Puma',
            'category' => 'Men',
            'is_active' => true,
        ]);

        // 9. Nike Air Max 270
        Product::create([
            'name' => 'Nike Air Max 270',
            'slug' => 'nike-air-max-270',
            'description' => 'Nike\'s first lifestyle Air Max brings you style, comfort and big attitude in the Nike Air Max 270.',
            'price' => 6299.00,
            'quantity' => 25,
            'image' => '/img/products-imgs/airmax.png',
            'brand' => 'Nike',
            'category' => 'Kids',
            'is_active' => true,
        ]);
    }
}