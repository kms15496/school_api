<?php

namespace App\Console\Commands;

use App\Models\Parents;
use App\Models\Student;
use Illuminate\Console\Command;

class AttachParentToStudent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:attach-parent-to-student';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Parents::chunk(200, function ($parents) {

            foreach ($parents as $parent) {

                $phone = trim($parent->phone);

                if ($phone === '') {
                    continue;
                }

                // Get ALL students with this phone
                $students = Student::where('phone', $phone)->get();

                if ($students->isEmpty()) {
                    $this->info("No student found with phone {$phone} for Parent ID {$parent->id}");
                    continue;
                }

                foreach ($students as $student) {

                    // Skip if already linked
                    if ($student->parent_id === $parent->id) {
                        continue;
                    }

                    $student->parent_id = $parent->id;
                    $student->save();

                    $this->info(
                        "Attached Parent ID {$parent->id} to Student ID {$student->id}"
                    );
                }
            }
        });
    }
}
