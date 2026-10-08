<?php

namespace App\Console\Commands;

use App\Services\Corporate\CorporateAcceptanceProvisioner;
use Illuminate\Console\Command;
use RuntimeException;

class ProvisionCorporateAcceptance extends Command
{
    protected $signature = 'corporate:provision-acceptance';

    protected $description = 'Provision fictional Kenyan acceptance identities in the designated isolated database only';

    public function handle(CorporateAcceptanceProvisioner $provisioner): int
    {
        try {
            $identities = $provisioner->provision((string) config('corporate_acceptance.password'));
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->table(['Role', 'Name', 'Login', 'Employer / operator'], $identities);
        $this->info('Acceptance identities are ready. No trips, invoices, settlements or gateway transactions were seeded.');
        $this->info('Use staging OTP authentication for mobile accounts. Web passwords are supplied by the operator and are never printed.');

        return self::SUCCESS;
    }
}
