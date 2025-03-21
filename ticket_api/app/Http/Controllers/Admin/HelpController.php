<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HelpController extends Controller
{
    /**
     * Display the admin help page.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        $faqs = [
            [
                'question' => 'Bagaimana cara menambahkan kapal baru?',
                'answer' => 'Buka menu Ferries di sidebar, lalu klik tombol "Tambah Kapal Baru". Isi semua detail yang diperlukan seperti nama kapal, kapasitas, dan status, kemudian klik "Simpan".'
            ],
            [
                'question' => 'Bagaimana cara mengelola pemesanan?',
                'answer' => 'Anda dapat mengelola semua pemesanan dari bagian Bookings. Di sini Anda dapat melihat, mengkonfirmasi, membatalkan atau menyelesaikan pemesanan. Gunakan filter untuk menemukan pemesanan berdasarkan status, tanggal, atau nama pelanggan.'
            ],
            [
                'question' => 'Bagaimana cara membuat rute baru?',
                'answer' => 'Navigasi ke bagian Routes dan klik "Tambah Rute". Masukkan asal, tujuan dan detail lainnya, lalu simpan rute tersebut. Setelah dibuat, Anda dapat menambahkan jadwal untuk rute ini.'
            ],
            [
                'question' => 'Bagaimana cara melihat laporan?',
                'answer' => 'Semua laporan tersedia di bagian Reports. Anda dapat melihat laporan harian, bulanan, rute-spesifik atau tingkat okupansi untuk menganalisis kinerja bisnis Anda.'
            ],
            [
                'question' => 'Bagaimana cara mengubah password saya?',
                'answer' => 'Buka Settings > Profile, masukkan password lama Anda dan password baru yang ingin Anda gunakan, lalu klik "Update Profile".'
            ],
        ];

        $guides = [
            [
                'title' => 'Panduan Memulai',
                'description' => 'Pengenalan dasar untuk panel admin dan fitur-fiturnya.',
                'link' => '#getting-started'
            ],
            [
                'title' => 'Mengelola Kapal Ferry',
                'description' => 'Cara menambah, mengedit, dan mengelola informasi kapal.',
                'link' => '#managing-ferries'
            ],
            [
                'title' => 'Mengatur Rute dan Jadwal',
                'description' => 'Panduan untuk membuat dan mengelola rute dan jadwal.',
                'link' => '#routes-schedules'
            ],
            [
                'title' => 'Menangani Pemesanan',
                'description' => 'Panduan lengkap tentang proses manajemen pemesanan.',
                'link' => '#handling-bookings'
            ],
            [
                'title' => 'Menghasilkan Laporan',
                'description' => 'Cara menggunakan sistem pelaporan secara efektif.',
                'link' => '#reports'
            ],
        ];

        return view('admin.help.index', compact('faqs', 'guides'));
    }

    /**
     * Display the specific help topic.
     *
     * @param  string  $topic
     * @return \Illuminate\Contracts\View\View
     */
    public function topic($topic)
    {
        // Logic to fetch specific help topic content
        $content = $this->getTopicContent($topic);

        return view('admin.help.topic', compact('topic', 'content'));
    }

    /**
     * Get content for a specific help topic.
     *
     * @param  string  $topic
     * @return array
     */
    private function getTopicContent($topic)
    {
        // This would ideally come from a database, but for now we'll use a switch case
        switch ($topic) {
            case 'getting-started':
                return [
                    'title' => 'Memulai dengan Admin Ferry Ticket',
                    'content' => 'Ini adalah konten panduan memulai...'
                ];
            case 'managing-ferries':
                return [
                    'title' => 'Mengelola Kapal Ferry',
                    'content' => 'Ini adalah konten panduan mengelola kapal ferry...'
                ];
            // Add more cases for other topics
            default:
                return [
                    'title' => 'Topik Bantuan Tidak Ditemukan',
                    'content' => 'Topik bantuan yang diminta tidak dapat ditemukan.'
                ];
        }
    }

    /**
     * Display the contact support form.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function contactSupport()
    {
        return view('admin.help.contact');
    }

    /**
     * Send a support request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendSupportRequest(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'category' => 'required|string|max:100',
        ]);

        // Di sini biasanya akan mengirim email atau membuat tiket di sistem support Anda
        // Untuk contoh ini, kita hanya akan redirect dengan pesan sukses

        return redirect()->route('admin.help.contact')->with('success', 'Permintaan dukungan Anda telah dikirim. Tim kami akan merespons segera.');
    }
}
