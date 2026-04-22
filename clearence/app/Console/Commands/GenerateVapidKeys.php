<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Minishlink\WebPush\VAPID;

class GenerateVapidKeys extends Command
{
    protected $signature   = 'push:generate-keys';
    protected $description = 'Generate a VAPID key pair for Web Push and print the .env entries';

    public function handle(): int
    {
        $keys = VAPID::createVapidKeys();

        $this->newLine();
        $this->line('<fg=green;options=bold>VAPID keys generated.</> Add the following lines to your <fg=yellow>.env</>:');
        $this->newLine();
        $this->line('  <fg=cyan>VAPID_PUBLIC_KEY=</>'. $keys['publicKey']);
        $this->line('  <fg=cyan>VAPID_PRIVATE_KEY=</>' . $keys['privateKey']);
        $this->line('  <fg=cyan>VAPID_SUBJECT=</>mailto:your@email.com');
        $this->newLine();
        $this->comment('The VAPID_SUBJECT must be either a mailto: or https: URI identifying the push sender.');
        $this->comment('The public key is also needed in subscribe.js — pass it to subscribe(serverPublicKey).');
        $this->newLine();

        return self::SUCCESS;
    }
}
