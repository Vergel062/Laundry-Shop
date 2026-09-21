<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class LandingContentController extends Controller
{
    private const DEFAULTS = [
        'about_title' => 'Modernizing Garment Care Since 2015',
        'about_content' => 'Established in 2015, Laundry Care and Services started with a mission to provide dependable washing and drying solutions for local communities. Today, our operations span across 7 strategic branches in Carmona, Cavite, Biñan, and Cabuyao, Laguna—handling hundreds of regular transactions monthly.',
        'about_secondary' => 'To ensure total service transparency, eliminate record errors, and streamline supply inventory, we integrated our Online Transaction and Monitoring System. This digital solution keeps you connected to your garment status every step of the way.',
        'about_image' => 'image/laundry.jpg',
        'contact_description' => 'Have questions about our multi-branch services, drop-offs, or payment processing? Send us a message!',
        'contact_address' => '9300 JM Loyola St., Maduya, Carmona, Cavite',
        'contact_email' => 'laundrycare4@gmail.com',
        'contact_phone' => '+63 909-825-6981 / +63 910-910-7296',
        'contact_hours' => 'Monday – Sunday: 8:00 AM – 7:00 PM',
    ];

    public function index()
    {
        if (!$this->isAdmin()) {
            return redirect()->route('login');
        }

        $content = $this->content();

        return view('admin.modified_content', compact('content'));
    }

    public function landing()
    {
        $content = $this->content();

        return view('landing', compact('content'));
    }

    public function update(Request $request)
    {
        if (!$this->isAdmin()) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'about_title' => 'required|string|max:255',
            'about_content' => 'required|string|max:5000',
            'about_secondary' => 'required|string|max:5000',
            'about_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'contact_description' => 'required|string|max:1000',
            'contact_address' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'required|string|max:100',
            'contact_hours' => 'required|string|max:255',
        ]);

        $currentImage = DB::table('landing_contents')
            ->where('content_key', 'about_image')
            ->value('content_value') ?: self::DEFAULTS['about_image'];

        if ($request->hasFile('about_image')) {
            $validated['about_image'] = $request->file('about_image')->store('landing_images', 'public');
        } else {
            $validated['about_image'] = $currentImage;
        }

        foreach ($validated as $key => $value) {
            DB::table('landing_contents')->updateOrInsert(
                ['content_key' => $key],
                ['content_value' => $value, 'updated_at' => now(), 'created_at' => now()]
            );
        }

        if (
            $request->hasFile('about_image') &&
            str_starts_with($currentImage, 'landing_images/') &&
            $currentImage !== $validated['about_image']
        ) {
            Storage::disk('public')->delete($currentImage);
        }

        return back()->with('success', 'Landing page content updated successfully.');
    }

    public function sendMessage(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:150',
            'message' => 'required|string|max:5000',
        ]);

        Mail::raw(
            "Name: {$validated['name']}\nEmail: {$validated['email']}\n\n{$validated['message']}",
            function ($mail) use ($validated) {
                $mail->to('laundrycare4@gmail.com')
                    ->replyTo($validated['email'], $validated['name'])
                    ->subject('LaundryCare Contact Message: ' . $validated['subject']);
            }
        );

        return back()->with('contact_success', 'Your message has been sent successfully.');
    }

    public static function defaults(): array
    {
        return self::DEFAULTS;
    }

    private function content(): array
    {
        $values = DB::table('landing_contents')
            ->whereIn('content_key', array_keys(self::DEFAULTS))
            ->pluck('content_value', 'content_key')
            ->all();

        return array_merge(self::DEFAULTS, $values);
    }

    private function isAdmin(): bool
    {
        $user = auth()->user();

        return $user && $user->role === 'admin';
    }
}
