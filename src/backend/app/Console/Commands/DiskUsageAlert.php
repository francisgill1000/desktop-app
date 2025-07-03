<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class DiskUsageAlert extends Command
{
    protected $signature = 'monitor:disk';
    protected $description = 'Send email alert if disk usage exceeds threshold';

    public function handle()
    {
        $output = shell_exec("df / | grep / | awk '{ print $5 }'");
        $usage = (int) trim(str_replace('%', '', $output));

        $to = env("ADMIN_MAIL_RECEIVERS", "francisgill1000@gmail.com");

        $this->info("SENDER MAIL: " . env("MAIL_FROM_ADDRESS", "francisgill1000@gmail.com"));

        $this->info("RECEIVERS MAIL: " . $to);

        if ($usage > 80) {

            $fixSteps = <<<EOT
⚠️ Your disk has reached {$usage}%.

Suggested steps to free space:

1. Check large folders and files:
   sudo du -h --max-depth=1 / | sort -hr | head -n 10

2. Clean apt cache:
   sudo apt-get clean

3. Clear old logs:
   sudo journalctl --vacuum-time=2d

4. Remove old Snap versions:
   sudo snap list --all
   sudo snap remove --purge <package-name> --revision=<old-revision>

5. Delete temporary files:
   sudo rm -rf /tmp/*

Please take immediate action to avoid system issues.
EOT;

            Mail::raw($fixSteps, function ($message) use ($to, $usage) {
                $message->to($to)
                    ->subject("Disk Alert: {$usage}% used");
            });

            $this->info("Alert email sent with fix instructions. Usage: {$usage}%");
        } else {
            $this->info("Disk usage is fine: {$usage}%");
        }

        return 0;
    }
}
