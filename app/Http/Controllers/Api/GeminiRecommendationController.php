<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GeminiRecommendationController extends Controller
{
    public function getRecommendation($deviceId)
    {
        try {
            // 1. Ambil data pH terakhir dari device tersebut
            $device = Device::with(['soilPHs' => function ($query) {
                $query->latest()->first();
            }])->findOrFail($deviceId);

            $latestPhData = $device->soilPHs->first();

            if (!$latestPhData) {
                return response()->json(['success' => false, 'message' => 'Data pH belum tersedia.'], 404);
            }

            $currentPh = $latestPhData->value_ph;

            // 2. Cek apakah pH masih dalam batas aman (Kopi Robusta idealnya 5.5 - 6.5)
            // Jika aman, tidak perlu memanggil API Gemini (menghemat kuota & waktu proses)
            if ($currentPh >= 5.5 && $currentPh <= 6.5) {
                return response()->json([
                    'success' => true,
                    'status' => 'Aman',
                    'ph' => $currentPh,
                    'recommendation' => 'Kadar pH tanah saat ini sangat ideal untuk bibit kopi Robusta. Lanjutkan perawatan rutin.'
                ]);
            }

            // 3. Jika pH tidak normal, susun Prompt/Perintah untuk Gemini
            // Kita beri instruksi ketat agar jawabannya singkat dan langsung ke solusi (tidak bertele-tele)
            $prompt = "Saya adalah petani bibit kopi robusta di greenhouse. Saat ini sensor IoT saya mendeteksi pH tanah berada di angka {$currentPh}. Angka ini di luar batas normal. Sebagai ahli pertanian, berikan 2 langkah praktis, murah, dan singkat untuk menormalkan kembali pH tanah tersebut. Jangan gunakan format markdown yang rumit, cukup teks biasa yang mudah dibaca.";

            // 4. Panggil API Gemini (Menggunakan model gemini-1.5-flash yang sangat cepat)
            $apiKey = env('GEMINI_API_KEY');
            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}";

            $response = Http::timeout(15)->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $geminiResult = $response->json();

                // Ekstrak teks jawaban dari struktur JSON Gemini
                $recommendationText = $geminiResult['candidates'][0]['content']['parts'][0]['text'] ?? 'Gagal memproses rekomendasi.';

                return response()->json([
                    'success' => true,
                    'status' => 'Peringatan',
                    'ph' => $currentPh,
                    'recommendation' => $recommendationText
                ]);
            }

            return response()->json(['success' => false, 'message' => 'Gagal menghubungi server AI.'], 500);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }
}
