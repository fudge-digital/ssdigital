<div class="siswa-form bg-white rounded-xl p-4 border mb-6">
    <div class="flex justify-between items-center mb-3">
        <button type="button" class="remove-siswa text-red-500 text-sm hover:underline hidden">Hapus</button>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="text-sm font-medium">Nama Lengkap</label>
            <input type="text" name="siswa[INDEX][nama_lengkap]" class="w-full border-gray-300 rounded-lg" required>
        </div>
        <div>
            <label class="text-sm font-medium">Nama Panggilan</label>
            <input type="text" name="siswa[INDEX][nama_panggilan]" class="w-full border-gray-300 rounded-lg" required>
        </div>

        <div>
            <label class="text-sm font-medium">Jenis Kelamin</label>
            <select name="siswa[INDEX][jenis_kelamin]" class="w-full border-gray-300 rounded-lg" required>
                <option value="">-- Pilih --</option>
                <option value="Laki-laki">Laki-laki</option>
                <option value="Perempuan">Perempuan</option>
            </select>
        </div>
        <div>
            <label class="text-sm font-medium">Tempat Lahir</label>
            <input type="text" name="siswa[INDEX][tempat_lahir]" class="w-full border-gray-300 rounded-lg" required>
        </div>

        <div>
            <label class="text-sm font-medium">Tanggal Lahir</label>
            <input type="date" name="siswa[INDEX][tanggal_lahir]" class="w-full border-gray-300 rounded-lg" required>
        </div>
        <div>
            <label class="text-sm font-medium">Asal Sekolah</label>
            <input type="text" name="siswa[INDEX][asal_sekolah]" class="w-full border-gray-300 rounded-lg" required>
        </div>

        <div>
            <label class="text-sm font-medium">Size Jersey</label>
            <select name="siswa[INDEX][size_jersey]" class="w-full border-gray-300 rounded-lg" required>
                <option value="">-- Pilih --</option>
                <option value="S">XS</option>
                <option value="S">S</option>
                <option value="M">M</option>
                <option value="L">L</option>
                <option value="XL">XL</option>
                <option value="2XL">XL</option>
                <option value="3XL">XL</option>
            </select>
        </div>
    </div>
</div>
