<?php namespace BadChoice\Grog\Commands;

use Illuminate\Console\Command;

use DB;
use Artisan;
use Exception;

class TenantsMigrate extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'tenants:migrate
	                            {--rollback : Rollback all tenants or tenant selected}
	                            {--tenant= : Specify a tenant to migrate (or rollback)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate all tenants';


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
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('');
        $this->info(' ===== TENANTS MIGRATION =====');
        $this->info('');

        if($this->option('rollback')){
            $this->error('*******************************');
            $this->error('**       ROLLING BACK        **');
            $this->error('*******************************');
            $this->info('');
        }

        if ($this->confirm('Do you wish to continue? [yes|no]',false))
        {
            //----------
            // SINGLE MIGRATE/ROLLBACK
            //----------
            if($this->option('tenant') != ''){
                $tenantName = $this->option('tenant');
                $this->info('Doing: '. $tenantName);
                $this->migrate($tenantName, $this->option('rollback'));
            }
            //----------
            // FULL MIGRATE/ROLLBACK
            //----------
            else {
                $this->info('');
                $class = config('tenants.user');
                $users = $class::toMigrate();

                $total = count($users);
                $i = 0;
                foreach ($users as $user) {
                    $i++;
                    $this->info('[' . ($i / $total) * 100 . '% ]  Doing ' . $user . '...');
                    $this->migrate($user, $this->option('rollback'));
                }
            }
            $this->info('');
            $this->info('Dump autoload');

            exec('composer dump-autoload');
            Artisan::call('optimize');
            Artisan::call('queue:restart'); //To reload the code for queues (on daemon)
        }
        else{
            $this->info('');
            $this->info(' !! Migration aborted !!');
        }
        $this->info('');
    }


    private function migrate($user,$rollback = false){
        $class = config('tenants.user');
        try{
            if($rollback){
                //$result = User::rollbackTenant($user);
                createDBConnection($user);
                $class::rollback($user);
                /*if($result!=""){
                    $this->error('[Error] '.$result);
                }*/
                //$this->info('!! Disabled !! needs to be enabled by code! (uncoment two lines)');
            }
            else {
                createDBConnection($user);
                $class::migrate($user);
                /*$result = User::migrateTenant($user);
                if($result != ""){
                    $this->error('[Error] '.$result);
                }*/

                //Custom raw
                //DB::statement("ALTER TABLE GS_businesses ADD printInvoice INT UNSIGNED NOT NULL DEFAULT '0'");
                //DB::statement("ALTER TABLE GS_inouts ADD tenantUser_id INT UNSIGNED NULL DEFAULT NULL AFTER turn_id");
                //DB::statement("ALTER TABLE GS_permissions ADD openCashDrawer TINYINT NOT NULL DEFAULT '0' AFTER cancelPayments");
                //DB::statement("ALTER TABLE GS_order_menu_menu_contents ADD itemName VARCHAR(255) NULL DEFAULT NULL AFTER price");

                //DB::statement("ALTER TABLE GS_permissions ADD cancelOrderContent TINYINT(1) NOT NULL DEFAULT '0' AFTER cancelOrder;");


                //DB::statement("ALTER TABLE GS_menu_items ADD modifier_category_id INT UNSIGNED NULL AFTER modifier_group_id");
                //DB::statement("ALTER TABLE GS_menu_categories ADD modifier_category_id INT UNSIGNED NULL AFTER modifier_group_id");

                //Menu item type (tickets and menu menu)
                //DB::statement("ALTER TABLE GS_menu_items ADD type INT UNSIGNED NOT NULL DEFAULT 0 AFTER info");
                //DB::statement("update GS_menu_items set type=1 where isMenu    = 1");
                //DB::statement("update GS_menu_items set type=2 where isLinked  = 1");


                //DB::statement("ALTER TABLE `GS_menu_menu_categories` ADD `isMultipleChoice` TINYINT NOT NULL DEFAULT '1' AFTER `order`");

                //DB::statement("ALTER TABLE GS_orders_contents ADD taxPercentage DECIMAL(8,2) NOT NULL DEFAULT '0' AFTER taxAmount;");
                //DB::statement("ALTER TABLE GS_orders_invoices ADD orderDiscountAmount DECIMAL(8,2) NOT NULL DEFAULT '0' AFTER discountAmount;");
                //DB::statement("ALTER TABLE GS_order_config ADD shouldRound TINYINT(1) NOT NULL DEFAULT '0' AFTER shouldSyncDevices");


                //DB::statement("ALTER TABLE GS_orders_contents ADD weight DECIMAL(8,3) NULL DEFAULT '0' AFTER quantity");
                //DB::statement("ALTER TABLE GS_cashiers ADD balance_ip    VARCHAR(255) NULL  AFTER cashkeeper_ip");
                //DB::statement("ALTER TABLE GS_cashiers ADD balance_port  INT UNSIGNED NULL  AFTER balance_ip");
                //DB::statement("ALTER TABLE GS_payment_methods ADD openCashDrawer  TINYINT(1) NOT NULL DEFAULT '1' AFTER `order`");

                //DB::statement("ALTER TABLE GS_businesses ADD geolocation  varchar(255) NULL DEFAULT '' AFTER `city`");

                //DB::statement("ALTER TABLE GS_order_config ADD printMenuMenuDishOrder TINYINT(1) NOT NULL DEFAULT '0' AFTER shouldRound");


                //DB::statement("ALTER TABLE GS_profiles_menu_items ADD printer_group_id INT UNSIGNED NULL AFTER printer_id");
                //DB::statement("ALTER TABLE GS_profiles_menu_categories ADD printer_group_id INT UNSIGNED NULL AFTER printer_id");


                //DB::statement("ALTER TABLE GS_menu_item_menu_menu_category ADD active TINYINT(1) NOT NULL DEFAULT '1' AFTER id");

                //DB::statement("ALTER TABLE GS_order_config ADD shouldShowChangeAlert TINYINT(1) NOT NULL DEFAULT '1' AFTER shouldSyncDevices");
                //DB::statement("ALTER TABLE GS_permissions  ADD deviceConfig TINYINT NOT NULL DEFAULT '0' AFTER cancelPayments");

                //DB::statement("ALTER TABLE GS_orders ADD saveToken VARCHAR(255) NULL AFTER lockedBy");
                //DB::statement("ALTER TABLE GS_modules ADD ibelsa_key VARCHAR(255) NULL AFTER wifipug_code");
                //DB::statement("ALTER TABLE GS_modules ADD cashkeeper_till VARCHAR(255) NULL AFTER ibelsa_key");

                /*DB::statement("ALTER TABLE `GS_orders` CHANGE `opened` `opened` DATETIME NULL");
                DB::statement("update GS_orders set opened   = null where opened = '0000-00-00 00:00:00'");
                DB::statement("update GS_orders set closed   = null where closed = '0000-00-00 00:00:00'");
                DB::statement("update GS_orders set merged   = null where merged = '0000-00-00 00:00:00'");
                DB::statement("update GS_orders set canceled = null where canceled = '0000-00-00 00:00:00'");*/

                //DB::statement("ALTER TABLE GS_connections ADD uuid varchar(255) NULL DEFAULT NULL AFTER model_id");
                //DB::statement("ALTER TABLE GS_businesses  ADD blindCashier tinyint(1) NOT NULL DEFAULT 0 AFTER cashControlType");

                /*DB::statement("ALTER TABLE GS_order_config ADD showSquareIcons TINYINT(1) NOT NULL DEFAULT '0' AFTER printMenuMenuDishOrder");
                $ticket = PrintTicket::first();
                if($ticket == null) {
                    $ticket = PrintTicket::create([
                        'invoice' => PrintTicket::getStandardInvoiceJson(),
                        'kitchen' => PrintTicket::getStandardKitchenJson()
                    ]);
                }*/

                //DB::statement("ALTER TABLE GS_businesses ADD exchangeRate DECIMAL(8,4) NOT NULL DEFAULT '0' AFTER secondCurrency;");
                //DB::statement("ALTER TABLE GS_menu_item_warehouse ADD defaultQuantity DECIMAL(8,2) NOT NULL DEFAULT '0' AFTER quantity;");
                //DB::statement("ALTER TABLE GS_order_config ADD allowNegativeQuantity TINYINT(1) NOT NULL DEFAULT '0' AFTER shouldSyncDevices");

                //DB::statement("ALTER TABLE GS_print_tickets ADD items text NULL after takeAway");
                //DB::statement("ALTER TABLE GS_permissions  ADD logout TINYINT NOT NULL DEFAULT '1' AFTER deviceConfig");
                //DB::statement("ALTER TABLE GS_kitchen_notes ADD printer_group_id INT UNSIGNED NULL");


                //DB::statement("ALTER TABLE GS_orders ADD notes_sent varchar(255) NULL after status");
                //DB::statement("ALTER TABLE GS_menu_categories ADD dish_order_id INT UNSIGNED NULL");
                //DB::statement("ALTER TABLE GS_order_config ADD printMenuMenuContentsAsNormal  TINYINT(1) NOT NULL DEFAULT '0' AFTER printMenuMenuDishOrder ");

                //DB::statement("ALTER TABLE GS_order_config ADD printReportsTotals  TINYINT(1) NOT NULL DEFAULT '1' AFTER shouldAskGuests ");

                //DB::statement("ALTER TABLE GS_table_tables ADD baseX  INTEGER NOT NULL DEFAULT '70' AFTER height ");
                //DB::statement("ALTER TABLE GS_table_tables ADD baseY  INTEGER NOT NULL DEFAULT '70' AFTER height ");
                //DB::statement("ALTER TABLE GS_table_tables ADD baseWidth  INTEGER NOT NULL DEFAULT '70' AFTER height ");
                //DB::statement("ALTER TABLE GS_table_tables ADD baseHeight  INTEGER NOT NULL DEFAULT '70' AFTER height ");
                //DB::statement("ALTER TABLE GS_table_tables ADD isJoined  TINYINT(1) NOT NULL DEFAULT '0' AFTER height ");
                //DB::statement("update GS_table_tables set baseX = x, baseY = y, baseWidth = width, baseHeight = height");


                //DB::statement("ALTER TABLE GS_order_config ADD useSeats  TINYINT(1) NOT NULL DEFAULT '0' AFTER printMenuMenuContentsAsNormal ");


                /*DB::statement("ALTER TABLE `GS_customers` CHANGE `nif` `nif` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;");
                DB::statement("ALTER TABLE GS_customers DROP INDEX customers_nif_unique;");
                DB::statement("ALTER TABLE GS_order_config ADD useDelivery  TINYINT(1) NOT NULL DEFAULT '0'");
                DB::statement("ALTER TABLE GS_order_config ADD deliveryMinutes  INT NOT NULL DEFAULT '45'");
                DB::statement("ALTER TABLE GS_permissions ADD delivery TINYINT NOT NULL DEFAULT '1'");
                */
                //-- REVO SERVER
                //DB::statement("ALTER TABLE `GS_stock_movements` CHANGE `quantity` `quantity` DECIMAL(8,2) NOT NULL");
            }
        }
        catch(\Exception $e){
            $this->error('[Error] ' . $e->getMessage());
        }
    }

}
