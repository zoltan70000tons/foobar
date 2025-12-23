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
        'name' => 'CREW',
        'type' => 'cabin',
        'color' => '#1976d2',
        'description' => 'cabin is assigned to crew',
      ],
      [
        'name' => 'CREW',
        'type' => 'booking',
        'color' => '#1976d2',
        'description' => 'booking contains crew members as passengers',
      ],
      [
        'name' => 'PAID',
        'type' => 'booking',
        'color' => '#038521',
        'description' => 'booking is fully paid',
      ],
      [
        'name' => 'ARTIST',
        'type' => 'cabin',
        'color' => '#9c27b0',
        'description' => 'cabin is reserved to artist',
      ],
      [
        'name' => 'PRESS',
        'type' => 'cabin',
        'color' => '#00bcd4',
        'description' => 'cabin is reserved to press members or vendors',
      ],
      [
        'name' => 'PRESS',
        'type' => 'booking',
        'color' => '#00bcd4',
        'description' => 'booking is reserved to press members or vendors',
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
        'name' => 'PARTIAL REVENUE',
        'type' => 'booking',
        'color' => '#00bfa5',
        'description' => 'Partial revenue applied to booking',
      ],
      [
        'name' => 'SPECIAL DISCOUNT',
        'type' => 'booking',
        'color' => '#ff5722',
        'description' => 'Special discount applied to booking',
      ],
      [
        'name' => 'VIP',
        'type' => 'booking',
        'color' => '#ffc107',
        'description' => 'Very Important Person booking',
      ],
      [
        'name' => 'BLACKLISTED',
        'type' => 'customer',
        'color' => '#000000',
        'description' => 'Block customer from booking',
      ]
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
