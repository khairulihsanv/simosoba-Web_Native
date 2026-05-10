# git_cleanup.ps1
Write-Host "--- Membersihkan Konfigurasi Git Global ---" -ForegroundColor Cyan

git config --global --unset http.sslVersion
git config --global http.sslBackend openssl
git config --global http.postBuffer 524288000
git config --global http.sslVerify true

Write-Host "Konfigurasi Git telah diperbarui untuk standar keamanan terbaru." -ForegroundColor Green
Write-Host "Langkah selanjutnya: Ikuti panduan SSH di git_fix_guide.md" -ForegroundColor Yellow
