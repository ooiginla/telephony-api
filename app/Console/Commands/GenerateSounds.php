<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Question;

class GenerateSounds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-sounds';

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
       //  Question::where('')
    }
}
