# Template Laporan Engagement

## Lokasi Template
Template harus disimpan di: `template_word/template_engagement.docx`

## Format Template

Template Word harus memiliki struktur berikut:

### 1. Judul Dokumen
- Placeholder: `${judul}`
- Format: Centered, Bold, Font Size 16

### 2. Informasi Tanggal
- Placeholder: `${tanggal}`
- Format: Bold, di bawah judul

### 3. Informasi Jumlah Akun (Opsional)
- Placeholder: `${jumlah_akun}`
- Format: Bold, di bawah tanggal

### 4. Tabel dengan Header
Tabel harus memiliki header dengan background kuning (FFFF00 atau FFFACD):
- Kolom 1: **No.** (Width: ~800)
- Kolom 2: **Tautan Konten** (Width: ~2500)
- Kolom 3: **Nama Akun** (Width: ~2000)
- Kolom 4: **Narasi** (Width: ~3500)
- Kolom 5: **Eviden** (Width: ~2500)

**PENTING untuk Kolom Eviden:**
- Kolom Eviden akan menampilkan gambar evidence dengan ukuran persegi (square) yang rapi
- Ukuran gambar: **144 points x 144 points** (~2 inch x 2 inch) - PERSEGI
- Gambar dibuat persegi agar rapi dan tidak keluar dari ukuran kolom tabel
- Pastikan lebar kolom Eviden minimal **160 points** (~2.2 inch) untuk menampilkan gambar dengan baik
- Tinggi baris tabel minimal **160 points** (~2.2 inch) untuk menampung gambar persegi dengan baik
- Gambar akan dipaksa menjadi persegi agar rapi dan pas dalam kolom

### 5. Baris Data (Row Template)
Baris pertama data harus menggunakan placeholder berikut:
- `${no}` atau `${no#1}` - Nomor urut
- `${tautan_konten}` atau `${tautan_konten#1}` - Link konten
- `${nama_akun}` atau `${nama_akun#1}` - Nama akun
- `${narasi}` atau `${narasi#1}` - Narasi/komentar
- `${eviden}` atau `${eviden#1}` - Gambar evidence (untuk gambar, gunakan placeholder khusus)

## Cara Membuat Template

1. Buka Microsoft Word
2. Buat dokumen baru dengan struktur di atas
3. Untuk placeholder, ketik langsung seperti: `${judul}`, `${tanggal}`, dll
4. Untuk tabel:
   - Buat tabel dengan 5 kolom
   - Isi header dengan background kuning
   - Di baris pertama data, masukkan placeholder: `${no}`, `${tautan_konten}`, `${nama_akun}`, `${narasi}`, `${eviden}`
   - Untuk kolom Eviden yang akan berisi gambar, pastikan placeholder `${eviden}` ada di cell tersebut
5. Simpan sebagai: `template_engagement.docx` di folder `template_word/`

## Catatan Penting

- Placeholder harus menggunakan format `${nama_placeholder}` atau `${nama_placeholder#1}` untuk row pertama
- Sistem akan otomatis clone baris sesuai jumlah data
- Untuk gambar evidence, placeholder `${eviden#1}` akan diganti dengan gambar jika tersedia
- **Ukuran Gambar Evidence**: Semua gambar evidence akan ditampilkan dengan ukuran persegi (square) yang rapi
  - Ukuran utama: **144 points x 144 points** (~2 inch x 2 inch) - PERSEGI
  - Gambar dibuat persegi agar rapi dan tidak keluar dari ukuran kolom tabel
  - Gambar akan dipaksa menjadi persegi (ratio => false) agar rapi dan pas dalam kolom
- Jika tidak ada gambar, akan diganti dengan teks "-"
- **Rekomendasi Template**: 
  - Lebar kolom Eviden minimal **160 points** (~2.2 inch) untuk menampung gambar persegi dengan baik
  - Tinggi baris tabel minimal **160 points** (~2.2 inch) untuk menampung gambar persegi dengan baik
  - Pastikan kolom Eviden cukup lebar dan tinggi baris cukup tinggi agar gambar persegi tidak terpotong atau keluar dari kolom

## Contoh Template Structure

```
[Centered, Bold, Size 16]
${judul}

[Bold]
Tanggal: ${tanggal}
Jumlah Akun: ${jumlah_akun}

[Table]
| No. | Tautan Konten | Nama Akun | Narasi | Eviden |
|-----|---------------|-----------|--------|--------|
| ${no} | ${tautan_konten} | ${nama_akun} | ${narasi} | ${eviden} |
```

## Troubleshooting

Jika template tidak ditemukan:
- Pastikan file `template_engagement.docx` ada di folder `template_word/`
- Pastikan nama file tepat: `template_engagement.docx` (case sensitive)
- Pastikan file tidak corrupt dan bisa dibuka di Word

