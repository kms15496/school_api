<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use App\Models\Parents as ParentModel;
use Illuminate\Support\Facades\Hash;

class MigrateParents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-parents';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate parents from students phone (unique by phone)';

    /**
     * Normalize phone number to avoid duplicates caused by formatting.
     */
    private function normalizePhone(?string $phone): string
    {
        $phone = trim((string) $phone);

        if ($phone === '') {
            return '';
        }

        // Remove spaces, dashes, parentheses
        $phone = preg_replace('/[\s\-\(\)]+/', '', $phone);

        // Optional: convert "+95xxxxxxxxx" to "0xxxxxxxxx"
        // Adjust this rule if your system stores +95 differently.
        if (str_starts_with($phone, '+95')) {
            $phone = '0' . substr($phone, 3);
        }

        // Optional: if someone stored "959..." without plus
        if (str_starts_with($phone, '959')) {
            $phone = '0' . substr($phone, 2); // 959 -> 09
        }

        return $phone;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting parents migration...');

        $totalInsertedOrIgnored = 0;

        Student::query()
            ->select(['id', 'phone','father_name'])
            // ->whereNotNull('phone')
            // ->where('phone', '!=', '')
            ->orderBy('id')
            ->chunkById(200, function ($students) use (&$totalInsertedOrIgnored) {

                $rows = [];

                foreach ($students as $student) {
                    $phone = $this->normalizePhone($student->phone);

                    // if ($phone === '') {
                    //     continue;
                    // }

                    $rows[] = [
                        'phone'      => $student->phone,
                        'name'       => $student->father_name ?? 'Parent of Student ' . $student->id,
                        'password'   => Hash::make($student->phone), // or Hash::make('123456')
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if (empty($rows)) {
                    return;
                }

                ParentModel::upsert($rows, ['phone'], []);

                $totalInsertedOrIgnored += count($rows);

                $this->line('Processed chunk: ' . count($rows) . ' rows');
            });

        $this->info("Done. Processed ~{$totalInsertedOrIgnored} rows (duplicates ignored by unique phone).");

        return Command::SUCCESS;
    }
}
