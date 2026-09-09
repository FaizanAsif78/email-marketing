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

        $gpPitch = <<<'HTML'
<!DOCTYPE html><html><head><style>
body { margin:0; padding:0; background:#f4f6fb; font-family:Arial,Helvetica,sans-serif; }
.preheader { padding:16px 0; text-align:center; font-size:12px; color:#a7adbd; }
.banner { background:linear-gradient(135deg,#696cff,#45998f); padding:42px 30px; text-align:center; }
.banner-sub { color:#e7eaff; font-size:12px; letter-spacing:2px; text-transform:uppercase; margin:0 0 10px; }
.banner-title { color:#ffffff; font-size:26px; font-weight:700; margin:0; }
.card { background:#ffffff; padding:32px 34px; }
.topic h2 { font-size:16px; color:#292e3d; margin:0 0 6px; }
.topic p { font-size:14px; line-height:1.7; color:#677788; margin:0 0 22px; }
.btn { display:inline-block; background:#696cff; color:#ffffff !important; text-decoration:none !important; padding:13px 26px; border-radius:6px; font-weight:600; font-size:14px; }
.signature { border-top:1px solid #eceef1; margin-top:26px; padding-top:18px; font-size:14px; color:#677788; line-height:1.6; }
.footer { padding:22px 30px; text-align:center; font-size:12px; color:#a7adbd; }
.footer a { color:#696cff; }
</style></head><body>
<div class="preheader">A guest post idea hand-picked for {blog_name}</div>
<table role="presentation" width="100%" bgcolor="#f4f6fb" cellpadding="0" cellspacing="0"><tr><td align="center">
<table role="presentation" width="620" cellpadding="0" cellspacing="0" style="max-width:620px; width:100%;">
<tr><td class="banner"><p class="banner-sub">{company_name} &middot; Outreach</p><h1 class="banner-title">A guest post idea for your audience</h1></td></tr>
<tr><td class="card">
<p style="font-size:15px; line-height:1.7; color:#384151; margin:0 0 18px;">Hi {contact_name},</p>
<p style="font-size:15px; line-height:1.7; color:#677788; margin:0 0 20px;">I regularly follow <strong>{blog_name}</strong> and love the way you break down complex topics. I&rsquo;d love to contribute an original, high-quality article your readers will actually use.</p>
<div class="topic"><h2>&#10003; Topics we can tailor to {blog_name}</h2><p>SEO strategy for 2026, conversion copywriting that sells, and link-building playbooks that still work — with fresh data and expert sources.</p></div>
<div class="topic"><h2>&#10003; What we deliver</h2><p>1,500+ words, fully researched, written in your tone, and delivered as a clean HTML draft ready to publish. Never duplicated, never spammy.</p></div>
<div style="text-align:center; margin:8px 0;"><a class="btn" href="#">Send Me the Full Pitch</a></div>
<div class="signature">Warm regards,<br/><strong>The {company_name} Editorial Team</strong><br/>{website_url} &#183; {email_address}</div>
</td></tr>
<tr><td class="footer">You&rsquo;re receiving this because we believe {blog_name} could be a great fit. <a href="#">Unsubscribe</a></td></tr>
</table></td></tr></table>
</body></html>
HTML;

        EmailTemplate::updateOrCreate(
            ['user_id' => $john->id, 'name' => 'Guest Post Pitch — Initial'],
            [
                'subject' => 'Guest Post Idea for {blog_name}',
                'content' => $gpPitch,
            ]
        );

        $gpFollowUp = <<<'HTML'
<!DOCTYPE html><html><head><style>
body { margin:0; padding:0; background:#f4f6fb; font-family:Arial,Helvetica,sans-serif; }
.preheader { padding:16px 0; text-align:center; font-size:12px; color:#a7adbd; }
.banner { background:linear-gradient(135deg,#f6a700,#f97316); padding:42px 30px; text-align:center; }
.banner-sub { color:#fff4e0; font-size:12px; letter-spacing:2px; text-transform:uppercase; margin:0 0 10px; }
.banner-title { color:#ffffff; font-size:26px; font-weight:700; margin:0; }
.card { background:#ffffff; padding:32px 34px; }
.btn { display:inline-block; background:#f97316; color:#ffffff !important; text-decoration:none !important; padding:13px 26px; border-radius:6px; font-weight:600; font-size:14px; }
.signature { border-top:1px solid #eceef1; margin-top:26px; padding-top:18px; font-size:14px; color:#677788; line-height:1.6; }
.footer { padding:22px 30px; text-align:center; font-size:12px; color:#a7adbd; }
.footer a { color:#f97316; }
</style></head><body>
<div class="preheader">A gentle follow-up about your guest post opportunity</div>
<table role="presentation" width="100%" bgcolor="#f4f6fb" cellpadding="0" cellspacing="0"><tr><td align="center">
<table role="presentation" width="620" cellpadding="0" cellspacing="0" style="max-width:620px; width:100%;">
<tr><td class="banner"><p class="banner-sub">{company_name} &middot; Outreach</p><h1 class="banner-title">Just following up</h1></td></tr>
<tr><td class="card">
<p style="font-size:15px; line-height:1.7; color:#384151; margin:0 0 18px;">Hi {contact_name},</p>
<p style="font-size:15px; line-height:1.7; color:#677788; margin:0 0 18px;">I wanted to make sure my last email didn&rsquo;t slip through the cracks. I&rsquo;d still love to contribute a guest post to <strong>{blog_name}</strong> — totally free, written exclusively for you.</p>
<p style="font-size:15px; line-height:1.7; color:#677788; margin:0 0 22px;">If the topics I shared weren&rsquo;t a fit, I&rsquo;m happy to brainstorm something your readers will love.</p>
<div style="text-align:center; margin:8px 0;"><a class="btn" href="#">Share Your Thoughts</a></div>
<div class="signature">Best,<br/><strong>The {company_name} Editorial Team</strong><br/>{website_url} &#183; {email_address}</div>
</td></tr>
<tr><td class="footer">Just a quick nudge from {website_url}. <a href="#">Unsubscribe</a></td></tr>
</table></td></tr></table>
</body></html>
HTML;

        EmailTemplate::updateOrCreate(
            ['user_id' => $john->id, 'name' => 'Guest Post Pitch — Follow-Up'],
            [
                'subject' => 'Re: Guest Post Idea for {blog_name}',
                'content' => $gpFollowUp,
            ]
        );

        $gpAccepted = <<<'HTML'
<!DOCTYPE html><html><head><style>
body { margin:0; padding:0; background:#f4f6fb; font-family:Arial,Helvetica,sans-serif; }
.preheader { padding:16px 0; text-align:center; font-size:12px; color:#a7adbd; }
.banner { background:linear-gradient(135deg,#10b981,#0d9488); padding:42px 30px; text-align:center; }
.banner-sub { color:#dffcf2; font-size:12px; letter-spacing:2px; text-transform:uppercase; margin:0 0 10px; }
.banner-title { color:#ffffff; font-size:26px; font-weight:700; margin:0; }
.card { background:#ffffff; padding:32px 34px; }
.steps h2 { font-size:16px; color:#292e3d; margin:0 0 6px; }
.steps p { font-size:14px; line-height:1.7; color:#677788; margin:0 0 20px; }
.btn { display:inline-block; background:#10b981; color:#ffffff !important; text-decoration:none !important; padding:13px 26px; border-radius:6px; font-weight:600; font-size:14px; }
.signature { border-top:1px solid #eceef1; margin-top:26px; padding-top:18px; font-size:14px; color:#677788; line-height:1.6; }
.footer { padding:22px 30px; text-align:center; font-size:12px; color:#a7adbd; }
</style></head><body>
<div class="preheader">Your guest post is in! Let&rsquo;s make it great.</div>
<table role="presentation" width="100%" bgcolor="#f4f6fb" cellpadding="0" cellspacing="0"><tr><td align="center">
<table role="presentation" width="620" cellpadding="0" cellspacing="0" style="max-width:620px; width:100%;">
<tr><td class="banner"><p class="banner-sub">{blog_name}</p><h1 class="banner-title">Accepted — thank you!</h1></td></tr>
<tr><td class="card">
<p style="font-size:15px; line-height:1.7; color:#384151; margin:0 0 18px;">Hi {contact_name},</p>
<p style="font-size:15px; line-height:1.7; color:#677788; margin:0 0 22px;">Brilliant news — we&rsquo;d love to publish your guest post on <strong>{blog_name}</strong>. Here&rsquo;s what makes the process smooth:</p>
<div class="steps"><h2>1 &#8212; Target keywords &amp; links</h2><p>Share the 2&#8211;3 places you&rsquo;d like to link to, plus target keywords for the title and headings.</p></div>
<div class="steps"><h2>2 &#8212; Send your draft</h2><p>We&rsquo;ll review, lightly edit for {blog_name}&rsquo;s style, and give you a publishing date.</p></div>
<div class="steps"><h2>3 &#8212; Add your bio</h2><p>A short author bio plus one link back to {website_url} &mdash; that&rsquo;s it.</p></div>
<div style="text-align:center; margin:8px 0;"><a class="btn" href="#">Upload Your Draft</a></div>
<div class="signature">Looking forward to it,<br/><strong>The {company_name} Editorial Team</strong><br/>{email_address}</div>
</td></tr>
<tr><td class="footer">&copy; {date} {company_name} &middot; {website_url}</td></tr>
</table></td></tr></table>
</body></html>
HTML;

        EmailTemplate::updateOrCreate(
            ['user_id' => $john->id, 'name' => 'Guest Post Accepted'],
            [
                'subject' => 'Guest Post Accepted — Let\'s Get Started',
                'content' => $gpAccepted,
            ]
        );

        $gpPaidOffer = <<<'HTML'
<!DOCTYPE html><html><head><style>
body { margin:0; padding:0; background:#f4f6fb; font-family:Arial,Helvetica,sans-serif; }
.preheader { padding:16px 0; text-align:center; font-size:12px; color:#a7adbd; }
.banner { background:linear-gradient(135deg,#e11d48,#f43f5e); padding:42px 30px; text-align:center; }
.banner-sub { color:#ffe4e8; font-size:12px; letter-spacing:2px; text-transform:uppercase; margin:0 0 10px; }
.banner-title { color:#ffffff; font-size:26px; font-weight:700; margin:0; }
.card { background:#ffffff; padding:32px 34px; }
.stat { text-align:center; padding:0 6px 24px; }
.stat b { display:block; font-size:24px; color:#e11d48; }
.stat small { font-size:12px; color:#677788; text-transform:uppercase; letter-spacing:1px; }
.price { background:#fff1f3; border:1px solid #fecdd3; border-radius:10px; padding:22px; text-align:center; margin:4px 0 24px; }
.price b { font-size:28px; color:#e11d48; }
.price p { font-size:13px; color:#677788; margin:8px 0 0; line-height:1.6; }
.btn { display:inline-block; background:#e11d48; color:#ffffff !important; text-decoration:none !important; padding:13px 26px; border-radius:6px; font-weight:600; font-size:14px; }
.signature { border-top:1px solid #eceef1; margin-top:26px; padding-top:18px; font-size:14px; color:#677788; line-height:1.6; }
.footer { padding:22px 30px; text-align:center; font-size:12px; color:#a7adbd; }
</style></head><body>
<div class="preheader">Publish your content on {website_url}</div>
<table role="presentation" width="100%" bgcolor="#f4f6fb" cellpadding="0" cellspacing="0"><tr><td align="center">
<table role="presentation" width="620" cellpadding="0" cellspacing="0" style="max-width:620px; width:100%;">
<tr><td class="banner"><p class="banner-sub">{company_name}</p><h1 class="banner-title">Publish a guest post to our audience</h1></td></tr>
<tr><td class="card">
<p style="font-size:15px; line-height:1.7; color:#384151; margin:0 0 18px;">Hi {contact_name},</p>
<p style="font-size:15px; line-height:1.7; color:#677788; margin:0 0 22px;">We&rsquo;re opening a limited number of guest post placements on <strong>{website_url}</strong> for brands that value quality content.</p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>
<td class="stat"><b>65+</b><small>Domain Authority</small></td>
<td class="stat"><b>180K</b><small>Monthly Readers</small></td>
<td class="stat"><b>48h</b><small>Time to Publish</small></td>
</tr></table>
<div class="price"><b>$199</b><p>One-time placement &middot; 2 dofollow links &middot; content guidelines included &middot; stats report after publishing</p></div>
<div style="text-align:center; margin:8px 0;"><a class="btn" href="#">Claim the Spot</a></div>
<div class="signature">Best,<br/><strong>The {company_name} Editorial Team</strong><br/>{email_address}</div>
</td></tr>
<tr><td class="footer">This offer is limited to 10 spots this month. <a href="#" style="color:#e11d48;">See our guidelines</a></td></tr>
</table></td></tr></table>
</body></html>
HTML;

        EmailTemplate::updateOrCreate(
            ['user_id' => $john->id, 'name' => 'Paid Guest Post Offer'],
            [
                'subject' => 'Publish a Guest Post on {company_name}',
                'content' => $gpPaidOffer,
            ]
        );

        $gpSponsored = <<<'HTML'
<!DOCTYPE html><html><head><style>
body { margin:0; padding:0; background:#f4f6fb; font-family:Arial,Helvetica,sans-serif; }
.preheader { padding:16px 0; text-align:center; font-size:12px; color:#a7adbd; }
.banner { background:linear-gradient(135deg,#0ea5e9,#6366f1); padding:42px 30px; text-align:center; }
.banner-sub { color:#e0f2fe; font-size:12px; letter-spacing:2px; text-transform:uppercase; margin:0 0 10px; }
.banner-title { color:#ffffff; font-size:26px; font-weight:700; margin:0; }
.card { background:#ffffff; padding:32px 34px; }
.steps h2 { font-size:15px; color:#292e3d; margin:0 0 6px; }
.steps p { font-size:14px; line-height:1.7; color:#677788; margin:0 0 18px; }
.btn { display:inline-block; background:#0ea5e9; color:#ffffff !important; text-decoration:none !important; padding:13px 26px; border-radius:6px; font-weight:600; font-size:14px; }
.signature { border-top:1px solid #eceef1; margin-top:26px; padding-top:18px; font-size:14px; color:#677788; line-height:1.6; }
.footer { padding:22px 30px; text-align:center; font-size:12px; color:#a7adbd; }
</style></head><body>
<div class="preheader">A sponsored spotlight for {blog_name}</div>
<table role="presentation" width="100%" bgcolor="#f4f6fb" cellpadding="0" cellspacing="0"><tr><td align="center">
<table role="presentation" width="620" cellpadding="0" cellspacing="0" style="max-width:620px; width:100%;">
<tr><td class="banner"><p class="banner-sub">{company_name}</p><h1 class="banner-title">Let&rsquo;s sponsor one of your next posts</h1></td></tr>
<tr><td class="card">
<p style="font-size:15px; line-height:1.7; color:#384151; margin:0 0 18px;">Hi {contact_name},</p>
<p style="font-size:15px; line-height:1.7; color:#677788; margin:0 0 22px;">We&rsquo;d love to sponsor a post on <strong>{blog_name}</strong> with a subtle, native mention of {website_url} — written by your team and kept 100% in your voice.</p>
<div class="steps"><h2>What&rsquo;s included</h2><p>A soft mention in one upcoming post, a curated topical suggestion for the next {proposed_title}, and a spot in the email recap for 30 days.</p></div>
<div style="text-align:center; margin:8px 0;"><a class="btn" href="#">Let&rsquo;s Get Started</a></div>
<div class="signature">Cheers,<br/><strong>The {company_name} Editorial Team</strong><br/>{email_address} &#183; {website_url}</div>
</td></tr>
<tr><td class="footer">&copy; {date} {company_name} &middot; {website_url}</td></tr>
</table></td></tr></table>
</body></html>
HTML;

        EmailTemplate::updateOrCreate(
            ['user_id' => $john->id, 'name' => 'Sponsored Post Proposal'],
            [
                'subject' => 'Sponsored Post Proposal — {company_name}',
                'content' => $gpSponsored,
            ]
        );

        $gpLinkBuilding = <<<'HTML'
<!DOCTYPE html><html><head><style>
body { margin:0; padding:0; background:#f4f6fb; font-family:Arial,Helvetica,sans-serif; }
.preheader { padding:16px 0; text-align:center; font-size:12px; color:#a7adbd; }
.banner { background:linear-gradient(135deg,#7c3aed,#4f46e5); padding:42px 30px; text-align:center; }
.banner-sub { color:#ede9fe; font-size:12px; letter-spacing:2px; text-transform:uppercase; margin:0 0 10px; }
.banner-title { color:#ffffff; font-size:26px; font-weight:700; margin:0; }
.card { background:#ffffff; padding:32px 34px; }
.points h2 { font-size:15px; color:#292e3d; margin:0 0 6px; }
.points p { font-size:14px; line-height:1.7; color:#677788; margin:0 0 18px; }
.btn { display:inline-block; background:#7c3aed; color:#ffffff !important; text-decoration:none !important; padding:13px 26px; border-radius:6px; font-weight:600; font-size:14px; }
.signature { border-top:1px solid #eceef1; margin-top:26px; padding-top:18px; font-size:14px; color:#677788; line-height:1.6; }
.footer { padding:22px 30px; text-align:center; font-size:12px; color:#a7adbd; }
</style></head><body>
<div class="preheader">A high-value link opportunity from {company_name}</div>
<table role="presentation" width="100%" bgcolor="#f4f6fb" cellpadding="0" cellspacing="0"><tr><td align="center">
<table role="presentation" width="620" cellpadding="0" cellspacing="0" style="max-width:620px; width:100%;">
<tr><td class="banner"><p class="banner-sub">{company_name}</p><h1 class="banner-title">A link your readers will appreciate</h1></td></tr>
<tr><td class="card">
<p style="font-size:15px; line-height:1.7; color:#384151; margin:0 0 18px;">Hi {contact_name},</p>
<p style="font-size:15px; line-height:1.7; color:#677788; margin:0 0 22px;">We track valuable, editorial links for sites like <strong>{blog_name}</strong>. This month we&rsquo;re placing one on {website_url} with a naturally-written anchor relevant to your niche.</p>
<div class="points"><h2>Why it works</h2><p>A dofollow link from a DR 72 page with tight topical relevance &mdash; surrounded by genuinely useful content your readers will love.</p></div>
<div class="points"><h2>Exactly one link</h2><p>No keyword stuffing, no spam networks. One clean, contextual mention built for the long run.</p></div>
<div style="text-align:center; margin:8px 0;"><a class="btn" href="#">Check the Opportunity</a></div>
<div class="signature">Warmly,<br/><strong>The {company_name} Editorial Team</strong><br/>{email_address} &#183; {website_url}</div>
</td></tr>
<tr><td class="footer">You&rsquo;re receiving this because of our outreach partnership program. <a href="#" style="color:#7c3aed;">Unsubscribe</a></td></tr>
</table></td></tr></table>
</body></html>
HTML;

        EmailTemplate::updateOrCreate(
            ['user_id' => $john->id, 'name' => 'Link Building Outreach'],
            [
                'subject' => 'A High-Value Link Opportunity from {company_name}',
                'content' => $gpLinkBuilding,
            ]
        );
    }
}
