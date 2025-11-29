<div id="docModal"
    class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-11/12 md:w-3/4 lg:w-1/2 p-4 relative flex flex-col">
        <h2 class="text-2xl text-center font-bold m-2">Dokumen Siswa</h2>
        <button
            class="absolute top-2 right-2 bg-red-700 rounded py-1 px-2 text-white hover:bg-black hover:text-white transition"
            onclick="closeDocModal()">
            ✖
        </button>

        <div id="docContent" class="flex justify-center items-center w-full h-[70vh] overflow-auto">
            <!-- Konten akan diinsert oleh JS -->
        </div>

    </div>
</div>

<script>
    function openDocModal(src) {
        const container = document.getElementById('docContent');
        container.innerHTML = ""; // reset

        if (src.toLowerCase().endsWith('.pdf')) {
            container.innerHTML = `<iframe src="${src}" class="w-full h-full rounded"></iframe>`;
        } else {
            container.innerHTML = `<img src="${src}" class="max-h-full max-w-full object-contain rounded border">`;
        }

        document.getElementById('docModal').classList.remove('hidden');
    }

    function closeDocModal() {
        document.getElementById('docModal').classList.add('hidden');
        document.getElementById('docContent').innerHTML = "";
    }
</script>
