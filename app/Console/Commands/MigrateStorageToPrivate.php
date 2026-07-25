<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MigrateStorageToPrivate extends Command
{
    protected $signature = 'storage:migrate-private';
    protected $description = 'Pindahkan/Salin seluruh berkas personel dari storage public/local ke storage private';

    public function handle()
    {
        $this->info('Memulai pemindahan berkas ke storage/app/private...');

        $targetPrivateDir = storage_path('app/private');
        if (!File::exists($targetPrivateDir)) {
            File::makeDirectory($targetPrivateDir, 0755, true);
        }

        $sourceDirs = [
            storage_path('app/public'),
            storage_path('app'),
            public_path('storage'),
        ];

        $subFolders = ['personel', 'documents', 'photos', 'asn_sks', 'pengkinian_data', 'skep'];
        $countMoved = 0;

        foreach ($sourceDirs as $sourceDir) {
            if (!File::exists($sourceDir)) continue;

            foreach ($subFolders as $folder) {
                $srcFolderPath = rtrim($sourceDir, '/') . '/' . $folder;
                if (File::isDirectory($srcFolderPath)) {
                    $destFolderPath = $targetPrivateDir . '/' . $folder;
                    if (!File::exists($destFolderPath)) {
                        File::makeDirectory($destFolderPath, 0755, true);
                    }

                    $files = File::allFiles($srcFolderPath);
                    foreach ($files as $file) {
                        $relativePath = $file->getRelativePathname();
                        $destPath = $destFolderPath . '/' . $relativePath;
                        $destDir = dirname($destPath);

                        if (!File::exists($destDir)) {
                            File::makeDirectory($destDir, 0755, true);
                        }

                        if (!File::exists($destPath)) {
                            File::copy($file->getRealPath(), $destPath);
                            $countMoved++;
                            $this->line("Disalin: {$folder}/{$relativePath} -> storage/app/private/{$folder}/{$relativePath}");
                        }
                    }
                }
            }
        }

        $this->info("Selesai! Sebanyak {$countMoved} berkas telah berhasil disalin ke storage/app/private.");
        return Command::SUCCESS;
    }
}
