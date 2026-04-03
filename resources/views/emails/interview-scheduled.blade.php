<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interview Scheduled</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            margin-bottom: 10px;
        }
        .content {
            padding: 30px 20px;
        }
        .greeting {
            font-size: 16px;
            color: #333;
            margin-bottom: 20px;
        }
        .interview-details {
            background: #f9f9f9;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .detail-row {
            display: flex;
            margin: 12px 0;
            font-size: 14px;
        }
        .detail-label {
            font-weight: 600;
            color: #667eea;
            width: 140px;
            margin-right: 15px;
        }
        .detail-value {
            color: #555;
            flex: 1;
        }
        .meeting-link {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 12px 24px;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 15px;
            font-weight: 600;
            transition: background 0.3s;
        }
        .meeting-link:hover {
            background: #764ba2;
        }
        .instructions {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            font-size: 13px;
            color: #333;
        }
        .instructions h3 {
            color: #2196f3;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .instructions ul {
            margin-left: 20px;
            line-height: 1.8;
        }
        .notes-section {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            font-size: 13px;
        }
        .notes-section h3 {
            color: #f57f17;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .footer {
            background: #f9f9f9;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #eee;
            font-size: 12px;
            color: #999;
        }
        .divider {
            height: 1px;
            background: #eee;
            margin: 20px 0;
        }
        .badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin: 5px 0;
        }
        .badge-technical {
            background: #c8e6c9;
            color: #2e7d32;
        }
        .badge-hr {
            background: #ffe0b2;
            color: #e65100;
        }
        .badge-general {
            background: #e1bee7;
            color: #7b1fa2;
        }
        .badge-final {
            background: #bbdefb;
            color: #0d47a1;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎯 Interview Scheduled</h1>
            <p>Your interview has been confirmed</p>
        </div>

        <div class="content">
            @if ($recipientType === 'applicant')
                <div class="greeting">
                    <p>Halo <strong>{{ $applicantName }}</strong>,</p>
                    <p>Selamat! Kami dengan senang hati mengundang Anda untuk mengikuti wawancara untuk posisi <strong>{{ $jobTitle }}</strong>. Berikut adalah detail wawancara Anda:</p>
                </div>
            @else
                <div class="greeting">
                    <p>Halo <strong>Admin/Recruiter</strong>,</p>
                    <p>Wawancara telah dijadwalkan untuk kandidat <strong>{{ $applicantName }}</strong> untuk posisi <strong>{{ $jobTitle }}</strong>. Berikut adalah detail lengkapnya:</p>
                </div>
            @endif

            <div class="interview-details">
                <div class="detail-row">
                    <div class="detail-label">📅 Tanggal & Waktu</div>
                    <div class="detail-value">{{ $interviewDateTime->format('d M Y H:i') }}</div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label">⏱️ Durasi</div>
                    <div class="detail-value">{{ $duration }} menit</div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label">🎤 Jenis Interview</div>
                    <div class="detail-value">
                        <span class="badge badge-{{ strtolower($interviewType) }}">
                            @switch($interviewType)
                                @case('technical')
                                    Technical Interview
                                    @break
                                @case('hr')
                                    HR Interview
                                    @break
                                @case('final')
                                    Final Interview
                                    @break
                                @default
                                    General Interview
                            @endswitch
                        </span>
                    </div>
                </div>

                <div class="detail-row">
                    <div class="detail-label">📍 Platform</div>
                    <div class="detail-value">
                        @if ($meetingProvider === 'zoom')
                            Zoom Meeting
                        @elseif ($meetingProvider === 'google_meet')
                            Google Meet
                        @else
                            Online Meeting
                        @endif
                    </div>
                </div>

                @if ($meetingLink)
                    <div style="text-align: center; margin-top: 15px;">
                        <a href="{{ $meetingLink }}" class="meeting-link">
                            🔗 Bergabung dengan Meeting
                        </a>
                    </div>
                @endif
            </div>

            <div class="instructions">
                <h3>📋 Persiapan Wawancara:</h3>
                <ul>
                    <li>Pastikan koneksi internet Anda stabil</li>
                    <li>Gunakan perangkat dengan kamera dan mikrofon yang berfungsi</li>
                    @if ($meetingProvider === 'zoom')
                        <li>Pastikan Anda memiliki Zoom installed atau buka link melalui browser</li>
                    @elseif ($meetingProvider === 'google_meet')
                        <li>Google Meet dapat diakses langsung dari browser, tidak perlu install aplikasi</li>
                    @endif
                    <li>Bergabunglah 5 menit sebelum jadwal yang ditentukan</li>
                    <li>Pilih lokasi yang tenang dan profesional</li>
                </ul>
            </div>

            @if ($notes)
                <div class="notes-section">
                    <h3>⚠️ Catatan Penting:</h3>
                    <p>{{ $notes }}</p>
                </div>
            @endif

            <div class="divider"></div>

            @if ($recipientType === 'applicant')
                <p style="color: #666; font-size: 14px;">
                    Jika Anda memiliki pertanyaan atau perlu mengubah jadwal, silakan hubungi kami melalui email atau dashboard aplikasi.
                </p>
            @else
                <p style="color: #666; font-size: 14px;">
                    Notifikasi ini juga telah dikirimkan kepada kandidat. Anda dapat mengelola jadwal atau menghubungi kandidat melalui dashboard.
                </p>
            @endif

            <p style="color: #999; font-size: 12px; margin-top: 15px;">
                ✅ Kalender Google Anda telah diperbarui dengan detail wawancara ini.
            </p>
        </div>

        <div class="footer">
            <p>
                Sistem Penjadwalan Wawancara Otomatis<br>
                Dryex - Platform Pencarian Kerja<br>
                <a href="{{ config('app.url') }}" style="color: #667eea; text-decoration: none;">Kunjungi Dashboard</a>
            </p>
        </div>
    </div>
</body>
</html>
