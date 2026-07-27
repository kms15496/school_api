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

        $parentRowsByPhone = [];
        $totalStudentsAttached = 0;

        Student::query()
            ->select(['id', 'phone', 'father_name'])
            ->orderBy('id')
            ->chunkById(200, function ($students) use (&$parentRowsByPhone) {
                foreach ($students as $student) {
                    $phone = $this->normalizePhone($student->phone);

                    if ($phone === '' || isset($parentRowsByPhone[$phone])) {
                        continue;
                    }

                    $parentRowsByPhone[$phone] = [
                        'phone' => $phone,
                        'name' => $student->father_name ?: 'Parent of Student ' . $student->id,
                        'password' => Hash::make('icec321'), // or Hash::make('123456')
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            });

        foreach (array_chunk($parentRowsByPhone, 200) as $rows) {
            ParentModel::insertOrIgnore(array_values($rows));
            $this->line('Processed parent rows: ' . count($rows));
        }

        Student::query()
            ->select(['id', 'phone', 'parent_id'])
            ->orderBy('id')
            ->chunkById(200, function ($students) use (&$totalStudentsAttached) {
                $studentPhones = [];

                foreach ($students as $student) {
                    $phone = $this->normalizePhone($student->phone);

                    if ($phone === '') {
                        continue;
                    }

                    $studentPhones[$student->id] = $phone;
                }

                if (empty($studentPhones)) {
                    return;
                }

                $parentsByPhone = ParentModel::whereIn('phone', array_unique($studentPhones))
                    ->get()
                    ->keyBy('phone');

                foreach ($students as $student) {
                    $phone = $studentPhones[$student->id] ?? null;
                    $parent = $phone ? $parentsByPhone->get($phone) : null;

                    if (!$parent || $student->parent_id === $parent->id) {
                        continue;
                    }

                    $student->parent_id = $parent->id;
                    $student->save();
                    $totalStudentsAttached++;
                }

                $this->line('Attached students so far: ' . $totalStudentsAttached);
            });

        $this->info('Done. Processed ' . count($parentRowsByPhone) . " unique parent phones and attached {$totalStudentsAttached} students.");

        return Command::SUCCESS;
    }
}
