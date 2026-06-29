<?php
namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Hash, Config};

class AdminSettingsController extends Controller
{
    public function show()
    {
        $smtp = [
            'host'       => Setting::get('mail_host', config('mail.mailers.smtp.host')),
            'port'       => Setting::get('mail_port', config('mail.mailers.smtp.port')),
            'username'   => Setting::get('mail_username', config('mail.from.address')),
            'encryption' => Setting::get('mail_encryption', config('mail.mailers.smtp.encryption')),
            'from_name'  => Setting::get('mail_from_name', config('mail.from.name')),
        ];
        return view('admin.settings', compact('smtp'));
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,' . Auth::id(),
        ]);
        Auth::user()->update($data);
        return back()->with('success', 'Profilo aggiornato!');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|min:8|confirmed',
        ]);
        if (!Hash::check($request->current_password, Auth::user()->password)) {
            return back()->withErrors(['current_password' => 'La password attuale non è corretta.']);
        }
        Auth::user()->update(['password' => Hash::make($request->password)]);
        return back()->with('success', 'Password cambiata con successo!');
    }

    public function updateSmtp(Request $request)
    {
        $request->validate([
            'mail_host'       => 'required|string',
            'mail_port'       => 'required|integer',
            'mail_username'   => 'required|email',
            'mail_password'   => 'nullable|string',
            'mail_encryption' => 'required|in:tls,ssl,starttls',
            'mail_from_name'  => 'required|string|max:100',
        ]);

        Setting::set('mail_host',       $request->mail_host);
        Setting::set('mail_port',       $request->mail_port);
        Setting::set('mail_username',   $request->mail_username);
        Setting::set('mail_encryption', $request->mail_encryption);
        Setting::set('mail_from_name',  $request->mail_from_name);
        Setting::set('reseller_email',  $request->mail_username);

        if ($request->filled('mail_password')) {
            Setting::set('mail_password', encrypt($request->mail_password));
        }

        return back()->with('success', 'Configurazione email salvata! Riavvia il server per applicarla.');
    }

    public function testSmtp(Request $request)
    {
        try {
            $this->applySmtpFromDb();
            \Illuminate\Support\Facades\Mail::raw(
                'Test email dal gestionale A.V.R. Informatica — configurazione SMTP funzionante!',
                fn($m) => $m->to(Auth::user()->email ?? Setting::get('mail_username'))
                             ->subject('Test SMTP — Gestionale AVR')
            );
            return back()->with('success', 'Email di test inviata con successo!');
        } catch (\Exception $e) {
            return back()->with('danger', 'Errore invio: ' . $e->getMessage());
        }
    }

    public static function applySmtpFromDb(): void
    {
        if (!Setting::find('mail_host')) return;
        config([
            'mail.mailers.smtp.host'       => Setting::get('mail_host'),
            'mail.mailers.smtp.port'       => Setting::get('mail_port'),
            'mail.mailers.smtp.username'   => Setting::get('mail_username'),
            'mail.mailers.smtp.password'   => Setting::get('mail_password') ? decrypt(Setting::get('mail_password')) : null,
            'mail.mailers.smtp.encryption' => Setting::get('mail_encryption'),
            'mail.from.address'            => Setting::get('mail_username'),
            'mail.from.name'               => Setting::get('mail_from_name'),
        ]);
        if (Setting::get('reseller_email')) {
            config(['avr.reseller_email' => Setting::get('reseller_email')]);
        }
    }
}
