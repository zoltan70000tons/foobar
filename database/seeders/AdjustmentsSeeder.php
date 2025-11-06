<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Adjustment;

class AdjustmentsSeeder extends Seeder
{
  public function run()
  {
    // Discounts
    Adjustment::updateOrCreate(
      ['code' => 'SINGLE_TICKET_FEE', 'event_id' => 1],
      [
        'type' => 'ADDON',
        'operation' => 'FIXED',
        'value' => 100.0,
        'restrictions' => null,
        'system' => true,
      ]
    );

    Adjustment::updateOrCreate(
      ['code' => 'PAID_IN_FULL', 'event_id' => 1],
      [
        'type' => 'DISCOUNT',
        'operation' => 'PERCENTAGE',
        'value' => 5.0,
        'restrictions' => null,
        'system' => true,
      ]
    );

    // Membership discounts
    Adjustment::updateOrCreate(
      ['code' => 'MEMBERSHIP_SILVER', 'event_id' => 1],
      [
        'type' => 'DISCOUNT',
        'operation' => 'PERCENTAGE',
        'value' => 5.0,
        'restrictions' => [
          'logic' => 'OR',
          'conditions' => [
            [
              'model' => 'event',
              'property' => 'status',
              'operator' => 'equals',
              'value' => 'PRE-SALE',
            ],
          ],
          'behavior' => 'APPLY_ONLY_IF_MATCHED',
        ],
        'system' => true,
      ]
    );

    Adjustment::updateOrCreate(
      ['code' => 'MEMBERSHIP_SILVER_PLUS', 'event_id' => 1],
      [
        'type' => 'DISCOUNT',
        'operation' => 'PERCENTAGE',
        'value' => 5.0,
        'restrictions' => [
          'logic' => 'OR',
          'conditions' => [
            [
              'model' => 'event',
              'property' => 'status',
              'operator' => 'equals',
              'value' => 'PRE-SALE',
            ],
          ],
          'behavior' => 'APPLY_ONLY_IF_MATCHED',
        ],
        'system' => true,
      ]
    );

    Adjustment::updateOrCreate(
      ['code' => 'MEMBERSHIP_GOLD', 'event_id' => 1],
      [
        'type' => 'DISCOUNT',
        'operation' => 'PERCENTAGE',
        'value' => 10.0,
        'restrictions' => [
          'logic' => 'OR',
          'conditions' => [
            [
              'model' => 'event',
              'property' => 'status',
              'operator' => 'equals',
              'value' => 'PRE-SALE',
            ],
          ],
          'behavior' => 'APPLY_ONLY_IF_MATCHED',
        ],
        'system' => true,
      ]
    );

    Adjustment::updateOrCreate(
      ['code' => 'MEMBERSHIP_GOLD_PLUS', 'event_id' => 1],
      [
        'type' => 'DISCOUNT',
        'operation' => 'PERCENTAGE',
        'value' => 10.0,
        'restrictions' => [
          'logic' => 'OR',
          'conditions' => [
            [
              'model' => 'event',
              'property' => 'status',
              'operator' => 'equals',
              'value' => 'PRE-SALE',
            ],
          ],
          'behavior' => 'APPLY_ONLY_IF_MATCHED',
        ],
        'system' => true,
      ]
    );

    Adjustment::updateOrCreate(
      ['code' => 'MEMBERSHIP_BLACK', 'event_id' => 1],
      [
        'type' => 'DISCOUNT',
        'operation' => 'PERCENTAGE',
        'value' => 10.0,
        'restrictions' => [
          'logic' => 'OR',
          'conditions' => [
            [
              'model' => 'event',
              'property' => 'status',
              'operator' => 'equals',
              'value' => 'PRE-SALE',
            ],
          ],
          'behavior' => 'APPLY_ONLY_IF_MATCHED',
        ],
        'system' => true,
      ]
    );

    // Addons
    Adjustment::updateOrCreate(
      ['code' => 'CHOOSE_YOUR_CABIN', 'event_id' => 1],
      [
        'type' => 'ADDON',
        'operation' => 'FIXED',
        'value' => 100.0,
        'restrictions' => [
          'logic' => 'OR',
          'conditions' => [
            [
              'model' => 'cabin',
              'property' => 'category_name',
              'operator' => 'equals',
              'value' => "Owner's Suite",
            ],
            [
              'model' => 'cabin',
              'property' => 'category_name',
              'operator' => 'equals',
              'value' => 'Grand Suite - 1 Bedroom',
            ],
            [
              'model' => 'cabin',
              'property' => 'category_name',
              'operator' => 'equals',
              'value' => 'Grand Suite - 2 Bedroom',
            ],
          ],
          'behavior' => 'APPLY_UNLESS_MATCHED',
        ],
        'system' => true,
      ]
    );

    Adjustment::updateOrCreate(
      ['code' => 'CARBON_OFFSET_I', 'event_id' => 1],
      [
        'type' => 'ADDON',
        'operation' => 'FIXED',
        'value' => 37.0,
        'restrictions' => null,
        'system' => true,
      ]
    );

    Adjustment::updateOrCreate(
      ['code' => 'CARBON_OFFSET_O', 'event_id' => 1],
      [
        'type' => 'ADDON',
        'operation' => 'FIXED',
        'value' => 37.0,
        'restrictions' => null,
        'system' => true,
      ]
    );

    Adjustment::updateOrCreate(
      ['code' => 'CARBON_OFFSET_B', 'event_id' => 1],
      [
        'type' => 'ADDON',
        'operation' => 'FIXED',
        'value' => 38.0,
        'restrictions' => null,
        'system' => true,
      ]
    );

    Adjustment::updateOrCreate(
      ['code' => 'CARBON_OFFSET_S', 'event_id' => 1],
      [
        'type' => 'ADDON',
        'operation' => 'FIXED',
        'value' => 49.0,
        'restrictions' => null,
        'system' => true,
      ]
    );

    Adjustment::updateOrCreate(
      ['code' => 'TAX', 'event_id' => 1],
      [
        'type' => 'ADDON',
        'operation' => 'FIXED',
        'value' => 494.0,
        'restrictions' => null,
        'system' => true,
      ]
    );
  }
}
