<?php

namespace App\Http\Controllers\Installer;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class InstallerController extends Controller
{
    public function index()
    {
        if (file_exists(storage_path('installed'))) {
            return redirect('/');
        }
        return view('installer.index');
    }

    public function checkRequirements()
    {
        $requirements = [
            'PHP >= 8.1' => version_compare(PHP_VERSION, '8.1.0', '>='),
            'BCMath' => extension_loaded('bcmath'),
            'Ctype' => extension_loaded('ctype'),
            'cURL' => extension_loaded('curl'),
            'DOM' => extension_loaded('dom'),
            'Fileinfo' => extension_loaded('fileinfo'),
            'JSON' => extension_loaded('json'),
            'Mbstring' => extension_loaded('mbstring'),
            'OpenSSL' => extension_loaded('openssl'),
            'PDO' => extension_loaded('pdo'),
            'PDO MySQL' => extension_loaded('pdo_mysql'),
            'Tokenizer' => extension_loaded('tokenizer'),
            'XML' => extension_loaded('xml'),
            'GD' => extension_loaded('gd'),
        ];

        $permissions = [
            'storage/app' => is_writable(storage_path('app')),
            'storage/framework' => is_writable(storage_path('framework')),
            'storage/logs' => is_writable(storage_path('logs')),
            'bootstrap/cache' => is_writable(base_path('bootstrap/cache')),
            '.env' => is_writable(base_path('.env')),
        ];

        return view('installer.requirements', compact('requirements', 'permissions'));
    }

    public function showDatabase()
    {
        return view('installer.database');
    }

    public function setupDatabase(Request $request)
    {
        $request->validate([
            'db_host' => 'required',
            'db_port' => 'required|numeric',
            'db_name' => 'required',
            'db_user' => 'required',
            'db_pass' => 'nullable',
        ]);

        try {
            $pdo = new \PDO(
                "mysql:host={$request->db_host};port={$request->db_port};dbname={$request->db_name}",
                $request->db_user,
                $request->db_pass
            );

            $envContent = file_get_contents(base_path('.env'));
            $envContent = preg_replace('/DB_HOST=.*/', "DB_HOST={$request->db_host}", $envContent);
            $envContent = preg_replace('/DB_PORT=.*/', "DB_PORT={$request->db_port}", $envContent);
            $envContent = preg_replace('/DB_DATABASE=.*/', "DB_DATABASE={$request->db_name}", $envContent);
            $envContent = preg_replace('/DB_USERNAME=.*/', "DB_USERNAME={$request->db_user}", $envContent);
            $envContent = preg_replace('/DB_PASSWORD=.*/', "DB_PASSWORD={$request->db_pass}", $envContent);
            file_put_contents(base_path('.env'), $envContent);

            Artisan::call('config:clear');

            config([
                'database.connections.mysql.host' => $request->db_host,
                'database.connections.mysql.port' => $request->db_port,
                'database.connections.mysql.database' => $request->db_name,
                'database.connections.mysql.username' => $request->db_user,
                'database.connections.mysql.password' => $request->db_pass,
            ]);

            DB::purge('mysql');
            Artisan::call('migrate', ['--force' => true]);

            return redirect()->route('installer.admin');
        } catch (\Exception $e) {
            return back()->withErrors(['db' => 'Koneksi database gagal: ' . $e->getMessage()]);
        }
    }

    public function showAdmin()
    {
        return view('installer.admin');
    }

    public function setupAdmin(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'admin',
            'referral_code' => strtoupper(Str::random(8)),
            'email_verified_at' => now(),
        ]);

        return redirect()->route('installer.site');
    }

    public function showSite()
    {
        return view('installer.site');
    }

    public function setupSite(Request $request)
    {
        $request->validate([
            'site_name' => 'required',
            'site_url' => 'required|url',
        ]);

        $envContent = file_get_contents(base_path('.env'));
        $envContent = preg_replace('/APP_NAME=.*/', "APP_NAME=\"{$request->site_name}\"", $envContent);
        $envContent = preg_replace('/APP_URL=.*/', "APP_URL={$request->site_url}", $envContent);
        file_put_contents(base_path('.env'), $envContent);

        \App\Models\Setting::set('site_name', $request->site_name, 'general');
        \App\Models\Setting::set('site_url', $request->site_url, 'general');

        Artisan::call('key:generate', ['--force' => true]);
        Artisan::call('storage:link');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');

        file_put_contents(storage_path('installed'), now()->toIso8601String());

        return redirect()->route('installer.complete');
    }

    public function complete()
    {
        return view('installer.complete');
    }
}
