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

        $defaultPassword = Hash::make('icec321'); // or Hash::make('123456')
        $totalParentsProcessed = 0;
        $totalStudentsAttached = 0;

        Student::query()
            ->select(['id', 'phone', 'father_name', 'parent_id'])
            ->orderBy('id')
            ->chunkById(200, function ($students) use ($defaultPassword, &$totalParentsProcessed, &$totalStudentsAttached) {
                $parentRowsByPhone = [];
                $studentPhones = [];

                foreach ($students as $student) {
                    $phone = $this->normalizePhone($student->phone);

                    if ($phone === '') {
                        continue;
                    }

                    if (!isset($parentRowsByPhone[$phone])) {
                        $parentRowsByPhone[$phone] = [
                            'phone' => $phone,
                            'name' => $student->father_name ?: 'Parent of Student ' . $student->id,
                            'password' => $defaultPassword,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }

                    $studentPhones[$student->id] = $phone;
                }

                if (empty($parentRowsByPhone)) {
                    return;
                }

                ParentModel::insertOrIgnore(array_values($parentRowsByPhone));
                $totalParentsProcessed += count($parentRowsByPhone);

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

                $this->line('Processed chunk: ' . count($parentRowsByPhone) . ' parent phones, attached students so far: ' . $totalStudentsAttached);
            });

        $this->info("Done. Processed ~{$totalParentsProcessed} parent phone rows and attached {$totalStudentsAttached} students.");

        return Command::SUCCESS;
    }
}
