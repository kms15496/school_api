<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use App\Models\Parents as ParentModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MigrateParents extends Command
{
    protected $signature = 'app:migrate-parents';

    protected $description = 'Migrate parents from students phone';

    public function handle()
    {
        $this->info('Starting parents migration...');

        $processed = 0;

        Student::query()
            ->select(['id', 'phone', 'father_name'])
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('parents')
                    ->whereColumn('parents.phone', 'students.phone');
            })
            ->orderBy('id')
            ->chunkById(200, function ($students) use (&$processed) {

                $rows = [];
                $usedPhones = [];

                foreach ($students as $student) {
                    $phone = trim($student->phone);

                    if ($phone === '') {
                        continue;
                    }

                    // skip duplicate phone inside same chunk
                    if (isset($usedPhones[$phone])) {
                        continue;
                    }

                    $usedPhones[$phone] = true;

                    $rows[] = [
                        'phone'      => $phone,
                        'name'       => $student->father_name ?: 'Parent of Student ' . $student->id,
                        'password'   => Hash::make('pnpt123'),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if (!empty($rows)) {
                    ParentModel::insertOrIgnore($rows);

                    $processed += count($rows);

                    $this->line('Processed: ' . count($rows));
                }
            });

        $this->info("Done. Processed: {$processed}");

        return Command::SUCCESS;
    }
}
