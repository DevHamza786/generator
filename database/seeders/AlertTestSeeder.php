<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Alert;
use App\Models\Generator;
use App\Models\Client;
use Carbon\Carbon;

class AlertTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some existing generators and clients
        $generators = Generator::take(3)->get();
        $clients = Client::take(2)->get();

        if ($generators->isEmpty() || $clients->isEmpty()) {
            $this->command->info('No generators or clients found. Please run other seeders first.');
            return;
        }

        // Create sample alerts
        $alerts = [
            [
                'generator_id' => $generators->first()->generator_id,
                'client_id' => $clients->first()->id,
                'sitename' => 'Test Site A',
                'type' => 'fuel_low',
                'title' => 'Low Fuel Level Alert',
                'message' => 'Generator ' . $generators->first()->generator_id . ' fuel level is at 15% (below 20% threshold)',
                'data' => [
                    'fuel_level' => 15,
                    'threshold' => 20,
                    'log_timestamp' => now()->toISOString()
                ],
                'severity' => 'high',
                'status' => 'active',
                'triggered_at' => now()->subMinutes(30),
            ],
            [
                'generator_id' => $generators->skip(1)->first()->generator_id,
                'client_id' => $clients->first()->id,
                'sitename' => 'Test Site B',
                'type' => 'battery_voltage',
                'title' => 'Battery Voltage Alert',
                'message' => 'Generator ' . $generators->skip(1)->first()->generator_id . ' battery voltage has been constant at 11V for 30+ minutes',
                'data' => [
                    'battery_voltage' => 11.0,
                    'duration_minutes' => 30,
                    'log_count' => 5,
                    'first_log' => now()->subMinutes(35)->toISOString(),
                    'last_log' => now()->toISOString()
                ],
                'severity' => 'medium',
                'status' => 'active',
                'triggered_at' => now()->subMinutes(35),
            ],
            [
                'generator_id' => $generators->last()->generator_id,
                'client_id' => $clients->last()->id,
                'sitename' => 'Test Site C',
                'type' => 'line_current',
                'title' => 'High Line Current Alert',
                'message' => 'Generator ' . $generators->last()->generator_id . ' line current is 1.45A (above 1.20A threshold). Running time will be managed.',
                'data' => [
                    'line_current' => 1.45,
                    'threshold' => 1.20,
                    'log_timestamp' => now()->toISOString()
                ],
                'severity' => 'medium',
                'status' => 'active',
                'triggered_at' => now()->subMinutes(10),
            ],
        ];

        foreach ($alerts as $alertData) {
            Alert::create($alertData);
        }

        $this->command->info('Created ' . count($alerts) . ' test alerts');
    }
}
