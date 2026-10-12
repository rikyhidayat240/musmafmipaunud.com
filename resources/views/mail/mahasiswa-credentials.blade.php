<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akun MUSMA LM FMIPA {{ $tahun }}</title>
    <style>
        body {
            margin: 0; padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            background-color: #24040b;
            color: #d1c4c7;
            -webkit-font-smoothing: antialiased;
        }
        .wrapper {
            max-width: 600px;
            margin: 40px auto;
            background-color: #3b0613;
            border: 1px solid #5a0e20;
            border-radius: 12px;
            overflow: hidden;
            width: 100%;
            box-sizing: border-box;
            box-shadow: 0 10px 25px rgba(0,0,0,0.4);
        }
        .header {
            background: linear-gradient(135deg, #51071B 0%, #35030e 100%);
            border-bottom: 2px solid rgba(235,206,103,0.5);
            padding: 32px 40px;
            text-align: center;
        }
        .header h1 {
            margin: 0 0 6px;
            font-size: 22px;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: 0.5px;
        }
        .header p {
            margin: 0;
            font-size: 12px;
            color: #EBCE67;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: 600;
        }
        .body {
            padding: 36px 40px;
            box-sizing: border-box;
        }
        .greeting {
            font-size: 15px;
            color: #f5efe6;
            margin: 0 0 20px;
            line-height: 1.6;
        }
        .info-card {
            background-color: #2b040e;
            border: 1px dashed #EBCE67;
            border-radius: 10px;
            padding: 24px 28px;
            margin: 24px 0;
            box-sizing: border-box;
            width: 100%;
        }
        .info-card h3 {
            margin: 0 0 16px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #EBCE67;
        }
        .credential-table {
            width: 100%;
        }
        .credential-table td {
            padding: 8px 0;
        }
        .credential-label {
            font-size: 13px;
            color: #d1c4c7;
            width: 100px;
        }
        .credential-value {
            font-family: 'Courier New', Courier, monospace;
            font-size: 16px;
            font-weight: 700;
            color: #ffffff;
            background-color: #1a0208;
            padding: 6px 12px;
            border-radius: 6px;
            letter-spacing: 2px;
            border: 1px solid #5a0e20;
            display: inline-block;
            word-break: break-all;
        }
        .btn-container {
            text-align: center;
            margin: 32px 0;
        }
        .btn {
            display: inline-block;
            padding: 12px 28px;
            background: linear-gradient(135deg, #EBCE67 0%, #C5AA6C 100%);
            color: #3b0613;
            font-weight: 700;
            font-size: 14px;
            border-radius: 6px;
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(235,206,103,0.3);
        }
        .warning {
            background-color: #4a0918;
            border: 1px solid #75142a;
            border-radius: 8px;
            padding: 14px 18px;
            font-size: 13px;
            color: #ffc9d2;
            margin: 20px 0;
            line-height: 1.5;
            box-sizing: border-box;
            width: 100%;
        }
        .steps {
            margin: 30px 0 10px;
        }
        .steps p {
            font-size: 13px;
            color: #a89498;
            margin: 0 0 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .step-table {
            width: 100%;
            margin-bottom: 12px;
            border-spacing: 0;
        }
        .step-num-td {
            width: 36px;
            vertical-align: top;
            padding-top: 2px;
        }
        .step-text-td {
            vertical-align: top;
            font-size: 14px;
            color: #d1c4c7;
            line-height: 1.6;
        }
        .step-num {
            display: inline-block;
            width: 24px;
            height: 24px;
            line-height: 24px;
            text-align: center;
            background-color: #24040b;
            border: 1px solid #EBCE67;
            border-radius: 50%;
            font-size: 12px;
            font-weight: 700;
            color: #EBCE67;
        }
        .footer {
            border-top: 1px solid #5a0e20;
            padding: 24px 40px;
            text-align: center;
            font-size: 12px;
            color: #8f797d;
            background-color: #24040b;
            box-sizing: border-box;
        }
        
        /* Mobile Responsiveness */
        @media only screen and (max-width: 600px) {
            .wrapper {
                margin: 20px auto;
                border-radius: 8px;
                border-left: none;
                border-right: none;
            }
            .header, .body, .footer {
                padding-left: 20px !important;
                padding-right: 20px !important;
            }
            .info-card {
                padding: 20px 16px !important;
            }
            .header h1 {
                font-size: 20px !important;
            }
            .credential-value {
                font-size: 15px !important;
                letter-spacing: 1px !important;
            }
            .credential-label {
                width: 80px !important;
                font-size: 12px !important;
            }
        }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>🏛️ MUSMA LM FMIPA {{ $tahun }}</h1>
        <p>Informasi Akun Peserta</p>
    </div>

    <div class="body">
        <p class="greeting">
            Halo <strong style="color: #ffffff;">{{ $mahasiswa->nama }}</strong>,<br>
            Akun Anda untuk sistem musyawarah <strong style="color: #EBCE67;">MUSMA LM FMIPA {{ $tahun }}</strong> telah berhasil didaftarkan.
            Gunakan informasi di bawah ini untuk masuk ke sistem pemilihan / persidangan.
        </p>

        <div class="info-card">
            <h3>Informasi Login Anda</h3>
            <table class="credential-table">
                <tr>
                    <td class="credential-label">NIM</td>
                    <td><span class="credential-value">{{ $mahasiswa->nim }}</span></td>
                </tr>
                <tr>
                    <td class="credential-label">Password</td>
                    <td><span class="credential-value">{{ $plainPassword }}</span></td>
                </tr>
            </table>
        </div>

        <div class="btn-container">
            <a href="{{ $loginUrl }}" class="btn">Masuk ke Sistem MUSMA</a>
        </div>

        <div class="warning">
            <strong style="color: #ffffff;">Penting:</strong> Jangan bagikan informasi login ini kepada siapapun. Hak suara Anda pada MUSMA LM FMIPA bersifat pribadi.
        </div>

        <div class="steps">
            <p>Cara Login:</p>
            <table class="step-table">
                <tr>
                    <td class="step-num-td"><div class="step-num">1</div></td>
                    <td class="step-text-td">Buka link <a href="{{ $loginUrl }}" style="color: #EBCE67; text-decoration: none;">{{ $loginUrl }}</a></td>
                </tr>
            </table>
            <table class="step-table">
                <tr>
                    <td class="step-num-td"><div class="step-num">2</div></td>
                    <td class="step-text-td">Masukkan <strong style="color: #ffffff;">NIM</strong> dan <strong style="color: #ffffff;">password</strong> seperti yang tertera di atas.</td>
                </tr>
            </table>
            <table class="step-table">
                <tr>
                    <td class="step-num-td"><div class="step-num">3</div></td>
                    <td class="step-text-td">Klik "Masuk" dan ikuti jalannya kegiatan.</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="footer">
        Email ini dikirim secara otomatis oleh panitia MUSMA LM FMIPA {{ $tahun }}.<br>
        Jika Anda mengalami kendala login, silakan hubungi panitia penyelenggara.<br><br>
        <em>Musyawarah Mahasiswa FMIPA Universitas Udayana</em>
    </div>
</div>
</body>
</html>
