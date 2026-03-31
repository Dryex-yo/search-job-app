<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f5f5f5;
            color: #333;
            overflow-x: hidden;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            padding: 40px 20px;
            text-align: center;
            color: white;
        }
        .header h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .header p {
            font-size: 14px;
            opacity: 0.9;
        }
        .content {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 24px;
            color: #333;
        }
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
            margin: 16px 0;
        }
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        .status-reviewing {
            background: #bfdbfe;
            color: #1e40af;
        }
        .status-shortlisted {
            background: #c7d2fe;
            color: #312e81;
        }
        .status-interviewed {
            background: #d1d5db;
            color: #374151;
        }
        .status-rejected {
            background: #fee2e2;
            color: #991b1b;
        }
        .status-accepted {
            background: #dcfce7;
            color: #166534;
        }
        .job-info {
            background: #f9fafb;
            border-left: 4px solid #06b6d4;
            padding: 20px;
            border-radius: 6px;
            margin: 24px 0;
        }
        .job-info-label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
            margin-bottom: 4px;
        }
        .job-info-value {
            font-size: 16px;
            color: #111;
            font-weight: 500;
        }
        .info-row {
            margin-bottom: 16px;
        }
        .info-row:last-child {
            margin-bottom: 0;
        }
        .message-box {
            background: #fef3c7;
            border: 1px solid #fcd34d;
            border-radius: 6px;
            padding: 16px;
            margin: 24px 0;
            color: #92400e;
            font-size: 14px;
            line-height: 1.6;
        }
        .button {
            display: inline-block;
            background: #06b6d4;
            color: white;
            padding: 12px 32px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin-top: 24px;
            transition: background 0.3s ease;
        }
        .button:hover {
            background: #0891b2;
        }
        .footer {
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
            padding: 20px 30px;
            font-size: 12px;
            color: #6b7280;
            text-align: center;
        }
        .footer-link {
            color: #06b6d4;
            text-decoration: none;
        }
        .footer-link:hover {
            text-decoration: underline;
        }
        .divider {
            height: 1px;
            background: #e5e7eb;
            margin: 24px 0;
        }
        .company-name {
            color: #0891b2;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <h1>📋 Update Status Lamaran</h1>
            <p>Dryex - Platform Pencarian Kerja Masa Depan</p>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">
                Halo <strong>{{ $userName }}</strong>,
            </div>

            <p style="line-height: 1.6; margin-bottom: 16px;">
                Ada update terbaru mengenai status lamaran Anda untuk posisi <strong>{{ $jobTitle }}</strong> di <span class="company-name">{{ $companyName }}</span>:
            </p>

            <div class="status-badge status-{{ $status }}">
                {{ $statusLabel }}
            </div>

            <div class="job-info">
                <div class="info-row">
                    <div class="job-info-label">Posisi</div>
                    <div class="job-info-value">{{ $jobTitle }}</div>
                </div>
                <div class="info-row">
                    <div class="job-info-label">Perusahaan</div>
                    <div class="job-info-value">
                        <span class="company-name">{{ $companyName }}</span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="job-info-label">Status Terbaru</div>
                    <div class="job-info-value" style="font-weight: 700; color: #06b6d4;">{{ $statusLabel }}</div>
                </div>
            </div>

            @if($status === 'shortlisted')
                <div class="message-box">
                    🎉 Selamat! Lamaran Anda telah lolos ke tahap seleksi berikutnya. Tim kami akan segera menghubungi Anda untuk instruksi selanjutnya.
                </div>
            @elseif($status === 'interviewed')
                <div class="message-box">
                    📞 Anda telah dipilih untuk sesi wawancara. Silakan cek email atau telepon Anda untuk jadwal wawancara yang lebih detail.
                </div>
            @elseif($status === 'accepted')
                <div class="message-box">
                    🎊 Selamat! Lamaran Anda telah diterima. Hubungi kami untuk diskusi lebih lanjut mengenai penawaran posisi ini.
                </div>
            @elseif($status === 'rejected')
                <div class="message-box">
                    Terima kasih atas minat Anda pada posisi ini. Sayangnya, kami telah memilih kandidat lain untuk kali ini. Kami mendorong Anda untuk tetap memantau lowongan kami di masa depan.
                </div>
            @endif

            <p style="line-height: 1.6; margin-bottom: 16px; margin-top: 24px;">
                Jika Anda memiliki pertanyaan atau memerlukan informasi lebih lanjut, jangan ragu untuk menghubungi tim dukungan kami.
            </p>

            <div style="text-align: center;">
                <a href="{{ $trackingUrl }}" class="button">Lihat Detail Lamaran</a>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>© 2026 Dryex Ecosystem. Semua hak dilindungi.</p>
            <p style="margin-top: 8px;">
                <a href="https://dryex.com" class="footer-link">Kunjungi Website</a> | 
                <a href="mailto:support@dryex.com" class="footer-link">Hubungi Kami</a>
            </p>
        </div>
    </div>
</body>
</html>
