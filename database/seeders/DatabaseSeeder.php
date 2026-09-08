<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use App\Models\MailConfiguration;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view dashboard',
            'manage users',
            'view account settings',
            'manage tenants',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin']);
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        $superAdminRole->syncPermissions($permissions);
        $adminRole->syncPermissions(['view dashboard', 'view account settings']);
        $userRole->syncPermissions(['view dashboard', 'view account settings']);

        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => 'password',
                'tenant_id' => null,
            ]
        );
        $superAdmin->syncRoles(['super-admin']);

        $acme = Tenant::updateOrCreate(['company_name' => 'Acme Corp'], [
            'timezone' => 'America/New_York',
            'country' => 'United States',
            'subscription_plan' => 'enterprise',
            'status' => 'active',
        ]);

        $globex = Tenant::updateOrCreate(['company_name' => 'Globex Ltd'], [
            'timezone' => 'Asia/Karachi',
            'country' => 'Pakistan',
            'subscription_plan' => 'pro',
            'status' => 'active',
        ]);

        $abdullah = User::updateOrCreate(
            ['email' => 'abdullah@gmail.com'],
            [
                'name' => 'Abdullah',
                'password' => 'password',
                'tenant_id' => $acme->id,
            ]
        );
        $abdullah->syncRoles(['admin']);

        $rehman = User::updateOrCreate(
            ['email' => 'rehman@gmail.com'],
            [
                'name' => 'Rehman',
                'password' => 'password',
                'tenant_id' => $globex->id,
            ]
        );
        $rehman->syncRoles(['admin']);

        $john = User::updateOrCreate(
            ['email' => 'john@acme.com'],
            [
                'name' => 'John Doe',
                'password' => 'password',
                'tenant_id' => $acme->id,
            ]
        );
        $john->syncRoles(['user']);

        $jane = User::updateOrCreate(
            ['email' => 'jane@globex.com'],
            [
                'name' => 'Jane Smith',
                'password' => 'password',
                'tenant_id' => $globex->id,
            ]
        );
        $jane->syncRoles(['user']);

        MailConfiguration::updateOrCreate(
            ['tenant_id' => $acme->id, 'smtp_host' => 'smtp.mailgun.org'],
            [
                'smtp_port' => 587,
                'username' => 'postmaster@mg.acmecorp.com',
                'password' => 'acme-smtp-password',
                'encryption' => 'tls',
                'from_name' => 'Acme Corp',
                'from_email' => 'noreply@acmecorp.com',
                'reply_to_email' => 'support@acmecorp.com',
                'is_default' => true,
            ]
        );

        MailConfiguration::updateOrCreate(
            ['tenant_id' => $globex->id, 'smtp_host' => 'smtp.gmail.com'],
            [
                'smtp_port' => 587,
                'username' => 'marketing@globex.com',
                'password' => 'globex-smtp-password',
                'encryption' => 'tls',
                'from_name' => 'Globex Ltd',
                'from_email' => 'marketing@globex.com',
                'reply_to_email' => 'hello@globex.com',
                'is_default' => true,
            ]
        );

        $welcomeTemplate = <<<'HTML'
<!DOCTYPE html><html><head><style>body { margin: 0; padding: 0; background: #f4f6fb; }
.hero { background: #ffffff; padding: 40px 30px; text-align: center; }
.title { font-family: Arial; font-size: 28px; font-weight: 700; color: #384151; margin: 0 0 12px; }
.subtitle { font-family: Arial; font-size: 15px; line-height: 1.6; color: #677788; margin: 0 0 24px; }
.cta { background: #696cff; color: #ffffff !important; text-decoration: none !important; padding: 14px 28px; border-radius: 6px; display: inline-block; font-family: Arial; font-weight: 600; }</style></head><body>
<div class="hero"><h1 class="title">Welcome to our newsletter</h1><p class="subtitle">Thanks for signing up! Expect great content, product updates and exclusive offers in your inbox.</p><a href="#" class="cta">Get Started</a></div>
</body></html>
HTML;

        EmailTemplate::updateOrCreate(
            ['user_id' => $john->id, 'name' => 'Welcome Email'],
            [
                'subject' => 'Welcome aboard!',
                'content' => $welcomeTemplate,
            ]
        );

        $promoTemplate = <<<'HTML'
<!DOCTYPE html><html><head><style>body { margin: 0; padding: 0; background: #f4f6fb; }
.banner { background: linear-gradient(135deg, #696cff, #8592a3); padding: 50px 30px; text-align: center; }
.banner-title { font-family: Arial; font-size: 30px; font-weight: 700; color: #ffffff; margin: 0 0 12px; }
.banner-text { font-family: Arial; font-size: 16px; line-height: 1.6; color: #eceef1; margin: 0 0 24px; }
.btn { background: #ffffff; color: #696cff !important; text-decoration: none !important; padding: 14px 28px; border-radius: 6px; display: inline-block; font-family: Arial; font-weight: 600; }
.footer { padding: 24px 30px; text-align: center; font-family: Arial; font-size: 13px; color: #677788; }</style></head><body>
<div class="banner"><h1 class="banner-title">50% OFF This Week Only</h1><p class="banner-text">Upgrade your plan today and save big on your first year.</p><a href="#" class="btn">Claim Offer</a></div>
<div class="footer">You received this email because you are subscribed to Globex updates.</div>
</body></html>
HTML;

        EmailTemplate::updateOrCreate(
            ['user_id' => $jane->id, 'name' => 'Promo Blast'],
            [
                'subject' => '50% OFF for you',
                'content' => $promoTemplate,
            ]
        );
    }
}
