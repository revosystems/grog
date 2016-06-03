<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Storage;

class TenantsCopyPhotos extends Command
{


    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:copyPhotos
                                {origin         : The tenant name of the origin account}
                                {destination    : the new account to be created as copy of origin}
                                ';


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle(){

        $from             = $this->argument('origin');
        $to               = $this->argument('destination');

        $this->info('Copying images from ' . $from . ' to ' . $to);

        $files = Storage::allFiles($from);

        $bar = $this->output->createProgressBar(count($files));

        foreach($files as $file){
            //$this->info($file);
            if(basename($file) != '.DS_Store'){
                $destination = $to . '/images/' . basename($file);
                if(!Storage::exists($destination)) {
                    Storage::copy($file, $destination);
                }
            }
            $bar->advance();
        }

        $bar->finish();

        $this->info('');
        $this->info('Done!');
    }
}
