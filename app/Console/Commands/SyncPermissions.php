<?php

namespace App\Console\Commands;

use App\Permissions\PermissionSynchronizer;
use Illuminate\Console\Command;

class SyncPermissions extends Command
{
    protected $signature = 'permissions:sync {--profiles : Repor também os perfis de config/permissions.php (substitui o que foi alterado no backoffice)}';

    protected $description = 'Cria as permissões do catálogo (config/permissions.php) que ainda não existem';

    public function handle(PermissionSynchronizer $synchronizer): int
    {
        $created = $synchronizer->syncCatalog();
        $this->info(count($created) . ' permissões criadas.');
        foreach ($created as $name) {
            $this->line("  + {$name}");
        }

        if ($this->option('profiles')) {
            if (!$this->confirm('Isto repõe as permissões dos perfis Importador, angariador e cms. Continuar?', true)) {
                return self::SUCCESS;
            }
            $synchronizer->applyProfiles();
            $this->info('Perfis repostos.');
        }

        return self::SUCCESS;
    }
}
