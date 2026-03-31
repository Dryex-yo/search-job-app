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
            <h1>✓ Lamaran Diterima</h1>
            <p>Dryex - Platform Pencarian Kerja Masa Depan</p>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">
                Halo <strong>{{ $userName }}</strong>,
            </div>

            <p style="line-height: 1.6; margin-bottom: 16px;">
                Terima kasih sudah melamar di platform Dryex. Kami dengan senang hati mengonfirmasi bahwa lamaran Anda untuk posisi <strong>{{ $jobTitle }}</strong> telah kami terima.
            </p>

            <div class="job-info">
                <div class="info-row">
                    <div class="job-info-label">Posisi yang Anda Lamar</div>
                    <div class="job-info-value">{{ $jobTitle }}</div>
                </div>
                <div class="info-row">
                    <div class="job-info-label">Perusahaan</div>
                    <div class="job-info-value">
                        <span class="company-name">{{ $companyName }}</span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="job-info-label">Tanggal Lamaran</div>
                    <div class="job-info-value">{{ $applicationDate }}</div>
                </div>
            </div>

            <p style="line-height: 1.6; margin-bottom: 16px;">
                Tim rekrutmen kami akan meninjau lamaran Anda dalam waktu dekat. Anda akan menerima notifikasi email setiap kali ada update status lamaran Anda.
            </p>

            <div class="divider"></div>

            <p style="font-size: 14px; color: #6b7280; margin-bottom: 16px;">
                <strong>Langkah Selanjutnya:</strong>
            </p>

            <ul style="margin-left: 20px; margin-bottom: 24px; color: #555; line-height: 1.8;">
                <li>Pantau email Anda untuk update status</li>
                <li>Pastikan informasi kontak Anda tetap aktif</li>
                <li>Persiapkan diri untuk kemungkinan wawancara</li>
            </ul>

            <div style="text-align: center;">
                <a href="{{ $trackingUrl }}" class="button">Pantau Lamaran Anda</a>
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
