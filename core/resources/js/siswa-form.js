document.addEventListener('DOMContentLoaded', () => {
    const siswaContainer = document.querySelector('#siswa-container');
    const addSiswaBtn = document.querySelector('#add-siswa');

    // Jika tidak ada tombol tambah siswa, hentikan script
    if (!addSiswaBtn || !siswaContainer) return;

    let siswaIndex = 1;

    addSiswaBtn.addEventListener('click', () => {
        const template = document.querySelector('.siswa-form').cloneNode(true);
        template.querySelectorAll('[name]').forEach(input => {
            input.name = input.name.replace('INDEX', siswaIndex);
            input.value = '';
        });

        // tampilkan tombol hapus untuk block siswa tambahan
        template.querySelector('.remove-siswa').classList.remove('hidden');
        template.querySelector('.remove-siswa').addEventListener('click', () => {
            template.remove();
        });

        siswaContainer.appendChild(template);
        siswaIndex++;
    });
});
