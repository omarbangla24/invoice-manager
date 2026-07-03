<?php

namespace App\Services;

use App\Models\AppSetting;

class StorageDiskManager
{
    public function activeDisk(): string
    {
        $disk = AppSetting::getValue('storage_disk', config('filesystems.default', 'local')) ?: 'local';

        if ($disk === 's3' && ! class_exists(\League\Flysystem\AwsS3V3\AwsS3V3Adapter::class)) {
            return 'local';
        }

        return $disk;
    }

    public function applyCloudConfig(): void
    {
        config([
            'filesystems.disks.s3.key' => AppSetting::getValue('s3_key', config('filesystems.disks.s3.key')),
            'filesystems.disks.s3.secret' => AppSetting::getValue('s3_secret', config('filesystems.disks.s3.secret')),
            'filesystems.disks.s3.region' => AppSetting::getValue('s3_region', config('filesystems.disks.s3.region')),
            'filesystems.disks.s3.bucket' => AppSetting::getValue('s3_bucket', config('filesystems.disks.s3.bucket')),
            'filesystems.disks.s3.endpoint' => AppSetting::getValue('s3_endpoint', config('filesystems.disks.s3.endpoint')),
            'filesystems.disks.s3.url' => AppSetting::getValue('s3_url', config('filesystems.disks.s3.url')),
            'filesystems.disks.s3.use_path_style_endpoint' => filter_var(AppSetting::getValue('s3_path_style', 'false'), FILTER_VALIDATE_BOOLEAN),
        ]);
    }
}
