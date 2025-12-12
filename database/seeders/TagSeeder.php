<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tag;

class TagSeeder extends Seeder
{
    public function run()
    {
        $tags = [
            [
                'name' => 'NEW',
                'type' => 'booking',
                'color' => '#FF981A',
                'description' => 'Booking is new and unprocessed',
            ],
            [
                'name' => 'OVERDUE',
                'type' => 'booking',
                'color' => '#FF0000',
                'description' => 'Booking has overdue payments',
            ],
            [
                'name' => 'MISSING PAX',
                'type' => 'booking',
                'color' => '#FFA500',
                'description' => 'Booking is missing required information',
            ],
            [
                'name' => 'STAFF',
                'type' => 'cabin',
                'color' => '#1976d2',
                'description' => 'cabin is assigned to staff',
            ],
            [
                'name' => 'ARTIST',
                'type' => 'cabin',
                'color' => '#9c27b0',
                'description' => 'cabin is assigned to artist',
            ],
            [
                'name' => 'PRESS',
                'type' => 'cabin',
                'color' => '#00bcd4',
                'description' => '',
            ],
            [
                'name' => 'PAID',
                'type' => 'booking',
                'color' => '#2196f3',
                'description' => 'booking is fully paid',
            ],
            [
                'name' => 'IN MANIFEST',
                'type' => 'booking',
                'color' => '#673ab7',
                'description' => 'booking is in the manifest file',
            ],
            [
                'name' => 'NON-REV',
                'type' => 'cabin',
                'color' => '#607d8b',
                'description' => 'cabin is assigned to non-revenue guest',
            ],
            [
                'name' => 'POTENTIAL NOISE',
                'type' => 'cabin',
                'color' => '#ff9800',
                'description' => 'potential noise issue',
            ],
            [
                'name' => 'RCCL',
                'type' => 'cabin',
                'color' => '#3f51b5',
                'description' => 'For RCCL use only',
            ],
            [
                'name' => 'BLACKLISTED',
                'type' => 'customer',
                'color' => '#000000',
                'description' => 'Block customer from booking',
            ],
        ];

        foreach ($tags as $tag) {
            Tag::firstOrCreate(
                [
                    'name' => $tag['name'],
                    'type' => $tag['type']
                ],
                [
                    'color' => $tag['color'],
                    'description' => $tag['description'],
                    'is_system' => true,
                ]
            );
        }
    }
}
