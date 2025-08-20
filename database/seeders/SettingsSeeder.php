<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            // General
            ['key'=>'general.site_name','group'=>'general','type'=>'string','value'=>['raw'=>'Hotspot Portal'],'meta'=>['label'=>'Nom du site','description'=>'Nom affiché dans l’interface']],
            ['key'=>'general.support_email','group'=>'general','type'=>'string','value'=>['raw'=>'support@example.com'],'meta'=>['label'=>'Support Email']],
            // Provisioning
            ['key'=>'provisioning.username_prefix','group'=>'provisioning','type'=>'string','value'=>['raw'=>env('HOTSPOT_USERNAME_PREFIX','HS')],'meta'=>['label'=>'Prefix Utilisateur']],
            ['key'=>'provisioning.password_length','group'=>'provisioning','type'=>'int','value'=>['raw'=> (int) env('HOTSPOT_PASSWORD_LENGTH',10)],'meta'=>['label'=>'Longueur mot de passe']],
            // Mikrotik
            ['key'=>'mikrotik.host','group'=>'mikrotik','type'=>'string','value'=>['raw'=>env('MIKROTIK_HOST','127.0.0.1')],'meta'=>['label'=>'Host']],
            ['key'=>'mikrotik.port','group'=>'mikrotik','type'=>'int','value'=>['raw'=>(int)env('MIKROTIK_PORT',8728)],'meta'=>['label'=>'Port']],
            ['key'=>'mikrotik.ssl','group'=>'mikrotik','type'=>'bool','value'=>['raw'=>env('MIKROTIK_SSL',false)],'meta'=>['label'=>'SSL']],
            // Notification
            ['key'=>'notification.default_channel','group'=>'notification','type'=>'string','value'=>['raw'=>env('NOTIFY_DEFAULT_CHANNEL','sms')],'meta'=>['label'=>'Canal par défaut']],
            // Alerting
            ['key'=>'alerting.severity_email_min','group'=>'alerting','type'=>'string','value'=>['raw'=>config('alerting.severity_email_min')],'meta'=>['label'=>'Seuil email']],
            ['key'=>'alerting.severity_slack_min','group'=>'alerting','type'=>'string','value'=>['raw'=>config('alerting.severity_slack_min')],'meta'=>['label'=>'Seuil Slack']],
            // SLA thresholds (expose un ou deux clés)
            ['key'=>'sla.threshold.mikrotik_ping_ms','group'=>'sla','type'=>'int','value'=>['raw'=>config('sla.thresholds.mikrotik.ping_ms') ?? 1000],'meta'=>['label'=>'Seuil Ping Mikrotik (ms)']],
        ];

        foreach ($defaults as $row) {
            Setting::firstOrCreate(['key'=>$row['key']], $row);
        }
    }
}