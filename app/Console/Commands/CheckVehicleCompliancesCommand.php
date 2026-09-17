<?php

namespace App\Console\Commands;

use App\Models\VehicleCompliance;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckVehicleCompliancesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vfms:check-compliances';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit all vehicle BRTA fitness, tax token, insurance, route permits and send escalating expiry alerts';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $today = Carbon::today();
        $this->info("Scanning vehicle compliances for date: {$today->toDateString()}");

        $compliances = VehicleCompliance::with('vehicle')->get();
        $alertCount = 0;

        foreach ($compliances as $compliance) {
            $days = (int) $today->diffInDays($compliance->expiry_date, false);
            $regNo = $compliance->vehicle->registration_no ?? 'Unknown Vehicle';
            $docType = $compliance->document_type;

            if ($days < 0) {
                $this->error("EXPIRED: [{$regNo}] {$docType} expired {$days} days ago on {$compliance->expiry_date->toDateString()}!");
                $alertCount++;
            } elseif ($days <= 7 && ! $compliance->alert_7d_sent_at) {
                $this->warn("CRITICAL (7 Days): [{$regNo}] {$docType} expires in {$days} days!");
                $compliance->update(['alert_7d_sent_at' => now()]);
                $alertCount++;
            } elseif ($days <= 15 && ! $compliance->alert_15d_sent_at) {
                $this->warn("HIGH ALERT (15 Days): [{$regNo}] {$docType} expires in {$days} days!");
                $compliance->update(['alert_15d_sent_at' => now()]);
                $alertCount++;
            } elseif ($days <= 30 && ! $compliance->alert_30d_sent_at) {
                $this->info("WARNING (30 Days): [{$regNo}] {$docType} expires in {$days} days.");
                $compliance->update(['alert_30d_sent_at' => now()]);
                $alertCount++;
            } elseif ($days <= 60 && ! $compliance->alert_60d_sent_at) {
                $this->line("NOTICE (60 Days): [{$regNo}] {$docType} expires in {$days} days.");
                $compliance->update(['alert_60d_sent_at' => now()]);
                $alertCount++;
            }
        }

        $this->info("Compliance audit completed. Alerts triggered: {$alertCount}");

        return self::SUCCESS;
    }
}
